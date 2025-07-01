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
 * The report page. Displays either the course grades for teacher, or user grades for student.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */


use mod_cmi5launch\local\grade_helpers;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/reportbuilder/classes/local/report/column.php');

require_login($course, false, $cm);

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);

global $cmi5launch, $cmi5launchsettings, $USER, $DB;
// Reload course information.
$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

// Activity Module ID.
$id = required_param('id', PARAM_INT);
$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); // Report mode.

// Item number, may be != 0 for activities that allow more than one grade per user.
// Itemnumber is from the moodle grade_items table, which holds info on the grade item
// itself such as course, mod type, activity title, etc.
$itemnumber = optional_param('itemnumber', 0, PARAM_INT);
// Currently logged in user.
$userid = optional_param('userid', 0, PARAM_INT);
// The itemid is from the Moodle grade_grades table, corresponds to a grade column (such as
// one cmi5launch or other activity part of a course).
$itemid = optional_param('itemid', 0, PARAM_INT);
// This is the gradeid, which is the id in the same grade_grades table. A row entry, a particular users info.
$gradeid = optional_param('gradeid', 0, PARAM_INT);

// Active page.
$page = optional_param('page', 0, PARAM_INT);
$showall   = optional_param('showall', null, PARAM_BOOL);
$cmid      = optional_param('cmid', null, PARAM_INT);

$url = new moodle_url('/mod/cmi5launch/report.php');

if ($page !== 0) {
    $url->param('page', $page);
} else if ($showall) {
    $url->param('showall', $showall);
}
$requestbody = file_get_contents('php://input');

$contextmodule = context_module::instance($cm->id);

// Setup page url.
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');


// Functions from other classes.
$gradehelpers = new grade_helpers;

$updategrades = $gradehelpers->get_cmi5launch_check_user_grades_for_updates();
$highestgrade = $gradehelpers->get_cmi5launch_highest_grade();
$averagegrade = $gradehelpers->get_cmi5launch_average_grade();


$cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/report.php', ['id' => $id]));

// Trigger a report viewed event.
// MB - we don't currently do this, maybe in future.
/*
$event = \mod_scorm\event\report_viewed::create(array(
    'context' => $contextmodule,
    'other' => array(
        'scormid' => $scorm->id,
        'mode' => $mode
    )
));

$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('scorm', $scorm);
$event->trigger();
*/

// Print the page header.
if (empty($noheader)) {

    $strreport = get_string('cmi5launchreport', 'cmi5launch');
    $strattempt = get_string('cmi5launchattemptrow', 'cmi5launch');

    // Setup the page.
    $PAGE->set_title("$course->shortname: " . format_string($course->id));
    $PAGE->set_heading($course->fullname);
    $PAGE->activityheader->set_attrs([
        'hidecompletion' => true,
        'description' => '',
    ]);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', ['id' => $cm->id]));

    echo $OUTPUT->header();
}

// Create table to display on page.
$reporttable = new \flexible_table('mod-cmi5launch-report');

$columns[] = get_string('cmi5launchautitleheader', 'cmi5launch');
$headers[] = get_string('cmi5launchautitleheader', 'cmi5launch');

// The table is always the same, but the amount of users shown varies.
// If user has capability, they can see all users.
if (has_capability('mod/cmi5launch:viewgrades', $context)) {

    // Get enrolled users for this course.
    $users = get_enrolled_users($contextmodule);


    foreach ($users as $user) {

        // Call updategrades to ensure all grades are up to date before view.
        $updategrades($user);

        // Each user needs their own column.
        $headers[] = $user->username;
        $columns[] = $user->username;

        // Backbutton goes to grader.
        $backurl = $CFG->wwwroot . '/grade/report/grader/index.php' . '?id=' . $cmi5launch->course;
    }
} else {

    // If the user does not have the correct capability then we are looking at a specific user.
    // Who is not a teacher and needs to see only their grades.
    // Retrieve that user from DB.
    $user = $DB->get_record('user', ['id' => $USER->id]);

    // Make sure their grades are up to date.
    $updategrades($user);
    // Add user to array for processing, the table build expects an array of users with their id as their index.
    $users[$user->id] = $user;

    // Each user needs their own column.
    $headers[] = $user->username;
    $columns[] = $user->username;

    // Where back button goes.
    $backurl = $CFG->wwwroot . '/grade/report/user/index.php' . '?id=' . $cmi5launch->course;
}

// Reload cmi5 course instance.
$record = $DB->get_record('cmi5launch', ['id' => $cmi5launch->id]);

// Retrieve AU ids for this course.
$aus = json_decode($record->aus, true);

// Separate AUS into array on id.
$auschunked = array_chunk($aus, 13, true);

// Add the columns and headers to the table.
$reporttable->define_columns($columns);
$reporttable->define_headers($headers);
$reporttable->define_baseurl($PAGE->url);
// Setup table (this needs to be done before data is added).
$reporttable->setup();

