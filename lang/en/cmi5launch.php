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
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @copyright  and on work by 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();




$string['modulename'] = 'cmi5 launch link';
$string['modulenameplural'] = 'cmi5 launch links';
$string['modulename_help'] = 'A plug in for Moodle that allows the launch of cmi5 (xAPI) content which is then tracked to a separate LRS.';

// Start Default LRS Admin Settings.
// Header.
$string['cmi5lrssettingsheader'] = 'LRS connection settings';

$string['cmi5launchlrsfieldset'] = 'Default values for cmi5 launch link activity settings';
$string['cmi5launchlrsfieldset_help'] = 'These are site-wide, default values used when creating a new activity. Each activity has the ability to override and provide alternative values.';

$string['cmi5launchlrsendpoint'] = 'Endpoint';
$string['cmi5launchlrsendpoint_help'] = 'The LRS endpoint (e.g. http://lrs.example.com/endpoint/). Must include trailing forward slash.';
$string['cmi5launchlrsendpoint_default'] = '';

$string['cmi5launchlrslogin'] = 'LRS: basic username';
$string['cmi5launchlrslogin_help'] = 'Your LRS login username.';
$string['cmi5launchlrslogin_default'] = '';

$string['cmi5launchlrspass'] = 'LRS: basic password';
$string['cmi5launchlrspass_help'] = 'Your LRS password (secret).';
$string['cmi5launchlrspass_default'] = '';

$string['cmi5launchlrsduration'] = 'Duration';
$string['cmi5launchlrsduration_help'] = 'Used with \'LRS integrated basic authentication\'. Requests the LRS to keep credentials valid for this number of minutes.';
$string['cmi5launchlrsduration_default'] = '9000';

$string['cmi5launchlrsauthentication'] = 'LRS integration';
$string['cmi5launchlrsauthentication_help'] = 'Use additional integration features to create new authentication credentials for each launch for supported LRSs.';
$string['cmi5launchlrsauthentication_watershedhelp'] = 'Note: for Watershed integration, the activity provider does not require API access enabled.';
$string['cmi5launchlrsauthentication_watershedhelp_label'] = 'Watershed integration';
$string['cmi5launchlrsauthentication_option_0'] = 'None';
$string['cmi5launchlrsauthentication_option_1'] = 'Watershed';
$string['cmi5launchlrsauthentication_option_2'] = 'Learning Locker 1';

$string['cmi5launchuseactoremail'] = 'Identify by email';
$string['cmi5launchuseactoremail_help'] = 'If selected, learners will be identified by their email address if they have one recorded in Moodle.';

$string['cmi5launchcustomacchp'] = 'Custom account homepage';
$string['cmi5launchcustomacchp_help'] = 'If entered, Moodle will use this homepage in conjunction with the ID number user profile field to identify the learner.
If the ID number is not entered for a learner, they will instead be identified by email or Moodle ID number.
Note: If a learner\'s id changes, they will lose access to registrations associated with former ids and completion data may be reset. Reports in your LRS may also be affected.';
$string['cmi5launchcustomacchp_default'] = 'https://moodle.com';

// Cmi5 grades admin.
// Start Default LRS Admin Settings.
$string['cmi5launchgradesettings'] = 'Default values ;ppppfor cmi5 launch link activity settings';
$string['cmi5launchgradesettings_help'] = 'These are site-wide, default values used when creating a new activity. Each activity has the ability to override and provide alternative values.';

$string['othersettings'] = 'Additional settings';

// Cmi5 settings.

// Header.
$string['cmi5launchsettingsheader'] = 'cmi5 player settings';
$string['cmi5launchlocationheader'] = 'Location: ';

// Cmi5 player root location.
$string['cmi5launchplayerurl'] = 'cmi5 player URL';
$string['cmi5launchplayerurl_help'] = 'The url to communicate with cmi5 player, can include port number(e.g. http://player.example.com or http://localhost:63398). Must NOT include a trailing forward slash.';
$string['cmi5launchplayerurl_default'] = '';

