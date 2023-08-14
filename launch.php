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
 * launches the experience with the requested registration
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 use mod_cmi5launch\au;
 use mod_cmi5launch\local\progress;
 use mod_cmi5launch\local\course;
 use mod_cmi5launch\local\cmi5_connectors;
 use mod_cmi5launch\local\au_helpers;
 use mod_cmi5launch\local\session_helpers;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('header.php');

global $CFG, $cmi5launch, $USER, $DB;

// Trigger Activity launched event.
$event = \mod_cmi5launch\event\activity_launched::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->trigger();

//External class and funcs to use
$aus_helpers = new au_helpers;
$connectors = new cmi5_connectors;
$ses_helpers = new session_helpers;

$saveSession = $ses_helpers->cmi5launch_get_create_session();
$cmi5launch_retrieve_url = $connectors->cmi5launch_get_retrieve_url();
$getAUs = $aus_helpers->get_cmi5launch_retrieve_aus_from_db();

//Retrieve registration id and au index (from AUview.php)
$fromAUview = required_param('launchform_registration', PARAM_TEXT);

//Break it into array (AU or session id is first index)
$idAndStatus = explode(",", $fromAUview);

//Retrieve AU OR session id
$id = array_shift($idAndStatus);

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

//Retrieve user's course record
$usersCourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);


//Retrieve registration id
$registrationid = $usersCourse->registrationid;

if (empty($registrationid)) {
	echo "<div class='alert alert-error'>" . get_string('cmi5launch_regidempty', 'cmi5launch') . "</div>";

	// Failed to connect to LRS.
	if ($CFG->debug == 32767) {

		echo "<p>Error attempting to get registration id querystring parameter.</p>";

		die();
	}
}
//To hold launch url or whether launch url is new!
$location = "";

//If true, this is a NEW launch
if ($idAndStatus[0] == "true") {
        
	//Retrieve AUs         
	$au = $getAUs($id);

	//Retrieve AU index
	$auIndex = $au->auindex;

	//Pass in the au index to retrieve a launchurl and session id
	$urlDecoded = $cmi5launch_retrieve_url($cmi5launch->id, $auIndex);

	//Retrieve and store session id in the aus table
	$sessionID = intval($urlDecoded['id']);
	
	//Check if there are previous sessions
    	if(!$au->sessions == NULL){
		//We don't want to overwrite so retrieve the sessions
		$sessionList = json_decode($au->sessions);
		//now add the new session number
		$sessionList[] = $sessionID;

   	}else{
		//It is null so just start fresh
		$sessionList = array();
		$sessionList[] = $sessionID;
   	}

	//Save sessions   
	$au->sessions =json_encode($sessionList );

     //The record needs to updated in db
   	$updated =  $DB->update_record('cmi5launch_aus', $au, true);

     //Retrieve the launch url
	$location = $urlDecoded['url'];
	//And launch method
	$launchMethod = $urlDecoded['launchMethod'];

	//Create and save session object to session table
	$saveSession($sessionID, $location, $launchMethod);

} else {
    //This is a new session, we want to get the launch url from the sessions
    $session = $DB->get_record('cmi5launch_sessions',  array('sessionid' => $id));
	
    //Launch url isss in old session record
    $location = $session->launchurl;
} 

header("Location: ". $location);

exit;