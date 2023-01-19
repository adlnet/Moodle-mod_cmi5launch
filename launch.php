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
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


 //So WHO calls THIS? We need to go into this WITH a UUID already

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('header.php');
//I added this global, It was in a wrongfile, or it was in cmi5_launchsettings even though it wasn't
//in githib?-MB
global $cmi5launch;
// Trigger Activity launched event.
$event = \mod_cmi5launch\event\activity_launched::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->trigger();


$newRecord = $DB->get_record('cmi5launch', ['id' => $cmi5launch->id], '*', IGNORE_MISSING);


//MB//
//BUT, launchform_reg IS this file.... So it will probably be empty,
//Where is it getting the id from?
//A-from  script running behind the scenes in view.php. View.php calls this form, but
//in the meantime the regid is being generated. We need to get there. 
// Get the registration id.
//I think this is the error!!!! Lets get regid from table here? YES
//THIS MAY be a problem, but not the one I'm working on

/*
$registrationid = required_param('launchform_registration', PARAM_TEXT);
if (empty($registrationid)) {
    echo "<div class='alert alert-error'>".get_string('cmi5launch_regidempty', 'cmi5launch')."</div>";
    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {
        echo "<p>E'rror attempting to get registration id querystring parameter.</p>";
    }
    die();
}*/
//Can it acces the table here??
//It can but doesn't know the sessionid to go byyy...
//Florian thinks we can get it from lrs. /This may be better approach, how ele to keep
//track of sessionid??
////////////////////////////////////////////////////////////////////////////////////
//to bring in functions from class cmi5Connector
$connectors = new cmi5Connectors;
//create instance of class functions

$retrieveUrl = $connectors->getRetrieveUrl();

//$result = $retrieveUrl($actorName, $homepage, $returnUrl, $url, $token);


//echo "Trying to launch url with this id " . $cmi5launch->id;
//Lets try making our own regid here 
$urlResults = $retrieveUrl($cmi5launch->id);


$urlDecoded = json_decode($urlResults, true);

$url = $urlDecoded['url'];
		//urlInfo is one big string so
		parse_str($url, $urlInfo);
	
		$registrationid = $urlInfo['registration'];

//test array
parse_str($urlResults, $urlParsed);

//Perhaps we could have a func that creates/retreives a reguuid, and IT will call
//the retreive url func? Just want it to be succint. And where should the info be saved to table?
//Perhaps in func?
//But whatever that returns, the reg id is IN THE URL right? So should that be parsed here or in
//another


//Ok, here is our new regid
//$registrationid = substr($urlParsed['registration'],0, -2);


//$record = $DB->get_record('cmi5launch_player', ['id' => $cmi5launch->id,], '*', IGNORE_MISSING);

//$registrationid = $record->registrationid;

////////////////////////////////////////////////////////////////////////////////////
echo'<br>';
echo "THIS IS IN LAUNCH FORM. HERE regid IS : " . $registrationid; 
echo'<br>';
//$registrationid = $DB->get_record('cmi5launch_player', ['registrationid' => $registrationid,], '*', IGNORE_MISSING);

echo'<br>';
echo "THIS IS IN LAUNCH FORM. HERE regid IS : " . $registrationid; 
echo'<br>';

//This is from above
if (empty($registrationid)) {
    echo "<div class='alert alert-error'>".get_string('cmi5launch_regidempty', 'cmi5launch')."</div>";
    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {
        echo "<p>E'rror attempting to get registration id querystring parameter.</p>";
    }
    die();
}
//MB//
//Here we want to send OUR REGG
// Save a record of this registration to the LRS state API.

//Below they are commi=unicating with sate...hmmmm!
$getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
    "http://cmi5api.co.uk/stateapikeys/registrations"
);
$errorhtml = "<div class='alert alert-error'>".get_string('cmi5launch_notavailable', 'cmi5launch')."</div>";
$lrsrespond = $getregistrationdatafromlrsstate->httpResponse['status'];
if ($lrsrespond != 200 && $lrsrespond != 404) {
    // Failed to connect to LRS.
    echo $errorhtml;
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to get registration data from State API.</p>";
        echo "<pre>";
        var_dump($getregistrationdatafromlrsstate);
        echo "</pre>";
    }
    die();
}
if ($lrsrespond == 200) {
    $registrationdata = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);
} else {
    $registrationdata = null;
}
$registrationdataetag = $getregistrationdatafromlrsstate->content->getEtag();

$datenow = date("c");

$registrationdataforthisattempt = array(
    $registrationid => array(
        "created" => $datenow,
        "lastlaunched" => $datenow
    )
);


//Nothing! This is where the error is, so why can their id change and not mine???

//MB
//Getting error on  - Exception - Attempt to modify property "httpResponse" on null
//It's because if this isnull it can't have a property, but it is trying to 
//access property anyway/
if (is_null($registrationdata)) {
    // If the error is 404 create a new registration data array.
   /* if ($registrationdata->httpResponse['status'] = 404) {*/
        $registrationdata = $registrationdataforthisattempt;
   /* }*/
} else if (array_key_exists($registrationid, $registrationdata)) {
    // Else if the regsitration exists update the lastlaunched date.
    $registrationdata[$registrationid]["lastlaunched"] = $datenow;
} else { // Push the new data on the end.
    $registrationdata[$registrationid] = $registrationdataforthisattempt[$registrationid];
}

// Sort the registration data by last launched (most recent first).
uasort($registrationdata, function($a, $b) {
    return strtotime($b['lastlaunched']) - strtotime($a['lastlaunched']);
});

// TODO: Currently this is re-PUTting all of the data - it may be better just to POST the new data.
// This will prevent us sorting, but sorting could be done on output.
$saveresgistrationdata = cmi5launch_get_global_parameters_and_save_state(
    $registrationdata,
    "http://cmi5api.co.uk/stateapikeys/registrations",
    $registrationdataetag
);
$lrsrespond = $saveresgistrationdata->httpResponse['status'];
if ($lrsrespond != 204) {
    // Failed to connect to LRS.
    echo $errorhtml;
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to set registration data to State API.</p>";
        echo "<pre>";
        var_dump($saveresgistrationdata);
        echo "</pre>";
    }
    die();
}

$langpreference = array(
    "languagePreference" => cmi5launch_get_moodle_langauge()
);

$saveagentprofile = cmi5launch_get_global_parameters_and_save_agentprofile($langpreference, "CMI5LearnerPreferences");

$lrsrespond = $saveagentprofile->httpResponse['status'];
if ($lrsrespond != 204) {
    // Failed to connect to LRS.
    echo $errorhtml;
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to set learner preferences to Agent Profile API.</p>";
        echo "<pre>";
        var_dump($saveagentprofile);
        echo "</pre>";
    }
    die();
}

//I think this is throwing the error. it must not like my regid, why? Lets ivestigate
$savelaunchedstatement = cmi5_launched_statement($registrationid);

$lrsrespond = $savelaunchedstatement->httpResponse['status'];
if ($lrsrespond != 204) {
    // Failed to connect to LRS.
    echo $errorhtml;
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to send 'launched' statement.</p>";
        echo "<pre>";
        var_dump($savelaunchedstatement);
        echo "</pre>";
    }
    die();
}

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

//If this si where it calls for the launch URL, then perhaps the URL should
//be generatedin function. Or at least retreived if exists already-MB
// Launch the experience.
//////
//Because this is where the regid is generated, maybe we can call our func here?
//I don't want it having wrong uuid...

////Ok, so now we need itt saved to a table so below func can retreive

header("Location: ". cmi5launch_get_launch_url($registrationid));

exit;