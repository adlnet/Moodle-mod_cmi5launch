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
$string['cmi5launchcustomacchp_default'] = '';

//cmi5 player root location
$string['cmi5launchplayerurl'] = 'cmi5 Player URL';
$string['cmi5launchplayerurl_help'] = 'The url (e.g. http://player.example.com). Must NOT include a trailing forward slash.';
$string['cmi5launchplayerurl_default'] = '';

$string['cmi5launchplayerport'] = 'cmi5 Player Port';
$string['cmi5launchplayerport_help'] = 'Used with \'cmi5 Player URL\'. The port used to interact with the cmi5 Player API.';
$string['cmi5launchplayerport_default'] = '66398';

//cmi5 player credentials
$string['cmi5launchtenantname'] = 'cmi5 Player: Basic Username';
$string['cmi5launchtenantname_help'] = 'The cmi5 tenant username.';
$string['cmi5launchtenantname_default'] = '';

$string['cmi5launchtenantpass'] = 'cmi5 Player: Basic Password';
$string['cmi5launchtenantpass_help'] = 'The cmi5 tenant password (secret).';
$string['cmi5launchtenantpass_default'] = '';

$string['cmi5launchtenanttoken'] = 'cmi5 Player: Bearer Token';
$string['cmi5launchtenanttoken_help'] = 'The cmi5 tenant bearer token (should be a long string).';
$string['cmi5launchtenanttoken_default'] = '';

// Start Activity Settings.
$string['cmi5launchname'] = 'Launch link name';
$string['cmi5launchname_help'] = 'The name of the launch link as it will appear to the user.';

$string['cmi5launchurl'] = 'Launch URL';
$string['cmi5launchurl_help'] = 'The base URL of the cmi5 activity you want to launch (e.g. http://example.com/content/index.html).';

$string['cmi5activityid'] = 'Activity ID';
$string['cmi5activityid_help'] = 'The identifying IRI for the primary activity being launched.';

$string['cmi5package'] = 'Zip package';
$string['cmi5package_help'] = 'If you have a packaged cmi5 course, you can upload it here. If you upload a package, the Launch URL and Activity ID fields above will be automatically populated when you save using data from the cmi5.xml file contained in the zip. You can edit these settings at any time, but should not change the Activity ID (either directly or by file upload) unless you understand the consequences.';

$string['cmi5packagetitle'] = 'Launch settings';
$string['cmi5packagetext'] = 'You can populate the Launch URL and Activity ID settings directly, or by uploading a zip package containing a cmi5.xml file. The launch url defined in the cmi5.xml may point to other files in the zip package, or to an external URL. The Activity ID must always be a full URL (or other IRI).';

$string['lrsheading'] = 'LRS settings';
$string['lrsdefaults'] = 'LRS Default Settings';
$string['lrssettingdescription'] = 'By default, this activity uses the global LRS settings found in Site administration > Plugins > Activity modules > cmi5 Launch Link. To change the settings for this specific activity, select Unlock Defaults.';
$string['overridedefaults'] = 'Unlock Defaults';
$string['overridedefaults_help'] = 'Allows activity to have different LRS settings than the site-wide, default LRS settings.';

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

// View settings.
$string['cmi5launchviewfirstlaunched'] = 'First launched';
$string['cmi5launchviewlastlaunched'] = 'Last launched';
$string['cmi5launchviewlaunchlinkheader'] = 'Launch link';
$string['cmi5launchviewlaunchlink'] = 'launch';

$string['cmi5launch_completed'] = 'Experience complete!';
$string['cmi5launch_progress'] = 'Attempt in progress.';
$string['cmi5launch_attempt'] = 'Start New Registration';
$string['cmi5launch_notavailable'] = 'The Learning Record Store is not available. Please contact a system administrator.

If you are the system administrator, go to Site admin / Development / Debugging and set Debug messages to DEVELOPER. Set it back to NONE or MINIMAL once the error details have been recorded.';
$string['cmi5launch_regidempty'] = 'Registration id not found. Please close this window.';

$string['idmissing'] = 'You must specify a course_module ID or an instance ID';

// Events.
$string['eventactivitylaunched'] = 'Activity launched';
$string['eventactivitycompleted'] = 'Activity completed';

$string['cmi5launch:addinstance'] = 'Add a new cmi5 (xAPI) activity to a course';

$string['expirecredentials'] = 'Expire credentials';
$string['checkcompletion'] = 'Check Completion';