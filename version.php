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
$plugin->version = 2026072004;
$plugin->requires = 2022041900; // Moodle 4.0+.
$plugin->dependencies = [
    'local_proctorcore' => 2026072003, // Section 1.1 capture and Section 5.3 recovery APIs.
];
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '0.10.0 - Sections 1.2 identity and 1.3 behaviour monitoring';
