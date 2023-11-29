<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * English strings for cmi5launch
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'cmi5 Launch Link';
$string['modulenameplural'] = 'cmi5 Launch Links';
$string['modulename_help'] = 'A plug in for Moodle that allows the launch of cmi5 (xAPI) content which is then tracked to a separate LRS.';

// Start Default LRS Admin Settings.
$string['cmi5launchlrsfieldset'] = 'Default values for cmi5 Launch Link activity settings';
$string['cmi5launchlrsfieldset_help'] = 'These are site-wide, default values used when creating a new activity. Each activity has the ability to override and provide alternative values.';

$string['cmi5launchlrsendpoint'] = 'Endpoint';
$string['cmi5launchlrsendpoint_help'] = 'The LRS endpoint (e.g. http://lrs.example.com/endpoint/). Must include trailing forward slash.';
$string['cmi5launchlrsendpoint_default'] = '';

$string['cmi5launchlrslogin'] = 'LRS: Basic Username';
$string['cmi5launchlrslogin_help'] = 'Your LRS login username.';
$string['cmi5launchlrslogin_default'] = '';

$string['cmi5launchlrspass'] = 'LRS: Basic Password';
$string['cmi5launchlrspass_help'] = 'Your LRS password (secret).';
$string['cmi5launchlrspass_default'] = '';

$string['cmi5launchlrsduration'] = 'Duration';
$string['cmi5launchlrsduration_help'] = 'Used with \'LRS integrated basic authentication\'. Requests the LRS to keep credentials valid for this number of minutes.';
$string['cmi5launchlrsduration_default'] = '9000';

$string['cmi5launchlrsauthentication'] = 'LRS integration';
$string['cmi5launchlrsauthentication_help'] = 'Use additional integration features to create new authentication credentials for each launch for supported LRSs.';
$string['cmi5launchlrsauthentication_watershedhelp'] = 'Note: for Watershed integration, the Activity Provider does not require API access enabled.';
$string['cmi5launchlrsauthentication_watershedhelp_label'] = 'Watershed integration';
$string['cmi5launchlrsauthentication_option_0'] = 'None';
$string['cmi5launchlrsauthentication_option_1'] = 'Watershed';
$string['cmi5launchlrsauthentication_option_2'] = 'Learning Locker 1';

$string['cmi5launchuseactoremail'] = 'Identify by email';
$string['cmi5launchuseactoremail_help'] = 'If selected, learners will be identified by their email address if they have one recorded in Moodle.';

$string['cmi5launchcustomacchp'] = 'Custom account homePage';
$string['cmi5launchcustomacchp_help'] = 'If entered, Moodle will use this homePage in conjunction with the ID number user profile field to identify the learner.
If the ID number is not entered for a learner, they will instead be identified by email or Moodle ID number.
Note: If a learner\'s id changes, they will lose access to registrations associated with former ids and completion data may be reset. Reports in your LRS may also be affected.';
$string['cmi5launchcustomacchp_default'] = 'https://moodle.com';

// Cmi5 grades admin.
// Start Default LRS Admin Settings.
$string['cmi5launchgradesettings'] = 'Default values for cmi5 Launch Link activity settings';
$string['cmi5launchgradesettings_help'] = 'These are site-wide, default values used when creating a new activity. Each activity has the ability to override and provide alternative values.';

$string['othersettings'] = 'Additional settings';

// Cmi5 player root location.
$string['cmi5launchplayerurl'] = 'cmi5 Player URL';
$string['cmi5launchplayerurl_help'] = 'The url to communicate with CMI5 player, can include port number(e.g. http://player.example.com or http://localhost:63398). Must NOT include a trailing forward slash.';
$string['cmi5launchplayerurl_default'] = '';

// Cmi5 player credentials.
$string['cmi5launchtenantname'] = 'cmi5 Player: Basic Username';
$string['cmi5launchtenantname_help'] = 'The cmi5 tenant username.';
$string['cmi5launchtenantname_default'] = '';

$string['cmi5launchtenantpass'] = 'cmi5 Player: Basic Password';
$string['cmi5launchtenantpass_help'] = 'The cmi5 tenant password (secret).';
$string['cmi5launchtenantpass_default'] = '';

$string['cmi5launchtenanttoken'] = 'cmi5 Player: Bearer Token';
$string['cmi5launchtenanttoken_help'] = 'The cmi5 tenant bearer token (should be a long string).';
$string['cmi5launchtenanttoken_default'] = '';

// Grading info - MB.
// Headers.
$string['defaultgradesettings'] = 'Default values for CMI5 Launch Link activity grades';
$string['defaultothersettings'] = 'Default values for CMI5 Launch Link activity attempts and completion';

// Other.
$string['maximumgradedesc'] = 'The maximum grade for a CMI5 Launch Link activity';