// Cmi5 player root location.
$string['cmi5launchcontenturl'] = 'cmi5 player URL';
$string['cmi5launchcontenturl_help'] = 'The url to communicate with cmi5 player, PUBLIC content http://player.example.com or http://localhost:63398). Must NOT include a trailing forward slash.';
$string['cmi5launchcontenturl_default'] = '';

// Cmi5 setupp.
$string['cmi5launchsetuptitle'] = 'CMI5 Setup';
$string['cmi5launchsetup_help'] = 'This is the setup page for CMI5. Here you can create a new tenant for your CMI5 player. Please enter a name for your tenant below.';
$string['cmi5launchsetupcancel'] = 'Cancelled';


// Cmi5 setupform.php.
$string['cmi5launchsettingtitle'] = 'CMI5 Setup Form';
$string['cmi5launchsettingsaved'] = 'Successfully saved settings.';
$string['cmi5launchsettingsavedfail'] = 'Failed to save to database. Please check database is accessable and try again.';



// Cmi5 player credentials.
$string['cmi5launchbasicname'] = 'cmi5 player: basic username';
$string['cmi5launchbasicname_help'] = 'The cmi5 base username.';
$string['cmi5launchbasicname_default'] = '';

$string['cmi5launchtenantname'] = 'cmi5 player: The cmi5 tenant username.';
$string['cmi5launchtenantnamesetup'] = 'Please enter a value for cmi5 tenant name.';
$string['cmi5launchtenantname'] = 'cmi5 player: The cmi5 tenant username.';
$string['cmi5launchtenantnamesetup_help'] = ' The tenant name to be used in cmi5 player requests, and attached to the token. Alphanumeric characters and spaces accepted.';
$string['cmi5launchtenantname_help'] = ' The tenant name attached to the token. Should only need to be used during initial setup. If for some reason the tenant name is changed a new bearer token will need to be generated. NOTE: changing a tenant name will require cmi5 launch link activites to need to be re-installed. Do not change name mid sessions! Data will be lost.';

$string['cmi5launchtenantname_default'] = '';

$string['cmi5launchbasepass'] = 'cmi5 player: basic password';
$string['cmi5launchbasepass_help'] = 'The cmi5 base password (secret).';
$string['cmi5launchbasepass_default'] = '';

// Tenant token related strings.
$string['cmi5launchtenanttoken'] = 'cmi5 player: bearer token';
$string['cmi5launchtenanttoken_help'] = 'The cmi5 tenant bearer token (should be a long string).  Should only need to be used during initial setup. If for some reason the tenant name is changed a new bearer token will need to be generated. This cannot be generated if a tenant name has not been made yet.';
$string['cmi5launchtenanttoken_default'] = '';
// Setup.
$string['cmi5launchtokendbfailed'] = 'Failed to save token to settings. Check connection with DB and try again.';
$string['cmi5launchtokensavefailed'] = 'Save failed. With result ';
$string['cmi5launchtokencreatedsuccess'] = 'Successfully retrieved and saved new bearer token';
$string['cmi5launchtokendbretrievefailed'] = 'Failed to retrieve token from cmi5 player. Check connection with player, ensure tenant name and ID exist, and try again.';
$string['cmi5launchtokenretrievefailed'] = 'Token retrieval failed. With result ';
$string['cmi5launchtokennonameid'] = 'Tenant name and/or ID not retrieved or blank. Please create a tenant before trying again.';

// First time setup button
// Button for cmi5, first time setup.
$string['cmi5launchsetupbutton'] = 'First time setup:';
$string['cmi5launchsetupbuttontitle'] = 'Enter cmi5 player info';


// Info for settings warnings.
$string['cmi5launchtenantnameis'] = '<b>Tenant name is ';
$string['cmi5launchtenantidis'] = '. Tenant ID is ';
$string['cmi5launchtenant_warning'] = "</b><div><br> The tenant name and ID have been set. They cannot be changed without causing problems with existing cmi5 launch link activities. To change, plugin must be uninstalled and reinstalled.</div> <div><br></div>";

