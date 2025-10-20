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
 * Class to report on sessions grades.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;
use mod_cmi5launch\local\cmi5_connectors;

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


global $cmi5launch, $CFG;

$cmi5 = new cmi5_connectors();
$cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

// Retrieve the grade type to use to calculate the overall score.
$gradetype = $cmi5launchsettings["grademethod"];

// External classes and functions.
$sessionhelper = new session_helpers;
$aushelpers = new au_helpers;
// Instantiate progress and cmi5_connectors to pass.
$progress = new \mod_cmi5launch\local\progress;
$cmi5connectors = new \mod_cmi5launch\local\cmi5_connectors;

$updatesession = $sessionhelper->cmi5launch_get_update_session();
$getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();

// Activity Module ID.
$id = required_param('id', PARAM_INT);

// Retrieve the user and AU specific info from previous page.
$fromreportpage = base64_decode(required_param('session_report', PARAM_TEXT));
// Break it into array.
$fromreportpage = json_decode($fromreportpage, true);

// The args from the previous page come through in this order:
// 0: cmi5 unique AU ID.
// 1: AU title.
// 2: AU IDs to retrieve AUs from DB for this user.
// 3: The user id, the one whose grades we need.
$cmi5idprevpage = $fromreportpage[0];
$currenttitle = $fromreportpage[1];
$auidprevpage = $fromreportpage[2];
$userid = $fromreportpage[3];

// Retrieve the course module.
$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$contextmodule = context_module::instance($cm->id);

// Set page url.
$url = new moodle_url('/mod/cmi5launch/session_report.php');
$url->param('id', $id);

// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/classes/local/session_report.php', ['id' => $id]));

if (!empty($download)) {
    $noheader = true;
}

