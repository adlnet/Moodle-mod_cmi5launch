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
 * Displays the AU's of a course and their progress
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

//For connecting to Progress class 
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/Progress.php");

//Classes for connecting to CMI5 player
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5_table_connectors.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/ausHelpers.php");

//bring in functions from classes cmi5Connector/Cmi5Tables
$progress = new progress;
$auHelper = new Au_Helpers;
//bring in functions from class Progress and AU helpers
$createAUs = $auHelper->getCreateAUs();
$connectors = new cmi5Connectors;
$tables = new cmi5Tables;

global $cmi5launch,$user, $mod;

//MB NOTE
//So look here it is getting 'context' Is this were we can check teacher roles? z
// Trigger module viewed event.
$event = \mod_cmi5launch\event\course_module_viewed::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

$PAGE->set_url('/mod/cmi5launch/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cmi5launch->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->jquery();

// Output starts here.
echo $OUTPUT->header();

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

//to bring in functions from class cmi5Connector
$connectors = new cmi5Connectors;
$getRegistration = $connectors->getRegistrationGet();
$registrationInfoFromCMI5 = $getRegistration($record->registrationid, $cmi5launch->id);

if ($cmi5launch->intro) { 
    // Conditions to show the intro can change to look for own settings or whatever.
    echo $OUTPUT->box(
        format_module_intro('cmi5launch', $cmi5launch, $cm->id),
        'generalbox mod_introbox',
        'cmi5launchintro'
    );
}

// TODO: Put all the php inserted data as parameters on the functions and put the functions in a separate JS file.
?>

    <script>
      
        function key_test(registration) {
        
            //Onclick calls this
            if (event.keyCode === 13 || event.keyCode === 32) {

                mod_cmi5launch_launchexperience(registration);
            }
        }

        // Function to run when the experience is launched (on click).
        function mod_cmi5launch_launchexperience(registrationInfo) {

            // Set the form paramters.
            $('#AU_view').val(registrationInfo);

            // Post it.
            $('#launchform').submit();

            //Add some new content.
            if (!$('#cmi5launch_status').length) {
                var message = "<? echo get_string('cmi5launch_progress', 'cmi5launch'); ?>";
                $('#region-main .card-body').append('\
                <div id="cmi5launch_status"> \
                    <span id="cmi5launch_completioncheck"></span> \
                    <p id="cmi5launch_attemptprogress">' + message + '</p> \
                    <p id="cmi5launch_exit"> \
                        <a href="complete.php?id=<?php echo $id ?>&n=<?php echo $n ?>" title="Return to course"> \
                            Return to course \
                        </a> \
                    </p> \
                </div>\
            ');
            }
            $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
        }
        //*/

        // TODO: there may be a better way to check completion. Out of scope for current project.
        //MB - Someone elses todo, may be worth looking into
    
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php


//Lets now retrieve our list of AUs from cmi5launch
$auIDs = json_decode($record->aus);

//Build url to pass as returnUrl
$returnUrl = $CFG->wwwroot .'/mod/cmi5launch/view.php'. '?id=' .$cm->id;

//Should never be null
if ( $record->registrationid == null) {

	echo "<p>Error attempting to get registration ID from DB. Registration ID is :  </p>";
     echo "<pre>";
     var_dump($record->registrationid);
     echo "</pre>";
	
} else{
	//Get registration id from record (it was made when course was)
	$registrationID = $record->registrationid; 
}

    $table = "cmi5launch";
    //Save the returnurl
	$record->returnurl = $returnUrl;
    //Update the DB
    $DB->update_record($table, $record, true);

//Retreive LRS info    
$getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
    "http://cmi5api.co.uk/stateapikeys/registrations"
);
//Parse for http response
$lrsrespond = $getregistrationdatafromlrsstate->httpResponse['status'];

//Array to hold info for table population
$tableData = array();

if ($lrsrespond != 200 && $lrsrespond != 404) {
    // On clicking new attempt, save the registration details to the LRS State and launch a new attempt.
    echo "<div class='alert alert-error'>" . get_string('cmi5launch_notavailable', 'cmi5launch') . "</div>";

    if ($CFG->debug == 32767) {
        echo "<p>Error attempting to get registration data from State API.</p>";
        echo "<pre>";
       var_dump($getregistrationdatafromlrsstate);
        echo "</pre>";
    }
    die();
}

