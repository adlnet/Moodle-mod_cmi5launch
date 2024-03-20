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
 * Launches the experience with the requested registration
 *
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_once("$CFG->dirroot/lib/outputcomponents.php");
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('header.php');

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

// Retrieve registration id and au index (from AUview.php).
$fromauview = required_param('launchform_registration', PARAM_TEXT);

// Break it into array (AU or session id is first index).
$idandstatus = explode(",", $fromauview);

// Retrieve AU OR session id.
$id = array_shift($idandstatus);

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

// Retrieve user's course record.
$userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

// Retrieve registration id.
$registrationid = $userscourse->registrationid;

if (empty($registrationid)) {
    echo "<div class='alert alert-error'>" . get_string('cmi5launch_regidempty', 'cmi5launch') . "</div>";

    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {

        echo "<p>Error attempting to get registration id querystring parameter.</p>";

        die();
    }
}

// To hold launch url.
$location = "";

// Retrieve AUs.
$au = $retrieveaus($id);

// Retrieve the au index.
$auindex = $au->auindex;

// Pass in the au index to retrieve a launchurl and session id.
$urldecoded = $cmi5launchretrieveurl($cmi5launch->id, $auindex);

// Retrieve and store session id in the aus table.
$sessionid = intval($urldecoded['id']);

// Check if there are previous sessions.
if (!$au->sessions == null) {
    // We don't want to overwrite so retrieve the sessions before changing them.
    $sessionlist = json_decode($au->sessions);
    // Now add the new session number.
    $sessionlist[] = $sessionid;

} else {
    // If it is null just start fresh.
    $sessionlist = array();
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

// Create and save session object to session table.
$savesession($sessionid, $location, $launchmethod);

// Last thing check for updates.
cmi5launch_update_grades($cmi5launch, $USER->id);

header("Location: ". $location);

exit;
