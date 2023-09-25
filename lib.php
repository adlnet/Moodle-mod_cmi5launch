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
 * Library of interface functions and constants for module cmi5launch
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the cmi5launch specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Cmi5PHP - required for interacting with the LRS in cmi5launch_get_statements.
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/autoload.php");

// SCORM library from the SCORM module. Required for its xml2Array class by cmi5launch_process_new_package.
require_once("$CFG->dirroot/mod/scorm/datamodels/scormlib.php");

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
    
use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\course;
use mod_cmi5launch\local\session_helpers;

global $cmi5launchsettings;
$cmi5launchsettings = null;

// Moodle Core API.

// TODO- this is where we add  functions for grades we want suchs as FEATURE_GRADE_HAS_GRADE.
// For now I added those SCORM had, we can whittle this down. -MB.
/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function cmi5launch_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the cmi5launch into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $cmi5launch An object from the form in mod_form.php
 * @param mod_cmi5launch_mod_form $mform
 * @return int The id of the newly inserted cmi5launch record
 */

function cmi5launch_add_instance(stdClass $cmi5launch, mod_cmi5launch_mod_form $mform = null) {

    global $DB, $CFG;

    $cmi5launch->timecreated = time();

    // Need the id of the newly created instance to return (and use if override defaults checkbox is checked).
    $cmi5launch->id = $DB->insert_record('cmi5launch', $cmi5launch);

    $cmi5launchlrs = cmi5launch_build_lrs_settings($cmi5launch);

    // We removed this part of lrs box -MB.
    /*
    // Determine if override defaults checkbox is checked or we need to save watershed creds.
    if ($cmi5launch->overridedefaults == '1' || $cmi5launchlrs->lrsauthentication == '2') {
        $cmi5launchlrs->cmi5launchid = $cmi5launch->id;

        // Insert data into cmi5launch_lrs table.
        if (!$DB->insert_record('cmi5launch_lrs', $cmi5launchlrs)) {
            return false;
        }
    }
    */

    // Process uploaded file.
    if (!empty($cmi5launch->packagefile)) {

        cmi5launch_process_new_package($cmi5launch);
    }

    return $cmi5launch->id;
}

/**
 * Updates an instance of the cmi5launch in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $cmi5launch An object from the form in mod_form.php
 * @param mod_cmi5launch_mod_form $mform
 * @return boolean Success/Fail
 */
function cmi5launch_update_instance(stdClass $cmi5launch, mod_cmi5launch_mod_form $mform = null) {
    global $DB, $CFG;

    $cmi5launch->timemodified = time();
    $cmi5launch->id = $cmi5launch->instance;

    $cmi5launchlrs = cmi5launch_build_lrs_settings($cmi5launch);

    // We removed this part of lrs box -MB
    /*
    // Determine if override defaults checkbox is checked.
    if ($cmi5launch->overridedefaults == '1') {
        // Check to see if there is a record of this instance in the table.
        $cmi5launchlrsid = $DB->get_field(
            'cmi5launch_lrs',
            'id',
            array('cmi5launchid' => $cmi5launch->instance),
            IGNORE_MISSING
        );
        // If not, will need to insert_record.
        if (!$cmi5launchlrsid) {
            if (!$DB->insert_record('cmi5launch_lrs', $cmi5launchlrs)) {
                return false;
            }
        } else { // If it does exist, update it.
            $cmi5launchlrs->id = $cmi5launchlrsid;

            if (!$DB->update_record('cmi5launch_lrs', $cmi5launchlrs)) {
                return false;
            }
        }
    }
    */

    if (!$DB->update_record('cmi5launch', $cmi5launch)) {
        return false;
    }

    // Process uploaded file.
    if (!empty($cmi5launch->packagefile)) {
        cmi5launch_process_new_package($cmi5launch);
    }

    return true;
}

function cmi5launch_build_lrs_settings(stdClass $cmi5launch) {
    global $DB, $CFG;

    // Data for cmi5launch_lrs table.
    $cmi5launchlrs = new stdClass();
    $cmi5launchlrs->lrsendpoint = $cmi5launch->cmi5launchlrsendpoint;
    $cmi5launchlrs->lrsauthentication = $cmi5launch->cmi5launchlrsauthentication;
    $cmi5launchlrs->customacchp = $cmi5launch->cmi5launchcustomacchp;
    $cmi5launchlrs->useactoremail = $cmi5launch->cmi5launchuseactoremail;
    $cmi5launchlrs->lrsduration = $cmi5launch->cmi5launchlrsduration;
    $cmi5launchlrs->cmi5launchid = $cmi5launch->instance;
    $cmi5launchlrs->lrslogin = $cmi5launch->cmi5launchlrslogin;
    $cmi5launchlrs->lrspass = $cmi5launch->cmi5launchlrspass;

    return $cmi5launchlrs;
}