// Token generation button.
$string['cmi5launchtokensetupbutton'] = 'Generate new bearer token';
$string['cmi5launchtokensetupheading'] = 'Generate new bearer token for cmi5 player';


// Lines for error messages.




// CMI5_connectors.
$string['cmi5launchcommerror'] = " CMI5 Player is not communicating. Is it running?";
$string['cmi5launchreturned'] = " CMI5 Player returned ";
$string['cmi5launchwith'] = " error. With message";

$string['cmi5launchcourseerror'] = "creating the course.";
$string['cmi5launchtenanterror'] = "creating the tenant.";
$string['cmi5launchtenantuncaughterror'] = "creating the tenant.";
$string['cmi5launchregistrationerror'] = "retrieving the registration.";
$string['cmi5launchregistrationinfoerror'] = "retrieving the registration information.";
$string['cmi5launchregistrationuncaughterror'] = "Uncaught error retrieving the registration information.";
$string['cmi5launchtokenerror'] = "retrieving the tenant token.";
$string['cmi5launchtokenuncaughterror'] = "Uncaught error retrieving the tenant token.";
$string['cmi5launchurlerror'] = "retrieving the launch url from player.";
$string['cmi5launchurluncaughterror'] = "Uncaught error retrieving the launch url from player.";
$string['cmi5launchposterror'] = "communicating with player, sending or crafting a POST request: ";
$string['cmi5launchgeterror'] = "communicating with player, sending or crafting a GET request: ";
$string['cmi5launchsessioninfoerror'] = "retrieving the session information.";
$string['cmi5launchsessioninfouncaughterror'] = "Uncaught error retrieving the session information.";

// Tenant setup.
$string['cmi5launchtenanttitle'] = 'Creating  a tenant';
$string['cmi5launchtenantmadesuccess'] = 'Tenant made and saved successfully';
$string['cmi5launchtenantfailsave'] = "Failed to save tenant to DB.";
$string['cmi5launchtenantfailsavemessage'] = "Tenant name failed to save as setting. With result ";
$string['cmi5launchtenantfailplayersavemessage'] = "Failed to make tenant. Check connection to player and tenant name (cannot reuse old tenant names).";
$string['cmi5launchtenantnamefail'] = 'Tenant name not retrieved or blank. Please try again.';

// For forms.
$string['cmi5launchsetupformplayer'] = 'This is needed to connect to player';
$string['cmi5launchtenantformplayer'] = '<p>Please enter a tenant name. When submitted it will create a tenant in the cmi5 player and automatically retrieve and save a bearer token for it as well</p>';
$string['cmi5launchbackbutton'] = 'Back';
// Grading info - MB.
// Headers.
$string['defaultgradesettings'] = 'Default values for cmi5 launch link activity grades';
$string['defaultothersettings'] = 'Default values for cmi5 launch link activity attempts and completion';

// Other.
$string['maximumgradedesc'] = 'The maximum grade for a cmi5 launch link activity';

$string['maximumattempts'] = 'Maxium attempt amount';
$string['whatmaxdesc'] = 'The maximum amount of allowed attempts';

$string['cmi5launchnolimit'] = 'No limit';
$string['cmi5launchattempt1'] = 'First attempt';
$string['cmi5launchattemptsx'] = '{$a} attempts';

$string['whatgrade'] = 'Attempts grading';
$string['whatgradedesc'] = 'If multiple attempts are allowed, this setting specifies whether the highest, average (mean), first or last completed attempt is recorded in the gradebook. The last completed attempt option does not include attempts with a \'failed\' status.';
$string['mod_cmi5launch_highest_attempt'] = 'Highest attempt';
$string['mod_cmi5launch_average_attempt'] = 'Average of attempts';
$string['mod_cmi5launch_first_attempt'] = 'First attempt';
$string['mod_cmi5launch_last_attempt'] = 'Last attempt';

