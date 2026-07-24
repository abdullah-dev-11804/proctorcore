<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Privacy provider for quizaccess_proctorcore.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_proctorcore\privacy;

defined('MOODLE_INTERNAL') || die();

final class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
