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
 * Displays the AU's of a course and their current progress (satisfied/ not satisifed etc.).
 * Also allows for launching of the AU.
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cmi5launch\local\customException;
use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\course;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;

require_once("../../config.php");
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

// Include the errorover (error override) funcs.
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

require_login($course, false, $cm);

// Bring in functions and classes.
$progress = new progress;
$aushelpers = new au_helpers;
$connectors = new cmi5_connectors;

// Functions from other classes.
$saveaus = $aushelpers->get_cmi5launch_save_aus();
$createaus = $aushelpers->get_cmi5launch_create_aus();
$getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
$getregistration = $connectors->cmi5launch_get_registration_with_post();
$getregistrationinfo = $connectors->cmi5launch_get_registration_with_get();

global $cmi5launch, $USER, $mod;

// MB - Not currently using events, but may in future.
/*
// Trigger module viewed event.
$event = \mod_cmi5launch\event\course_module_viewed::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));

$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();
*/

// Print the page header.
$PAGE->set_url('/mod/cmi5launch/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cmi5launch->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css('/mod/cmi5launch/styles.css');
$PAGE->requires->jquery();

// Output starts here.
echo $OUTPUT->header();

// Reload cmi5 course instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

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

        // TODO: there may be a better way to check completion. Out of scope for current project.
        //MB - Someone elses todo, may be worth looking into
    
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php

// Check for updates.
cmi5launch_update_grades($cmi5launch, $USER->id);

// Check if a course record exists for this user yet.
$exists = $DB->record_exists('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);
  
// Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
set_error_handler('mod_cmi5launch\local\custom_warningview', E_WARNING);
set_exception_handler('mod_cmi5launch\local\custom_warningview');

try {
    // If it does not exist, create it.
    if ($exists == false) {

        // Make a new course record.
        $userscourse = new course($record);

        // Retreive user id.
        $userscourse->userid = $USER->id;

        // Build url to pass as returnUrl.
        $returnurl = $CFG->wwwroot . '/mod/cmi5launch/view.php' . '?id=' . $cm->id;
        $userscourse->returnurl = $returnurl;

        // Assign new record a registration id.
        $registrationid = $getregistration($userscourse->courseid, $cmi5launch->id);
        $userscourse->registrationid = $registrationid;

        // Retreive the Moodle course id

        $userscourse->moodlecourseid = $cm->instance;

        // Retrieve AU ids for this user/course.
        $aus = json_decode($record->aus);

        // We should not even be able to et here if these are false on record
        $auids = $saveaus($createaus($aus));
        $userscourse->aus = (json_encode($auids));

        // Save new record to DB.
        $newid = $DB->insert_record('cmi5launch_usercourse', $userscourse);

        // Now assign id created by DB.
        $userscourse->id = $newid;

    } else { // Record exists.

        // We have a record, so we need to retrieve it.
        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $record->courseid, 'userid' => $USER->id]);

        // Retrieve registration id.
        $registrationid = $userscourse->registrationid;

        // We need to verify if there is a registration id. Sometimes errors with player can cause a null id, in that case we want to
        // retrieve a new one.
        if ($registrationid == null) {
            // Retrieve registration id.
            $registrationid = $getregistration($record->courseid, $cmi5launch->id);
            // Update course record.
            $userscourse->registrationid = $registrationid;
            // Update DB.
            $DB->update_record("cmi5launch_usercourse", $userscourse);
        }
        // Retrieve AU ids.
        $auids = (json_decode($userscourse->aus));
//        $auids = (json_decode($userscourse));

    }
    
} catch (Exception $e) {

    // Restore default hadlers.
    restore_exception_handler();
    restore_error_handler();

    // If there is an error, display it.
    throw new customException('Creating or retrieving user course record. Contact your system administrator with error: ' . $e->getMessage(), 0);
}

// Array to hold info for table population.
$tabledata = array();

// We need id to get progress.
$cmid = $cmi5launch->id;

// Create table to display on page.
$table = new html_table();
$table->id = 'cmi5launch_autable';
$table->caption = get_string('autableheader', 'cmi5launch');
$table->attributes['class'] = 'generaltable cmi5launch-table au-table';
$table->head = array(
    get_string('cmi5launchviewAUname', 'cmi5launch'),
    get_string('cmi5launchviewstatus', 'cmi5launch'),
    get_string('cmi5launchviewgradeheader', 'cmi5launch'),
    get_string('cmi5launchviewregistrationheader', 'cmi5launch'),
);

