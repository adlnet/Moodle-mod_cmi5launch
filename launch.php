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
//How about if infofornext pae is like a string or not?
$fromAUview = required_param('launchform_registration', PARAM_TEXT);

//Break it into array (AU or session id is first index)
$idAndStatus = explode(",", $fromAUview);

//Retrieve AU OR session id
$id = array_shift($idAndStatus);

 // Reload cmi5 instance.
 $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
//REtrieve registration id
 $registrationid = $record->registrationid;



if (empty($registrationid)) {
    echo "<div class='alert alert-error'>".get_string('cmi5launch_regidempty', 'cmi5launch')."</div>";

    // Failed to connect to LRS.
    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to get registration id querystring parameter.</p>";
    }
    die();
}

//If it's 'true' than the "Start New Registration" was pushed

//To hold launch url or whether launch url is new!
$location = "";

echo "<br>";
echo "What is coming over???";
echo "<br>";
echo "the ession id/au id  is ";
var_dump($id);
echo "<br>";
echo"and the leftover/ is new or not is?";
echo "<br>";
var_dump($idAndStatus[0]);
echo "<br>";
echo "<br>";

if ($idAndStatus[0] == "true") {
   // echo "Are we entering this if";
   echo "<br>";
   echo "Are   we making it to the starts this NEW LAUNCH";
        //Then this is a NEW launch
//Retrieve AU helper functions        
$aus_helpers = new Au_Helpers;
$getAUs = $aus_helpers->getAUsFromDB();

$au = $getAUs($id);
        //I....I..don't thinkkk we need this launchlink anymore
        //I now build it
        //$location = cmi5launch_get_launch_url($registrationid, $id);
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
   
    $sessionID = intval($urlDecoded['id']);
	//Check if there are previous sessions
    if(!$au->sessions == NULL){
    //We don't want to overwrite so retreive the sessions
    $sessionList = json_decode($au->sessions);
    //now add the new session number
    $sessionList[] = $sessionID;

   }else{//It  is null so just start fresh

	$sessionList = array();
	$sessionList[] = $sessionID;
   }

	//Save sessions   
		$au->sessions =json_encode($sessionList );

    //The record needs to be saved!!!
   $updated =  $DB->update_record('cmi5launch_aus', $au, true);

         //Url is the location we want to go
        $location = $urlDecoded['url'];
	$launchMethod = $urlDecoded['launchMethod'];

	   $ses_helpers = new Session_Helpers;
	   //So we also want to make a session object and save to session table, 
	$saveSession = $ses_helpers->getSaveSession();
	
	$saveSession($sessionID, $location, $launchMethod);

} else {
    // Ok if it is false and this is a new session, we want to get the launch url from the sessions
    //table using the id passed over, which in this case is the session id
    
	//IT was false and this is NOT a new sessions
    echo "Are we in the else where is everyone?!?!?";
 

    //Ok, this makes new stuff
    //Does it make sense to call get progres shere? sa well?
    //actually it may make more sense to just pass the url through, cause otherwise
    //it has to make the session object again, or does it? it can just read from record object reight?



    //Honestly, I don't know if we need any of this old code
    //UNLESS we want to keep the last launched???
    /*
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
            var_dump($getregistrationdatafromlr);//sstate);
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
        //$registrationdata = $registrationdataforthisattempt;
        /* }
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
    /*
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

//////*
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
*/
    //Is it this????
//I think it may be!!!

    //If this isn't 'true' this should be the launurl.
    //$location = $idAndStatus[0];
    
    //No, if this isn;t true it should be 
    
    //We WANT to retriev ethe launchurl from sessions
    
    $session = $DB->get_record('cmi5launch_sessions',  array('sessionid' => $id));

    echo "<br>";
    echo "to the end of this else? if so what is location";
    var_dump($location);
    $location = $session->launchurl;

} //end else

//Nope. howabout we put the launch url HERE! and either get or retreive it depending on what was pushed@
header("Location: ". $location);

exit;