$string['lastattempt'] = 'Last completed attempt';
$string['mod_cmi5launch_last_attempt_lock'] = 'Lock after final attempt';
$string['lastattemptlock_help'] = 'If enabled, a student is prevented from launching the cmi5 player after using up all their allocated attempts.';
$string['mod_cmi5launch_last_attempt_lockdesc'] = 'If enabled, a student is prevented from launching the cmi5 player after using up all their allocated attempts.';

$string['general'] = 'General data';
$string['mod_cmi5launch_grade_average'] = 'Average grade';
$string['gradeforattempt'] = 'Grade for attempt';
$string['mod_cmi5launch_grade_highest'] = 'Highest grade';
$string['grademethod'] = 'Grading method';
$string['grademethod_help'] = 'The grading method defines how the grade for a single attempt of the activity is determined.';

// For lib.php.
$string['cmi5launchaunotfound'] = 'No ids match';
$string['cmi5launchstatementdoesnotequal'] = 'Type from statement does not equal either block or AU.';
$string['cmi5lauchincorrectvalue'] = 'Incorrect value passed to function cmi5launch_find_au_satisfied. Correct values are a boolean or array';

// For tokensetup.php.
$string['cmi5launchtokensetuptitle'] = 'Creating a tenant';
$string['cmi5launchtokensaveerror'] = 'Failed to save token to settings. Check connection with DB and try again.';
$string['cmi5launchtokensaveerror'] = 'Failed to save token to settings. Check connection with DB and try again.';

// There are 2 grading methods.
// Highest grade - The highest score obtained in all passed learning objects.
// Average grade - The mean of all the scores.
$string['grademethoddesc'] = 'The grading method defines how the grade for a single attempt of the activity is determined.';
$string['gradereported'] = 'Grade reported';
$string['gradesettings'] = 'Grade settings';

// Grade errors.
$string['cmi5launchnogradeerror'] = 'No grades to update. No record for user found in this course.';
$string['cmi5launchgradeerror'] = ' Error in updating or checking user grades. Report this error to system administrator: ';
$string['cmi5launchgradetypenotfound'] = 'Grade type not found.';

// Errors on launch.php.
$string['cmi5launchsessionerror'] = 'Error in launching experience. Session ID cannot be null. Report this error to system administrator.';
$string['cmi5launcherrorexp'] = 'Error in launching experience. Report this error to system administrator: ';

// Errors on AUview.php.
$string['cmi5launchloadsessionerror'] = 'loading session table on AUview page. Check that session information is present in DB and session id is correct. Report the following to system administrator: ';


// Progress errors.
// LRS errors.
$string['cmi5launchlrsstatementretrievalerror'] = 'Error retrieving statements from LRS. Caught exception: ';
$string['cmi5launchlrssettingsretrievalerror'] = "Unable to retrieve LRS settings. Caught exception: ";
$string['cmi5launchlrssettingscorrect'] = ' Check LRS settings are correct.';
$string['cmi5launchlrscommunicationerror'] = 'Unable to communicate with LRS. Caught exception: ';
$string['cmi5launchlrschecksettings'] = " Check LRS is up, username and password are correct, and LRS endpoint is correct.";

// Related to actor.
$string['cmi5launchactorretrievalerror'] = 'Unable to retrieve actor name from LRS. Caught exception: ';
$string['cmi5launchactornotretrieved'] = '(Actor name not retrieved)';

// Related to verb.
$string['cmi5launchverbretrievalerror'] = 'Unable to retrieve verb from LRS. Caught exception: ';
$string['cmi5launchverbnotretrieved'] = '(Verb not retrieved)';

// Related to object.
$string['cmi5launchobjectnotpresent'] = '(Object name not retrieved/there is no object in this statement)';
$string['cmi5launchobjectretrievalerror'] = 'Unable to retrieve object name from LRS. Caught exception: ';
$string['cmi5launchobjectnotretrieved'] = '(Object name not retrieved)';

