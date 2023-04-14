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
$getProgress = $progress->getRetrieveStatement();
$createAUs = $auHelper->getCreateAUs();
$connectors = new cmi5Connectors;
$tables = new cmi5Tables;

global $cmi5launch;

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

                mod_cmi5launch_launchexperience(registration);
          
            }
        }

        // Function to run when the experience is launched (on click).
        function mod_cmi5launch_launchexperience(registrationInfo) {

            // Set the form paramters.
            $('#AU_view').val(registrationInfo);

            // Post it.
            $('#launchform').submit();

            //TODO, remove these? 
            // Remove the launch links.
/*
            $('#cmi5launch_autable').remove();////
            $('#cmi5launch_newattempt').remove();
            $('#cmi5launch_attempttable').remove();
            $('#cmi5launch_attempt').remove();
  */
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
//This will change eventually, because we always want it to be one thing and that one thing generated
//on this page

//Start at 1, if continuing old attempt it will draw previous regid from LRS
//$registrationid = 1;

//THIS should be what we need! Moved it from launch.php


//to bring in functions from class cmi5Connector
$connectors = new cmi5Connectors;

//Get retrieve URL function
$retrieveUrl = $connectors->getRetrieveUrl();
//Build url to pass as returnUrl
$returnUrl = $CFG->wwwroot .'/mod/cmi5launch/view.php'. '?id=' .$cm->id;

//Because we want to use the cmi5 to create the reg id we need to get it from
//the launch url request. We will send it au id 0, this will create a base launch url
//on the whole course. since 0 is beginning index
$auID = 0;

//to bring in functions from class cmi5Connector
$connectors = new cmi5Tables;
$saveUrl = $connectors->getSaveURL();


//Retrieve launch URL from CMI5 player (this is the URL used to retrieve the course and regid)
$urlDecoded = $retrieveUrl($cmi5launch->id, $returnUrl, $auID); 

//Retreive the url from launch response
////Nermind, its decoded in retrievURl before returning
//$urlDecoded = json_decode($launchResponse, true);


$url = $urlDecoded['url'];


//urlInfo is one big string so
 parse_str($url, $urlInfo);

 //Cmi5 is always goin to return a regid, we only want the first one
 //So check if one has been saved yet, if NOT then csaaave
if ($record->registrationid == null) {



    //Retrieve registration id from end of parsed URL
    $registrationid = $urlInfo['registration'];
    ///AHA!! IT's making this dang thing everytime
//AHYA
//What is we check if record->registrationid has something there or not

    //Ok, the urlinfo we are passing in doesn't seem to be the same as what we are replacing
    //so like it looks like it is looking for info like the course return info, but it's ok to be blank right?
    //cause like...it's saving it in other places too. maybe wrap in a try catch?

    //Save the returned info to the correct table
    $saveUrl($cmi5launch->id, $urlDecoded, $returnUrl, $registrationid);


    //Now this is the regid we want to use THROUGHOUT, and we will
    //need to use the way we retreive as well to take off the old ones
    //because unfortauntely each launch request will come with regid

    //Ok, the regid is saved to the tableeee cmi5launch_player in the above
    // 'retrieveURL func. In it, after retreivin the url, it also saves infop to the taBLE WITH SAVEurls

    //Interesting, it is saving to CMI5Launch_player. I reckon we should save it  to cmi5l;aunch
//you know what? Lets just make a function if import record doesn't work! I mean we are just savin to a table, 
//no bigggee
    $table = "cmi5launch";
    //Update RegID
    $record->registrationid = $registrationid;
    //Update the DB
    $DB->update_record($table, $record, true);
}else{
    $registrationid = $record->registrationid;
}

echo "<br>";
echo "Ok, what is , we need to get regid?";
var_dump($registrationid);
echo "<br>";

//Ok, so what is being sent that we are etting a 404?

$getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
    "http://cmi5api.co.uk/stateapikeys/registrations"
);

//echo "OK, where is the reg bein made? what is registration data here? ";
//var_dump($getregistrationdatafromlrsstate);
//echo "<br>";

//I think we need to let it go forward on a 404, because it 404s if no data is saved yet
//But maybe now that the reg 

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

//if ($lrsrespond == 200) {

    //Get session info from LRS
    $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

    echo "OK, what does it have here?  ";
    var_dump($registrationdatafromlrs);
    echo "<br>";
