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


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('header.php');
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/sessionHelpers.php");


// Trigger Activity launched event.
$event = \mod_cmi5launch\event\activity_launched::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->trigger();

//Retrieve registration id and au index (from AUview.php)
$fromAUview = required_param('launchform_registration', PARAM_TEXT);

//Break it into array (AU or session id is first index)
$idAndStatus = explode(",", $fromAUview);

//Retrieve AU OR session id
$id = array_shift($idAndStatus);

//Ai are you there?
echo"<br>";
// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

//Nope now we need OUR thing

$exists = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

if($exists == false){

    //Record should exist, throw error message
    echo"<br>";
    echo "Error: User does not exist in this course";
    echo"<br>";

}else{

    $usersCourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);
}

//Retrieve registration id
$registrationid = $usersCourse->registrationid;

if (empty($registrationid)) {
    echo "<div class='alert alert-error'>".get_string('cmi5launch_regidempty', 'cmi5launch')."</div>";

    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to get registration id querystring parameter.</p>";
    }
    die();
}

//To hold launch url or whether launch url is new!
$location = "";

//If tture, this is a NEW launch
if ($idAndStatus[0] == "true") {
        
	//Retrieve AUs         
	$aus_helpers = new Au_Helpers;
	$getAUs = $aus_helpers->getAUsFromDB();
	$au = $getAUs($id);
		
	//to bring in functions from class cmi5Connector
	$connectors = new cmi5Connectors;
	$retrieveUrl = $connectors->getRetrieveUrl();

	//Now, we want the au index to pass in
	$auIndex = $au->auindex;

	//Now pass in the au index so we can retrieve a launchurl and session id
	$urlDecoded = $retrieveUrl($cmi5launch->id, $auIndex);
	echo"<br>";
	echo"What url is it retrieving?";
	var_dump($urlDecoded);
	echo"<br>";

	$sessionID = intval($urlDecoded['id']);
	
	//Check if there are previous sessions
    	if(!$au->sessions == NULL){
		//We don't want to overwrite so retrieve the sessions
		$sessionList = json_decode($au->sessions);
		//now add the new session number
		$sessionList[] = $sessionID;

   	}else{//It is null so just start fresh

		$sessionList = array();
		$sessionList[] = $sessionID;
   	}

	//Save sessions   
	$au->sessions =json_encode($sessionList );

    //The record needs to updated in db
   	$updated =  $DB->update_record('cmi5launch_aus', $au, true);

    //Url is the location we want to go
    $location = $urlDecoded['url'];
	$launchMethod = $urlDecoded['launchMethod'];

	$ses_helpers = new Session_Helpers;

	//We also want to make a session object and save to session table, 
	$saveSession = $ses_helpers->getSaveSession();
	$saveSession($sessionID, $location, $launchMethod);

} else {
    // Ok if it is false and this is a new session, we want to get the launch url from the sessions
    //table using the id passed over, which in this case is the session id
    
    //We want to retrieve the launchurl from sessions
    $session = $DB->get_record('cmi5launch_sessions',  array('sessionid' => $id));
    /*
	echo"<br>";
	echo "waht is session? is problem here?";
	var_dump($session);
	echo"<br>";
	*/
	
	$location = $session->launchurl;

} 

header("Location: ". $location);

exit;