// Related to timestamp.
$string['cmi5launchtimestampnotpresent'] = '(Timestamp not retrieved or not present in statement)';
$string['cmi5launchtimestampretrievalerror'] = 'Unable to retrieve timestamp from LRS. Caught exception: ';
$string['cmi5launchtimestampnotretrieved'] = '(Timestamp not retrieved)';

// Related to score.
$string['cmi5launchscorenotpresent'] = '(Score not retrieved or not present in statement)';
$string['cmi5launchscoreretrievalerror'] = 'Unable to retrieve score from LRS. Caught exception: ';
$string['cmi5launchscorenotretrieved'] = '(Score not retrieved)';

// Related to session.
$string['cmi5launchsessionidretrievalerror'] = 'Unable to retrieve session id from LRS. Caught exception: ';
$string['cmi5launchsessionupdateerror'] = 'Error in updating session. Report this error to system administrator: ';
$string['cmi5launchsessioncreationerror'] = 'Error in creating session. Report this error to system administrator: ';
$string['cmi5launchsessionretrievederror'] = '<p>Error attempting to get session data from DB. Check session id.</p>';
$string['cmi5launchsessionbuilderror'] = 'Statement to build session is null or not an array/object.';

// Related to statements.
$string['cmi5launchstatementsretrievalerror'] = 'Unable to retrieve statements from LRS. Caught exception: ';
$string['cmi5launchstatementsnotretrieved'] = '(Statements not retrieved)';

// AU errors.
$string['cmi5launchaubuilderror'] = 'Statement to build AU is null or not an array/object. ';

// Au_helpers errors.
$string['cmi5launchaucannotretrieve'] = 'Cannot retrieve AUs. Error found when trying to parse them from course creation: Please check the connection to player or course format and try again. ';
$string['cmi5launchaucannotretrievedb'] = 'Cannot retrieve AU information. AU statements from DB are: ';
$string['cmi5launchaucannotsave'] = 'Cannot save AU information. AU object array is: null';
$string['cmi5launchaucannotsavedb'] = 'Cannot save to DB. Stopped at record with ID number ';
$string['cmi5launchaucannotsavefield'] = ' One of the fields is incorrect. Check data for field ';
$string['cmi5launchaudatadb'] = 'Error attempting to get AU data from DB. Check AU id. AU id is: ';

// Error handler page.
$string['cmi5launcherror'] = 'Error: ';
$string['cmi5launcherrorover'] = 'Error OVER';
$string['cmi5launcherrorgrade'] = 'Error in checking user grades: ';
$string['cmi5launcherrorau'] = 'Error loading session table on AU view page. Report this to system administrator: ';
$string['cmi5launcherrorausession'] = ' Check that session information is present in DB and session id is correct.';
$string['cmi5launcherrorexperience'] = 'Error launching experience.  Report this to system administrator: <br>';
$string['cmi5launcherrormain'] = ' error on main view page.  Report this to system administrator: <br> ';
$string['cmi5launchparsearray'] = 'Cannot parse array. Error: ';
$string['cmi5launchplayerexception'] = 'Player communication error. Something went wrong ';
$string['cmi5launchcustomexceptionmessage'] = 'Caught error. Something went wrong';

// Start Activity Settings.
$string['cmi5launchname'] = 'Launch link name';
$string['cmi5launchname_help'] = 'The name of the launch link as it will appear to the user.';

$string['cmi5launchurl'] = 'Launch URL';
$string['cmi5launchurl_help'] = 'The base URL of the cmi5 activity you want to launch (e.g. http://example.com/content/index.html).';

$string['cmi5activityid'] = 'Activity ID';
$string['cmi5activityid_help'] = 'The identifying IRI for the primary activity being launched.';

$string['cmi5package'] = 'Zip package';
$string['cmi5package_help'] = 'If you have a packaged cmi5 course, you can upload it here. If you upload a package, the launch URL and activity ID fields above will be automatically populated when you save using data from the cmi5.xml file contained in the zip. You can edit these settings at any time, but should not change the activity ID (either directly or by file upload) unless you understand the consequences.';