$string['maximumattempts'] = 'Maxium Attempt Amount';
$string['whatmaxdesc'] = 'The maximum amount of allowed attempts';

$string['maximumattempts'] = 'Maxium Attempt Amount';
$string['whatmaxdesc'] = 'The maximum amount of allowed attempts';

$string['nolimit'] = 'No limit';
$string['attempt1'] = '1 attempt';
$string['attemptsx'] = '{$a} attempts';

$string['whatgrade'] = 'Attempts grading';
$string['whatgradedesc'] = 'If multiple attempts are allowed, this setting specifies whether the highest, average (mean), first or last completed attempt is recorded in the gradebook. The last completed attempt option does not include attempts with a \'failed\' status.';
$string['HIGHEST_ATTEMPT_CMI5'] = 'Highest attempt';
$string['AVERAGE_ATTEMPT_CMI5'] = 'Average of attempts';
$string['FIRST_ATTEMPT_CMI5'] = 'First attempt';
$string['last_attempt_cmi5'] = 'Last attempt';

$string['lastattempt'] = 'Last completed attempt';
$string['last_attempt_cmi5_lock'] = 'Lock after final attempt';
$string['lastattemptlock_help'] = 'If enabled, a student is prevented from launching the CMI5 player after using up all their allocated attempts.';
$string['last_attempt_cmi5_lockdesc'] = 'If enabled, a student is prevented from launching the CMI5 player after using up all their allocated attempts.';

/*
MB - Not sure if we need ALL of these
No - If a previous attempt is completed, passed or failed, the student will be provided with the option to enter in review mode or start a new attempt.
When previous attempt completed, passed or failed - This relies on the SCORM package setting the status of \'completed\', \'passed\' or \'failed\'.
Always - Each re-entry to the SCORM activity will generate a new attempt and the student will not be returned to the same point they reached in their previous attempt.';

$string['forceattemptalways'] = 'Always';
$string['forceattemptoncomplete'] = 'When previous attempt completed, passed or failed';
$string['forcejavascript'] = 'Force users to enable JavaScript';
$string['forcejavascript_desc'] = 'If enabled (recommended) this prevents access to SCORM objects when JavaScript is not supported/enabled in a users browser. If disabled the user may view the SCORM but API communication will fail and no grade information will be saved.';
$string['forcejavascriptmessage'] = 'JavaScript is required to view this object, please enable JavaScript in your browser and try again.';
$string['found'] = 'Manifest found';
$string['frameheight'] = 'The height of the stage frame or window.';
$string['framewidth'] = 'The width of the stage frame or window.';
$string['fromleft'] = 'From left';
$string['fromtop'] = 'From top';
$string['fullscreen'] = 'Fill the whole screen';

Not sure if we want to implement these?
$string['masteryoverride'] = 'Mastery score overrides status';
$string['masteryoverride_help'] = 'If enabled and a mastery score is provided, when LMSFinish is called and a raw score has been set, status will be recalculated using the raw score and mastery score and any status provided by the SCORM (including "incomplete") will be overridden.';
$string['masteryoverridedesc'] = 'This preference sets the default for the mastery score override setting';
*/

$string['general'] = 'General data';
$string['GRADE_AVERAGE_CMI5'] = 'Average grade';
$string['gradeforattempt'] = 'Grade for attempt';
$string['GRADE_HIGHEST_CMI5'] = 'Highest grade';
$string['grademethod'] = 'Grading method';
// TODO - Is this accurate? Does it define for only ONE attempt?  
$string['grademethod_help'] = 'The grading method defines how the grade for a single attempt of the activity is determined.

There are 4 grading methods:

* Learning objects - The number of completed/passed learning objects
* Highest grade - The highest score obtained in all passed learning objects
* Average grade - The mean of all the scores
* Sum grade - The sum of all the scores';
$string['grademethoddesc'] = 'The grading method defines how the grade for a single attempt of the activity is determined.';
$string['gradereported'] = 'Grade reported';
$string['gradesettings'] = 'Grade settings';
$string['GRADE_CMI5_AUS'] = 'Learning objects';
$string['GRADE_SUM_CMI5'] = 'Sum grade';

// Start Activity Settings.
$string['cmi5launchname'] = 'Launch link name';
$string['cmi5launchname_help'] = 'The name of the launch link as it will appear to the user.';

$string['cmi5launchurl'] = 'Launch URL';
$string['cmi5launchurl_help'] = 'The base URL of the cmi5 activity you want to launch (e.g. http://example.com/content/index.html).';

$string['cmi5activityid'] = 'Activity ID';
$string['cmi5activityid_help'] = 'The identifying IRI for the primary activity being launched.';

