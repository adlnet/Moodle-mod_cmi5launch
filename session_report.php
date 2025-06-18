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
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
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
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', array('id' => $cm->id)));

    echo $OUTPUT->header();
}

// Back button.
?>
<form action="report.php" method="get">
    <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
    <input type="submit" value="Back" />
</form>
<?php

// Retrieve the user.
$user = $DB->get_record('user', array('id' => $userid));

// Create tables to display on page.
// This is the main table with session info.
$table = new \flexible_table('mod-cmi5launch-report');
// This table holds the overall score, showing the grading type.
$scoretable = new \flexible_table('mod-cmi5launch-report');

$columns[] = 'Attempt';
$headers[] = get_string('cmi5launchattemptheader', 'cmi5launch');
$columns[] = 'Started';
$headers[] = get_string('cmi5launchstartedheader', 'cmi5launch');
$columns[] = 'Finished';
$headers[] = get_string('cmi5launchfinishedheader', 'cmi5launch');
$columns[] = 'Status';
$headers[] = get_string('cmi5launchsatisfiedstatusheader', 'cmi5launch');
$columns[] = 'Score';
$headers[] = get_string('cmi5launchscoreheader', 'cmi5launch');

$scorecolumns = array();
$scoreheaders = array();

// Add the columns and headers to the table.
$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);

// Decode and put AU ids in array.
$auids = (json_decode($auidprevpage, true));
// Aurecord to hold record
$aurecord = null;

// For each AU id, find the one that matches our auid from previous page, this is the record we want.
foreach ($auids as $key => $auid) {

    //This maybe better. it looks for the record rather than loop through ALL records.
    // Retrieve record from table.
    $aurecord = $DB->get_record('cmi5launch_aus', ['id' => $auid, 'lmsid' => $cmi5idprevpage]);
    if (!$aurecord) {
        // If no record found,
       //do nothin,
        continue;
    } else {


        if ($aurecord->sessions != null || false) {
            // Retrieve session ids for this course.
            $sessions = json_decode($aurecord->sessions, true);

            // Start Attempts at one.
            $attempt = 1;

            // Arrays to hold row info.
            $rowdata = array();
            $scorerow = array();

            // An array to hold grades for max or mean scoring.
            $sessionscores = array();
            // Set table up, this needs to be done before rows added.
            $table->setup();
            $austatus = "";

            // There may be more than one session.
            foreach ($sessions as $sessionid) {


                $session = $updatesession($progress, $cmi5, $sessionid, $cmi5launch->id, $user);
                // Add score to array for AU.
                $sessionscores[] = $session->score;

                if ($session->createdat != null || false) {

                    // Retrieve createdAt and format.
                    $date = new DateTime($session->createdat, new DateTimeZone('US/Eastern'));
                    $date->setTimezone(new DateTimeZone('America/New_York'));
                    $datestart = $date->format('D d M Y H:i:s');
                } else {
                    // If no createdAt, set to empty.
                    $datestart = "";
                }

                if ($session->lastrequesttime != null || false) {

                    // Retrieve lastRequestTime and format.
                    $date = new DateTime($session->lastrequesttime, new DateTimeZone('US/Eastern'));
                    $date->setTimezone(new DateTimeZone('America/New_York'));
                    $datefinish = $date->format('D d M Y H:i:s');
                } else {
                    // If no lastRequesttime then use updatedat.
                    // Retrieve lastRequestTime and format.
                    $date = new DateTime($session->updatedat, new DateTimeZone('US/Eastern'));
                    $date->setTimezone(new DateTimeZone('America/New_York'));
                    $datefinish = $date->format('D d M Y H:i:s');
                }
                // The users sessions.
                $usersession = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

                // Add row data.
                $rowdata["Attempt"] = get_string('cmi5launchattemptrow', 'cmi5launch') . $attempt;
                $rowdata["Started"] = $datestart;
                $rowdata["Finished"] = $datefinish;

                // AUs moveon specification.
                $aumoveon = $aurecord->moveon;

                // 0 is no 1 is yes, these are from CMI5 player
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
                $scorerow[get_string('cmi5launchattemptrow', 'cmi5launch') . $attempt] = $usersession->score;

                switch ($gradetype) {

                    // 'GRADE_AUS_CMI5' = '0').
                    // 'GRADE_HIGHEST_CMI5' = '1'.
                    // 'GRADE_AVERAGE_CMI5', =  '2'.
                    // 'GRADE_SUM_CMI5', = '3'.

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

                $rowdata["Score"] = $usersession->score;

                $table->add_data_keyed($rowdata);
            }
        }
    } // end else from aurecord if 
} // end of for each auids
// Display the grading type, highest, avg, etc.
$scorecolumns[] = 'Grading type';
$scoreheaders[] = 'Grading type';
$scorecolumns[] = 'Overall Score';
$scoreheaders[] = 'Overall Score';

// Session score may be null or empty.
if (!empty($sessionscores)) {

    $scorerow["Overall Score"] = $overall;
}else{
    $scorerow["Overall Score"] = '';
}

// Setup score table.
$scoretable->define_columns($scorecolumns);
$scoretable->define_headers($scoreheaders);
$scoretable->define_baseurl($PAGE->url);
$scoretable->setup();
$scoretable->add_data_keyed($scorerow);
$scoretable->add_data_keyed("SCORE");

$table->get_page_start();
$table->get_page_size();

$scoretable->get_page_start();
$scoretable->get_page_size();

$table->add_separator();
$scoretable->finish_output();
$table->finish_output();