/**
 * Removes an instance of the cmi5launch from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function cmi5launch_delete_instance($id) {
    global $DB;

    if (! $cmi5launch = $DB->get_record('cmi5launch', array('id' => $id))) {
        return false;
    }

    // Determine if there is a record of this (ever) in the cmi5launch_lrs table.
    $cmi5launchlrsid = $DB->get_field('cmi5launch_lrs', 'id', array('cmi5launchid' => $id), $strictness = IGNORE_MISSING);
    if ($cmi5launchlrsid) {
        // If there is, delete it.
        $DB->delete_records('cmi5launch_lrs', array('id' => $cmi5launchlrsid));
    }

    $DB->delete_records('cmi5launch', array('id' => $cmi5launch->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function cmi5launch_user_outline($course, $user, $mod, $cmi5launch) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $cmi5launch the module instance record
 * @return void, is supposed to echp directly
 */
function cmi5launch_user_complete($course, $user, $mod, $cmi5launch) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in cmi5launch activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function cmi5launch_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link cmi5launch_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function cmi5launch_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see cmi5launch_get_recent_mod_activity()}
 * @return void
 */
function cmi5launch_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function cmi5launch_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function cmi5launch_get_extra_capabilities() {
    return array();
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function cmi5launch_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('areacontent', 'scorm');
    $areas['package'] = get_string('areapackage', 'scorm');
    return $areas;
}

/**
 * File browsing support for cmi5launch file areas
 *
 * @package mod_cmi5launch
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function cmi5launch_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'package') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_cmi5launch', 'package', 0, $filepath, $filename)) {
            if ($filepath === '/' && $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_cmi5launch', 'package', 0);
            } else {
                // Not found.
                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
    }

    return false;
}

/**
 * Serves cmi5 content, introduction images and packages. Implements needed access control ;-)
 *
 * @package  mod_cmi5launch
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function cmi5launch_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);
    $canmanageactivity = has_capability('moodle/course:manageactivities', $context);

    $filename = array_pop($args);
    $filepath = implode('/', $args);
    if ($filearea === 'content') {
        $lifetime = null;
    } else if ($filearea === 'package') {
        $lifetime = 0; // No caching here.
    } else {
        return false;
    }

    $fs = get_file_storage();

    if (
        (!$file = $fs->get_file($context->id, 'mod_cmi5launch', $filearea, 0, '/'.$filepath.'/', $filename)) 
        || ($file->is_directory())
    ) {
        if ($filearea === 'content') { // Return file not found straight away to improve performance.
            send_header_404();
            die;
        }
        return false;
    }

    // Finally send the file.
    send_stored_file($file, $lifetime, 0, false, $options);
}

/**
 * Export file resource contents for web service access.
 *
 * @param cm_info $cm Course module object.
 * @param string $baseurl Base URL for Moodle.
 * @return array array of file content
 */
function cmi5launch_export_contents($cm, $baseurl) {
    global $CFG;
    $contents = array();
    $context = context_module::instance($cm->id);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_cmi5launch', 'package', 0, 'sortorder DESC, id ASC', false);

    foreach ($files as $fileinfo) {
        $file = array();
        $file['type'] = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_cmi5launch/package'.
            $fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

    return $contents;
}

// Navigation API.

/**
 * Extends the global navigation tree by adding cmi5launch nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the cmi5launch module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function cmi5launch_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the cmi5launch settings
 *
 * This function is called when the context for the page is a cmi5launch module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $cmi5launchnode {@link navigation_node}
 */
function cmi5launch_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $cmi5launchnode = null) {
}

// Called by Moodle core.
function cmi5launch_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;
    $result = $type; // Default return value.

     // Get cmi5launch.
    if (!$cmi5launch = $DB->get_record('cmi5launch', array('id' => $cm->instance))) {
        throw new Exception("Can't find activity {$cm->instance}"); // TODO: localise this.
    }

    $cmi5launchsettings = cmi5launch_settings($cm->instance);

    $expirydate = null;
    $expirydays = $cmi5launch->cmi5expiry;
    if ($expirydays > 0) {
        $expirydatetime = new DateTime();
        $expirydatetime->sub(new DateInterval('P'.$expirydays.'D'));
        $expirydate = $expirydatetime->format('c');
    }

    if (!empty($cmi5launch->cmi5verbid)) {
        // Try to get a statement matching actor, verb and object specified in module settings.
        $statementquery = cmi5launch_get_statements(
            $cmi5launchsettings['cmi5launchlrsendpoint'],
            $cmi5launchsettings['cmi5launchlrslogin'],
            $cmi5launchsettings['cmi5launchlrspass'],
            $cmi5launchsettings['cmi5launchlrsversion'],
            $cmi5launch->cmi5activityid,
            cmi5launch_getactor($cm->instance),
            $cmi5launch->cmi5verbid,
            $expirydate
        );

        // If the statement exists, return true else return false.
        if (!empty($statementquery->content) && $statementquery->success) {
            $result = true;
        } else {
            $result = false;
        }
    }

    return $result;
}

