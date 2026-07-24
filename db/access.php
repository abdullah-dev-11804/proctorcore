<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Capabilities for quizaccess_proctorcore.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'quizaccess/proctorcore:bypass' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
