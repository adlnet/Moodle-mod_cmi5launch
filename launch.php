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


/*
//this will change cause there will only be ONE regid going forward
//Break it into array (AU is first index)
$regAndId = explode(",", $fromAUview);
//Retrieve the AU ID 
$auID = array_shift($regAndId);
//Now the registration ID, it should be the first element in the array after AU ID was taken
$registrationid = $regAndId[0];
*/


//Break it into array (AU is first index)
$regAndId = explode(",", $fromAUview);
//Retrieve AU ID
$auID = array_shift($regAndId);

/*
echo "<br>";
echo"Okdokey what is AU ID? What is it coming from the previous pae as? THIS IS LAUNCH";
var_dump($auID);
echo "<br>";
*/

 // Reload cmi5 instance.
 $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
//Ok what is record here?
$registrationid = $record->registrationid;



if (empty($registrationid)) {
    echo "<div class='alert alert-error'>".get_string('cmi5launch_regidempty', 'cmi5launch')."</div>";

    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to get registration id querystring parameter.</p>";
    }
    die();
}
//todo
//this will change, it shoulkd never be one now???
//or wait!
 //is this what needs to be moved to view.php??

//If it's 1 than the "Start New Registration" was pushed
//TODO
//This won't ever be one, it will be the one reg, so I guess we need to check
//if its null or not? 
if ($registrationid == 1) {

    //Ok, if its one than we need to get the 'id' returned with the launch url request and 
    //use it to save reggid to tabkle
    //So THIS stays here? Right, this changes based on AU

    //Maybe her eit can retrieve the reid from the table instead of generating

} else {

    // Save a record of this registration to the LRS state API.
    $getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
        "http://cmi5api.co.uk/stateapikeys/registrations"
    );
    $errorhtml = "<div class='alert alert-error'>" . get_string('cmi5launch_notavailable', 'cmi5launch') . "</div>";
    $lrsrespond = $getregistrationdatafromlrsstate->httpResponse['status'];

    //Unable to connect to LRS
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
    //Successfully connected to LRS
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

    //Getting error on  - Exception - Attempt to modify property "httpResponse" on null
//It's because if this isnull it can't have a property, but it is trying to 
//access property anyway
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
    uasort($registrationdata, function ($a, $b) {
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
    /*
    Moodle used to send a launched statement to LRS. This is no longer needed as CMI%
    player handles the tracking. - MB 1/27/23
    MB - BUT will this help now that we are replacing regid? - 4-17-23
    */
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
    //So 204 is nortmal! This part is ok
/*
elseif ($lrsrespond != 404){
    //Um, could this help, like if we DON't want it to die?
    //but shouldn't it die rather than go on?
    echo "<p>getting a 404</p>";
    echo "<pre>";
    var_dump($lrsrespond);
    echo "</pre>";
    //Wait! ItDOES seem to equal 302, is it not seeing it? Maybe its returned in a diff
    //category since i fiddles with things?

}
//////*/
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

    //Is it this????
//I think it may be!!!

} //end else

header("Location: ". cmi5launch_get_launch_url($registrationid, $auID));

exit;