// Cmi5Launch specific functions.
/*
The functions below should really be in locallib, however they are required for one
or more of the functions above so need to be here.
It looks like the standard Quiz module does that same thing, so I don't feel so bad.
*/

/**
 * Handles uploaded zip packages when a module is added or updated. Unpacks the zip contents
 * and extracts the launch url and activity id from the cmi5.xml file.
 * Note: This takes the *first* activity from the cmi5.xml file to be the activity intended
 * to be launched. It will not go hunting for launch URLs any activities listed below.
 * Based closely on code from the SCORM and (to a lesser extent) Resource modules.
 * @package  mod_cmi5launch
 * @category cmi5
 * @param object $cmi5launch An object from the form in mod_form.php
 * @return array empty if no issue is found. Array of error message otherwise
 */

function cmi5launch_process_new_package($cmi5launch) {

    global $DB, $CFG;
    $cmid = $cmi5launch->coursemodule;
    $context = context_module::instance($cmid);

    // Bring in functions from classes cmi5Connector and AU helpers.
    $connectors = new cmi5_connectors;
    $auhelper = new au_helpers;

    // Bring in functions from class cmi5_table_connectors and AU helpers.
    $createcourse = $connectors->cmi5launch_get_create_course();
    $retrieveaus = $auhelper-> get_cmi5launch_retrieve_aus();
    // Reload cmi5 instance.
    $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

    $fs = get_file_storage();

    $fs->delete_area_files($context->id, 'mod_cmi5launch', 'package');
    file_save_draft_area_files(
        $cmi5launch->packagefile,
        $context->id,
        'mod_cmi5launch',
        'package',
        0,
        array('subdirs' => 0, 'maxfiles' => 1)
    );
    // Get filename of zip that was uploaded.
    $files = $fs->get_area_files($context->id, 'mod_cmi5launch', 'package', 0, '', false);
    if (count($files) < 1) {
        return false;
    }

    $zipfile = reset($files);
    $zipfilename = $zipfile->get_filename();
    $packagefile = false;

    $packagefile = $fs->get_file($context->id, 'mod_cmi5launch', 'package', 0, '/', $zipfilename);
 
    // Retrieve user settings to apply to newly created record.
    $settings = cmi5launch_settings($cmi5launch->id);
    $token = $settings['cmi5launchtenanttoken'];
    // Create the course and retrieve info for saving to DB.
    $courseresults = $createcourse($context->id, $token, $packagefile);

	// Take the results of created course and save new course id to table.
    $record->courseinfo = $courseresults;

    $returnedinfo = json_decode($courseresults, true);
    // Retrieve the lmsId of course.
    $lmsid = $returnedinfo["lmsId"];
    $record->cmi5activityid = $lmsid;

    $record->courseid = $returnedinfo["id"];

    // Create url for sending to when requesting launch url for course.
    $playerurl = $settings['cmi5launchplayerurl'];

    // Build and save launchurl.
    $url = $playerurl . "/api/v1/". $record->courseid. "/launch-url/";
    $record->launchurl = $url;

    //Can we cut out some of the 0s here -MB
    // no i dont think so dang it -MB
    $aus = ($retrieveaus($returnedinfo));

    $record->aus = (json_encode($aus));

	// Populate player table with new course info.
    $DB->update_record('cmi5launch', $record, true);

    $fs->delete_area_files($context->id, 'mod_cmi5launch', 'content');

    $packer = get_file_packer('application/zip');
    $packagefile->extract_to_storage($packer, $context->id, 'mod_cmi5launch', 'content', 0, '/');

    // If the cmi5.xml file isn't there, don't do try to use it.
    // This is unlikely as it should have been checked when the file was validated.
    if ($manifestfile = $fs->get_file($context->id, 'mod_cmi5launch', 'content', 0, '/', 'cmi5.xml')) {
        $xmltext = $manifestfile->get_content();

        $defaultorgid = 0;
        $firstinorg = 0;

        $pattern = '/&(?!\w{2,6};)/';
        $replacement = '&amp;';
        $xmltext = preg_replace($pattern, $replacement, $xmltext);

        $objxml = new xml2Array();
        $manifest = $objxml->parse($xmltext);

	    // Update activity id from the first activity in cmi5.xml, if it is found.
        // Skip without error if not. (The Moodle admin will need to enter the id manually).
        if (isset($manifest[0]["children"][0]["children"][0]["attrs"]["ID"])) {
            $record->cmi5activityid = $manifest[0]["children"][0]["children"][0]["attrs"]["ID"];
        }

        // Update launch from the first activity in cmi5.xml, if it is found.
        // Skip if not. (The Moodle admin will need to enter the url manually).
        foreach ($manifest[0]["children"][0]["children"][0]["children"] as $property) {
            if ($property["name"] === "LAUNCH") {
                $record->cmi5launchurl = $CFG->wwwroot."/pluginfile.php/".$context->id."/mod_cmi5launch/"
                .$manifestfile->get_filearea()."/".$property["tagData"];
            }
        }
    }
    // Save reference.
    // Turn off to trigger echo.
   return $DB->update_record('cmi5launch', $record);
}

