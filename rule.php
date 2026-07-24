<?php
// This file is part of Moodle - http://moodle.org/

/**
 * ProctorCore Quiz access rule with Section 1.1 capture and Section 5.3 recovery.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Load the local ProctorCore bridge functions before using the preflight callbacks.
// Moodle does not guarantee that a local plugin's lib.php has already been loaded
// when a quiz access rule builds the preflight form.
$localproctorcorelib = $CFG->dirroot . '/local/proctorcore/lib.php';
if (is_readable($localproctorcorelib)) {
    require_once($localproctorcorelib);
}

if (class_exists('\mod_quiz\local\access_rule_base')) {
    class_alias('\mod_quiz\local\access_rule_base', '\quizaccess_proctorcore_parent');
} else {
    require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
    class_alias('\quiz_access_rule_base', '\quizaccess_proctorcore_parent');
}

/**
 * ProctorCore access rule.
 */
class quizaccess_proctorcore extends quizaccess_proctorcore_parent {
    /**
     * Create rule only when enabled and not bypassed.
     *
     * @param mixed $quizobj Quiz settings object.
     * @param int $timenow Current time.
     * @param bool $canignoretimelimits Ignore-time-limits flag.
     * @return self|null
     */
    public static function make($quizobj, $timenow, $canignoretimelimits) {
        global $USER;

        if (!class_exists('\local_proctorcore\local\session_repository')) {
            return null;
        }
        $quiz = $quizobj->get_quiz();
        $service = new \quizaccess_proctorcore\local\gate_service();
        if (!$service->is_enabled((int) $quiz->id, (int) $quiz->course, (int) $USER->id)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    /**
     * Rule summary shown on Quiz view page.
     *
     * @return string
     */
    public function description() {
        global $OUTPUT, $USER;

        $config = (new \quizaccess_proctorcore\local\gate_service())
            ->get_effective_config((int) $this->quiz->id, (int) $this->quiz->course, (int) $USER->id);

        $parts = [
            get_string('proctoringenabled', 'quizaccess_proctorcore'),
            get_string('preflightdescription', 'quizaccess_proctorcore'),
        ];
        if (!empty($config->timerenabled)) {
            $parts[] = get_string('timerdescription', 'quizaccess_proctorcore', (int) $config->durationminutes);
            if (!empty($config->warningsenabled) && !empty($config->warningtimes)) {
                $parts[] = get_string('warningsdescription', 'quizaccess_proctorcore', $config->warningtimes);
            }
        }
        if (!empty($config->allowresume)) {
            $parts[] = get_string('recoverydescription', 'quizaccess_proctorcore',
                format_time((int) $config->resumewindowsecs));
        } else {
            $parts[] = get_string('recoverydisabled', 'quizaccess_proctorcore');
        }

        $context = $this->quizobj->get_context();
        $systemcontext = context_system::instance();
        $canviewreports = has_capability('mod/quiz:viewreports', $context)
            || has_capability('local/proctorcore:viewcompanyreports', $systemcontext)
            || has_capability('local/proctorcore:viewallreports', $systemcontext)
            || is_siteadmin();

        if (has_capability('moodle/course:manageactivities', $context)
                && function_exists('local_proctorcore_get_preview_url')) {
            $cm = get_coursemodule_from_instance('quiz', (int) $this->quiz->id,
                (int) $this->quiz->course, false, IGNORE_MISSING);
            if ($cm) {
                $parts[] = html_writer::link(
                    local_proctorcore_get_preview_url((int) $cm->id),
                    get_string('previewbutton', 'quizaccess_proctorcore'),
                    ['class' => 'btn btn-secondary mt-2']
                );
            }
        }

        // Keep the report action visible even when the active theme does not
        // expose local-plugin settings nodes in the Quiz More menu.
        if ($canviewreports && class_exists('\local_proctorcore\local\report_service')) {
            $parts[] = html_writer::link(
                new moodle_url('/local/proctorcore/reports.php', [
                    'courseid' => (int) $this->quiz->course,
                    'quizid' => (int) $this->quiz->id,
                ]),
                get_string('report:reports', 'local_proctorcore'),
                ['class' => 'btn btn-secondary mt-2 ml-2']
            );
        }

        return implode('<br>', $parts);
    }

    /**
     * Access enforcement for an existing attempt.
     *
     * @return false|string
     */
    public function prevent_access() {
        global $USER;

        if ($this->user_can_bypass()) {
            return false;
        }

        $service = new \quizaccess_proctorcore\local\gate_service();
        $config = $service->get_effective_config((int) $this->quiz->id,
            (int) $this->quiz->course, (int) $USER->id);

        if (!empty($config->requirehttps) && !is_https()) {
            return get_string('httpsrequired', 'quizaccess_proctorcore');
        }

        $attempt = $service->get_request_attempt((int) $this->quiz->id, (int) $USER->id);
        if (!$attempt || !empty($attempt->preview)
                || !in_array((string) $attempt->state, ['inprogress', 'overdue'], true)) {
            return false;
        }

        try {
            $service->ensure_active_session((int) $attempt->id, (int) $USER->id);
            return false;
        } catch (\moodle_exception $exception) {
            $message = trim(html_to_text((string) $exception->getMessage(), 0, false));
            return get_string('accessblocked', 'quizaccess_proctorcore', $message);
        } catch (\Throwable $exception) {
            debugging('ProctorCore Quiz access failure: ' . $exception->getMessage(), DEBUG_DEVELOPER);
            return get_string('accessblockedgeneric', 'quizaccess_proctorcore');
        }
    }

    /**
     * New attempt is not blocked here because Section 4.1 needs the attempt id.
     *
     * @param int $numprevattempts Previous attempts.
     * @param mixed $lastattempt Last attempt.
     * @return false
     */
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        return false;
    }

    /**
     * Requires Moodle's preflight form before a new proctored attempt.
     *
     * The attempt is only created after the browser/device checks pass.
     * Existing active attempts do not repeat the checks.
     *
     * @param int|null $attemptid Existing attempt id, if any.
     * @return bool
     */
    public function is_preflight_check_required($attemptid) {
        global $USER;

        if ($this->user_can_bypass()) {
            return false;
        }
        if (!$attemptid) {
            return true;
        }

        $repository = new \local_proctorcore\local\session_repository();
        $session = $repository->get_by_attempt_and_user((int) $attemptid, (int) $USER->id);
        return !$session
            || $session->techcheckstatus !== 'passed';
    }

    /**
     * Adds the automatic ProctorCore browser/device check to Moodle's preflight form.
     *
     * @param mixed $quizform Preflight form.
     * @param MoodleQuickForm $mform Wrapped form.
     * @param int|null $attemptid Existing attempt id.
     * @return void
     */
    public function add_preflight_check_form_fields($quizform, $mform, $attemptid) {
        global $PAGE, $USER;

        if (!function_exists('local_proctorcore_render_precheck_panel')) {
            throw new \moodle_exception('error:missinglocalplugin', 'quizaccess_proctorcore');
        }

        $config = $this->get_gate_service()->get_effective_config(
            (int) $this->quiz->id,
            (int) $this->quiz->course,
            (int) $USER->id
        );
        $serverhealthy = false;
        $companyid = 0;
        try {
            $companyid = function_exists('local_proctorcore_get_user_companyid')
                ? local_proctorcore_get_user_companyid((int) $USER->id, (int) $this->quiz->course)
                : 0;
            $health = (new \local_proctorcore\local\integration_service())->health($companyid);
            $serverhealthy = !empty($health['success']) || (($health['status'] ?? '') === 'healthy');
        } catch (\Throwable $exception) {
            debugging('ProctorCore preflight Server B health check failed: ' . $exception->getMessage(), DEBUG_DEVELOPER);
        }

        $localconfig = (new \local_proctorcore\local\company_config_repository())
            ->get_effective_config($companyid);

        $precheck = new \local_proctorcore\local\precheck_service();
        $token = $precheck->issue_token((int) $this->quiz->id, (int) $USER->id);
        $panelid = 'local-proctorcore-student-precheck';

        $mform->addElement('header', 'proctorcore_precheck_header',
            get_string('precheckformheader', 'quizaccess_proctorcore'));
        $mform->setExpanded('proctorcore_precheck_header');
        $mform->addElement('html', local_proctorcore_render_precheck_panel($panelid, false));

        $hidden = [
            'proctorcore_preflight_passed' => 0,
            'proctorcore_preflight_server' => 0,
            'proctorcore_preflight_browser' => 0,
            'proctorcore_preflight_secure' => 0,
            'proctorcore_preflight_network' => 0,
            'proctorcore_preflight_camera' => 0,
            'proctorcore_preflight_microphone' => 0,
            'proctorcore_preflight_lighting' => 0,
            'proctorcore_preflight_snapshot' => 0,
            'proctorcore_preflight_speedmbps' => 0,
            'proctorcore_preflight_latencyms' => 0,
            'proctorcore_preflight_brightness' => 0,
            'proctorcore_preflight_browsername' => '',
            'proctorcore_preflight_browserversion' => '',
            'proctorcore_preflight_token' => $token,
        ];
        foreach ($hidden as $name => $value) {
            $mform->addElement('hidden', $name, $value);
        }

        foreach ([
            'proctorcore_preflight_passed',
            'proctorcore_preflight_server',
            'proctorcore_preflight_browser',
            'proctorcore_preflight_secure',
            'proctorcore_preflight_network',
            'proctorcore_preflight_camera',
            'proctorcore_preflight_microphone',
            'proctorcore_preflight_lighting',
            'proctorcore_preflight_snapshot',
            'proctorcore_preflight_latencyms',
            'proctorcore_preflight_brightness',
        ] as $name) {
            $mform->setType($name, PARAM_INT);
        }
        $mform->setType('proctorcore_preflight_speedmbps', PARAM_FLOAT);
        $mform->setType('proctorcore_preflight_browsername', PARAM_TEXT);
        $mform->setType('proctorcore_preflight_browserversion', PARAM_TEXT);
        $mform->setType('proctorcore_preflight_token', PARAM_ALPHANUM);
        $PAGE->requires->css('/local/proctorcore/styles.css');
        $PAGE->requires->js_call_amd('local_proctorcore/precheck', 'init', [[
            'panelId' => $panelid,
            'previewMode' => false,
            'serverHealthy' => $serverhealthy,
            'requireHttps' => !empty($config->requirehttps),
            'requireCamera' => !empty($config->requirecamera),
            'requireMicrophone' => !empty($config->requiremicrophone),
            'requireSnapshot' => !empty($config->requiresnapshot),
            'minimumSpeedMbps' => (float) $localconfig->minimumspeedmbps,
            'minimumLighting' => (int) $localconfig->minimumlighting,
            'pingUrl' => (new moodle_url('/local/proctorcore/precheck_ping.php'))->out(false),
            'strings' => $this->precheck_strings(),
        ]]);
    }

    /**
     * Validates the client-reported preflight checks.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @param array $errors Existing errors.
     * @param int|null $attemptid Existing attempt id.
     * @return array
     */
    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        global $USER;

        $config = $this->get_gate_service()->get_effective_config(
            (int) $this->quiz->id,
            (int) $this->quiz->course,
            (int) $USER->id
        );
        $newerrors = (new \local_proctorcore\local\precheck_service())
            ->validate_and_remember(
                (int) $this->quiz->id,
                (int) $USER->id,
                (array) $data,
                $config
            );
        return array_merge($errors, $newerrors);
    }

