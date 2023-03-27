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

//MB
    //bring in functions from classes cmi5Connector/Cmi5Tables
    $progress = new progress;
    $auHelper = new Au_Helpers;
    //bring in functions from class cmi5_table_connectors
    $getProgress = $progress->getRetrieveStatement();
    $createAUs = $auHelper->getCreateAUs();
    //Bring in functions
    //bring in functions from classes cmi5Connector/Cmi5Tables
    $connectors = new cmi5Connectors;
    $tables = new cmi5Tables;
    //bring in functions from class cmi5_table_connectors
    //$createCourse = $connectors->getCreateCourse();
//    $retrieveAus = $connectors-> getRetrieveAus();
//Why are we creating a record here? They are already made...
//
  //  $populateTable = $tables->getPopulateTable();

//MB
//Do we still need this?
// Trigger module viewed event.
$event = \mod_cmi5launch\event\course_module_viewed::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

// Print the page header.
//MB
//This seems ok, we still want the course name, we are going to have aus
//on the next page
$PAGE->set_url('/mod/cmi5launch/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cmi5launch->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->jquery();

// Output starts here.
echo $OUTPUT->header();

global $cmi5launch;
//Take the results of created course and save new course id to table
//Load a record and parse for aus
//MB
//Hmmmmm should this be it's own
// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
//Retrieve the saved course results
//Omg, here is the prob I am not actually accessing db!
//I am doing it backlwards! lol
//Retrieve saved AU
$auList = json_decode($record->aus, true);
/*
echo"<br>";
echo"What is auList being returned as?" ;
var_dump($auList);
echo"<br>";
*/
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
                //Ok, so we DON't want this right? This might be where
                //its good to put in our redirect!!
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
        //Someone elses TODO! But this IS in scope of THSI PromiseRejectionEvent//
        //Maybe a good place to put the red/green/yellow update stuff
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php

//Mb
//Actually! We DO for like progress? Unless we store tht elsewhere! Like should it be checked
//So we can
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
//MB
 

//  bring in functions from classes cmi5Connector/Cmi5Tables/Progress
    $progress = new progress;

    //
   // $getProgress = $progress->getRetrieveStatement();

    //IT helps to CALL the function sheik lol
   ////////////// $currentProgress = $getProgress($regId, $id);

//MB
//Ok, here is where I want to put progress in the tables here.
//so here is a good place to see what params are available to pass in,
//Hey! IS regid a tatmentid???
if ($lrsrespond == 200) {

    //Get session info from LRS
    $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);
	
    //we need id to get progress
	global $cmi5launch;
    $id = $cmi5launch->id;

   
    // Needs to come after previous attempts so a non-sighted user can hear launch options.
    if ($cmi5launch->cmi5multipleregs) {
        echo "<p id='cmi5launch_newattempt'><a tabindex=\"0\"
        onkeyup=\"key_test('".$registrationid ."')\" onclick=\"mod_cmi5launch_launchexperience('"
            . $registrationid 
            . "')\" style=\"cursor: pointer;\">"
            . get_string('cmi5launch_attempt', 'cmi5launch')
            . "</a></p>";
    }
    
   // echo"What iss the returned data here and how can we use it?";
    //var_dump($getregistrationdatafromlrsstate);
    //echo"<br>";
//

//Here is whercontent/protecred seems to be a niv elong string
//or array holding info basically, regid, started, 

//Here is where the table is outlined
//Here is where I can change the headers
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

///////////////////
////Lets test
//$cmi5launch;

//bring in functions from class cmi5_table_connectors
//Get retrieve statment works but not getRetrieveProgress cause its in progress	

//THIS is what works in auview lets see what it returns
 $getProgress = $progress->getRetrieveStatement();

//This is new one that takes lrs info and sorts regids athen sends to lrs
  ////// $getProgress = $progress->getRetrieveProgress();

//$getCompletion = $progress->getCompletion();
	////$getCom = $progress->getRequestCompleted();
    
    //LEts get the LRS info
    $getLRS = $progress->getRequestLRSInfo();


    $tableData = array();


$resultDecoded = $getLRS($registrationdatafromlrs, $id);


$resultChunked = array_chunk($resultDecoded, 1);




//For each au
foreach ($aus as $key => $item) {
    //Retrieve individual AU as array
    $au = (array)($aus[$key]);


    //Verify object
    if (!is_array($au)) {
        $reason = "Excepted array, found " . $au;
        throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
    }

    //We can match on lmsId!! IT matches Object->id from lrs chunked
    $auId = $au['lmsId'];
	
    //Loop through the statements and match with the LRS statments whose object/id matches the aus lmsID
    
    //array to hold list of relevant registrations
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

                //Therefore we want THIS verb
                $getVerb = $progress->retrieveVerbs($i, $regid);

            $verbs[] = $getVerb;

            //is the id NOT the regid? Is thats whats going on??
            //yEP! that was your prob! chnae below to regid, we want above to be what it is
            $relevantReg[] = $regid;
        }
    }
    var_dump($relevantReg == null);

    //So like if it is NA we can save time and just print NA on screen
    $auMoveon = $au['moveOn'];
        //This is different than whether to display in proress or not,
            //If the session hasn't been started there will be no data, so THAT
            //will be inprogress, this only applis IF THERE HAS BEEN data to determine if it is 
            //completd or pass or just 'in proress'
            //Then we don't need to worry or look into it at all!
            if ($auMoveon == "NotApplicable") {
                //If it is anything else then it needs to be investigated
                $auStatus = "viewed";
            }
            else{
                //Ok, it is something other than na, which means it is some form of 
                // completed and/or passed
    //If relevant re is not null, then it found some session ids. If those exist then this
    //au has been launched and is therefore 'in progress' or 'completed'
    //If this IS NULL then the au has not been attempted and we can mark it as such
        if (!$relevantReg == null) {

            
            $completed = $progress->getCompletion();

            $com = $completed($auMoveon, $verbs);

        //If com is returned true we moveON! if not, its in progress
            if($com == true){
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
            //formatted this way: CompletedOrPassed or CompletedAndPassed, etc
            //var_dump($auMoveon)

            //List of verbs that may apply toward completuion
            $verbs = array();

        }
        

    //Create array of wanted info
    $auInfo = array();
    $auInfo [] = $au['title'][0]['text'];

    //Ok lets see if this works
//    $auInfo[] = "Put progress here!";
    $auInfo[] = ($auStatus);


    $auInfo [] = "<a tabindex=\"0\" id='cmi5relaunch_attempt'
    onkeyup=\"key_test('". "view" ."')\" onclick=\"mod_cmi5launch_launchexperience('". "view ". "')\" style='cursor: pointer;'>"
    . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>"
    ;   
    
    //add to be fed to table
    $tableData[] = $auInfo;
   
}
//This feeds the table, note registrationdatafromlrs is anOBJECT, so maybe I can foreach loop through au objects
$table->data = $tableData;
//Ok, this makes the table:
echo html_writer::table($table);

} else {

    //Forgot when their is nothing the table should be empty but still needs to be reated
    //$tableData = array();
//wait
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