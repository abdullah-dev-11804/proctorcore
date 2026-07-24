<?php
// This file is part of Moodle - http://moodle.org/

namespace quizaccess_proctorcore;

/**
 * Basic unit tests for settings helpers.
 *
 * @package quizaccess_proctorcore
 */
final class gate_service_test extends \advanced_testcase {
    public function test_warning_times_are_normalised(): void {
        $this->assertSame([15, 5],
            \quizaccess_proctorcore\local\settings_service::parse_warning_times('5, 15, 5'));
    }
}