$string['cmi5packagetitle'] = 'cmi5 package upload';
$string['cmi5packagetext'] = 'Here you upload a zip package containing a cmi5.xml file. The launch URL defined in the cmi5.xml may point to other files in the zip package, or to an external URL.';

$string['lrsheading'] = 'LRS settings';
$string['lrsdefaults'] = 'LRS default settings';
$string['lrssettingdescription'] = 'By default, this activity uses the global LRS settings found in Site administration > Plugins > Activity modules > cmi5 launch link. To change the settings for this specific activity, select \'unlock Defaults.\'';

$string['behaviorheading'] = 'Module behavior';

$string['cmi5multipleregs'] = 'Allow multiple registrations.';
$string['cmi5multipleregs_help'] = 'If selected, allow the learner to start more than one registration for the activity. Learners can always return to any registrations they have started, even if this setting is unchecked.';

$string['apCreationFailed'] = 'Failed to create Watershed activity provider.';

// Zip errors.
$string['badmanifest'] = 'Some manifest errors: see errors log';
$string['badimsmanifestlocation'] = 'A cmi5.xml file was found but it was not in the root of your zip file, please re-package your course';
$string['badarchive'] = 'You must provide a valid zip file';
$string['nomanifest'] = 'Incorrect file package - missing cmi5.xml';

$string['cmi5launch'] = 'cmi5 launch link';
$string['pluginadministration'] = 'cmi5 launch link administration';
$string['pluginname'] = 'cmi5 launch link';

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
$string['autableheader'] = 'Assignable Units';


$string['cmi5launch_completed'] = 'Experience complete!';
$string['cmi5launch_progress'] = 'Attempt in progress.';
$string['cmi5launch_attempt'] = 'Start new session';
$string['cmi5launch_notavailable'] = 'The Learning Record Store is not available. Please contact a system administrator.

If you are the system administrator, go to Site admin / Development / Debugging and set Debug messages to DEVELOPER. Set it back to NONE or MINIMAL once the error details have been recorded.';
$string['cmi5launch_regidempty'] = 'Registration id not found. Please close this window.';

$string['cmi5launchidmissing'] = 'You must specify a course_module ID or an instance ID';

// Events.
$string['eventactivitylaunched'] = 'Activity launched';
$string['eventactivitycompleted'] = 'Activity completed';

$string['cmi5launch:addinstance'] = 'Add a new cmi5 (xAPI) activity to a course';
$string['cmi5launch:view'] = 'View cmi5 (xAPI) activity';
$string['cmi5launch:viewgrades'] = "Ability to view all of a course's grades. Often assigned to teachers.";

$string['expirecredentials'] = 'Expire credentials';
$string['checkcompletion'] = 'Check completion';

// For reports.
$string['cmi5launchreport'] = 'Report';
$string['cmi5launchattemptheader'] = 'Attempt';
$string['cmi5launchstartedheader'] = 'Started';
$string['cmi5launchfinishedheader'] = 'Finished';
$string['cmi5launchscoreheader'] = 'Score';
$string['cmi5launchstatusheader'] = 'Status';
$string['cmi5launchsatisfiedstatusheader'] = 'AU Satisfied Status';

$string['cmi5launchautitleheader'] = 'AU Title';
$string['cmi5launchattemptrow'] = 'Attempt ';
$string['cmi5launchautitleheader'] = 'AU Title';
$string['cmi5launchattemptrow'] = 'Attempt ';
$string['cmi5launchstarted'] = 'Started';
$string['cmi5launchfinished'] = 'Finished';
$string['cmi5launchscore'] = 'Score';

// Session reports.
$string['cmi5launchsessionaucompleted'] = 'Completed';
$string['cmi5launchsessionaucompletedpassed'] = 'Completed and Passed';
$string['cmi5launchsessionaucompletedfailed'] = 'Completed and Failed';
$string['cmi5launchsessiongradehigh'] = 'Highest';
$string['cmi5launchsessiongradeaverage'] = 'Average';