$string['cmi5package'] = 'Zip package';
$string['cmi5package_help'] = 'If you have a packaged cmi5 course, you can upload it here. If you upload a package, the Launch URL and Activity ID fields above will be automatically populated when you save using data from the cmi5.xml file contained in the zip. You can edit these settings at any time, but should not change the Activity ID (either directly or by file upload) unless you understand the consequences.';

$string['cmi5packagetitle'] = 'CMI5 Package Upload';
$string['cmi5packagetext'] = 'Here you upload a zip package containing a cmi5.xml file. The launch url defined in the cmi5.xml may point to other files in the zip package, or to an external URL.';

$string['lrsheading'] = 'LRS settings';
$string['lrsdefaults'] = 'LRS Default Settings';
$string['lrssettingdescription'] = 'By default, this activity uses the global LRS settings found in Site administration > Plugins > Activity modules > cmi5 Launch Link. To change the settings for this specific activity, select Unlock Defaults.';

$string['behaviorheading'] = 'Module behavior';

$string['cmi5multipleregs'] = 'Allow multiple registrations.';
$string['cmi5multipleregs_help'] = 'If selected, allow the learner to start more than one registration for the activity. Learners can always return to any registrations they have started, even if this setting is unchecked.';

$string['apCreationFailed'] = 'Failed to create Watershed Activity Provider.';

// Zip errors.
$string['badmanifest'] = 'Some manifest errors: see errors log';
$string['badimsmanifestlocation'] = 'A cmi5.xml file was found but it was not in the root of your zip file, please re-package your course';
$string['badarchive'] = 'You must provide a valid zip file';
$string['nomanifest'] = 'Incorrect file package - missing cmi5.xml';

$string['cmi5launch'] = 'cmi5 Launch Link';
$string['pluginadministration'] = 'cmi5 Launch Link administration';
$string['pluginname'] = 'cmi5 Launch Link';

// Verb completion settings.
$string['completionverb'] = 'Verb';
$string['completionverbgroup'] = 'Track completion by verb';
$string['completionverbgroup_help'] = 'Moodle will look for statements where the actor is the current user, the object is the specified activity id and the verb is the one set here. If it finds a matching statement, the activity will be marked complete.';

// Expiry completion settings.
$string['completionexpiry'] = 'Expiry';
$string['completionexpirygroup'] = 'Completion Expires After (days)';
$string['completionexpirygroup_help'] = 'If checked, when looking for completion Moodle will only look at data stored in the LRS in the previous X days. It will unset completion for learners who had previously completed but whose completion has now expired.';

// AU View settings.
$string['cmi5launchviewfirstlaunched'] = 'First launched';
$string['cmi5launchviewlastlaunched'] = 'Last launched';
$string['cmi5launchviewlaunchlinkheader'] = 'Launch link';
$string['cmi5launchviewlaunchlink'] = 'launch';
$string['cmi5launchviewprogress'] = 'Progress';
$string['cmi5launchviewgradeheader'] = 'Grade';

// View settings.
$string['cmi5launchviewAUname'] = 'Name';
$string['cmi5launchviewstatus'] = 'AU Satisfied Status';
$string['cmi5launchviewregistrationheader'] = 'Sessions';
$string['cmi5launchviewgradeheader'] = 'Grade';
$string['cmi5launchviewlaunchlink'] = 'view';
$string['AUtableheader'] = 'Assignable Units';


$string['cmi5launch_completed'] = 'Experience complete!';
$string['cmi5launch_progress'] = 'Attempt in progress.';
$string['cmi5launch_attempt'] = 'Start New Session';
$string['cmi5launch_notavailable'] = 'The Learning Record Store is not available. Please contact a system administrator.

If you are the system administrator, go to Site admin / Development / Debugging and set Debug messages to DEVELOPER. Set it back to NONE or MINIMAL once the error details have been recorded.';
$string['cmi5launch_regidempty'] = 'Registration id not found. Please close this window.';

$string['idmissing'] = 'You must specify a course_module ID or an instance ID';

// Events.
$string['eventactivitylaunched'] = 'Activity launched';
$string['eventactivitycompleted'] = 'Activity completed';

$string['cmi5launch:addinstance'] = 'Add a new cmi5 (xAPI) activity to a course';
$string['cmi5launch:view'] = 'View cmi5 (xAPI) activity';
$string['cmi5launch:viewgrades'] = "Ability to view all of a course's grades. Often assigned to teachers.";

$string['expirecredentials'] = 'Expire credentials';
$string['checkcompletion'] = 'Check Completion';

// For reports.
$string['report'] = 'Report';
$string['attempt'] = 'Attempt';
$string['started'] = 'Started';
$string['last'] = 'Finished';
$string['score'] = 'Score';

$string['autitle'] = 'AU Title';
$string['attempt'] = 'Attempt';
$string['started'] = 'Started';
$string['last'] = 'Finished';
$string['score'] = 'Score';