    /**
     * Creates and starts the real ProctorCore session after Moodle creates the attempt.
     *
     * @param int|null $attemptid New or existing Quiz attempt id.
     * @return void
     */
    public function notify_preflight_check_passed($attemptid) {
        global $USER;

        if (!$attemptid) {
            return;
        }
        $this->get_gate_service()->complete_preflight_and_start(
            (int) $attemptid,
            (int) $USER->id
        );
    }

    /**
     * Activate the session, load heartbeat, and recovery UI.
     *
     * @param moodle_page $page Attempt page.
     * @return void
     */
    public function setup_attempt_page($page) {
        global $USER;

        if ($this->user_can_bypass()) {
            return;
        }

        $service = new \quizaccess_proctorcore\local\gate_service();
        $attempt = $service->get_request_attempt((int) $this->quiz->id, (int) $USER->id);
        if (!$attempt || !empty($attempt->preview)
                || !in_array((string) $attempt->state, ['inprogress', 'overdue'], true)) {
            return;
        }

        try {
            $session = $service->ensure_active_session((int) $attempt->id, (int) $USER->id);
            if (!function_exists('local_proctorcore_require_heartbeat')
                    || !function_exists('local_proctorcore_get_reconnect_url')) {
                throw new \moodle_exception('error:missingrecoveryapi', 'quizaccess_proctorcore');
            }

            local_proctorcore_require_heartbeat((int) $session->id);
            $page->requires->css('/mod/quiz/accessrule/proctorcore/styles.css');
            $page->requires->js_call_amd('quizaccess_proctorcore/recovery_ui', 'init', [[
                'sessionId' => (int) $session->id,
                'attemptId' => (int) $attempt->id,
                'reconnectUrl' => local_proctorcore_get_reconnect_url((int) $attempt->id)->out(false),
                'strings' => [
                    'connected' => get_string('statusconnected', 'quizaccess_proctorcore'),
                    'reconnecting' => get_string('statusreconnecting', 'quizaccess_proctorcore'),
                    'lost' => get_string('statuslost', 'quizaccess_proctorcore'),
                    'interrupted' => get_string('statusinterrupted', 'quizaccess_proctorcore'),
                    'reconnect' => get_string('reconnectbutton', 'quizaccess_proctorcore'),
                ],
            ]]);
        } catch (\Throwable $exception) {
            debugging('ProctorCore attempt setup failed: ' . $exception->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Whether the current user may bypass student enforcement.
     *
     * @return bool
     */
    private function user_can_bypass(): bool {
        return has_capability('quizaccess/proctorcore:bypass', $this->quizobj->get_context());
    }

    /**
     * Returns the Quiz-side gate service.
     *
     * @return \quizaccess_proctorcore\local\gate_service
     */
    private function get_gate_service(): \quizaccess_proctorcore\local\gate_service {
        return new \quizaccess_proctorcore\local\gate_service();
    }

    /**
     * Localised strings used by the shared precheck AMD module.
     *
     * @return array
     */
    private function precheck_strings(): array {
        return [
            'checking' => get_string('precheck:checking', 'local_proctorcore'),
            'serverHealthy' => get_string('precheck:serverhealthy', 'local_proctorcore'),
            'serverUnavailable' => get_string('precheck:serverunavailable', 'local_proctorcore'),
            'passed' => get_string('precheck:passed', 'local_proctorcore'),
            'failed' => get_string('precheck:failed', 'local_proctorcore'),
            'running' => get_string('precheck:running', 'local_proctorcore'),
            'allPassed' => get_string('precheck:allpassed', 'local_proctorcore'),
            'someFailed' => get_string('precheck:somefailed', 'local_proctorcore'),
            'notRequired' => get_string('precheck:notrequired', 'local_proctorcore'),
            'browserUnsupported' => get_string('precheck:browserunsupported', 'local_proctorcore'),
            'secureRequired' => get_string('precheck:securerequired', 'local_proctorcore'),
            'networkOffline' => get_string('precheck:networkoffline', 'local_proctorcore'),
            'networkFailed' => get_string('precheck:networkfailed', 'local_proctorcore'),
            'mediaUnsupported' => get_string('precheck:mediaunsupported', 'local_proctorcore'),
            'permissionDenied' => get_string('precheck:permissiondenied', 'local_proctorcore'),
            'tooDark' => get_string('precheck:toodark', 'local_proctorcore'),
            'cameraRequiredFirst' => get_string('precheck:camerarequiredfirst', 'local_proctorcore'),
            'snapshotCaptured' => get_string('precheck:snapshotcaptured', 'local_proctorcore'),
        ];
    }

    /**
     * Add all preserved SENTAL options plus Section 5.3 settings.
     *
     * @param mixed $quizform Quiz form.
     * @param MoodleQuickForm $mform Form.
     * @return void
     */
    public static function add_settings_form_fields($quizform, $mform) {
        $mform->addElement('header', 'proctorcoreheader',
            get_string('settingsheader', 'quizaccess_proctorcore'));

        $mform->addElement('advcheckbox', 'proctorcore_enabled',
            get_string('enabled', 'quizaccess_proctorcore'));
        $mform->addHelpButton('proctorcore_enabled', 'enabled', 'quizaccess_proctorcore');
        $mform->setDefault('proctorcore_enabled', 0);

        foreach (['requirehttps', 'requirecamera', 'requiremicrophone', 'requiresnapshot'] as $name) {
            $field = 'proctorcore_' . $name;
            $mform->addElement('advcheckbox', $field, get_string($name, 'quizaccess_proctorcore'));
            $mform->addHelpButton($field, $name, 'quizaccess_proctorcore');
            $mform->setDefault($field, 1);
            $mform->hideIf($field, 'proctorcore_enabled', 'notchecked');
        }

        $mform->addElement('html', \html_writer::tag('h4',
            get_string('recoveryheading', 'quizaccess_proctorcore'), ['class' => 'mt-4 mb-3']));
        $mform->addElement('advcheckbox', 'proctorcore_allowresume',
            get_string('allowresume', 'quizaccess_proctorcore'));
        $mform->addHelpButton('proctorcore_allowresume', 'allowresume', 'quizaccess_proctorcore');
        $mform->setDefault('proctorcore_allowresume', 1);
        $mform->hideIf('proctorcore_allowresume', 'proctorcore_enabled', 'notchecked');

        $mform->addElement('text', 'proctorcore_resumewindowsecs',
            get_string('resumewindowsecs', 'quizaccess_proctorcore'), ['size' => 8]);
        $mform->setType('proctorcore_resumewindowsecs', PARAM_INT);
        $mform->setDefault('proctorcore_resumewindowsecs', 600);
        $mform->addHelpButton('proctorcore_resumewindowsecs', 'resumewindowsecs', 'quizaccess_proctorcore');
        $mform->hideIf('proctorcore_resumewindowsecs', 'proctorcore_enabled', 'notchecked');
        $mform->hideIf('proctorcore_resumewindowsecs', 'proctorcore_allowresume', 'notchecked');

        $mform->addElement('html', \html_writer::tag('h4',
            get_string('timerheading', 'quizaccess_proctorcore'), ['class' => 'mt-4 mb-3']));
        $mform->addElement('advcheckbox', 'proctorcore_timerenabled',
            get_string('timerenabled', 'quizaccess_proctorcore'));
        $mform->addHelpButton('proctorcore_timerenabled', 'timerenabled', 'quizaccess_proctorcore');
        $mform->setDefault('proctorcore_timerenabled', 1);
        $mform->hideIf('proctorcore_timerenabled', 'proctorcore_enabled', 'notchecked');

        $mform->addElement('text', 'proctorcore_durationminutes',
            get_string('durationminutes', 'quizaccess_proctorcore'), ['size' => 8]);
        $mform->setType('proctorcore_durationminutes', PARAM_INT);
        $mform->setDefault('proctorcore_durationminutes', 120);
        $mform->addHelpButton('proctorcore_durationminutes', 'durationminutes', 'quizaccess_proctorcore');
        $mform->hideIf('proctorcore_durationminutes', 'proctorcore_enabled', 'notchecked');
        $mform->hideIf('proctorcore_durationminutes', 'proctorcore_timerenabled', 'notchecked');

        $mform->addElement('advcheckbox', 'proctorcore_warningsenabled',
            get_string('warningsenabled', 'quizaccess_proctorcore'));
        $mform->addHelpButton('proctorcore_warningsenabled', 'warningsenabled', 'quizaccess_proctorcore');
        $mform->setDefault('proctorcore_warningsenabled', 1);
        $mform->hideIf('proctorcore_warningsenabled', 'proctorcore_enabled', 'notchecked');
        $mform->hideIf('proctorcore_warningsenabled', 'proctorcore_timerenabled', 'notchecked');

        $options = [];
        for ($i = 1; $i <= 10; $i++) {
            $options[$i] = (string) $i;
        }
        $mform->addElement('select', 'proctorcore_warningcount',
            get_string('warningcount', 'quizaccess_proctorcore'), $options);
        $mform->setDefault('proctorcore_warningcount', 2);
        $mform->hideIf('proctorcore_warningcount', 'proctorcore_enabled', 'notchecked');
        $mform->hideIf('proctorcore_warningcount', 'proctorcore_timerenabled', 'notchecked');
        $mform->hideIf('proctorcore_warningcount', 'proctorcore_warningsenabled', 'notchecked');

        $mform->addElement('text', 'proctorcore_warningtimes',
            get_string('warningtimes', 'quizaccess_proctorcore'), ['size' => 30]);
        $mform->setType('proctorcore_warningtimes', PARAM_TEXT);
        $mform->setDefault('proctorcore_warningtimes', '15,5');
        $mform->addHelpButton('proctorcore_warningtimes', 'warningtimes', 'quizaccess_proctorcore');
        $mform->hideIf('proctorcore_warningtimes', 'proctorcore_enabled', 'notchecked');
        $mform->hideIf('proctorcore_warningtimes', 'proctorcore_timerenabled', 'notchecked');
        $mform->hideIf('proctorcore_warningtimes', 'proctorcore_warningsenabled', 'notchecked');
    }

    /**
     * Validate settings.
     *
     * @param array $errors Existing errors.
     * @param array $data Form data.
     * @param mixed $files Files.
     * @param mixed $quizform Quiz form.
     * @return array
     */
    public static function validate_settings_form_fields(array $errors, array $data, $files, $quizform) {
        if (empty($data['proctorcore_enabled'])) {
            return $errors;
        }

        if (!empty($data['proctorcore_allowresume'])) {
            $seconds = (int) ($data['proctorcore_resumewindowsecs'] ?? 0);
            if ($seconds < 60 || $seconds > 3600) {
                $errors['proctorcore_resumewindowsecs'] =
                    get_string('error:invalidresumewindow', 'quizaccess_proctorcore');
            }
        }

        if (!empty($data['proctorcore_timerenabled'])) {
            $duration = (int) ($data['proctorcore_durationminutes'] ?? 0);
            if ($duration < 1 || $duration > 10080) {
                $errors['proctorcore_durationminutes'] =
                    get_string('durationinvalid', 'quizaccess_proctorcore');
            }
            if (!empty($data['proctorcore_warningsenabled'])) {
                $count = (int) ($data['proctorcore_warningcount'] ?? 0);
                $times = \quizaccess_proctorcore\local\settings_service::parse_warning_times(
                    (string) ($data['proctorcore_warningtimes'] ?? ''));
                if ($count < 1 || $count > 10) {
                    $errors['proctorcore_warningcount'] =
                        get_string('warningcountinvalid', 'quizaccess_proctorcore');
                } else if (count($times) !== $count) {
                    $errors['proctorcore_warningtimes'] =
                        get_string('warningtimescountmismatch', 'quizaccess_proctorcore', $count);
                } else {
                    foreach ($times as $minutes) {
                        if ($minutes >= $duration) {
                            $errors['proctorcore_warningtimes'] =
                                get_string('warningtimeinvalid', 'quizaccess_proctorcore', $duration);
                            break;
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Load form values from local_proctorcore_quizcfg or legacy table.
     *
     * @param int $quizid Quiz id.
     * @return array
     */
    public static function get_extra_settings($quizid) {
        global $DB, $USER;

        $quiz = $DB->get_record('quiz', ['id' => $quizid], 'id,course');
        $config = $quiz
            ? (new \quizaccess_proctorcore\local\settings_service())
                ->get_effective_config((int) $quiz->id, (int) $quiz->course, (int) $USER->id)
            : (object) [];

        return [
            'proctorcore_enabled' => (int) ($config->enabled ?? 0),
            'proctorcore_requirehttps' => (int) ($config->requirehttps ?? 1),
            'proctorcore_requirecamera' => (int) ($config->requirecamera ?? 1),
            'proctorcore_requiremicrophone' => (int) ($config->requiremicrophone ?? 1),
            'proctorcore_requireidentity' => 0,
            'proctorcore_requiresnapshot' => (int) ($config->requiresnapshot ?? 1),
            'proctorcore_allowresume' => (int) ($config->allowresume ?? 1),
            'proctorcore_resumewindowsecs' => (int) ($config->resumewindowsecs ?? 600),
            'proctorcore_timerenabled' => (int) ($config->timerenabled ?? 1),
            'proctorcore_durationminutes' => (int) ($config->durationminutes ?? 120),
            'proctorcore_warningsenabled' => (int) ($config->warningsenabled ?? 1),
            'proctorcore_warningcount' => (int) ($config->warningcount ?? 2),
            'proctorcore_warningtimes' => (string) ($config->warningtimes ?? '15,5'),
        ];
    }

    /**
     * Save settings and preserve Moodle's native timer/answers behaviour.
     *
     * @param stdClass $quiz Quiz record.
     * @return void
     */
    public static function save_settings($quiz) {
        global $DB, $USER;

        (new \quizaccess_proctorcore\local\settings_service())
            ->save_from_quiz($quiz, (int) $USER->id);

        if (!empty($quiz->proctorcore_enabled)) {
            if (!empty($quiz->proctorcore_timerenabled)) {
                $duration = max(1, min(10080, (int) ($quiz->proctorcore_durationminutes ?? 120)));
                $DB->set_field('quiz', 'timelimit', $duration * MINSECS, ['id' => (int) $quiz->id]);
                $DB->set_field('quiz', 'overduehandling', 'autosubmit', ['id' => (int) $quiz->id]);
            } else {
                $DB->set_field('quiz', 'timelimit', 0, ['id' => (int) $quiz->id]);
            }
        }
    }

    /**
     * Delete local Quiz configuration.
     *
     * @param stdClass $quiz Quiz record.
     * @return void
     */
    public static function delete_settings($quiz) {
        (new \quizaccess_proctorcore\local\settings_service())
            ->delete_quiz_settings((int) $quiz->id);
    }
}
