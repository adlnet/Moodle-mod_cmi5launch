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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');


// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');


require_login($course, false, $cm);

global $cmi5launch, $USER;

// Classes and functions.
$auhelper = new au_helpers;
$sessionhelper = new session_helpers;
$retrieveaus = $auhelper->get_cmi5launch_retrieve_aus_from_db();

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


// Output starts here.
echo $OUTPUT->header();

// Create the back button.
?>
<form action="view.php" method="get">
    <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
    <input type="submit" value="Back" />
</form>
<?php

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
        document.getElementById('launchform_registration').value = registration;
        // Post it.
        document.getElementById('launchform').submit();
    }

    // TODO: there may be a better way to check completion. Out of scope for current project.
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(function() {
            fetch('completion_check.php?id=<?php echo $id ?>&n=<?php echo $n ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('cmi5launch_completioncheck').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }, 30000); // TODO: make this interval a configuration setting.
    });
</script>

<?php

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
if (!$au->sessions == null) {

    try {

        // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\custom_warningAU', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\custom_warningAU');

        // Array to hold info for table population.
        $tabledata = array();

        // Build table.
        $table = new html_table();
        $table->id = 'cmi5launch_auSessionTable';
        $table->caption = get_string('modulenameplural', 'cmi5launch');
        $table->head = array(
            get_string('cmi5launchviewfirstlaunched', 'cmi5launch'),
            get_string('cmi5launchviewlastlaunched', 'cmi5launch'),
            get_string('cmi5launchviewprogress', 'cmi5launch'),
            get_string('cmi5launchviewgradeheader', 'cmi5launch'),
        );


        // Retrieve session ids.
        $sessionids = json_decode($au->sessions);

        // Iterate through each session by id.
        foreach ($sessionids as $key => $sessionid) {

            // Get the session from DB with session id.
            $session = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

            // Array to hold data for table.
            $sessioninfo = array();

            if ($session->createdat != null) {

                // Retrieve createdAt and format.
                $date = new DateTime($session->createdat, new DateTimeZone('US/Eastern'));
                $date->setTimezone(new DateTimeZone('America/New_York'));
                // date_timezone_set($date, new DateTimeZone('America/New_York'));
                $sessioninfo[] = $date->format('D d M Y H:i:s');
            }

            if ($session->lastrequesttime != null) {

                // Retrieve lastRequestTime and format.
                $date = new DateTime($session->lastrequesttime, new DateTimeZone('US/Eastern'));
                $date->setTimezone(new DateTimeZone('America/New_York'));
                $sessioninfo[] = $date->format('D d M Y H:i:s');
            }
            // Add progress to table.
            $sessioninfo[] = ("<pre>" . implode("\n ", json_decode($session->progress)) . "</pre>");

            // Add score to table.
            $sessioninfo[] = $session->score;
            // Add score to array for AU.
            $sessionscores[] = $session->score;

            // Add to be fed to table.
            $tabledata[] = $sessioninfo;
        }
    } catch (Exception $e) {

        // Restore default hadlers.
        restore_exception_handler();
        restore_error_handler();

        // Throw an exception.
        throw new customException(get_string('cmi5launchloadsessionerror', 'cmi5launch') . $e->getMessage() );
    }

    // Write table.
    $table->data = $tabledata;
    echo html_writer::table($table);

    // Update AU in table with new info.
    $DB->update_record('cmi5launch_aus', $au);

    // Restore default hadlers.
    restore_exception_handler();
    restore_error_handler();
}

// Pass the auid and new session info to next page (launch.php).
// New attempt button.
echo "<p tabindex=\"0\"onkeyup=\"key_test('"
    . $auid . "')\"id='cmi5launch_newattempt'><button onclick=\"mod_cmi5launch_launchexperience('"
    . $auid
    . "')\" style=\"cursor: pointer;\">"
    . get_string('cmi5launch_attempt', 'cmi5launch')
    . "</button></p>";

// Add a form to be posted based on the attempt selected.
?>

    <form id="launchform" action="launch.php" method="get">
        <input id="launchform_registration" name="launchform_registration" type="hidden" value="default">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="n" name="n" type="hidden" value="<?php echo $n ?>">
        <input id="auid" name="auid" type="hidden" value="<?php echo $auid ?>"> <!-- Pass AU ID -->

    </form>


<?php

echo $OUTPUT->footer();