/**
 * Check that a Zip file contains a cmi5.xml file in the right place. Used in mod_form.php.
 * Heavily based on scorm_validate_package in /mod/scorm/lib.php
 * @package  mod_cmi5launch
 * @category cmi5
 * @param stored_file $file a Zip file.
 * @return array empty if no issue is found. Array of error message otherwise
 */
function cmi5launch_validate_package($file) {
    $packer = get_file_packer('application/zip');
    $errors = array();
    $filelist = $file->list_files($packer);
    if (!is_array($filelist)) {
        $errors['packagefile'] = get_string('badarchive', 'cmi5launch');
    } else {
        $badmanifestpresent = false;
        foreach ($filelist as $info) {
            if ($info->pathname == 'cmi5.xml') {
                return array();
            } else if (strpos($info->pathname, 'cmi5.xml') !== false) {
                // This package has cmi5 xml file inside a folder of the package.
                $badmanifestpresent = true;
            }
            if (preg_match('/\.cst$/', $info->pathname)) {
                return array();
            }
        }
        if ($badmanifestpresent) {
            $errors['packagefile'] = get_string('badimsmanifestlocation', 'cmi5launch');
        } else {
            $errors['packagefile'] = get_string('nomanifest', 'cmi5launch');
        }
    }
    return $errors;
}

/**
 * Fetches Statements from the LRS. This is used for completion tracking -
 * we check for a statement matching certain criteria for each learner.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $url LRS endpoint URL
 * @param string $basiclogin login/key for the LRS
 * @param string $basicpass pass/secret for the LRS
 * @param string $version version of xAPI to use
 * @param string $activityid Activity Id to filter by
 * @param cmi5 Agent $agent Agent to filter by
 * @param string $verb Verb Id to filter by
 * @param string $since Since date to filter by
 * @return cmi5 LRS Response
 */
function cmi5launch_get_statements($url, $basiclogin, $basicpass, $version, $activityid, $agent, $verb, $since = null) {

    $lrs = new \cmi5\RemoteLRS($url, $version, $basiclogin, $basicpass);

    $statementsquery = array(
        "agent" => $agent,
        "verb" => new \cmi5\Verb(array("id" => trim($verb))),
        "activity" => new \cmi5\Activity(array("id" => trim($activityid))),
        "related_activities" => "false",
        "format" => "ids"
    );

    if (!is_null($since)) {
        $statementsquery["since"] = $since;
    }

    // Get all the statements from the LRS.
    $statementsresponse = $lrs->queryStatements($statementsquery);

    if ($statementsresponse->success == false) {
        return $statementsresponse;
    }

    $allthestatements = $statementsresponse->content->getStatements();
    $morestatementsurl = $statementsresponse->content->getMore();
    while (!empty($morestatementsurl)) {
        $morestmtsresponse = $lrs->moreStatements($morestatementsurl);
        if ($morestmtsresponse->success == false) {
            return $morestmtsresponse;
        }
        $morestatements = $morestmtsresponse->content->getStatements();
        $morestatementsurl = $morestmtsresponse->content->getMore();
        // Note: due to the structure of the arrays, array_merge does not work as expected.
        foreach ($morestatements as $morestatement) {
            array_push($allthestatements, $morestatement);
        }
    }

    return new \cmi5\LRSResponse(
        $statementsresponse->success,
        $allthestatements,
        $statementsresponse->httpResponse
    );
}

/**
 * Build a cmi5 Agent based on the current user
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @return cmi5 Agent $agent Agent
 */

function cmi5launch_getactor($instance) {
    global $USER, $CFG;

    $settings = cmi5launch_settings($instance);

    if ($USER->idnumber && $settings['cmi5launchcustomacchp']) {
        $agent = array(
            "name" => fullname($USER),
            "account" => array(
                "homePage" => $settings['cmi5launchcustomacchp'],
                "name" => $USER->idnumber
            ),
            "objectType" => "Agent"
        );
    } else if ($USER->email && $settings['cmi5launchuseactoremail']) {
        $agent = array(
            "name" => fullname($USER),
            "mbox" => "mailto:".$USER->email,
            "objectType" => "Agent"
        );
    } else {
        $agent = array(
            "name" => fullname($USER),
            "account" => array(
                "homePage" => $CFG->wwwroot,
                "name" => $USER->username
            ),
            "objectType" => "Agent"
        );
    }

    return new \cmi5\Agent($agent);
}


