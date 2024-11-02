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
 * Prints an AUs session information and allows start of new one.
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cmi5launch\local\session_helpers;
use mod_cmi5launch\local\customException;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\progress;
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_once("$CFG->dirroot/lib/outputcomponents.php");


// Include the errorover (error override) funcs.
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');


require_login($course, false, $cm);

global $cmi5launch, $USER; 

// Classes and functions.
$auhelper = new au_helpers;
$sessionhelper = new session_helpers;
$retrievesession = $sessionhelper->cmi5launch_get_retrieve_sessions_from_db();
$retrieveaus = $auhelper->get_cmi5launch_retrieve_aus_from_db();
$progress = new progress;


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
$PAGE->requires->jquery();
$PAGE->requires->css('/mod/cmi5launch/styles.css');

// Output starts here.
echo $OUTPUT->header();

// Create the back button.
?>
<form action="view.php" method="get">
    <input id="id" name="id" type="hidden" value="<?php echo $id; ?>">
    <button type="submit" class="btn btn-primary resume-btn">Back</button>
</form>
<?php

// TODO: Put all the php inserted data as parameters on the functions and put the functions in a separate JS file.

?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#cmi5launch_auSessionTable tbody tr');
        const toggleButton = document.getElementById('toggleRowsButton');
        const initialVisibleCount = 5;

        // Show only the first 5 rows initially
        for (let i = 0; i < initialVisibleCount && i < rows.length; i++) {
            rows[i].classList.add('visible');
        }

        // Function to toggle rows and button text
        function toggleRows() {
            const isShowingMore = toggleButton.textContent === 'Show More';
            if (isShowingMore) {
                // Show all rows
                rows.forEach(row => row.classList.add('visible'));
                toggleButton.textContent = 'Show Less';
            } else {
                // Show only the first 5 rows
                rows.forEach((row, index) => {
                    if (index < initialVisibleCount) {
                        row.classList.add('visible');
                    } else {
                        row.classList.remove('visible');
                    }
                });
                toggleButton.textContent = 'Show More';
            }
        }

        // Add click event to the button to toggle rows
        toggleButton.addEventListener('click', toggleRows);
     });

        window.addEventListener("pageshow", function (event) {
            // Check if the page was loaded from cache
            if (event.persisted) {
                window.location.reload(); // Reload the page to refresh data
            }
        });

        function toggleProgress(progressCellId) {
            const content = document.getElementById(progressCellId);
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                content.previousElementSibling.querySelector('button').textContent = 'Hide Progress';
            } else {
                content.style.display = 'none';
                content.previousElementSibling.querySelector('button').textContent = 'View Progress';
            }
        }

        function key_test(registration) {

            if (event.keyCode === 13 || event.keyCode === 32) {
                mod_cmi5launch_launchexperience(registration);
            }
        }

        function resumeSession(auid) {
            $('#launchform_registration').val(auid);
            $('#launchform_restart').val(false);
            $('#launchform').submit();
        }

        // Function to handle restarting the session.
        function restartSession(auid) {
            // Add logic if needed to reset the session data here
            $('#launchform_registration').val(auid);
            $('#launchform_restart').val(true);
            $('#launchform').submit();
        }

        // Function to run when the experience is launched.
        function mod_cmi5launch_launchexperience(registration) {
            
            // Set the form paramters.
            $('#launchform_registration').val(registration);
            // Post it.
            $('#launchform').submit();
        }

        // // Function to run when the experience is launched.
        // function mod_cmi5launch_abandon(registration) {
        //     $progress->
        //     $statements = $progress.cmi5launch_send_request_to_lrs('cmi5launch_stream_and_send', $data, $session->id);
        // }



        // TODO: there may be a better way to check completion. Out of scope for current project.
        $(document).ready(function() {
            setInterval(function() {
                $('#cmi5launch_completioncheck').load('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>');
            }, 30000); // TODO: make this interval a configuration setting.
        });
    </script>
<?php

// Is this all necessary? Cant the data come through on its own

// Retrieve the registration and AU ID from view.php.
$auid = required_param('AU_view', PARAM_TEXT);

