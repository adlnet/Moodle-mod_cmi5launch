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
 * Prints a particular instance of cmi5launch
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

//For connecting to Progress class - MB
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/Progress.php");

//Classes for connecting to CMI5 player
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5_table_connectors.php");
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/ausHelpers.php");

    //bring in functions from classes cmi5Connector/Cmi5Tables
    $progress = new progress;
    $auHelper = new Au_Helpers;
    //bring in functions from class cmi5_table_connectors and AU helpers
    $getProgress = $progress->getRetrieveStatement();
    $createAUs = $auHelper->getCreateAUs();
    $connectors = new cmi5Connectors;
    $tables = new cmi5Tables;

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

global $cmi5launch;
//Take the results of created course and save new course id to table

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

//Retrieve saved AUs
$auList = json_decode($record->aus, true);
$aus = $createAUs($auList);


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
                //MBMBMBMBMB
                //This needs to be looked into, it is not loading auview correctly
                //problem with the id and course stuff in search params (at bottom of page)

                mod_cmi5launch_launchexperience(registration);
          
            }
        }
        
        //function to be run on onclick

        // Function to run when the experience is launched.
        function mod_cmi5launch_launchexperience(registration) {
            // Set the form paramters.
            $('#launchform_registration').val(registration);
            // Post it.
            $('#launchform').submit();
            // Remove the launch links.
            $('#cmi5launch_newattempt').remove();
            $('#cmi5launch_attempttable').remove();
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
        //MB
        //Someone elses TODO! But this IS in scope of THIS? PromiseRejectionEvent//
        //Maybe a good place to put the red/green/yellow update stuff
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php

//Start at 1, if continuing old attempt it will draw previous regid from LRS
$registrationid = 1;

$getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
    "http://cmi5api.co.uk/stateapikeys/registrations"
);

$lrsrespond = $getregistrationdatafromlrsstate->httpResponse['status'];

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

if ($lrsrespond == 200) {

    //Get session info from LRS
    $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);
	
    //We need id to get progress
	global $cmi5launch;
    $cmid = $cmi5launch->id;

    // Needs to come after previous attempts so a non-sighted user can hear launch options.
    if ($cmi5launch->cmi5multipleregs) {
        echo "<p id='cmi5launch_newattempt'><a tabindex=\"0\"
        onkeyup=\"key_test('".$registrationid ."')\" onclick=\"mod_cmi5launch_launchexperience('"
            . $registrationid 
            . "')\" style=\"cursor: pointer;\">"
            . get_string('cmi5launch_attempt', 'cmi5launch')
            . "</a></p>";
    }

//Here is where the table is outlined
$table = new html_table();
//MB
//I think I will change the table id, doesn't seem to be defined elsewhere
$table->id = 'cmi5launch_autable';
$table->caption = get_string('AUtableheader', 'cmi5launch');
$table->head = array(
    get_string('cmi5launchviewAUname', 'cmi5launch'),
    get_string('cmi5launchviewstatus', 'cmi5launch'),
    get_string('cmi5launchviewregistrationheader', 'cmi5launch'),

);


//Get the LRS info
$getLRS = $progress->getRequestLRSInfo();

//Array to hold info for table population
$tableData = array();

//Retrieve LRS session info
$resultDecoded = $getLRS($registrationdatafromlrs, $cmid);

//For each au
foreach ($aus as $key => $item) {
    //Retrieve individual AU as array
    $au = (array)($aus[$key]);

    //Verify object
    if (!is_array($au)) {
        $reason = "Excepted array, found " . $au;
        throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
    }

    //Match on lmsId. This ties the au to the session info from LRS.
    //It matches Object->id from lrs chunked
    $auId = $au['lmsId'];
	
    //Loop through the statements and match with the LRS statments whose object/id matches the aus lmsID
    //Array to hold list of relevant registrations
    $relevantReg = array();

    //This is the info back from the lrs
    foreach($resultDecoded as $result => $i){
            //i is each separate statment
            //We don't know the regid, but need it because it's the first array key, 
            //sosimply retrieve the key itself.
            //current regid
            $regid = array_key_first($i);

            //If the lmsId matches the object id, then this reg is applicable to this au 
            if($auId==$i[$regid][0]["object"]["id"]){

                //Therefore we want this verb
                $getVerb = $progress->retrieveVerbs($i, $regid);

                $verbs[] = $getVerb;

                $relevantReg[] = $regid;
            }
    }

    //Retreive AUs moveon specification
    $auMoveon = $au['moveOn'];
        //If moveon is not applicable, then we don't need to check it's progress, it's just viewed or not
        if ($auMoveon == "NotApplicable") {
                $auStatus = "viewed";
        }
        else{
    //If relevant registrations are not null, then it found some session ids. If those exist then this
    //AU has been launched and is therefore 'in progress' or 'completed'
    //If this IS NULL then the AU has not been attempted and we can mark it as such
        if (!$relevantReg == null) {

            $getCompleted = $progress->getCompletion();

            $completed = $getCompleted($auMoveon, $verbs);

        //If completed is returned true we move on. If not, its in progress
            if($completed == true){

                $auStatus = "Completed";
            }
            else{
            
                $auStatus = "In Progress";
            }

        }
        //If relevenat reg is null than this is not attmepted
        else{
                $auStatus = "Not attempted";
        }

    }
    
    //List of verbs that may apply toward completuion
    $verbs = array();

    //Create array of info to place in tablee
    $auInfo = array();

    //Assign au name and progress
    $auInfo [] = $au['title'][0]['text'];
    $auInfo[] = ($auStatus);

    //Assign au link to auviews
    $auInfo [] = "<a tabindex=\"0\" id='cmi5relaunch_attempt'
    onkeyup=\"key_test('". "view" ."')\" onclick=\"mod_cmi5launch_launchexperience('". "view ". "')\" style='cursor: pointer;'>"
    . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>"
    ;   
    
    //add to be fed to table
    $tableData[] = $auInfo;
   
}

//This feeds the table, note registrationdatafromlrs is an OBJECT
$table->data = $tableData;
//Ok, this makes the table:
echo html_writer::table($table);

} else {

    //No registrations, the table should be empty but still needs to be created
    echo "<p tabindex=\"0\"
        onkeyup=\"key_test('".$registrationid."')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $registrationid
        . "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";
}


// Add a form to be posted based on the attempt selected.
//I don't think we need this, posting a form would be to activate launch.php and
//we are really just linking yeah? 
?>

 
    <form id="launchform" action="AUview.php" method="get">
        <input id="AU_view" name="AU_view" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>

<?php

echo $OUTPUT->footer();