/**
 * Returns the LRS settings relating to a cmi5 Launch module instance
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $instance The Moodle id for the cmi5 module instance.
 * @return array LRS settings to use
 */
 function cmi5launch_settings($instance) {
    global $DB, $CFG, $cmi5launchsettings;

    if (!is_null($cmi5launchsettings)) {
        return $cmi5launchsettings;
    }

    $expresult = array();
    $activitysettings = $DB->get_record(
        'cmi5launch_lrs',
        array('cmi5launchid' => $instance),
        $fields = '*',
        $strictness = IGNORE_MISSING
    );
  
    // If global settings are not used, retrieve activity settings.
    if (!use_global_cmi5_lrs_settings($instance)) {
        $expresult['cmi5launchlrsendpoint'] = $activitysettings->lrsendpoint;
        $expresult['cmi5launchlrsauthentication'] = $activitysettings->lrsauthentication;
        $expresult['cmi5launchlrslogin'] = $activitysettings->lrslogin;
        $expresult['cmi5launchlrspass'] = $activitysettings->lrspass;
        $expresult['cmi5launchcustomacchp'] = $activitysettings->customacchp;
        $expresult['cmi5launchuseactoremail'] = $activitysettings->useactoremail;
        $expresult['cmi5launchlrsduration'] = $activitysettings->lrsduration;
    } else { // Use global lrs settings.
        $result = $DB->get_records('config_plugins', array('plugin' => 'cmi5launch'));
        foreach ($result as $value) {
            $expresult[$value->name] = $value->value;
        }
    }

    $expresult['cmi5launchlrsversion'] = '1.0.0';

    $cmi5launchsettings = $expresult;

    return $expresult;
}


/**
 * Should the global LRS settings be used instead of the instance specific ones?
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $instance The Moodle id for the cmi5 module instance.
 * @return bool
 */
function use_global_cmi5_lrs_settings($instance) {
    global $DB;
    // Determine if there is a row in cmi5launch_lrs matching the current activity id.
    $activitysettings = $DB->get_record('cmi5launch', array('id' => $instance));
   
    /* Removed override defaults from db
    if ($activitysettings->overridedefaults == 1) {
        return false;
    }
    */
    return true;
}

/*

// Grade functions
// TODO - this is a copy of how scorm handles grades. I got these from SCORM's lib.php
// We will need to tweak
/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $cmi5id id of scorm
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */

 //I think maybe THIS is why the grades aren't showing. 
 // This func needs to work to call for report file? 
function cmi5launch_get_user_grades($cmi5launch, $userid=0) {
    global $CFG, $DB;
    //For now do this manually, and later it can read gradetypee wanted
    $gradetypewanted = "overall";
    $grades = array();

    // They are pulling mutliple beacue this may be ALL grades! Like x student had this and y student had that.
    // Because if the userid is empty they want all as opposed to a particulr student.
    if (empty($userid)) {
        
		// We are going to need to make CMI5_tables.
        // They retrieve the list of users.
        $users = $DB->get_records_select('cmi5launch_course', "userid=? GROUP BY userid",
                                            array($cmi5launch->id), "", "userid,null");

        // AHA! We are  not getting into these for some reaons, lets check users
        echo "<br>";
        echo "well what is USERS ";
        var_dump($users);
        echo "<br>";
        // If there is a list of users then they iterate through it and make user objects with the individual users.
        if ($users) {
            //Aha! We are not entering this
            foreach ($users as $user) {
                $grades[$user->userid] = new stdClass();
                $grades[$user->userid]->id         = $user->userid;
                $grades[$user->userid]->userid     = $user->userid;
                $grades[$user->userid]->rawgrade = cmi5launch_grade_user($cmi5launch, $user->userid, $gradetypewanted);
             //For debugging
    
           
           
            }
        } else {
            return false;
        }

    } else {
        $preattempt = $DB->get_records_select('cmi5launch_course', "userid=? AND userid=? GROUP BY userid",
                                                array($cmi5launch->id, $userid), "", "userid,null");
       /* if (!$preattempt) {
            return false; // No attempt yet.
        }*/
        $grades[$userid] = new stdClass();
        $grades[$userid]->id         = $userid;
        $grades[$userid]->userid     = $userid;
        $grades[$userid]->rawgrade = cmi5launch_grade_user($cmi5launch, $userid, $gradetypewanted);
        
        //But we are entering here, and therefore the problem must be in grade_user
        //For debugging
     
    }

    return $grades;
}
// This appears to be the function that is called to grade the user. SPECIFICALLY
// Im guessing this just retrieves grades rather than updates them 
// We can ue this to draw grades from our tables? Maybe the AUS? Or do we want course overall?
// OR do we want to add a apram that lets user pick what they want????]=we=