// Array to hold Au scores.
$auscores = array();
try {
    // Query CMI5 player for updated registration info.
    $registrationinfofromcmi5 = json_decode($getregistrationinfo($registrationid, $cmi5launch->id), true);

    // Take only info about AUs out of registrationinfofromcmi5.
    $ausfromcmi5 = array_chunk($registrationinfofromcmi5["metadata"]["moveOn"]["children"], 1, true);

    // Cycle through AU IDs making AU objects and checking progress.
    foreach ($auids as $key => $auid) {

        // Array to hold scores for AU.
        $sessionscores = array();
        $au = $getaus($auid);

        // Verify object is an au object.
        if (!is_a($au, 'mod_cmi5launch\local\au', false)) {

            $reason = "Excepted AU, found ";
            var_dump($au);
            throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
        }

        // Retrieve AU's lmsID.
        $aulmsid = $au->lmsid;

        // To hold if the au is satisfied.
        $ausatisfied = "";

        // Cycle through AUs (or blocks) in registration info from player, we are looking for the one
        // that matches our AU lmsID.
        foreach ($ausfromcmi5 as $key => $value) {

            // Check for the AUs satisfied status. Compare with lmsId to find status for that instance.
            $ausatisfied = cmi5launch_find_au_satisfied($value, $aulmsid);
            // If au satisfied is ever true then we found it, once satisified it
            // doesn't matter if others have failed or were also satisified.
            if ($ausatisfied == "true") {
                break;

                // This elseif was built as a failsafe. Very rarely there may be an instance where the player issues
                // a duplicate lms id or registration number. For example, this can happen if the server crashes while a course
                // is being made or updated.
                // However, under normal circumstances, the AU LMSID should always match at least one of the AUs returned by player.
            } else if ($ausatisfied = "No ids match") {

                // If there are sessions for this AU.
                if ($au->sessions != null) {

                    // Retrieve session ids for this AU from DB.
                    $sessions = json_decode($au->sessions, true);
                    $sessionhelpers = new session_helpers;
                    $getsessioninfo = $sessionhelpers->cmi5launch_get_retrieve_sessions_from_db();

                    // Retrieve what this AU needs to moveon. We will search through the session data to see if it is fulfilled.
                    $aumoveon = $au->moveon;

                    // Hold if completed or passed is found.
                    $completedfound = false;
                    $passedfound = false;

                    // Cycle through them looking to see if any were passed and/or completed.
                    foreach ($sessions as $key => $value) {

                        // Get the session from DB with session id.
                        $ausession = $DB->get_record('cmi5launch_sessions', array('sessionid' => $value));

                        if ($ausession->iscompleted == "1") {
                            $completedfound = true;
                        }
                        if ($ausession->ispassed == "1") {
                            $passedfound = true;
                        }

                        // See if the pass and completed fulfill move on value for AU.
                        switch ($aumoveon) {
                            case "Completed":
                                if ($completedfound == true) {
                                    $ausatisfied = "true";
                                }
                                ;
                                break;
                            case "Passed":
                                if ($passedfound == true) {
                                    $ausatisfied = "true";
                                }
                                ;
                                break;
                            case "CompletedOrPassed":
                                if ($completedfound == true || $passedfound == true) {
                                    $ausatisfied = "true";
                                }
                                ;
                                break;
                            case "CompletedAndPassed":
                                if ($completedfound == true && $passedfound == true) {
                                    $ausatisfied = "true";
                                }
                                ;
                                break;
                        }

                        // If even one AU satisifed is met, then the AU is satisfied overall. Later or earlier sessions don't matter.
                        if ($ausatisfied == "true") {
                            break;
                        }
                    }
                }
            }
        }
        // If the 'sessions' in this AU are null we know this hasn't even been attempted.
        if ($au->sessions == null) {

            $austatus = "Not attempted";

        } else {

            // Retrieve AUs moveon specification.
            $aumoveon = $au->moveon;

            // If it's been attempted but no moveon value.
            if ($aumoveon == "NotApplicable") {
                $austatus = "viewed";
            } else {
                // IF it DOES have a moveon value.
                // If satisifed is returned true.
                if ($ausatisfied == "true") {

                    $austatus = "Satisfied";
                    // Also update AU.
                    $au->satisfied = "true";
                } else {

                    // If not, its in progress.
                    $austatus = "In Progress";
                    // Also update AU.
                    $au->satisfied = "false";
                }
            }
        }

        // Create array of info to place in table.
        $auinfo = array();

        // Assign au name, progress, and index.
        $auinfo[] = $au->title;
        $auinfo[] = ($austatus);

        $grade = 0;

        // Retrieve grade.
        if (!$au->grade == 0 || $au->grade == null) {

            $grade = $au->grade;

            $auinfo[] = ($grade);
        } else if ($au->grade == 0) {

            // Display the 0.
            $auinfo[] = ($grade);
            // TODO - This needs to be more interactive, course creators need to be able top control
            // whether a satisified AU is considered a 0 or not, but for now, if satisfied, don't show 0.
            if ($austatus == "Satisfied") {
                $auinfo[2] = " ";
            }

        } else {
            // There is no grade, leave blank.
            $auinfo[] = (" ");
        }

        $auindex = $au->auindex;

        // AU id for next page (to be loaded).
        //  $infofornextpage = $auid;

        // Assign au link to auviews.
        $auinfo[] = "<button class=\"btn btn-primary resume-btn\"  tabindex=\"0\" id='cmi5relaunch_attempt'
            onkeyup=\"key_test('" . $auid . "')\"
            onclick=\"mod_cmi5launch_launchexperience('" . $auid . "')\" style='cursor: pointer;'>"
            . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</button>";

        // Add to be fed to table.
        $tabledata[] = $auinfo;

        // Update AU scores.
        $auscores[$au->lmsid] = array($au->title => $au->scores);

        // Update the AU in DB.
        $DB->update_record("cmi5launch_aus", $au);
    }

    // Add our newly updated auscores array to the course record.
    $userscourse->ausgrades = json_encode($auscores);
} catch (Exception $e) {

    // Restore default hadlers.
    restore_exception_handler();
    restore_error_handler();

    // If there is an error, display it.
    throw new customException('retrieving and displaying AU satisfied status and grade. Contact your system administrator with error: ' . $e->getMessage(), 0);
}
// Lastly, update our course table.
$updated = $DB->update_record("cmi5launch_usercourse", $userscourse);

// This feeds the table.
$table->data = $tabledata;

// Restore default hadlers.
restore_exception_handler();
restore_error_handler();

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
