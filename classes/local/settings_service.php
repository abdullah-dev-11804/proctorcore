<?php
// This file is part of Moodle - http://moodle.org/

namespace quizaccess_proctorcore\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Owns Quiz-level ProctorCore settings and legacy migration.
 *
 * New settings are stored in local_proctorcore_quizcfg. The old
 * quizaccess_sentalproctoring table is read only as a migration source.
 *
 * @package quizaccess_proctorcore
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class settings_service {
    private const TABLE = 'local_proctorcore_quizcfg';
    private const LEGACY_TABLE = 'quizaccess_sentalproctoring';
    private const JSON_KEY = 'quizaccess_proctorcore';

    /**
     * Returns effective settings for a user/company, with company 0 fallback.
     *
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @return \stdClass
     */
    public function get_effective_config(int $quizid, int $courseid, int $userid): \stdClass {
        global $DB;

        $defaults = $this->default_config($quizid, $courseid);
        $dbman = $DB->get_manager();

        if ($dbman->table_exists(new \xmldb_table(self::TABLE))) {
            $companyid = $this->resolve_company_id($userid, $courseid);
            $config = $DB->get_record(self::TABLE, [
                'companyid' => $companyid,
                'quizid' => $quizid,
            ]);

            if (!$config && $companyid !== 0) {
                $config = $DB->get_record(self::TABLE, [
                    'companyid' => 0,
                    'quizid' => $quizid,
                ]);
            }

            if ($config) {
                return $this->normalise_local_config($config);
            }
        }

        // Transitional fallback so existing SENTAL settings continue to appear
        // before the administrator runs the migration or saves the Quiz again.
        if ($dbman->table_exists(new \xmldb_table(self::LEGACY_TABLE))) {
            $legacy = $DB->get_record(self::LEGACY_TABLE, ['quizid' => $quizid]);
            if ($legacy) {
                return $this->normalise_legacy_config($legacy, $quizid, $courseid);
            }
        }

        return $defaults;
    }

    /**
     * Saves all access-rule settings into local_proctorcore_quizcfg.
     *
     * @param \stdClass $quiz Submitted Quiz record.
     * @param int $userid User making the change.
     * @return \stdClass Saved record.
     */
    public function save_from_quiz(\stdClass $quiz, int $userid): \stdClass {
        global $DB;

        if (!$DB->get_manager()->table_exists(new \xmldb_table(self::TABLE))) {
            throw new \moodle_exception('error:missingconfigtable', 'quizaccess_proctorcore');
        }

        $quizid = (int) $quiz->id;
        $courseid = (int) $quiz->course;
        $companyid = $this->resolve_company_id($userid, $courseid);
        $cmid = $this->resolve_cmid($quizid, $courseid, $quiz);
        $now = time();

        $enabled = empty($quiz->proctorcore_enabled) ? 0 : 1;
        $requirecamera = empty($quiz->proctorcore_requirecamera) ? 0 : 1;
        $requiremicrophone = empty($quiz->proctorcore_requiremicrophone) ? 0 : 1;
        $requiresnapshot = empty($quiz->proctorcore_requiresnapshot) ? 0 : 1;
        $requireidentity = empty($quiz->proctorcore_requireidentity) ? 0 : 1;
        $allowresume = empty($quiz->proctorcore_allowresume) ? 0 : 1;
        $window = min(3600, max(60, (int) ($quiz->proctorcore_resumewindowsecs ?? 600)));

        $existing = $DB->get_record(self::TABLE, [
            'companyid' => $companyid,
            'quizid' => $quizid,
        ]);

        $json = $this->decode_json($existing->settingsjson ?? null);
        $warningtimes = self::parse_warning_times((string) ($quiz->proctorcore_warningtimes ?? '15,5'));
        $json[self::JSON_KEY] = [
            'requirehttps' => empty($quiz->proctorcore_requirehttps) ? 0 : 1,
            'requirecamera' => $requirecamera,
            'requiremicrophone' => $requiremicrophone,
            'requiresnapshot' => $requiresnapshot,
            'requireidentity' => $requireidentity,
            'timerenabled' => empty($quiz->proctorcore_timerenabled) ? 0 : 1,
            'durationminutes' => max(1, min(10080,
                (int) ($quiz->proctorcore_durationminutes ?? 120))),
            'warningsenabled' => empty($quiz->proctorcore_warningsenabled) ? 0 : 1,
            'warningcount' => count($warningtimes),
            'warningtimes' => implode(',', $warningtimes),
            'section53' => [
                'allowresume' => $allowresume,
                'resumewindowsecs' => $window,
            ],
            'updatedat' => $now,
            'source' => 'quizaccess_proctorcore',
        ];

        if ($existing) {
            $existing->courseid = $courseid;
            $existing->cmid = $cmid;
            $existing->enabled = $enabled;
            $existing->requireidentity = $requireidentity;
            $existing->requiretechcheck = ($requirecamera || $requiremicrophone || $requireidentity) ? 1 : 0;
            $existing->allowresume = $allowresume;
            $existing->resumewindowsecs = $window;
            $existing->settingsjson = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $existing->timemodified = $now;
            $existing->usermodified = $userid ?: null;
            $DB->update_record(self::TABLE, $existing);
            return $existing;
        }

        $record = (object) [
            'companyid' => $companyid,
            'courseid' => $courseid,
            'cmid' => $cmid,
            'quizid' => $quizid,
            'enabled' => $enabled,
            'requireidentity' => $requireidentity,
            'requiretechcheck' => ($requirecamera || $requiremicrophone || $requireidentity) ? 1 : 0,
            'requirerulesack' => 1,
            'allowresume' => $allowresume,
            'resumewindowsecs' => $window,
            'ruleshtml' => null,
            'settingsjson' => json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'timecreated' => $now,
            'timemodified' => $now,
            'usermodified' => $userid ?: null,
        ];
        $record->id = $DB->insert_record(self::TABLE, $record);
        return $record;
    }

    /**
     * Deletes ProctorCore settings for every company when a Quiz is deleted.
     *
     * @param int $quizid Quiz id.
     * @return void
     */
    public function delete_quiz_settings(int $quizid): void {
        global $DB;
        if ($DB->get_manager()->table_exists(new \xmldb_table(self::TABLE))) {
            $DB->delete_records(self::TABLE, ['quizid' => $quizid]);
        }
    }

    /**
     * Migrates all legacy SENTAL access-rule settings to company 0.
     *
     * Legacy records did not contain an IOMAD company id, so company 0 is the
     * safe global fallback. Company-specific settings can be saved later from
     * each company's Quiz settings page.
     *
     * @param bool $disablelegacy Disable old rule rows after successful migration.
     * @return array Counts.
     */
    public function migrate_all_legacy(bool $disablelegacy = true): array {
        global $DB;

        $counts = ['found' => 0, 'migrated' => 0, 'existing' => 0, 'skipped' => 0, 'errors' => 0, 'disabled' => 0];
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists(new \xmldb_table(self::LEGACY_TABLE))
                || !$dbman->table_exists(new \xmldb_table(self::TABLE))) {
            return $counts;
        }

        foreach ($DB->get_records(self::LEGACY_TABLE) as $legacy) {
            $counts['found']++;
            try {
                $result = $this->migrate_legacy_record($legacy);
                $counts[$result]++;
                if ($disablelegacy && $result !== 'skipped' && !empty($legacy->enabled)) {
                    $DB->set_field(self::LEGACY_TABLE, 'enabled', 0, ['id' => (int) $legacy->id]);
                    $counts['disabled']++;
                }
            } catch (\Throwable $exception) {
                $counts['errors']++;
                debugging('ProctorCore legacy migration failed for Quiz ' . (int) $legacy->quizid
                    . ': ' . $exception->getMessage(), DEBUG_DEVELOPER);
            }
        }
        return $counts;
    }

    /**
     * Parse and normalise warning times.
     *
     * @param string $value Comma, semicolon, or whitespace separated minutes.
     * @return int[] Descending unique minutes.
     */
    public static function parse_warning_times(string $value): array {
        $parts = preg_split('/[\s,;]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        $times = [];
        foreach ($parts as $part) {
            if (preg_match('/^\d+$/', $part)) {
                $minutes = (int) $part;
                if ($minutes > 0) {
                    $times[$minutes] = $minutes;
                }
            }
        }
        rsort($times, SORT_NUMERIC);
        return array_values($times);
    }

    /**
     * Migrates one legacy row.
     *
     * @param \stdClass $legacy Legacy record.
     * @return string migrated or skipped.
     */
    private function migrate_legacy_record(\stdClass $legacy): string {
        global $DB;

        $quiz = $DB->get_record('quiz', ['id' => (int) $legacy->quizid], 'id,course', IGNORE_MISSING);
        if (!$quiz) {
            return 'skipped';
        }
        $cm = get_coursemodule_from_instance('quiz', (int) $quiz->id, (int) $quiz->course,
            false, IGNORE_MISSING);
        if (!$cm) {
            return 'skipped';
        }

        $existing = $DB->get_record(self::TABLE, [
            'companyid' => 0,
            'quizid' => (int) $quiz->id,
        ]);
        $json = $this->decode_json($existing->settingsjson ?? null);
        if ($existing && isset($json[self::JSON_KEY])) {
            // A newer ProctorCore configuration already exists. Do not replace it
            // with an older SENTAL row; only disable the legacy rule afterwards.
            return 'existing';
        }
        $json[self::JSON_KEY] = [
            'requirehttps' => (int) ($legacy->requirehttps ?? 1),
            'requirecamera' => (int) ($legacy->requirecamera ?? 1),
            'requiremicrophone' => (int) ($legacy->requiremicrophone ?? 1),
            'requiresnapshot' => (int) ($legacy->requiresnapshot ?? 1),
            'timerenabled' => (int) ($legacy->timerenabled ?? 1),
            'durationminutes' => (int) ($legacy->durationminutes ?? 120),
            'warningsenabled' => (int) ($legacy->warningsenabled ?? 1),
            'warningcount' => (int) ($legacy->warningcount ?? 2),
            'warningtimes' => (string) ($legacy->warningtimes ?? '15,5'),
            'section53' => ['allowresume' => 1, 'resumewindowsecs' => 600],
            'updatedat' => time(),
            'source' => 'quizaccess_sentalproctoring_migration',
        ];

        $record = (object) [
            'companyid' => 0,
            'courseid' => (int) $quiz->course,
            'cmid' => (int) $cm->id,
            'quizid' => (int) $quiz->id,
            'enabled' => (int) ($legacy->enabled ?? 0),
            'requireidentity' => (int) ($legacy->requiresnapshot ?? 1),
            'requiretechcheck' => (!empty($legacy->requirecamera) || !empty($legacy->requiremicrophone)) ? 1 : 0,
            'requirerulesack' => 1,
            'allowresume' => 1,
            'resumewindowsecs' => 600,
            'ruleshtml' => $existing->ruleshtml ?? null,
            'settingsjson' => json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'timecreated' => $existing->timecreated ?? time(),
            'timemodified' => time(),
            'usermodified' => null,
        ];

        if ($existing) {
            // Preserve any Section 4/5 core settings that are already stored in
            // local_proctorcore; only add the legacy timer/media settings JSON.
            $existing->courseid = (int) $quiz->course;
            $existing->cmid = (int) $cm->id;
            $existing->settingsjson = $record->settingsjson;
            $existing->timemodified = time();
            $DB->update_record(self::TABLE, $existing);
        } else {
            $DB->insert_record(self::TABLE, $record);
        }
        return 'migrated';
    }

    /**
     * Normalises a local_proctorcore_quizcfg record for access-rule use.
     *
     * @param \stdClass $config DB record.
     * @return \stdClass
     */
    private function normalise_local_config(\stdClass $config): \stdClass {
        $json = $this->decode_json($config->settingsjson ?? null);
        $extra = $json[self::JSON_KEY] ?? [];

        $config->requirehttps = (int) ($extra['requirehttps'] ?? 1);
        $config->requirecamera = (int) ($extra['requirecamera'] ?? (int) $config->requiretechcheck);
        $config->requiremicrophone = (int) ($extra['requiremicrophone'] ?? (int) $config->requiretechcheck);
        $config->requiresnapshot = (int) ($extra['requiresnapshot'] ?? 1);
        $config->requireidentity = (int) ($extra['requireidentity'] ?? (int) $config->requireidentity);
        $config->timerenabled = (int) ($extra['timerenabled'] ?? 1);
        $config->durationminutes = max(1, (int) ($extra['durationminutes'] ?? 120));
        $config->warningsenabled = (int) ($extra['warningsenabled'] ?? 1);
        $config->warningtimes = (string) ($extra['warningtimes'] ?? '15,5');
        $config->warningcount = (int) ($extra['warningcount']
            ?? count(self::parse_warning_times($config->warningtimes)));
        $config->source = 'local_proctorcore_quizcfg';
        return $config;
    }

    /**
     * Normalises an old access-rule record.
     *
     * @param \stdClass $legacy Legacy row.
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @return \stdClass
     */
    private function normalise_legacy_config(\stdClass $legacy, int $quizid, int $courseid): \stdClass {
        return (object) [
            'companyid' => 0,
            'courseid' => $courseid,
            'quizid' => $quizid,
            'enabled' => (int) ($legacy->enabled ?? 0),
            'requireidentity' => (int) ($legacy->requiresnapshot ?? 1),
            'requiretechcheck' => (!empty($legacy->requirecamera) || !empty($legacy->requiremicrophone)) ? 1 : 0,
            'requirerulesack' => 1,
            'allowresume' => 1,
            'resumewindowsecs' => 600,
            'requirehttps' => (int) ($legacy->requirehttps ?? 1),
            'requirecamera' => (int) ($legacy->requirecamera ?? 1),
            'requiremicrophone' => (int) ($legacy->requiremicrophone ?? 1),
            'requiresnapshot' => (int) ($legacy->requiresnapshot ?? 1),
            'timerenabled' => (int) ($legacy->timerenabled ?? 1),
            'durationminutes' => max(1, (int) ($legacy->durationminutes ?? 120)),
            'warningsenabled' => (int) ($legacy->warningsenabled ?? 1),
            'warningcount' => (int) ($legacy->warningcount ?? 2),
            'warningtimes' => (string) ($legacy->warningtimes ?? '15,5'),
            'source' => 'quizaccess_sentalproctoring',
        ];
    }

    /**
     * Default settings.
     *
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @return \stdClass
     */
    private function default_config(int $quizid, int $courseid): \stdClass {
        return (object) [
            'companyid' => 0,
            'courseid' => $courseid,
            'quizid' => $quizid,
            'enabled' => 0,
            'requireidentity' => 0,
            'requiretechcheck' => 1,
            'requirerulesack' => 1,
            'allowresume' => 1,
            'resumewindowsecs' => 600,
            'requirehttps' => 1,
            'requirecamera' => 1,
            'requiremicrophone' => 1,
            'requiresnapshot' => 1,
            'timerenabled' => 1,
            'durationminutes' => 120,
            'warningsenabled' => 1,
            'warningcount' => 2,
            'warningtimes' => '15,5',
            'source' => 'defaults',
        ];
    }

    /**
     * Resolves company id without failing normal Moodle installations.
     *
     * @param int $userid User id.
     * @param int $courseid Course id.
     * @return int
     */
    private function resolve_company_id(int $userid, int $courseid): int {
        if (!function_exists('local_proctorcore_get_user_companyid')) {
            return 0;
        }
        try {
            return max(0, (int) local_proctorcore_get_user_companyid($userid, $courseid));
        } catch (\Throwable $exception) {
            debugging('ProctorCore company resolution fallback: ' . $exception->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Resolves course module id.
     *
     * @param int $quizid Quiz id.
     * @param int $courseid Course id.
     * @param \stdClass $quiz Submitted Quiz object.
     * @return int
     */
    private function resolve_cmid(int $quizid, int $courseid, \stdClass $quiz): int {
        $cmid = !empty($quiz->coursemodule) ? (int) $quiz->coursemodule : 0;
        if (!$cmid) {
            $cm = get_coursemodule_from_instance('quiz', $quizid, $courseid, false, IGNORE_MISSING);
            $cmid = $cm ? (int) $cm->id : 0;
        }
        if (!$cmid) {
            throw new \moodle_exception('error:missingcmid', 'quizaccess_proctorcore');
        }
        return $cmid;
    }

    /**
     * Decode settings JSON safely.
     *
     * @param string|null $json JSON string.
     * @return array
     */
    private function decode_json(?string $json): array {
        if (!$json) {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
