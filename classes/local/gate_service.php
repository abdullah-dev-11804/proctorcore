<?php
// This file is part of Moodle - http://moodle.org/

namespace quizaccess_proctorcore\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Thin integration between Moodle Quiz and local_proctorcore.
 *
 * It preserves the original quiz attempt and never changes timestart.
 *
 * @package quizaccess_proctorcore
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class gate_service {
    /**
     * Is ProctorCore enabled for the effective tenant?
     *
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @return bool
     */
    public function is_enabled(int $quizid, int $courseid, int $userid): bool {
        return !empty($this->get_effective_config($quizid, $courseid, $userid)->enabled);
    }

    /**
     * Returns effective settings.
     *
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @return \stdClass
     */
    public function get_effective_config(int $quizid, int $courseid, int $userid): \stdClass {
        return (new settings_service())->get_effective_config($quizid, $courseid, $userid);
    }

    /**
     * Ensures an in-progress attempt has an active ProctorCore session.
     *
     * @param int $attemptid Quiz attempt id.
     * @param int $userid User id.
     * @return \stdClass
     */
    public function ensure_active_session(int $attemptid, int $userid): \stdClass {
        global $DB;

        if (!class_exists('\local_proctorcore\local\session_repository')) {
            throw new \moodle_exception('error:missinglocalplugin', 'quizaccess_proctorcore');
        }

        $attempt = $DB->get_record('quiz_attempts', [
            'id' => $attemptid,
            'userid' => $userid,
        ], '*', MUST_EXIST);

        if (!empty($attempt->preview)) {
            throw new \moodle_exception('error:previewnotproctored', 'quizaccess_proctorcore');
        }
        if (!in_array((string) $attempt->state, ['inprogress', 'overdue'], true)) {
            throw new \moodle_exception('error:attemptnotinprogress', 'quizaccess_proctorcore');
        }

        $repository = new \local_proctorcore\local\session_repository();
        $session = $repository->get_by_attempt_and_user($attemptid, $userid);

        // The attempt may already have a partial local row from an earlier
        // failed Server B request. Re-run the passed preflight persistence for
        // missing/pending sessions, then repair the external binding when the
        // local precheck is already marked passed.
        if (!$session || $session->techcheckstatus !== 'passed') {
            $quiz = $DB->get_record('quiz', ['id' => (int) $attempt->quiz], 'id,course', MUST_EXIST);
            $config = $this->get_effective_config((int) $quiz->id, (int) $quiz->course, $userid);
            $session = (new \local_proctorcore\local\precheck_service())
                ->record_passed_attempt($attemptid, $userid, $config);
        }

        if (empty($session->server_sessionid)) {
            if (!function_exists('local_proctorcore_create_session_for_attempt')) {
                throw new \moodle_exception('error:missinglocalplugin', 'quizaccess_proctorcore');
            }
            $session = local_proctorcore_create_session_for_attempt($attemptid);
        }

        if ((int) $session->userid !== $userid) {
            throw new \moodle_exception('error:sessionowner', 'quizaccess_proctorcore');
        }
        if ($session->techcheckstatus !== 'passed') {
            throw new \moodle_exception('error:precheckpending', 'quizaccess_proctorcore');
        }
        if (!in_array((string) $session->identitystatus, ['passed', 'notrequired'], true)) {
            throw new \moodle_exception('error:identitypending', 'quizaccess_proctorcore');
        }

        if ($session->status === 'interrupted') {
            if (!function_exists('local_proctorcore_resume_attempt')) {
                throw new \moodle_exception('error:missingrecoveryapi', 'quizaccess_proctorcore');
            }
            local_proctorcore_resume_attempt($attemptid, $userid);
            return $repository->get_by_id((int) $session->id);
        }

        if ($session->status === 'created') {
            return $this->activate_created_session($session);
        }
        if ($session->status === 'active') {
            return $session;
        }
        if ($session->status === 'precheck') {
            throw new \moodle_exception('error:precheckpending', 'quizaccess_proctorcore');
        }
        if (in_array($session->status, ['completed', 'failed', 'abandoned', 'expired'], true)) {
            throw new \moodle_exception('error:sessionclosed', 'quizaccess_proctorcore', '', $session->status);
        }

        throw new \moodle_exception('error:invalidsessionstatus', 'quizaccess_proctorcore', '', $session->status);
    }

    /**
     * Persists a successful preflight result, creates the Server B session and starts it.
     *
     * @param int $attemptid Quiz attempt id created by Moodle.
     * @param int $userid User id.
     * @return \stdClass Active ProctorCore session.
     */
    public function complete_preflight_and_start(int $attemptid, int $userid): \stdClass {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', [
            'id' => $attemptid,
            'userid' => $userid,
        ], 'id,quiz,userid,preview,state', MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => (int) $attempt->quiz], 'id,course', MUST_EXIST);
        $config = $this->get_effective_config((int) $quiz->id, (int) $quiz->course, $userid);

        (new \local_proctorcore\local\precheck_service())
            ->record_passed_attempt($attemptid, $userid, $config);

        return $this->ensure_active_session($attemptid, $userid);
    }

    /**
     * Gets the attempt from request when it belongs to the current user/Quiz.
     *
     * @param int $quizid Quiz id.
     * @param int $userid User id.
     * @return \stdClass|null
     */
    public function get_request_attempt(int $quizid, int $userid): ?\stdClass {
        global $DB;
        $attemptid = optional_param('attempt', 0, PARAM_INT);
        if (!$attemptid) {
            return null;
        }
        return $DB->get_record('quiz_attempts', [
            'id' => $attemptid,
            'quiz' => $quizid,
            'userid' => $userid,
        ]) ?: null;
    }

    /**
     * Starts a newly created Server B session once.
     *
     * @param \stdClass $session Session.
     * @return \stdClass
     */
    private function activate_created_session(\stdClass $session): \stdClass {
        $factory = \core\lock\lock_config::get_lock_factory('quizaccess_proctorcore');
        $lock = $factory->get_lock('activate_session_' . (int) $session->id, 10);
        if (!$lock) {
            throw new \moodle_exception('error:activationbusy', 'quizaccess_proctorcore');
        }

        try {
            $repository = new \local_proctorcore\local\session_repository();
            $session = $repository->get_by_id((int) $session->id);
            if ($session->status === 'active') {
                return $session;
            }
            if ($session->status !== 'created') {
                throw new \moodle_exception('error:invalidsessionstatus', 'quizaccess_proctorcore', '', $session->status);
            }
            if (empty($session->server_sessionid)) {
                throw new \moodle_exception('error:missingserversession', 'quizaccess_proctorcore');
            }

            $client = new \local_proctorcore\local\server_client((int) $session->companyid);
            $client->start_session((string) $session->server_sessionid, [
                'moodleSessionId' => (int) $session->id,
                'companyId' => (int) $session->companyid,
                'attemptId' => (int) $session->attemptid,
                'userId' => (int) $session->userid,
                'startedAt' => gmdate('c'),
                'source' => 'quizaccess_proctorcore',
            ]);

            $repository->update_status((int) $session->id, 'active');
            return $repository->get_by_id((int) $session->id);
        } finally {
            $lock->release();
        }
    }
}
