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
 * Prints an AUs session information annd allows retreival of session or start of new one. 
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

//For connecting to Progress class - MB
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/Progress.php");

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

// Print the page header.
$PAGE->set_url('/mod/cmi5launch/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cmi5launch->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->jquery();

// Output starts here.
echo $OUTPUT->header();


if ($cmi5launch->intro) { // Conditions to show the intro can change to look for own settings or whatever.
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
        
            if (event.keyCode === 13 || event.keyCode === 32) {
                mod_cmi5launch_launchexperience(registration);
          
            }
        }
        
        // Function to run when the experience is launched.
        function mod_cmi5launch_launchexperience(registration) {
            // Set the form paramters.
            $('#launchform_registration').val(registration);
            // Post it.
            $('#launchform').submit();
            
            //MB
            //Still needed?
            /* 
            // Remove the launch links.
            $('#cmi5launch_newattempt').remove();
            $('#cmi5launch_auSessionTable').remove();
            */
            
            //Add some new content.
            if (!$('#cmi5launch_status').length) {
                var message = "<?php echo get_string('cmi5launch_progress', 'cmi5launch'); ?>";
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

        // TODO: there may be a better way to check completion. Out of scope for current project.
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php


//Retrieve the registration AND au ID from view.php
$fromView = required_param('AU_view', PARAM_TEXT);
//Break it into array (AU is first index)
$regAndId = explode(",", $fromView);
//Retrieve AU ID
$auID = array_shift($regAndId);

echo "<br>";
echo"Okdokey what is AU ID? What is it coming from the previous pae as?";
var_dump($auID);
echo "<br>";

//TODO
//Maybe the only thing we need then is to retreive the AUID or sessionid and pull in
//the regid rom table

 // Reload cmi5 instance.
 $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
//Ok what is record here?
$regid = $record->registrationid;
//TODO.
//We may just need this, which means adjust above so its only expecting au, whichever seems easier
echo "<br>";
echo "Ok, what is record, we need to get regid? Why???";
var_dump($regid);
echo "<br>";

//MB
//hmmm now it will never be null, it  will be one

//If there were no regids there will be an array with 1 element left 
//That element will be an empty string.
//If regid does not equal `1' it is not a new one so go ahead and look for object id
if ($regid != 1) {
//    $regAndId = null; //There are no relevant registrations

    echo "<br>";
    echo "Ok, are we coming into number 1? 111111111111";
    var_dump($regAndId);
    echo "<br>";




    //If it is NOT null there are relevent registrations
//And now we are using object id
    if (!$regAndId == null) {
        echo "<br>";
        echo "Ok, are we coming into number 2?   222222222222222222222";
        var_dump($regAndId);
        echo "<br>";
        //Will we need some better way to know what to show? like session ids in place of regids? 
        //Li change previous page and thiss one to have a sessionId array not a regid array

        //Ok so ideally going forward this should not be one, it should be
        //the one that is created on previous page view.php
        //I'll take it out now, crashing is probably better than creating new ids.
        /*
        //Start at 1, if continuing old attempt it will draw previous regid from LRS
        $registrationid = 1;
        */
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
        //bring in functions from classes cmi5Connector/Cmi5Tables
        $progress = new progress;
        $getProgress = $progress->getRetrieveStatement();

        if ($lrsrespond == 200) {

            $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

            //Array to hold verbs and be returned
            $progress = array();

            //Remove dupliicate registration IDs (now it removes dupe object ids)
            $regAndId = array_unique($regAndId);

            //Array to hold info for table population
            $tableData = array();

            //Build table
            $table = new html_table();
            $table->id = 'cmi5launch_auSessionTable';
            $table->caption = get_string('modulenameplural', 'cmi5launch');
            $table->head = array(
                get_string('cmi5launchviewfirstlaunched', 'cmi5launch'),
                get_string('cmi5launchviewlastlaunched', 'cmi5launch'),
                get_string('cmi5launchviewprogress', 'cmi5launch'),
                get_string('cmi5launchviewlaunchlinkheader', 'cmi5launch'),
            );

            //Ok here, so there is nothing innnn the dan
            //Loop through unique registration ids
            //No longer loop, just use regid ?
            //We still need a way to sort info from lrs, something to separate aus 
//maybe the lmsid???
            foreach ($regAndId as $regId) {

                //array to hold data for table
                $sessionInfo = array();

                $sessionInfo[] = date_format(
                    date_create($registrationdatafromlrs[$regId]['created']),
                    'D, d M Y H:i:s'
                );
                $sessionInfo[] = date_format(
                    date_create($registrationdatafromlrs[$regId]['lastlaunched']),
                    'D, d M Y H:i:s'
                );

                echo "<br>";
                echo "Okdokey what is AU ID? Wht is it goin tom the next page as?";
                var_dump($auID);
                echo "<br>";

                //Create a string to pass the AU ID and registration to next page (launch.ph)
                $infoForNextPage = $auID; // . "," . $regId;

                $sessionInfo[] =
                    ("<pre>" . implode("\n ", $getProgress($regId, $cmi5launch->id)) . "</pre>");

                $sessionInfo[] =
                    "<a tabindex=\"0\" id='cmi5relaunch_attempt'
                onkeyup=\"key_test('" . $infoForNextPage . "')\" onclick=\"mod_cmi5launch_launchexperience('" . $infoForNextPage . "')\" style='cursor: pointer;'>"
                    . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>"
                ;

                //add to be fed to table
                $tableData[] = $sessionInfo;
            }

            $table->data = $tableData;


            echo html_writer::table($table);

            //This builds the start new reg button - MB
            // Needs to come after previous attempts so a non-sighted user can hear launch options.
            if ($cmi5launch->cmi5multipleregs) {
                echo "<p id='cmi5launch_newattempt'><a tabindex=\"0\"
            onkeyup=\"key_test('" . $infoForNextPage . "')\" onclick=\"mod_cmi5launch_launchexperience('"
                    . $infoForNextPage
                    . "')\" style=\"cursor: pointer;\">"
                    . get_string('cmi5launch_attempt', 'cmi5launch')
                    . "</a></p>";
            }

        }
        //Honestly is this needed here? 
        /*else {
        //This is a new attempt, set registraion id to one
        $registrationid = 1;
        echo "<p tabindex=\"0\"
        onkeyup=\"key_test('" . $registrationid . "')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $registrationid
        . "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";
        }*/
    }
}
//If it is one it is new request
//elseif($regid == 1) {
    echo "<br>";
    echo "Ok, are we coming into number 3 (elseif) ?   333333333333";
    var_dump($regid);
    echo "<br>";
       //Ok so ideally going forward this should not be one, it should be
    //the one that is created on previous page view.php
    //I'll take it out now, crashing is probably better than creating new ids.
    /*
    //Start at 1, if continuing old attempt it will draw previous regid from LRS
    $registrationid = 1;
*/

    //Create a string to pass the auid and reg to next page (launch.php)
    $infoForNextPage = $auID; // . "," . $regId;
   

    echo "<p tabindex=\"0\"
        onkeyup=\"key_test('" . $infoForNextPage . "')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $infoForNextPage
        . "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";
//}


// Add a form to be posted based on the attempt selected.
?>
    <form id="launchform" action="launch.php" method="get" target="_blank">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>
<?php

echo $OUTPUT->footer();
