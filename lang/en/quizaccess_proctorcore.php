<?php
// This file is part of Moodle - http://moodle.org/

/**
 * English strings for quizaccess_proctorcore.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ProctorCore';
$string['privacy:metadata'] = 'The ProctorCore Quiz access rule stores Quiz configuration in local_proctorcore. Session and tenant data are managed by local_proctorcore.';
$string['proctorcore:bypass'] = 'Bypass ProctorCore Quiz access controls';
$string['settingsheader'] = 'ProctorCore';
$string['enabled'] = 'Enable ProctorCore for this Quiz';
$string['enabled_help'] = 'Connects this Quiz to local_proctorcore and Server B. Students complete the automatic preflight check before Moodle creates the attempt. The attempt page then starts Section 1.1 camera/microphone capture and Section 5.3 recovery heartbeats.';
$string['requirehttps'] = 'Require HTTPS';
$string['requirehttps_help'] = 'Block student access when the site is not using HTTPS. Disable only on a controlled test server.';
$string['requirecamera'] = 'Require camera check';
$string['requirecamera_help'] = 'Requires a working camera during the automatic pre-exam check.';
$string['requiremicrophone'] = 'Require microphone check';
$string['requiremicrophone_help'] = 'Requires a working microphone during the automatic pre-exam check.';
$string['requiresnapshot'] = 'Require identity snapshot';
$string['requiresnapshot_help'] = 'Requires the browser to capture a camera frame during the pre-exam check. This confirms capture readiness; biometric identity comparison remains a separate feature.';
$string['recoveryheading'] = 'Section 5.3 — connection recovery';
$string['allowresume'] = 'Allow return to the same attempt after connection loss';
$string['allowresume_help'] = 'The learner can reopen the same Moodle Quiz attempt. Existing answers and the original Quiz timer are preserved.';
$string['resumewindowsecs'] = 'Reconnect window (seconds)';
$string['resumewindowsecs_help'] = 'The specification value is 600 seconds (10 minutes). Accepted range: 60 to 3600 seconds.';
$string['timerheading'] = 'Timer and warnings';
$string['timerenabled'] = 'Enable Moodle Quiz timer';
$string['timerenabled_help'] = 'Uses Moodle Quiz native time limit and automatic submission, so reconnecting does not reset the timer.';
$string['durationminutes'] = 'Exam duration (minutes)';
$string['durationminutes_help'] = 'The default SPC duration is 120 minutes.';
$string['warningsenabled'] = 'Enable timer warnings';
$string['warningsenabled_help'] = 'Store warning times for the candidate interface.';
$string['warningcount'] = 'Number of warnings';
$string['warningtimes'] = 'Warning times (minutes remaining)';
$string['warningtimes_help'] = 'Comma-separated values, for example 15,5.';
$string['durationinvalid'] = 'Enter an exam duration from 1 to 10080 minutes.';
$string['warningcountinvalid'] = 'Choose between 1 and 10 warnings.';
$string['warningtimescountmismatch'] = 'Enter exactly {$a} different warning times.';
$string['warningtimeinvalid'] = 'Every warning time must be greater than 0 and less than the {$a}-minute exam duration.';
$string['proctoringenabled'] = 'ProctorCore is enabled for this Quiz.';
$string['timerdescription'] = 'Exam timer: {$a} minutes.';
$string['warningsdescription'] = 'Warnings at {$a} minutes remaining.';
$string['recoverydescription'] = 'A disconnected attempt can resume for {$a} using the same answers and original timer.';
$string['recoverydisabled'] = 'Connection recovery is disabled for this Quiz.';
$string['httpsrequired'] = 'This proctored Quiz requires HTTPS. Ask the administrator to open the site through a secure HTTPS address.';
$string['accessblocked'] = 'The proctored attempt cannot continue: {$a}';
$string['accessblockedgeneric'] = 'The proctored attempt cannot continue because ProctorCore is unavailable. Contact the administrator.';
$string['statusconnected'] = 'Proctoring connection active';
$string['statusreconnecting'] = 'Internet restored. Checking the ProctorCore session…';
$string['statuslost'] = 'Connection lost. Your Moodle answers remain in the same attempt; reconnect within the allowed window.';
$string['statusinterrupted'] = 'The ProctorCore session is interrupted. Reconnect to continue the same Quiz attempt.';
$string['reconnectbutton'] = 'Reconnect and continue';
$string['error:invalidresumewindow'] = 'The reconnect window must be between 60 and 3600 seconds.';
$string['error:missinglocalplugin'] = 'local_proctorcore or its Section 4.1 API is unavailable.';
$string['error:missingrecoveryapi'] = 'local_proctorcore Section 1.1 capture or Section 5.3 recovery API is unavailable.';
$string['error:missingconfigtable'] = 'The local_proctorcore_quizcfg table is missing.';
$string['error:missingcmid'] = 'The Quiz course-module ID could not be resolved.';
$string['error:previewnotproctored'] = 'Teacher preview attempts are not proctored.';
$string['error:attemptnotinprogress'] = 'The original Quiz attempt is no longer in progress.';
$string['error:sessionowner'] = 'The ProctorCore session belongs to another user.';
$string['error:precheckpending'] = 'The required pre-exam checks have not been completed.';
$string['error:sessionclosed'] = 'The ProctorCore session is closed ({$a}).';
$string['error:invalidsessionstatus'] = 'The ProctorCore session has an unsupported status ({$a}).';
$string['error:activationbusy'] = 'The ProctorCore session is currently being activated. Try again.';
$string['error:missingserversession'] = 'The Server B session ID is missing. Complete Section 4.1 first.';
$string['previewbutton'] = 'Proctoring preview';
$string['preflightdescription'] = 'Students must pass the automatic ProctorCore browser and equipment check before Moodle creates the Quiz attempt.';

$string['precheckformheader'] = 'Proctoring checks';
$string['requireidentity'] = 'Require automatic face identity verification';
$string['requireidentity_help'] = 'Before Moodle creates the attempt, compare a live centre/left/right camera challenge with the learner’s Moodle profile photo. A failed match blocks entry.';
$string['error:identitypending'] = 'The required identity verification has not passed.';