// For view.php.
$string['cmi5launchviewcourseerror'] = 'Creating or retrieving user course record. Contact your system administrator with error: ';
$string['cmi5launchviewexceptionau'] = 'Excepted AU, found ';
$string['cmi5launchviewauerror'] = 'retrieving and displaying AU satisfied status and grade. Contact your system administrator with error: ';




// For privacy module.

// Usercourse table.
$string['privacy:metadata:cmi5launch_usercourse'] = 'The cmi5 launch link plugin stores a users particular instance of a cmi5 Activity. While some things, like the courseid are generic to all users of the course, others, like the grade are specific to user.';
$string['privacy:metadata:cmi5launch_usercourse:id'] = 'The ID of the user course\'s particular instance assigned by Moodle.';
$string['privacy:metadata:cmi5launch_usercourse:userid'] = 'The ID of the user';
$string['privacy:metadata:cmi5launch_usercourse:registrationid'] = 'The registration ID is unique to each users particular cmi5 activity and is assigned by the cmi5 player.';
$string['privacy:metadata:cmi5launch_usercourse:ausgrades'] = 'All the AUs and their grades (overall session grades) saved in this format: AU lmsid => [AU Title => [Scores from that title\'s sessions].';
$string['privacy:metadata:cmi5launch_usercourse:grade'] = 'The current overall grade (based on grading type) for the cmi5 activity.';

// Sessions table.
$string['privacy:metadata:cmi5launch_sessions'] = 'The cmi5 launch link plugin stores each session of a users particular instance of a cmi5 Activity. While some things, like the masteryscore are generic to all users of the course, others, like the grade are specific to user.';
$string['privacy:metadata:cmi5launch_sessions:id'] = 'The ID of the user\'s session\'s particular instance assigned by Moodle.';
$string['privacy:metadata:cmi5launch_sessions:sessionid'] = 'The session id. This is created by the cmi5 player and returned with URL request. Each session has a unique ID.';
$string['privacy:metadata:cmi5launch_sessions:userid'] = 'User id, which combined with course ID can be used to retrieve unique records.';
$string['privacy:metadata:cmi5launch_sessions:registrationscoursesausid'] = 'ID assigned by the cmi5 player to AUs.';
$string['privacy:metadata:cmi5launch_sessions:createdat'] = 'Time a session started (string that is returned by CMI5 player).';
$string['privacy:metadata:cmi5launch_sessions:updatedat'] = 'Time a session was updated (string that is returned by CMI5 player).';
$string['privacy:metadata:cmi5launch_sessions:code'] = 'Unique code for each session assigned by CMI5 plyer.';
$string['privacy:metadata:cmi5launch_sessions:launchtokenid'] = 'Launchtoken assigned by CMI5 player.';
$string['privacy:metadata:cmi5launch_sessions:lastrequesttime'] = 'Time of last request (string that is returned by CMI5 player).';
$string['privacy:metadata:cmi5launch_sessions:score'] = 'The score of session (returned from "result" parameter in statements from LRS).';
$string['privacy:metadata:cmi5launch_sessions:islaunched'] = 'Whether the session has been launched.';
$string['privacy:metadata:cmi5launch_sessions:isinitialized'] = 'Whether the session has been initialized.';
$string['privacy:metadata:cmi5launch_sessions:initializedat'] = 'The status of session (returned from "success" parameter in statements from LRS).';
$string['privacy:metadata:cmi5launch_sessions:duration'] = 'Time a session lasted (from "result" parameter).';
$string['privacy:metadata:cmi5launch_sessions:iscompleted'] = 'Whether the session has been completed.';
$string['privacy:metadata:cmi5launch_sessions:ispassed'] = 'Whether the session has been passed.';
$string['privacy:metadata:cmi5launch_sessions:isfailed'] = 'Whether the session has been failed.';
$string['privacy:metadata:cmi5launch_sessions:isterminated'] = 'Whether the session has been terminated.';
$string['privacy:metadata:cmi5launch_sessions:isabandoned'] = 'Whether the session has been abandoned.';
$string['privacy:metadata:cmi5launch_sessions:progress'] = 'The full string of session progress reported from LRS".';
$string['privacy:metadata:cmi5launch_sessions:launchurl'] = 'Returned launch url from cmi5 player.';