function cmi5launch_grade_user($cmi5launch, $userid, $requestedgrade) {

    // Ensure we dont grade user beyond $scorm->maxattempt settings.
   /* $lastattempt = scorm_get_last_attempt($scorm->id, $userid);
    if ($scorm->maxattempt != 0 && $lastattempt >= $scorm->maxattempt) {
        $lastattempt = $scorm->maxattempt;
    }*/

    switch ($requestedgrade) {
        case 'overall':
            // Let make a func that gets the highest grade in a COURSE.
            // Sessions go into AUs and AUs into sessions, so ....whatever is in grade should be highest
            // I wonder if we need a grade class, might make sense

            return cmi5launch_highest_grade($cmi5launch, $userid);
        break;
      //  case LASTATTEMPT:
        //    return scorm_grade_user_attempt($cmi5launch, $userid, scorm_get_last_completed_attempt($scorm->id, $userid));
        //break;
       /* case HIGHESTATTEMPT:
            $maxscore = 0;
            for ($attempt = 1; $attempt <= $lastattempt; $attempt++) {
                $attemptscore = scorm_grade_user_attempt($cmi5launch, $userid, $attempt);
                $maxscore = $attemptscore > $maxscore ? $attemptscore : $maxscore;
            }
            return $maxscore;

        break;
        case AVERAGEATTEMPT:
            $attemptcount = scorm_get_attempt_count($userid, $scorm, true, true);
            if (empty($attemptcount)) {
                return 0;
            } else {
                $attemptcount = count($attemptcount);
            }
            $lastattempt = scorm_get_last_attempt($scorm->id, $userid);
            $sumscore = 0;
            for ($attempt = 1; $attempt <= $lastattempt; $attempt++) {
                $attemptscore = scorm_grade_user_attempt($scorm, $userid, $attempt);
                $sumscore += $attemptscore;
            }

            return round($sumscore / $attemptcount);
        break;*/ 
    }
}

function cmi5launch_highest_grade($cmi5launch, $userid)
{

    global $cmi5launch, $USER, $DB;

    $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

    $userrecord =$DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

    // So grade is 0, we need this updated,maybe even in one of our new fancy updates fucns
    // But for here we need to focus on the fact that this function is meant to get the highest grade,
    // So lets do that from all our AUs
    $allscores[] = array();
      // Lets see what we get here in augrades

      //Lets add an if for when max is null
    if (!json_decode($userrecord->ausgrades, true) == null) {
        // Add score to array of scores
        $highestgrade = (max(json_decode($userrecord->ausgrades, true)) );
        // Ok intval is not working due to those damn brackeeets,

        echo"<br>";
        echo "what is highestgrade";
        var_dump($highestgrade);
        echo"<br>";
        // so lets make an if clause that checks for them and removes them if found, that way it can handle both
        if(str_contains($highestgrade, "[")){
            $highestgrade = str_replace("[", "", $highestgrade);
        }
        // Now lets apply intval
        $highestgrade = intval($highestgrade);
        //what is it now
      
    }else{
        $highestgrade = 0;
    }
   // $highestgrade = 0;
   /* foreach ($augrades as $key => $augrade) {
        
        // If a session has more than one score, we only want the highest.
        if (!$augrade == null && $augrade > $highestgrade) {

            $highestgrade = $augrade;
        }
    }*/
  
    // Aha! ITs 0 and it shouldnt be!!!

    return $highestgrade;

}
/**
 * For example, this callback for the assignment module is assignment_update_grades().
 * This callback should update the grade(s) for the supplied user.
 * This may be as simple as retrieving the grades for the user from the activity module's own tables.
 * then calling {$modname}_grade_item_update().
 * Its parameters are:
 * stdClass $modinstance the activity module settings.
 * int $userid A user ID or 0 for all users.
 * bool $nullifnone If a single user is specified, $nullifnone is true and the user has no grade then a grade item with a null rawgrade should be inserted.
 * Update grades in central gradebook.
 *
 * @category grade
 * @param object $cmi5launch
 * @param int $userid specific user only, 0 mean all
 * @param bool $nullifnone
 */
// This can call grade_item_update which helps interact with Moodle gradebook.
// it can pull from DB and then call grade_item_update.
// We could also later use this to change how the array is massaged.
// in case they want highest or lowest.
// MB
// According to notes I found this function is called automatically by moodle if it needs the users grades updated.
// It ca also be called manually when you want to push a new grade to gradebook.
// This function should do whatever is needed to generate the relevant grades to push into gradebook
// Based on activitydbrecord (whats that?) and optionalllly userid (if proided.,)
// then call 'myplugin_grade_item_update with the grades to write

