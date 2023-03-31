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
            $('#cmi5launch_auSessionTable').remove();
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

//TODO
//Ask florian if he thinks LRS should be queired on this pae or only the previous for speed?
//Seeing as browser opens class in diff window, might bee good to query on BOTH pages for updates

//Ok, so lets see if this helps with only displaying the relevant regs
    //
    //I think this brings it over from previous ppae
    //and now should we decode? YES! To make it array
   // $relevantReg = unserialize(required_param('AU_view', PARAM_TEXT), true );
//try with explode



$fromView = required_param('AU_view', PARAM_TEXT);
$regAndId = explode(",", $fromView);

//First or 0 is always auid
//$auID = $viewArray[0];
//maybe pop or somehting to take first and rest be the same array? 
$auID = array_shift($regAndId);


//If there were no regids there will be an array with 1 element left 
//That element will be an mepty string.
if ($regAndId[0] == "") {
    $regAndId = null;//There are no relevant registrations
}else{
    
} 

    //if (empty($registrationid)) {
  //  echo "<div class='alert alert-error'>" . get_string('cmi5launch_regidempty', 'cmi5launch') . "</div>";
//}
    //If it is NOT null there are relevent regs! To either retrieve from lrs or move over? Whats best to do this? Get
    //on previous page or requery? Cause I reckon 
if (!$regAndId == null) {
    

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

        $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

        //AHA! Wha tis registration data! Does it have an extra field!!!!
        echo "<br>";
        echo "What is registrationdatafrom lrs-----: ";
        var_dump($registrationdatafromlrs);
        echo "<br>";
        //Array to hold verbs and be returned
        $progress = array();
        echo "<br>";
        echo "Ok, but there were three in registration for whatever, howmay in the LOOP! ";
        var_dump($regAndId);
        //This may be it, there are ectra in loop cause it saves every reg id but some of these verbs are for the SAME one!
        //We need to maybe still loop through LRS
        //WE went this way cause we dont want ALL the data from lrs I think
        //Is there a way to remove dups from arrays????
        //lets try
        $regAndId = array_unique($regAndId);
        //Yep that was it! more regid than needed
        echo "<br>";

        ///////WAIT MB
        //What if we make a 'progress key' HERE. Then we can just pop later?
        //Or hell, pop here!!!!
        //Populate table with previous experiences
        global $cmi5launch;

        //Now what if we go through OUR array from previous page instead of theirs
        foreach($regAndId as $key){
        //        foreach ($registrationdatafromlrs as $key => $item) {
          /*  if (!is_array($registrationdatafromlrs[$key])) {
                $reason = "Excepted array, found " . $registrationdatafromlrs[$key];
                throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
            }*/
            array_push(
                $registrationdatafromlrs[$key],
                "<a tabindex=\"0\" id='cmi5relaunch_attempt'
                onkeyup=\"key_test('" . $key . "')\" onclick=\"mod_cmi5launch_launchexperience('" . $key . "')\" style='cursor: pointer;'>"
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
                ("<pre>" . implode("\n ", $getProgress($key, $cmi5launch->id)) . "</pre>");
            //   echo "<ul><li>" . implode("</li><li>", $getProgress) . "</li></ul>";
            //Dangit! But if we pass back array then it can't convert
            //HERE!!! dangit!
            //Do we need a foreach here to or in the getprogress? Only seems
            //to have most recent verb

        }

        
        //MB - below builds the table, so we need to add the header for progress here

        $table = new html_table();
        $table->id = 'cmi5launch_auSessionTable';
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

    
        //Nope not this either!
        $infoForNextPage = $auID . "," . $registrationid;
    
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
////////////////////////
        

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
else {
    
    //ITs not this one causing the problem! This is what appears on empty new ones.

    //This is a new attempt, set registraion id to one
    $registrationid = 1;

    //Create a string to pass the auid and reg to next pager (launch)
    $infoForNextPage = $auID . "," . $registrationid;


    echo "<p tabindex=\"0\"
        onkeyup=\"key_test('" . $infoForNextPage . "')\"
        id='cmi5launch_newattempt'><a onclick=\"mod_cmi5launch_launchexperience('"
        . $infoForNextPage
        . "')\" style=\"cursor: pointer;\">"
        . get_string('cmi5launch_attempt', 'cmi5launch')
        . "</a></p>";
/////////////////////////////////////////
}//End my trial if/else


// Add a form to be posted based on the attempt selected.
?>
    <form id="launchform" action="launch.php" method="get" target="_blank">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>
<?php

echo $OUTPUT->footer();