// Print the page header.
if (empty($noheader)) {

    $strreport = get_string('cmi5launchreport', 'cmi5launch');

    // Setup the page.
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('report');
    $PAGE->set_title("$course->shortname: " . format_string($cm->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->activityheader->set_attrs([
        'hidecompletion' => true,
        'description' => '',
    ]);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', ['id' => $cm->id]));

    echo $OUTPUT->header();
}

// Retrieve the user.
$user = $DB->get_record('user', ['id' => $userid]);

// Create tables to display on page.
// This is the main table with session info.
$table = new \flexible_table('mod-cmi5launch-report');
// This table holds the overall score, showing the grading type.
$scoretable = new \flexible_table('mod-cmi5launch-report');

$columns[] = get_string('cmi5launchattemptheader', 'cmi5launch');
$headers[] = get_string('cmi5launchattemptheader', 'cmi5launch');
$columns[] = get_string('cmi5launchstartedheader', 'cmi5launch');
$headers[] = get_string('cmi5launchstartedheader', 'cmi5launch');
$columns[] = get_string('cmi5launchfinishedheader', 'cmi5launch');
$headers[] = get_string('cmi5launchfinishedheader', 'cmi5launch');
$columns[] = get_string('cmi5launchstatusheader', 'cmi5launch');
$headers[] = get_string('cmi5launchstatusheader', 'cmi5launch');
$columns[] = get_string('cmi5launchscoreheader', 'cmi5launch');
$headers[] = get_string('cmi5launchscoreheader', 'cmi5launch');

$scorecolumns = [];
$scoreheaders = [];

// Add the columns and headers to the table.
$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);

// Decode and put AU ids in array.
$auids = (json_decode($auidprevpage, true));
// Aurecord to hold record.
$aurecord = null;

// For each AU id, find the one that matches our auid from previous page, this is the record we want.
foreach ($auids as $key => $auid) {

    // This maybe better. it looks for the record rather than loop through ALL records.
    // Retrieve record from table.
    $aurecord = $DB->get_record('cmi5launch_aus', ['id' => $auid, 'lmsid' => $cmi5idprevpage]);
    if (!$aurecord) {
        // If no record found, do nothing.
        continue;
    } else {

        if (isset($aurecord->sessions)) {
            // Retrieve session ids for this course.
            $sessions = json_decode($aurecord->sessions, true);

            // Start Attempts at one.
            $attempt = 1;

            // Arrays to hold row info.
            $rowdata = [];
            $scorerow = [];

            // An array to hold grades for max or mean scoring.
            $sessionscores = [];
            // Set table up, this needs to be done before rows added.
            $table->setup();
            $austatus = "";

            // There may be more than one session.
            foreach ($sessions as $sessionid) {


                $session = $updatesession($progress, $cmi5, $sessionid, $cmi5launch->id, $user);
                // Add score to array for AU.
                $sessionscores[] = (float)$session->score;

                if (!empty($session->createdat)) {
                    $datestart = userdate(strtotime($session->createdat));
                } else {
                    $datestart = "";
                }
                
                if (!empty($session->lastrequesttime)) {
                    $datefinish = userdate(strtotime($session->lastrequesttime));
                } else if (!empty($session->updatedat)) {
                    $datefinish = userdate(strtotime($session->updatedat));
                } else {
                    $datefinish = '';
                }
                
                // The users sessions.
                $usersession = $DB->get_record('cmi5launch_sessions',
                    ['sessionid' => $sessionid, 'userid' => $userid, 'moodlecourseid' => $id]);

                // Add row data.
                $rowdata["Attempt"] = get_string('cmi5launchattemptrow', 'cmi5launch') . $attempt;
                $rowdata["Started"] = $datestart;
                $rowdata["Finished"] = $datefinish;

                // AUs moveon specification.
                $aumoveon = $aurecord->moveon;

                // 0 is no 1 is yes, these are from CMI5 player.
                $iscompleted = $session->iscompleted;
                $ispassed = $session->ispassed;
                $isfailed = $session->isfailed;
                $isterminated = $session->isterminated;
                $isabandoned = $session->isabandoned;

                // If it's been attempted but no moveon value.
                if ($iscompleted == 1) {

                    $austatus = get_string('cmi5launchsessionaucompleted', 'cmi5launch');

                    if ($ispassed == 1) {
                        $austatus = get_string('cmi5launchsessionaucompletedpassed', 'cmi5launch');
                    }
                    if ($isfailed == 1) {
                        $austatus = get_string('cmi5launchsessionaucompletedfailed', 'cmi5launch');
                    }
                }
                // Update table.
                $scorecolumns[] = get_string('cmi5launchattemptrow', 'cmi5launch') . $attempt;
                $scoreheaders[] = get_string('cmi5launchattemptrow', 'cmi5launch') . $attempt;
                if ($usersession) {
                    $scorerow[get_string('cmi5launchattemptrow', 'cmi5launch')
                    . $attempt] = is_numeric($usersession->score) ? number_format((float)$usersession->score, 2) : '';
                }
                switch ($gradetype) {

                    // GRADE_AUS_CMI5 = 0.
                    // GRADE_HIGHEST_CMI5 = 1.
                    // GRADE_AVERAGE_CMI5 =  2.
                    // GRADE_SUM_CMI5 = 3.

                    case 1:
                        $grade = get_string('cmi5launchsessiongradehigh', 'cmi5launch');
                        $overall = max($sessionscores);
                        break;
                    case 2:
                        $grade = get_string('cmi5launchsessiongradeaverage', 'cmi5launch');
                        $overall = (array_sum($sessionscores) / count($sessionscores));
                        break;
                }

                $scorerow["Grading type"] = $grade;

                $attempt++;

                $rowdata["Status"] = $austatus;

                if ($usersession) {
                    $rowdata["Score"] = is_numeric($usersession->score) ? number_format((float)$usersession->score, 2) : '';

                }

                $table->add_data_keyed($rowdata);
            }
        }
    } // End else from aurecord if.
} // End of for each auids.

// Display the grading type, highest, avg, etc.
$scorecolumns[] = 'Grading type';
$scoreheaders[] = 'Grading type';
$scorecolumns[] = 'Overall Score';
$scoreheaders[] = 'Overall Score';

// Session score may be null or empty.
if (!empty($sessionscores)) {

    $scorerow["Overall Score"] = isset($overall) && is_numeric($overall) ? number_format((float)$overall, 2) : '';
} else {
    $scorerow["Overall Score"] = '';
}

// Setup score table.
$scoretable->define_columns($scorecolumns);
$scoretable->define_headers($scoreheaders);
$scoretable->define_baseurl($PAGE->url);
$scoretable->setup();
$scoretable->add_data_keyed($scorerow);

$table->get_page_start();
$table->get_page_size();

$scoretable->get_page_start();
$scoretable->get_page_size();

$table->add_separator();
$scoretable->finish_output();
$table->finish_output();
// Back button.
echo html_writer::start_tag('form', [
    'action' => 'report.php',
    'method' => 'get',
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'id' => 'id',
    'name' => 'id',
    'value' => $id,
]);

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('cmi5launchbackbutton', 'mod_cmi5launch'),
]);

echo html_writer::end_tag('form');