//    echo "OK, does it have id ";
//var_dump($cmi5launch->id);
//echo "<br>";

    //We need id to get progress
    $cmid = $cmi5launch->id;

    ////Wait to see about meeting today, this may be needed. It's the start reg
    /*
    // Needs to come after previous attempts so a non-sighted user can hear launch options.
    if ($cmi5launch->cmi5multipleregs) {
    echo "<p id='cmi5launch_newattempt'><a tabindex=\"0\"
    onkeyup=\"key_test('".$registrationid ."')\" onclick=\"mod_cmi5launch_launchexperience('"
    . $registrationid 
    . "')\" style=\"cursor: pointer;\">"
    . get_string('cmi5launch_attempt', 'cmi5launch')
    . "</a></p>";
    }
    */

    //Create table
    $table = new html_table();
    $table->id = 'cmi5launch_autable';
    $table->caption = get_string('AUtableheader', 'cmi5launch');
    $table->head = array(
        get_string('cmi5launchviewAUname', 'cmi5launch'),
        get_string('cmi5launchviewstatus', 'cmi5launch'),
        get_string('cmi5launchviewregistrationheader', 'cmi5launch'),

    );

    //Get the LRS info
    $getLRS = $progress->getRequestLRSInfo();



    //Retrieve LRS session info
    $resultDecoded = $getLRS($registrationdatafromlrs, $cmid);

    //For each au
    foreach ($aus as $key => $item) {
echo"<br>";
    echo "Ok, what is key here? --------";
    var_dump($key);
    echo"<br>";
echo"And what is item??? @@@@@@";
    var_dump($item);
    echo"<br>";


        //Retrieve individual AU as array
        $au = (array) ($aus[$key]);

        //Verify object
        if (!is_array($au)) {
            $reason = "Excepted array, found " . $au;
            throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
        }

        //Retrieve AU's lmsID
        $auId = $au['lmsId'];
        
        
        //Loop through the statements and match with the LRS statments whose object/id matches the aus lmsID
        //Match on lmsId. This ties the au to the session info from LRS.
        //It matches Object->id from lrs chunked
        
        //Array to hold list of relevant registrations
        //todo
        //sOOOO, WE NOlonger need to keep a lis tof registrations but insteD
        //MAYBE ON LMS id? or CONTEXT>EXT>SESSIONid
        $relevantReg = array();

        //This is the info back from the lrs
        foreach ($resultDecoded as $result => $i) {
            //i is each separate statement
            //We don't know the regid, but need it because it's the first array key, 
            //so simply retrieve the key itself.
            //current regid
            $regid = array_key_first($i);

            //If the lmsId matches the object id, then this registration is applicable to this au 
            if ($auId == $i[$regid][0]["object"]["id"]) {

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
        } else {
            //If relevant registrations are not null, then it found some session ids. If those exist then this
            //AU has been launched and is therefore 'in progress' or 'completed'
            //If this IS NULL then the AU has not been attempted and we can mark it as such
            if (!$relevantReg == null) {

                $getCompleted = $progress->getCompletion();

                $completed = $getCompleted($auMoveon, $verbs);

                //If completed is returned true we move on. If not, its in progress
                if ($completed == true) {

                    $auStatus = "Completed";
                } else {

                    $auStatus = "In Progress";
                }

            }
            //If relevenat reg is null than this is not attmepted
            else {
                $auStatus = "Not attempted";
            }

        }

        //List of verbs that may apply toward completion
        $verbs = array();

        //Create array of info to place in table
        $auInfo = array();

        //Assign au name, progress, and index
        $auInfo[] = $au['title'][0]['text'];
        $auInfo[] = ($auStatus);
        $auIndex = $au['auIndex'];

        //ReleventReg and AU index needs to be a string to pass as variable to next page
        $regForNextPage = implode(',', $relevantReg);
        $infoForNextPage = $auIndex . "," . $regForNextPage;

        //Assign au link to auviews
        $auInfo[] = "<a tabindex=\"0\" id='cmi5relaunch_attempt'
    onkeyup=\"key_test('" . $infoForNextPage . "')\" onclick=\"mod_cmi5launch_launchexperience('" . $infoForNextPage . "')\" style='cursor: pointer;'>"
            . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>"
        ;

        //add to be fed to table
        $tableData[] = $auInfo;

    }
//This feeds the table, note registrationdatafromlrs is an OBJECT
$table->data = $tableData;

echo html_writer::table($table);
//} old if end



///////////////} 
/*
else {
*/
    //MB 
   //Check with Andy and Florian on this in meeting today. It's the start new registration button
   /*
   //Start new button/ if we keep this, just um be AU 0 and start from 0?
    //If we keep what auID do we want? 
   $auID = "0";
$registrationid = "1";
//If auid is first always then it doesn't matter how many reg
//they are naything after 0
$info = array("au" => $auID, "reg" => $registrationid);
$infoForNextPage = implode(",", $info);

   echo "<p tabindex=\"0\"
        onkeyup=\"key_test('" . $infoForNextPage . "')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $infoForNextPage .
        "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";

        /*
    }
*/

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