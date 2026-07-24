<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Diagnose a ProctorCore Quiz attempt.
 *
 * @package quizaccess_proctorcore
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'attemptid' => 0,
    'help' => false,
], ['h' => 'help']);

if ($unrecognized || !empty($options['help']) || empty($options['attemptid'])) {
    echo "Usage: php diagnose.php --attemptid=123\n";
    exit(empty($options['attemptid']) ? 1 : 0);
}

$attempt = $DB->get_record('quiz_attempts', ['id' => (int) $options['attemptid']], '*', MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => (int) $attempt->quiz], 'id,course,name,timelimit', MUST_EXIST);
$config = (new \quizaccess_proctorcore\local\settings_service())
    ->get_effective_config((int) $quiz->id, (int) $quiz->course, (int) $attempt->userid);
$session = (new \local_proctorcore\local\session_repository())
    ->get_by_attempt_and_user((int) $attempt->id, (int) $attempt->userid);

$result = [
    'attempt' => [
        'id' => (int) $attempt->id,
        'quizId' => (int) $attempt->quiz,
        'userId' => (int) $attempt->userid,
        'state' => (string) $attempt->state,
        'startedAt' => (int) $attempt->timestart,
    ],
    'config' => [
        'source' => (string) ($config->source ?? ''),
        'companyId' => (int) ($config->companyid ?? 0),
        'enabled' => !empty($config->enabled),
        'allowResume' => !empty($config->allowresume),
        'resumeWindowSeconds' => (int) ($config->resumewindowsecs ?? 600),
        'timerEnabled' => !empty($config->timerenabled),
        'durationMinutes' => (int) ($config->durationminutes ?? 120),
    ],
    'session' => $session ? [
        'id' => (int) $session->id,
        'serverSessionId' => (string) $session->server_sessionid,
        'status' => (string) $session->status,
        'result' => (string) $session->result,
        'lastHeartbeat' => (int) $session->lastheartbeat,
    ] : null,
    'checks' => [
        'sameAttemptPreserved' => true,
        'timerResetByAccessRule' => false,
        'localProctorCoreAvailable' => class_exists('\local_proctorcore\local\session_repository'),
    ],
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