function cmi5launch_update_grades($cmi5launch, $userid = 0, $nullifnone = true)
{
    // Bring in functions and classes.
    $progress = new progress;
    $aushelpers = new au_helpers;
    $connectors = new cmi5_connectors;
    $sessionhelpers = new session_helpers;

    // Functions from other classes.
    $saveaus = $aushelpers->get_cmi5launch_save_aus();
    $createaus = $aushelpers->get_cmi5launch_create_aus();
    $getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
    $getregistration = $connectors->cmi5launch_get_registration_with_post();
    $getregistrationinfo = $connectors->cmi5launch_get_registration_with_get();
    $getprogress = $progress->cmi5launch_get_retrieve_statements();
    $updatesession = $sessionhelpers->cmi5launch_get_update_session();

    global $CFG, $DB, $USER;

    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->libdir . '/completionlib.php');

    // Reload cmi5 course instance.
    $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

    // Then we have a record, so we need to retrieve it.
    $userscourse = $DB->get_record('cmi5launch_course', ['courseid' => $record->courseid, 'userid' => $USER->id]);

    // Retrieve registration id.
    $registrationid = $userscourse->registrationid;

    // Retrieve AU ids.
    $auids = (json_decode($userscourse->aus));

    // We need id to get progress.
    $cmid = $cmi5launch->id;


        // Ok, now we need to retrieve the sessions and find the average score.
        $grade = json_decode($userscourse->ausgrades, true);
  
    /// SO grades are an item or arra!!!!
    // SO scorm makes the graes in this func then passes on
    echo "<br>";
    echo "what is grade";
    var_dump($grade);
    echo "<br>";


    //The rawgarde is now coming back as an int, but I wonder if it needs to be somethin 
    // else besides a rawgrade to show up in boopk
    if ($grades = cmi5launch_get_user_grades($cmi5launch, $userid)) {
    
        cmi5launch_grade_item_update($cmi5launch, $grades);
        

        // Set complete.
        ///scorm_set_completion($scorm, $userid, COMPLETION_COMPLETE, $grades);
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        cmi5launch_grade_item_update($cmi5launch, $grade);
       
        // Set incomplete.
       /// scorm_set_completion($scorm, $userid, COMPLETION_INCOMPLETE);
    } else {
        cmi5launch_grade_item_update($cmi5launch);
    }
        // Ok so if this does whatever is needed to generate the relevant grades to push into gradebook
        // Then is this a good place to calculate grades? 
    ///////cmi5launch_grade_item_update($userscourse, $grade);
    }
    

    /**
     * Update/create grade item for given cmi5
     * calls grade_update from moodle gradelib.php
     * @category grade
     * @uses GRADE_TYPE_VALUE
     * @uses GRADE_TYPE_NONE
     * @param object $cmi5launch object with extra cmidnumber //now cmi5launch
     * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
     * @return object grade_item
     */
    // MB.
// Ok, according to the forum I found this is the only place their grade_update should be called.
// And this func should be called from 'myplugin'_add_instance, 'myplugin'_update_instance and 'myplugin'_update_grades.
// IT should look at settings in the activity $activitydbrecord to determine grading type, max and min
// values, etc. Then setupgradeinfo to pass to grade_update, it should also pass
// on the optional grades value

// Params is the $gradeinfo info in the forum i found and needs
//$gradeinfo is an array containing: ['itemname' => $activityname, 'idnumber' => $activityidnumber, 'gradetype' => GRADE_TYPE_VALUE, 'grademax' => 100, 'grademin' => 0]
    function cmi5launch_grade_item_update($cmi5launch, $grades = null)
    {
        global $CFG, $DB, $cmi5launch, $USER;
        //Note the SCORM version called it's locallib, we may need to get funcs from there as well and place into 
        //OUR locallib
        //May be where those constants were kept
        require_once($CFG->dirroot . '/mod/cmi5launch/locallib.php');
        $settings = cmi5launch_settings($cmi5launch->id);
        //gradelib?? We need to see wha this is 
        //I beleive they are referencing moodle/lib/gradelib
        //In case they need their base grade_update?
        if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
            require_once($CFG->libdir . '/gradelib.php');
        }
