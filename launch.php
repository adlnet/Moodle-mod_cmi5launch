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
 * Launches the experience with the requested registration number.
 * The cmi5 player does the actual playing. This file enables handling of launch url from the player and data saving.
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\customException;
use mod_cmi5launch\local\session_helpers;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');


// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

require_login($course, false, $cm);

global $CFG, $cmi5launch, $USER, $DB;

// MB - currently not utilizing events, but may in future.
/*
// Trigger Activity launched event.
$event = \mod_cmi5launch\event\activity_launched::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->trigger();
*/

// External class and funcs to use.
$auhelper = new au_helpers;
$connectors = new cmi5_connectors;
$sessionhelper = new session_helpers;

$savesession = $sessionhelper->cmi5launch_get_create_session();
$cmi5launchretrieveurl = $connectors->cmi5launch_get_retrieve_url();
$retrieveaus = $auhelper->get_cmi5launch_retrieve_aus_from_db();

// Retrieve the registration id from previous page.
$id = required_param('launchform_registration', PARAM_TEXT);

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', ['id' => $cmi5launch->id]);

// Retrieve user's course record.
$userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

// To hold launch url.
$location = "";

// Most of the functions below have their own error handling.
// We will encapsulate here in case there are any php errors, such as json_decode not working, etc.
// Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
set_error_handler('mod_cmi5launch\local\custom_warning', E_WARNING);

try {
    // Retrieve AUs.
    $au = $retrieveaus($id);

    // Retrieve the au index.
    $auindex = $au->auindex;

    // Pass in the au index to retrieve a launchurl and session id.
    $urldecoded = $cmi5launchretrieveurl($cmi5launch->id, $auindex);

    // Retrieve and store session id in the aus table.

    $sessionid = intval($urldecoded['id']);

    // Failsafe here to keep from null value.
    if ($sessionid == null) {
        // Restore default handlers.
        restore_exception_handler();
        restore_error_handler();

        // Throw exception.
        throw new customException(get_string('cmi5launchsessionerror', 'cmi5launch') );
    }
    // Check if there are previous sessions.
    if (!$au->sessions == null) {
        // We don't want to overwrite so retrieve the sessions before changing them.
        $sessionlist = json_decode($au->sessions);
        // Now add the new session number.
        $sessionlist[] = $sessionid;
    } else {
        // If it is null just start fresh.
        $sessionlist = [];
        $sessionlist[] = $sessionid;
    }

    // Save sessions.
    $au->sessions = json_encode($sessionlist);

    // The record needs to updated in DB.
    $updated = $DB->update_record('cmi5launch_aus', $au, true);

    // Retrieve the launch url.
    $location = $urldecoded['url'];
    // And launch method.
    $launchmethod = $urldecoded['launchMethod'];
} catch (\Throwable $e) {
    // Restore default handlers.
    restore_exception_handler();
    restore_error_handler();

    // If there is an error, return the error.
    throw new customException(get_string('cmi5launcherror', 'cmi5launch') . $e->getMessage());
}
// Create and save session object to session table.
$savesession($sessionid, $location, $launchmethod);

// Last thing check for updates.
cmi5launch_update_grades($cmi5launch, $USER->id);

header(get_string('cmi5launchlocationheader', 'cmi5launch') . $location);



// Restore default handlers.
restore_exception_handler();
restore_error_handler();

exit;