//Get session info from LRS
//If there is no previous attempts, this will return a 404 error, no state found.
//So we do not necessarily need a 200 response.
$registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

//We need id to get progress
$cmid = $cmi5launch->id;

//Create table
$table = new html_table();
$table->id = 'cmi5launch_autable';
$table->caption = get_string('AUtableheader', 'cmi5launch');
$table->head = array(
	get_string('cmi5launchviewAUname', 'cmi5launch'),
	get_string('cmi5launchviewstatus', 'cmi5launch'),
	get_string('cmi5launchviewregistrationheader', 'cmi5launch'),

);

// Retrieve courses AUs
$aus_helpers = new Au_Helpers;
$getAUs = $aus_helpers->getAUsFromDB();

	//Cycle through and get each au ID
    	foreach($auIDs as $key  => $auID){
       
    		$au = $getAUs($auID);
        
		//We now want an auobject. 
        	//Verify object is an au object
        	if (!is_a($au, 'Au')) {
            $reason = "Excepted AU, found ";
        	var_dump($au);
            throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
        	}

        //Retrieve AU's lmsID
        $auLmsId = $au->lmsid;

        //Take only info about AUs out of registrationInfoFromCMI5
        $ausFromCMI5 = array_chunk($registrationInfoFromCMI5["metadata"]["moveOn"]["children"], 1, true);

       //TODO now we can get the AU's satisifed FROM the CMI5 player
       //TODO (for that matter couldn't we make it, notattempetd, satisifed, not satisfied??)

     	//find the AU statement returned from cmi5 that matches this current au (match on lmsId)
     	foreach($ausFromCMI5 as $key => $auInfo){

          	if ($auInfo[$key]["lmsId"] == $auLmsId){
               	//Then this is the au info we want 
				//Grab it's 'satisfied' info
               	$auSatisfied = $auInfo[$key]["satisfied"];
	          }
        	}

        	//If the 'sessions' in this AU are null we know this hasn't even been attempted
        	if($au->sessions == null ){

	          $auStatus = "Not attempted";
	     
     	}else{
            
			//Retreive AUs moveon specification
			$auMoveon = $au->moveon;
            
            	//If it's been attempted but no moveon value
          	if ($auMoveon == "NotApplicable") {
               	$auStatus = "viewed";
	          }
            	//IF it DOES have a moveon value 
            	else {
               
				//If satisifed is returned true,  I
                	if ($auSatisfied == "true") {
                    	$auStatus = "Satisfied";
					
					//Also update AU
					$au->satisfied = "true";
                	}else {
					//If not, its in progress
					$auStatus = "In Progress";
					
					//Also update AU
					$au->satisfied = "false";
	               }
            	};
        	};

		//Create array of info to place in table
		$auInfo = array();

		//Assign au name, progress, and index
		$auInfo[] = $au->title;
		$auInfo[] = ($auStatus);
		$auIndex = $au->auindex;

      	//Send au id now
		 $infoForNextPage = $auID;
        
		//Assign au link to auviews
        	$auInfo[] = "<a tabindex=\"0\" id='cmi5relaunch_attempt'
    			onkeyup=\"key_test('" . $infoForNextPage . "')\" onclick=\"mod_cmi5launch_launchexperience('" . $infoForNextPage . "')\" style='cursor: pointer;'>"
            	. get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>";
        
		//add to be fed to table
        	$tableData[] = $auInfo;

		//And lastly, update the au in DB
		$DB->update_record("cmi5launch_aus", $au);
		
	}
	
//This feeds the table, note registrationdatafromlrs is an OBJECT
$table->data = $tableData;

echo html_writer::table($table);

// Add a form to be posted based on the attempt selected.
?>
    <form id="launchform" action="AUview.php" method="get">
        <input id="AU_view" name="AU_view" type="hidden" value="default">
        <input id="AU_view_id" name="AU_view_id" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>
<?php

echo $OUTPUT->footer();