// AUs table.
$string['privacy:metadata:cmi5launch_aus'] = 'The cmi5 launch link plugin stores each AU of a users particular instance of a cmi5 Activity. While some things, like the masteryscore are generic to all users of the course, others, like the grade are specific to user.';
$string['privacy:metadata:cmi5launch_aus:id'] = 'The ID of the user\'s AU\'s particular instance assigned by Moodle.';
$string['privacy:metadata:cmi5launch_aus:userid'] = 'User id, which combined with course ID can be used to retrieve unique records.';
$string['privacy:metadata:cmi5launch_aus:attempt'] = 'The attempt of the au, ie, first, second, third.';
$string['privacy:metadata:cmi5launch_aus:lmsid'] = 'The lmsid id from the course packet. The singular CMI5 compliant id.';
$string['privacy:metadata:cmi5launch_aus:completed'] = 'Whether an AU has met completed criteria (0 if false, 1 if true).';
$string['privacy:metadata:cmi5launch_aus:passed'] = 'Whether an AU has met passed criteria (0 if false, 1 if true).';
$string['privacy:metadata:cmi5launch_aus:inprogress'] = 'Whether an AU is in progress (0 if false, 1 if true).';
$string['privacy:metadata:cmi5launch_aus:noattempt'] = 'Whether an AU has been attempted (0 if false, 1 if true).';
$string['privacy:metadata:cmi5launch_aus:satisfied'] = 'Whether an AU has been satisfied (0 if false, 1 if true).';
$string['privacy:metadata:cmi5launch_aus:sessions'] = 'The IDs of the AU\'s individual sessions, saved as array for retrieval.';
$string['privacy:metadata:cmi5launch_aus:scores'] = 'The scores of the AU\'s individual sessions, saved as array for retrieval.';
$string['privacy:metadata:cmi5launch_aus:grade'] = 'The overall grade of the AU (based on grading type).';

// For external systems.

// LRS info.
$string['privacy:metadata:lrs'] = 'The cmi5 launch link requests xAPI statements from an LRS to dispaly progress reports to students.';
$string['privacy:metadata:lrs:registrationid'] = 'There are several ways to request xAPI statements from an LRS, the only way compatible with Moodle information is the registration ID. This ID will request all statements from that instance of cmi5 launch link activity.';
$string['privacy:metadata:lrs:createdat'] = 'By sending the \'created at\' time we can filter out irrelevant statements and only get those created after or on that time.';

// Cmi5 player info.
$string['privacy:metadata:cmi5_player'] = 'The cmi5 launch link communicates with the cmi5 player to upload cmi5 activities, request launch URLs for them, and track staus of the activity and it\'s AU\s status such as completed or not satisified. It can also assign registration ID\'s and return info on registrations and sessions.';
$string['privacy:metadata:cmi5_player:registrationid'] = 'The cmi5 player assigns each user and their activity instance a registration id, and it can be used to query updated information from the cmi5 player.';
$string['privacy:metadata:cmi5_player:actor'] = 'When retrieving a launch URL or new registration the cmi5 player requests the "actor" name, which is the username in Moodle.';
$string['privacy:metadata:cmi5_player:courseid'] = 'The cmi5 player assigns each user and their activity instance a course ID, and it can is used to request a launch URL.';
$string['privacy:metadata:cmi5_player:returnurl'] = 'The return URL is a parameter sent to the cmi5 player when requesting a launch URL. It is where the browser returns to upon closing the launched activity. It has a unique ID on the end directing back to the user\'s particular course instance.';
$string['privacy:metadata:cmi5_player:sessionid'] = 'The cmi5 player assigns each user\'s session a unique ID, and this is sent to the cmi5 player when requesting updated session info.';