// Unfortunately, array_chunk nests our AU's, we need to use an index to grab them.
// I have not found a way to reliably separate AUs from DB without nesting -MB.
foreach ($auschunked[0] as $au) {

    // Array to hold data for rows.
    $rowdata = [];

    // For each AU, iterate through each user.
    foreach ($users as $user) {

        // Array to hold info for next page, that will be placed into buttons for user to click.
        $infofornextpage = [];

        // Retrieve the current au id, this is always unique and will help with retrieving the
        // student grades. It is the uniquie id cmi5 spec id.
        $aulmsid = $au[0]['lmsId'];
        $infofornextpage[] = $aulmsid;
        // Grab the current title of the AU for the row header, also to be sent to next page.
        $currenttitle = $au[0]['title'][0]['text'];
        $infofornextpage[] = $currenttitle;
        $rowdata["AU Title"] = ($currenttitle);

        $username = $user->username;

        // Retrieve users specific info for this course.
        $userrecord = $DB->get_record('cmi5launch_usercourse', ['courseid' => $record->courseid, 'userid' => $user->id]);

        // Retrieve grade type from settings.
        $gradetype = $cmi5launchsettings["grademethod"];

        // User record may be null if user has not participated in course yet.
        if ($userrecord == null) {

            $userscore = " ";
            $infofornextpage[] = null;
        } else {

            // Retrieve the users grades for this course.
            $usergrades = json_decode($userrecord->ausgrades, true);

            // These are the AUS we want to send on if clicked, the more specific ids (THIS users AU ids).
            $currentauids = $userrecord->aus;
            $infofornextpage[] = $currentauids;

            $userscore = "";

            if (!$usergrades == null) {

                // Now compare the usergrades array keys to lmsid of current au, if
                // it matches then we want to display, that's what userscore is.
                if (array_key_exists($aulmsid, $usergrades)) {

                    // If it is, we want it's info which should be title => grade(s).
                    $auinfo = [];
                    $auinfo = $usergrades[$aulmsid];
                    $augrades = $auinfo[$currenttitle];

                    // This is just to display, it calculates here so it doesn't effect the base array stored for AU.
                    switch ($gradetype) {

                        // GRADE_AUS_CMI5' = '0'.
                        // GRADE_HIGHEST_CMI5' = '1'.
                        // GRADE_AVERAGE_CMI5', =  '2'.
                        // GRADE_SUM_CMI5', = '3'.
                        case 1:
                            $userscore = strval($highestgrade($augrades));
                            break;
                        case 2:
                            // We need to update rawgrade not all of grades, that wipes out the array format it needs.
                            $userscore = strval($averagegrade($augrades));
                            break;
                    }

                    // Remove [] from userscore if they are there.
                    $toremove = ["[", "]"];
                    if ($userscore != null && str_contains($userscore, "[")) {
                        $userscore = str_replace($toremove, "", $userscore);
                    }
                }
            } else {
                $userscore = "N/A";
            }
        }
        // Add the userid to info for next page.
        $infofornextpage[] = $user->id;

        // Convert their grade to string to be passed into html button.
        $userscoreasstring = is_numeric($userscore) ? number_format((float)$userscore, 2) : $userscore;

        // Encode to send to next page, because it has to go as a string and pass through the Javascript function.
        $sendtopage = base64_encode(json_encode($infofornextpage, JSON_HEX_QUOT));

        // Build the button to be displayed. It appears as the users score, but is also a link
        // to session_report if user wants to break down the score.
        $button = "<a tabindex=\"0\" id='newreport'
                onkeyup=\"key_test('" . $sendtopage . "')\" onclick=\"mod_cmi5launch_open_report('"
            . $sendtopage . "')\" style='cursor: pointer;'>"
            . $userscoreasstring . "</a>";

        // Add the button to the row data under the correct user.
        $rowdata[$username] = ($button);
    }

    // Add the row data to the table.
    $reporttable->add_data_keyed($rowdata);
}

// Finish building table now that all data is passed in.
$reporttable->get_page_start();
$reporttable->get_page_size();
$reporttable->finish_output();

// Back button form.
echo html_writer::start_tag('form', [
    'action' => $backurl,
    'method' => 'get',
]);
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'id',
    'id' => 'id',
    'value' => $cmi5launch->course,
]);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('cmi5launchbackbutton', 'mod_cmi5launch'),
]);
echo html_writer::end_tag('form');

// Launch form to session_report.php.
echo html_writer::start_tag('form', [
    'id' => 'launchform',
    'action' => 'session_report.php',
    'method' => 'get',
]);
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'id',
    'id' => 'id',
    'value' => $id,
]);
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'session_report',
    'id' => 'session_report',
    'value' => 'default',
]);
echo html_writer::end_tag('form');


if (empty($noheader)) {
    echo $OUTPUT->footer();
}
?>
<script>
    function key_test(inforfornextpage) {

        // Onclick calls this
        if (event.keyCode === 13 || event.keyCode === 32) {

            mod_cmi5launch_open_report(inforfornextpage);
        }
    }


    // Function to run when the experience is launched (on click).
    function mod_cmi5launch_open_report(inforfornextpage) {
        // Set the form paramters.
        document.getElementById('session_report').value = inforfornextpage;
        // Post it.
        document.getElementById('launchform').submit();
    }


</script>
