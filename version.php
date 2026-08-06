<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Version information for the ProctorCore Quiz access rule.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'quizaccess_proctorcore';
$plugin->version = 2026080700;
$plugin->requires = 2022041900; // Moodle 4.0+.
$plugin->dependencies = [
    'local_proctorcore' => 2026080700, // First-exam face enrollment APIs.
];
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '0.11.0 - First-exam face enrollment workflow';