// Reload cmi5 course instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

        //so this is getting the scorm id right? Well cmi5 id should work to?
        //But a cmi5 id isntusually gotten with cmidnumber I don't
        //yeah the cmi5 launch table has a 'courseid' and just a plain 'id'
        //I think in this context its 'id' they want?
        //If not it has to be 'courseid'
        $params = array('itemname' => $cmi5launch->name);
        if (isset($cmi5launch->id)) {
            $params['idnumber'] = $cmi5launch->id;//He said make this cmidnumber? But what is that?
            //I think its just id, or is in another part of code
        }

        //Ok this is passing as arrays the grades to be updated and the params to do so
        //GRADE_TYPE_VALUE  appears to be a moodle constant, so should be found when proggram, runs lovally
      //  if ($cmi5launch->grademethod == GRADE_AUS_CMI5) {
            /// Three slashes means I don't think we need. -MB. 
            ///   $maxgrade = $DB->count_records_select('scorm_scoes', 'scorm = ? AND '.
            ///                                        $DB->sql_isnotempty('scorm_scoes', 'launch', false, true), array($scorm->id));
            
            //That's weird, they should exist
            //what is cmi5launch properteis
        // Iterate through grades and grab the max grade.
       // $overallgrade = 0;

        //Grades are an int?
        /*
        foreach ($grades as $key => $grade) {
        
            echo "<br>";
            echo "grade D +A LOOP IS : ";
            $toprint = ($grade);
            var_dump($toprint);
            echo "<br>";
        echo " and its intval is";
        var_dump(intval($grade));
        echo "<br>";
        echo "What does this find? isnumeric? :";
        var_dump(is_numeric($grade));
        echo "<br>";
        //Will trim work?
        echo "Will trim work :";
       // var_dump(trim($grade, '[]'));
        echo "<br>";
        // OH yess! the brackets were a problem!
          /*  if (intval(trim($grade, '[]')) >= $overallgrade) {
                $overallgrade = intval(trim($grade, '[]'));
                echo"<br>";
                echo "Overall grade in if loop is is: ";
                var_dump($overallgrade);
                echo"<br>";
     
            }*/
//    }


            $maxgrade = $settings['maxgrade'];
            //What is grademax here
            //Heres whats weird, why is settings not holding our max grade?
            //what are all of settins?
       
            $params['grademax'] = $maxgrade;
            $params['grademin'] = 0;
            if ($maxgrade) {
                $params['gradetype'] = GRADE_TYPE_VALUE;//do we have this constant??
                $params['grademax'] = $maxgrade;
                $params['grademin'] = 0;
            } else {
                $params['gradetype'] = GRADE_TYPE_NONE;
            }
       /*  } else {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax'] = $cmi5launch->maxgrade;
            $params['grademin'] = 0;
        }
*/
        if ($grades === 'reset') {
            $params['reset'] = true;
            $grades = null;
        }


       // $grades = $record->grade;

        // Why are grades blank? Why is record not havving grades?
     /*  echo"<br>";
        echo "Record full is  are: ";
        var_dump($record);
        //So if grades arent gettin set in record...himmmm...thats an issue elsewhere righ?
        echo "<br>";
        // LEt's try a little debugging
        echo "<br>";
        echo "cmi5launch_grade_item_update called overall are: ";
        var_dump($overallgrade);
        echo "<br>";

        //Where  is the 179 coming from? 
    echo "<br>";
    echo" record id is: ";
    var_dump($record->id);
    echo "<br>"; */
    // Ok, the abov eis the 179
    // Then we have a record, so we need to retrieve it.
    $userscourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);
    //Maybe we needd the VERY specific usercourse?
        //A bool?

        ///what is overall grade?
       /* echo "<br>";
        echo "Overall grade is: ";
        var_dump($overallgrade);
        echo "<br>";
        */

        //if grades are SUPPOSED to be an array, even singular??? Does this mean we dont have to worry about brackets above?
   
    //The table course has TWO only two courses, Moodle- 1 and test-2. What id is this? How to grab this?
    /// Lets look at everything in a cmi5launch
    //echo "<br>";
    ///echo "CMI% launch is :";
   //var_dump($cmi5launch);
   /// echo "<br>";
    // Ok, look here at this part of cmi5
    //["course"]=> string(1) "2"
    //Yeay! cmi5launch->course is what we need as a param!
    //Now, to get the grades to show
    // ok and they don't want 'cmi5, here, they want what is in ['modulename] so - cmi5 Launch Link - or not that broke it!
        return grade_update('mod/cmi5launch', $cmi5launch->course, 'mod', 'cmi5launch', $cmi5launch->id, 0, $grades, $params);
    
        //From forum: grade_update('mod/cmi5launch', $cmi5launch->course, 'mod', 'cmi5launch', $cmi5launch->id, 0, $grades, $params);

        // Ok what is grade item update retunring?


    
    }


/**
 * Delete grade item for given scorm
 *
 * @category grade
 * @param object $scorm object
 * @return object grade_item
 */
/*
 function scorm_grade_item_delete($scorm) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/scorm', $scorm->course, 'mod', 'scorm', $scorm->id, 0, null, array('deleted' => 1));
}
*/