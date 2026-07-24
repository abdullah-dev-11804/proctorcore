<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Post-install actions for quizaccess_proctorcore.
 *
 * @package quizaccess_proctorcore
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Migrate old quizaccess_sentalproctoring rows into local_proctorcore_quizcfg.
 *
 * This changes configuration data only; it does not create or alter tables.
 */
function xmldb_quizaccess_proctorcore_install(): void {
    if (!class_exists('\\quizaccess_proctorcore\\local\\settings_service')) {
        require_once(__DIR__ . '/../classes/local/settings_service.php');
    }
    try {
        $counts = (new \quizaccess_proctorcore\local\settings_service())
            ->migrate_all_legacy(true);
        if (!empty($counts['migrated'])) {
            mtrace('ProctorCore migrated ' . $counts['migrated']
                . ' legacy SENTAL Quiz configuration record(s).');
        }
    } catch (\Throwable $exception) {
        debugging('ProctorCore legacy migration during installation failed: '
            . $exception->getMessage(), DEBUG_DEVELOPER);
    }
}
