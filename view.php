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
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/Progress.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/course.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/ausHelpers.php");

//bring in functions from class Progress and AU helpers, Connectors
$progress = new progress;
$aus_helpers = new Au_Helpers;
$connectors = new cmi5Connectors;

global $cmi5launch, $USER, $mod;

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

// Reload cmi5 course instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

echo"br";
echo "<p> User is: " . $USER->username . "</p>";
echo"<br>";
echo"br";
echo "<p> User id: " . $USER->id . "</p>";
echo"<br>";
echo"br";
echo "<p> course id is: " . $record->courseid . "</p>";
echo"<br>";

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

//Check if a course record exists for this user yet
$exists = $DB->record_exists('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);


$saveAUs = $aus_helpers->getSaveAUs();
$createAUs = $aus_helpers->getCreateAUs();
$getAUs = $aus_helpers->getAUsFromDB();

//Whats happening is it is making new AUs EVERYtime.
//This page can be gotten to as both a new user and a returning user
//If a new user, we need to create AUs and save them to DB
//If a returning user, we need to retrieve AUs from DB
//We need to check if the user has AUs already, if not, create them
//This is creating if they don't have any


//If it does not exist, create it
if($exists == false){

    $usersCourse = new course($record);

    $usersCourse->userid = $USER->id;
    //Build url to pass as returnUrl
    $returnUrl = $CFG->wwwroot .'/mod/cmi5launch/view.php'. '?id=' .$cm->id;
    //Save the returnurl
    $usersCourse->returnurl = $returnUrl;

    //Assign new record an registration id
    $getRegistration = $connectors->getRegistrationPost();
    $registrationID = $getRegistration($record->courseid, $cmi5launch->id);
    //Save the registration to the users course object
    $usersCourse->registrationid = $registrationID;
    $aus = json_decode($record->aus);
    //SaveAus will need to take user id into account now, tweak it
    $auIDs = $saveAUs($createAUs($aus));
    $usersCourse->aus = (json_encode($auIDs));
    //Save new record to DB
    $DB->insert_record('cmi5launch_course', $usersCourse);

}else{

    //Then we have a record, so we need to retrieve it
    $usersCourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);
    //Retrieve registration id
    $registrationID = $usersCourse->registrationid; 

    //This is retrieving if they do have some
    $auIDs = (json_decode($usersCourse->aus) );
}



//Get info from cmi5 for Progress updates
//$registrationInfoFromCMI5 = $getRegistration($registrationID, $cmi5launch->id);

//Array to hold info for table population
$tableData = array();

//We need id to get progress
$cmid = $cmi5launch->id;

//Create table to display on page
$table = new html_table();
$table->id = 'cmi5launch_autable';
$table->caption = get_string('AUtableheader', 'cmi5launch');
$table->head = array(
	get_string('cmi5launchviewAUname', 'cmi5launch'),
	get_string('cmi5launchviewstatus', 'cmi5launch'),
    get_string('cmi5launchviewgradeheader', 'cmi5launch'),
	get_string('cmi5launchviewregistrationheader', 'cmi5launch'),

);
//TODO MB
//Return to for grades
//cmi5_update_grades($cmi5launch, 0);


//Lets now retrieve our list of AUs
//Cycle through and get each au ID
foreach($auIDs as $key  => $auID){
    
    //We may need to tweak this to, the aus id may be same?? no they shouldn't its sequential!!
    $au = $getAUs($auID);

    //Verify object is an au object
    if (!is_a($au, 'Au')) {
    
        $reason = "Excepted AU, found ";
        var_dump($au);
        throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
    }

    //Retrieve AU's lmsID
    $auLmsId = $au->lmsid;

    //we need to make $registrion date, is this the one that uses post?
    $getRegistrationInfo = $connectors->getRegistrationGet();
    $registrationInfoFromCMI5 = $getRegistrationInfo($registrationID, $cmi5launch->id);
    //Take only info about AUs out of registrationInfoFromCMI5
    $ausFromCMI5 = array_chunk($registrationInfoFromCMI5["metadata"]["moveOn"]["children"], 1, true);

    //TODO now we can get the AU's satisifed FROM the CMI5 player
    //TODO (for that matter couldn't we make it, notattempetd, satisifed, not satisfied??)

    foreach($ausFromCMI5 as $key => $auInfo){

        if ($auInfo[$key]["lmsId"] == $auLmsId){
            //Grab it's 'satisfied' info
            $auSatisfied = $auInfo[$key]["satisfied"];
        }
    }
    
    //If the 'sessions' in this AU are null we know this hasn't even been attempted
    if($au->sessions == null ){

        $auStatus = "Not attempted";
        
    }else{
        
        //Retrieve AUs moveon specification
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
            }
            else {

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
       
        //Ok, now we need to retreive the sessions and find the average score
        $grade = 0;
        if($au->scores != null){

            $sessionScores = json_decode($au->scores); 
    
            //TODO MB
            //Are we sure we want average here?
            $grade = array_sum($sessionScores) / count($sessionScores);
        }
    
        $au->grade = $grade;
        $auInfo[] = ($grade);
       
        $auIndex = $au->auindex;

      	//Send au id now
		 $infoForNextPage = $auID;
        
		//Assign au link to auviews
        $auInfo[] = "<a tabindex=\"0\" id='cmi5relaunch_attempt'
            onkeyup=\"key_test('" . $infoForNextPage . "')\" onclick=\"mod_cmi5launch_launchexperience('" . $infoForNextPage . "')\" style='cursor: pointer;'>"
            . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>";
    
		//add to be fed to table
        $tableData[] = $auInfo;
            
        //Maybe in view.php it does this same thing, saving to the student record instead.
    //so these stay as a 'master' record and the students tweak their own
   // $aus = ($retrieveAus($returnedInfo));
    //Maybe better to save AUs here and feed it the array returned by retreieveAUS
    //So maybe record just holds 'aus' and then the velow lines parses that, returnes ids to saves, and save saves to DB FOR students!!

	/////$auIDs = $saveAUs($createAUs($aus));
    ////$record->aus = (json_encode($auIDs));
    //See above is from lib.php and saves to AU table, but we can save to student record
    //and insert new ones here?
        
		//And lastly, update the au in DB
        //What makes this AU unique? So other students can't update it?
        //Well, their is a 'tenantname' if we change to a 'userid' they will be specific to the user
        //we will need to custom make the AU's for each user
		$DB->update_record("cmi5launch_aus", $au);
	}

//Lastly, update our course table
$updated = $DB->update_record("cmi5launch_course", $usersCourse);

//This feeds the table
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