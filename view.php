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

//Classes for connecting to Progress class - MB
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/Progress.php");


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
            // Remove the launch links.
            $('#cmi5launch_newattempt').remove();
            $('#cmi5launch_attempttable').remove();
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
	//bring in functions from classes cmi5Connector/Cmi5Tables
	$progress = new progress;

    //bring in functions from class cmi5_table_connectors
	$getProgress = $progress->getRetrieveStatement();

//MB
//Ok, here is where I want to put progress in the tables here.
//so here is a good place to see what params are available to pass in,
//Hey! IS regid a tatmentid???
if ($lrsrespond == 200) {
    echo"<br>";
    echo"<br>";
    echo"What is plain registrationdatafromlrsstate here?";
    //var_dump($getregistrationdatafromlrsstate);
    echo"<br>";
    echo"<br>";

    $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

    //Array to hold verbs and be returned
	$progress = array();


    ///////WAIT MB
    //What if we make a 'progress key' HERE. Then we can just pop later?
    //Or hell, pop here!!!!
    //Populate table with previous experiences
    global $cmi5launch;
    foreach ($registrationdatafromlrs as $key => $item) {
        if (!is_array($registrationdatafromlrs[$key])) {
            $reason = "Excepted array, found " . $registrationdatafromlrs[$key];
            throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
        }
        array_push(
            $registrationdatafromlrs[$key],
            "<a tabindex=\"0\" id='cmi5relaunch_attempt'
            onkeyup=\"key_test('".$key."')\" onclick=\"mod_cmi5launch_launchexperience('".$key. "')\" style='cursor: pointer;'>"
            . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>"
        );
        $registrationdatafromlrs[$key]['created'] = date_format(
            date_create($registrationdatafromlrs[$key]['created']),
            'D, d M Y H:i:s'
        );
        $registrationdatafromlrs[$key]['lastlaunched'] = date_format(
            date_create($registrationdatafromlrs[$key]['lastlaunched']),
            'D, d M Y H:i:s'
        );
        //YES!! Maybe I can have an array.push here and call my progress clas! So simple!!!
        $registrationdatafromlrs[$key]['progress'] = 
           ("<pre>". implode( "\n ", $getProgress($key, $cmi5launch->id)) ."</pre>" );
         //   echo "<ul><li>" . implode("</li><li>", $getProgress) . "</li></ul>";
            //Dangit! But if we pass back array then it can't convert
            //HERE!!! dangit!
            //Do we need a foreach here to or in the getprogress? Only seems
            //to have most recent verb
        
    }

    //Here is where it is making the table....so what we want is this
    //When you click on one of the rows (they are separate when you hover)
    //We want it to dropdown and reveal all that session history with our 
    //brand new progress getter. So we need to
    //MAke each row clickable and 
    //make each row able to drop down,
    //make the rows call the progress getter,
    //populate the dropped down row with the proggress 
   
    //MB - below builds the table, so we need to add the header for progress here

    $table = new html_table();
    $table->id = 'cmi5launch_attempttable';
    $table->caption = get_string('modulenameplural', 'cmi5launch');
    $table->head = array(
        get_string('cmi5launchviewfirstlaunched', 'cmi5launch'),
        get_string('cmi5launchviewlastlaunched', 'cmi5launch'),
        get_string('cmi5launchviewlaunchlinkheader', 'cmi5launch'),
        get_string('cmi5launchviewprogress', 'cmi5launch'),

    );

    //mb table data takes arrays, can I adjus theirs?
   // $candy = array("truffle" => "candycorn");
    //$registrationdatafromlrs = array_merge($registrationdatafromlrs, $candy);
    //The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
/////OOOOHHHHH registrationdatafromlrs is an OBJECT!!!!
//so a foreach here instead of a for???
    //$resultChunked = array_chunk($registrationdatafromlrs[0]["data"], 1);
        


    
    //Now we need 

    $table->data = $registrationdatafromlrs;
 
    //MB
    //This builds the table, it uses a moodle made fucntion to do so,
    //I'm going to see if its..wait, look above, it may use moodle method to build
    //the table BUT it builds it with the data above. 
    //So I can either try to adjust data above or try to write a script to activate
    //on clicking a row AFTER table built

    echo html_writer::table($table);

    //This builds the start new reg button - MB
    // Needs to come after previous attempts so a non-sighted user can hear launch options.
    if ($cmi5launch->cmi5multipleregs) {
        echo "<p id='cmi5launch_newattempt'><a tabindex=\"0\"
        onkeyup=\"key_test('".$registrationid ."')\" onclick=\"mod_cmi5launch_launchexperience('"
            . $registrationid 
            . "')\" style=\"cursor: pointer;\">"
            . get_string('cmi5launch_attempt', 'cmi5launch')
            . "</a></p>";
    }

} else {
    echo "<p tabindex=\"0\"
        onkeyup=\"key_test('".$registrationid."')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $registrationid
        . "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";
}

// Add a form to be posted based on the attempt selected.
?>
    <form id="launchform" action="launch.php" method="get" target="_blank">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>
<?php

echo $OUTPUT->footer();
