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

//MB//
//HA!!! There it is the event.keycode are the buttons!!!
//Oh! ITs a func! The key_test func might be how it decideds.
//but its called with OR, si it's called no matter what,
//but i can still change it to call my url

//Maybe we can put the isset into the key_test func? and it can check if regid is already dsaved or not?


// TODO: Put all the php inserted data as parameters on the functions and put the functions in a separate JS file.
?>

    <script>
        // Function to test for key press and call launch function if space or enter is hit.
        //Why is this an OR?? Is THIS the problem? Lets put our new thing under
        //this as an if
        //YES, cause the mod_cmi5launch_experiance is being called no matter whaat!!! 
      
        function key_test(registration) {
        
          
            if (event.keyCode === 13 || event.keyCode === 32) {
                mod_cmi5launch_launchexperience(registration);
          
            }
        }
        
        // Function to run when the experience is launched.
        function mod_cmi5launch_launchexperience(registration) {
            // Set the form paramters.
            //So this is called when BOTH buttons pushed, which then calls the launch form//This 
            //This means that the switch should be in launch???
            $('#launchform_registration').val(registration);

            ///THIS SHOULD ALLOW BUTTON PRESSED TO BE screen
        //    $('#launchform_button').val(button);
            //Could we use this to see what was clicked and pass info in like the regid?

            // Post it.
            $('#launchform').submit();
            //So above is creating with regid? This is what populates form?

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


 // Generate a registration id for any new attempt.
//$cmi5phputil = new \cmi5\Util();
//MB//
//$registrationid = $cmi5phputil->getUUID();

//Start at 0, if continuing old attempt it will draw previous regid
//from LRS
$registrationid = 1;
//Ok, we KNOW this works when done in launch.php except it works on both start new and launch,
//we only want it on sytart new, so trying to call it here
/////NOPE, this is causing it to make REGID on loading screen! AND Can't find it on start new
////////////////////////////////////////////////////////////////////////////////////


//Ok, so maybe we can check if there is ANY regid at all?
echo "<br>";
echo "<br>";
//$testing = $cmi5launch->cmi5multipleregs;
//echo "OKEY POKEY. What is the check here " . $testing;
//So it's a true or false value, then maybe we can put this as the judger

echo "<br>";
echo "<br>";
//if ($testing === 0) {

    /*
    //////////////////////////////
//to bring in functions from class cmi5Connector
    $connectors = new cmi5Connectors;
    //create instance of class functions
    $retrieveUrl = $connectors->getRetrieveUrl();

    //Lets try making our own regid here 
    $urlResults = $retrieveUrl($cmi5launch->id);

    $urlDecoded = json_decode($urlResults, true);

    $url = $urlDecoded['url'];
    //urlInfo is one big string so
    parse_str($url, $urlInfo);

    $registrationid = $urlInfo['registration'];

    //Would it work to temp save the regid and url to the cmi5launch table and only save them to other
    //DB when successfully at launch?
    //test array
    parse_str($urlResults, $urlParsed);
    ///////////////////////////////////////////////////////////////////////
*/

//I don't know why it used to generate a reg id here, below it seems to get data from
//lrs to build table. IT builds the table with that data so this may be a good place to find
//out how to get the redata. //IS etag what keeps it separate?
$getregistrationdatafromlrsstate = cmi5launch_get_global_parameters_and_get_state(
    "http://cmi5api.co.uk/stateapikeys/registrations"
);


//There is an etag, does IT belong to a name? 
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
    $registrationdatafromlrs = json_decode($getregistrationdatafromlrsstate->content->getContent(), true);

    foreach ($registrationdatafromlrs as $key => $item) {

        echo "<br>";
        echo "What are these key?? " .$key;
        echo "<br>";
        //The keys are the regid!!
        //And they are sorting by date, so I think talking to lrs to check like they do
        //May be the answer. Should MY class do it? Like in retreiveLrs???
        //Well it setsup the key to launchexperiance, but where is the start new?
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
    }
    $table = new html_table();
    $table->id = 'cmi5launch_attempttable';
    $table->caption = get_string('modulenameplural', 'cmi5launch');
    $table->head = array(
        get_string('cmi5launchviewfirstlaunched', 'cmi5launch'),
        get_string('cmi5launchviewlastlaunched', 'cmi5launch'),
        get_string('cmi5launchviewlaunchlinkheader', 'cmi5launch')
    );
    $table->data = $registrationdatafromlrs;
    echo html_writer::table($table);
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
//AHA! view.php launches launch.phpo as a FORM - MB
?>
    <form id="launchform" action="launch.php" method="get" target="_blank">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>
<?php

echo $OUTPUT->footer();
