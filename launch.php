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
//How about if infofornext pae is like a string or not?
$fromAUview = required_param('launchform_registration', PARAM_TEXT);
/*
echo "<br>";
echo "Did it work? what is plain fromauview  : ";
var_dump($fromAUview);
echo "<br>";
echo "<br>";
*/
//Break it into array (AU is first index)
$regAndId = explode(",", $fromAUview);

//Retrieve AU ID
$auID = array_shift($regAndId);
//Ok, HERE! We can now go through our au array to get the ids,    then use THOSE to pull the info
// Array of ids => $auIDs
$aus_helpers = new Au_Helpers;
$getAUs = $aus_helpers->getAUsFromDB();
$au = $getAUs($auID);
/*
echo "<br>";
echo "Did it work? what is au id";
var_dump($auID);
echo "<br>";
echo "Did it work? what is  reg and id after explosion : ";
var_dump($regAndId);
echo "<br>";
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

//To hold launch url or whether launch url is new!
$location = "";
if ($regAndId[0] == "true") {
   // echo "Are we entering this if";
   echo "<br>";
   echo "Are   we making it to the starts this is fiirst if?";
        //Then this is a NEW launch
        
        //I....I..don't thinkkk we need this launchlink anymore
        //I now build it
        //$location = cmi5launch_get_launch_url($registrationid, $auID);
        //TODO - check with florian, but I thik this is outdated
        //Get retrieve URL function
 //to bring in functions from class cmi5Connector
$connectors = new cmi5Connectors;
        $retrieveUrl = $connectors->getRetrieveUrl();
//Now, we want the au index to pass in
    $auIndex = $au->auindex;
 
    //Now pass in the au index so we can retrieve a launchurl and session id
    //it can have the sessions saved to it
    $urlDecoded = $retrieveUrl($cmi5launch->id, $auIndex);

    //Make array?
   // $sessionList = array();
   
   //Check if its null or not to get rid of unneeccesary comma
   if(!$au->sessions == NULL){
    //We don't want to overwrite so retreive the sessions
    $sessionList = json_decode($au->sessions);
    //now add the new session number
    $sessionList = $sessionList . ',' .($urlDecoded['id'] );

   }else{//It  is null so just start fresh
//now add the new session number
$sessionList = ($urlDecoded['id'] );
   }


    echo "<br>";
    echo "What is sessionlist after being pulled straiht out of au->sessions$$#$##$#$#$$##$$#";
    var_dump($sessionList);
    echo "<br>";
    echo "<br>";
    echo "Mysql has trouble with arrays! Why is it an array? what is it strait";
    var_dump($urlDecoded['id']);
    echo "<br>";
   
    //BAM!~!
   //experiment, is the problem an arrya?
    echo "<br>";
    echo "What is sessionlist after being added onto#";
    var_dump($sessionList);
    echo "<br>";
   
//Oh and uh, reassign the sessionlist sheik smh
$au->sessions = json_encode($sessionList );

echo "<br>";
    echo "huh, well what is AU here?";
    var_dump($au);
    echo "<br>";
    echo "<br>";
   echo "Campare this to record, can it not see the id field???";
   var_dump($record);
   echo "<br>";
   //They LOOK the same, but does it only take a standard class object and not a freakin
   //au opbject? They are basically the same
    //The record needs to be saved!!!
   $updated =  $DB->update_record('cmi5launch_aus', $au, true);
   echo "<br>";
   echo "So the au is populated, what is the DB response??";
   var_dump($updated);
   echo "<br>";
         //Url is the location we want to go
    //    $location = $urlDecoded['url'];

} else {

    echo "<br>";
    echo "Are we in the else where is everyone?!?!?";
 

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

    //If this isn't 'true' this should be the launurl.
    //$location = $regAndId[0];
    
    //No, if this isn;t true it should be 
    
    echo "<br>";
    echo "to the end of this else? if so what is location";
    var_dump($location);
 

} //end else

//Nope. howabout we put the launch url HERE! and either get or retreive it depending on what was pushed@
header("Location: ". $location);

exit;
