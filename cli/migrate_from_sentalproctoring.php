<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Migrate legacy quizaccess_sentalproctoring settings.
 *
 * @package quizaccess_proctorcore
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'help' => false,
    'keep-legacy-enabled' => false,
], [
    'h' => 'help',
]);

if ($unrecognized || !empty($options['help'])) {
    echo "Migrate quizaccess_sentalproctoring settings to local_proctorcore_quizcfg.\n\n";
    echo "Options:\n";
    echo "  --keep-legacy-enabled  Do not disable old rule rows after migration.\n";
    echo "  -h, --help             Show this help.\n";
    exit($unrecognized ? 1 : 0);
}

$counts = (new \quizaccess_proctorcore\local\settings_service())
    ->migrate_all_legacy(empty($options['keep-legacy-enabled']));

echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