// First thing check for updates.
cmi5launch_update_grades($cmi5launch, $USER->id);

// Retrieve appropriate AU from DB.
$au = $retrieveaus($auid);

// Array to hold session scores for the AU.
$sessionscores = array();

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

// Reload user course instance.
$userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

// If it is null there have been no previous sessions.
if (!is_null($au->sessions)) {
    try {
        // Set custom error and exception handlers
        set_error_handler('mod_cmi5launch\local\custom_warningAU', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\custom_warningAU');

       // Set custom error and exception handlers
       set_error_handler('mod_cmi5launch\local\custom_warningAU', E_WARNING);
       set_exception_handler('mod_cmi5launch\local\custom_warningAU');

       // Prepare table structure
       $tabledata = array();
       $table = new html_table();
       $table->id = 'cmi5launch_auSessionTable';
       $table->attributes['class'] = 'generaltable cmi5launch-table launch-table';
       $table->caption = get_string('modulenameplural', 'cmi5launch');
       $table->head = array(
           get_string('cmi5launchviewfirstlaunched', 'cmi5launch'),
           get_string('cmi5launchviewprogress', 'cmi5launch'),
           get_string('cmi5launchviewgradeheader', 'cmi5launch'),
       );
       $table->colclasses = array('', 'progress-column', '');


       // Decode and iterate through session IDs
       $sessionids = json_decode($au->sessions);
       $sessionids = array_reverse($sessionids);
       foreach ($sessionids as $sessionid) {
           $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid]);

           if ($session) {
               $sessioninfo = [];

               // Format and add session created date
               if ($session->createdat) {
                   $createdAt = new DateTime($session->createdat, new DateTimeZone('US/Eastern'));
                   $createdAt->setTimezone(new DateTimeZone('America/New_York'));
                   $sessioninfo[] = "<span class='date-cell'>" . $createdAt->format('D d M Y H:i:s') . "</span>";
               }

               // Add minimized progress information with a toggle button
               $progressContent = "<pre>" . implode("\n ", json_decode($session->progress)) . "</pre>";
               $progressCellId = "progress-cell-" . $sessionid;

               $sessioninfo[] = "
                   
                   <button type='button' class='btn btn-primary resume-btn'' onclick='toggleProgress(\"$progressCellId\")'>View Progress</button>
                 
                   <div id='$progressCellId' class='progress-cell hidden-content' style='display: none;'>$progressContent</div>
               ";

               // Add score
               $sessioninfo[] = "<span class='score-cell'>" . $session->score . "</span>";
               $sessionscores[] = $session->score;

               // Add session info to table data
               $tabledata[] = $sessioninfo;
           }
       }

        // Output table
        $table->data = $tabledata;
        echo "<div class=\"cmi5launch-table-container\">";
        echo html_writer::table($table);
        echo "</div>";
        // Update AU record in the database
        $DB->update_record('cmi5launch_aus', $au);

    } catch (Exception $e) {
        restore_exception_handler();
        restore_error_handler();
        throw new customException(
            'Error loading session table on AU view page. Contact the system administrator with this message: ' .
            $e->getMessage() . '. Check that session information is present in DB and session ID is correct.', 0
        );
    } finally {
        restore_exception_handler();
        restore_error_handler();
    }
}

echo "<button id='toggleRowsButton' class='btn btn-secondary'>Show More</button>";


// Pass the auid and new session info to next page (launch.php).
// New attempt button.


echo "<div class='button-container' tabindex='0' onkeyup=\"key_test('" . $auid . "')\" id='cmi5launch_newattempt'>
        <button class='btn btn-primary resume-btn' onclick=\"resumeSession('" . $auid . "')\">"
        . ($au->sessions === null ? "Start AU" : "Resume AU")
        . "</button>";

if ($au->sessions !== null) {
    echo "<button class='btn btn-primary restart-btn' onclick=\"restartSession('" . $auid . "')\">Restart AU</button>";
}

echo "</div>";

// Add a form to be posted based on the attempt selected.
?>
    <form id="launchform" action="launch.php" method="get">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="launchform_restart" name="restart" type="hidden" value="false">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
    </form>

<?php

echo $OUTPUT->footer();
