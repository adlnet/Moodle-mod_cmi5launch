<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version..
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>

// A break down of the users AU sessions as grade report.
// Megan Bohland 2023

use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;
use core_reportbuilder\local\report\column;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("../../config.php");
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/report/basic/classes/report.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');

global $cmi5launch;

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();
// Activity Module ID
$id = required_param('id', PARAM_INT);

// Retrieve the user and AU specific info from previous page. 
$fromreportpage = base64_decode(required_param('AU_view', PARAM_TEXT) );
// Break it into array.
$fromreportpage = json_decode($fromreportpage, true);

// The args from the previous page come through in this order:
// 0: cmi5 unique AU ID
// 1: AU title
// 2: AU IDs to retrieve AUs from DB for this user
// 3: The user id, the one whose grades we need
$cmi5idprevpage = $fromreportpage[0];
$currenttitle = $fromreportpage[1];
$auidprevpage = $fromreportpage[2];
$userid = $fromreportpage[3];

// Other classes.
$progress = new progress;
$aushelpers = new au_helpers;
$connectors = new cmi5_connectors;
$sessionhelpers = new session_helpers;

// Functions from other classes.
$saveaus = $aushelpers->get_cmi5launch_save_aus();
$createaus = $aushelpers->get_cmi5launch_create_aus();
$getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
$getregistration = $connectors->cmi5launch_get_registration_with_post();
$getregistrationinfo = $connectors->cmi5launch_get_registration_with_get();
$getprogress = $progress->cmi5launch_get_retrieve_statements();
$updatesession = $sessionhelpers->cmi5launch_get_update_session();

$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$contextmodule = context_module::instance($cm->id);
$url = new moodle_url('/mod/cmi5launch/session_report.php');

$url->param('id', $id);
$PAGE->set_url($url);

$PAGE->set_pagelayout('report');

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_once("$CFG->dirroot/lib/outputcomponents.php");
require_login($course, false, $cm);

global $cmi5launch, $USER;

// Reload cmi5 course instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
  
// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/session_report.php', ['id' => $id]));


if (!empty($download)) {
    $noheader = true;
}
// Print the page header.
if (empty($noheader)) {

    $strreport = get_string('report', 'cmi5launch');
    
    // Setup thew page
    $PAGE->set_title("$course->shortname: ".format_string($cm->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->activityheader->set_attrs([
        'hidecompletion' => true,
        'description' => ''
    ]);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', array('id' => $cm->id)));

    echo $OUTPUT->header();
}
// Back button.
?>
<form action="report.php" method="get" target="_blank">
    <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
  <input type="submit" value="Back"/>
</form>
<?php


// Create tables to display on page.
// This is the main table with session info.
$table = new \flexible_table('mod-cmi5launch-report');

// This table holds the overall score, showing the grading type.
$scoretable = new \flexible_table('mod-cmi5launch-report');

$columns[] = 'Attempt';
$headers[] = get_string('attempt', 'cmi5launch');
$columns[] = 'Started';
$headers[] = get_string('started', 'cmi5launch');
$columns[] = 'Finished';
$headers[] = get_string('last', 'cmi5launch');
$columns[] = 'Status';
$headers[] = "Status";
$columns[] = 'Score';
$headers[] = get_string('score', 'cmi5launch');

$scorecolumns = array();
$scoreheaders = array();

// Add the columns and headers to the table.
$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);


//Decode and put AU ids in array.
$auids = (json_decode($auidprevpage, true) );

// For each au id, find the one that matches our auid from previous page, this is the record 
// we want.
foreach ($auids as $key => $auid) {
        
        // Retrieve AU from DB and make object. 
        $au = $getaus($auid);

        // This uses the auid to pull the right record from the aus table
        $au = $DB->get_record('cmi5launch_aus', ['id' => $auid, 'auid' => $cmi5idprevpage]);
     
        // When it is not null this is our aurecord
        if (!$au == null || false) {

            $aurecord = $au;
        }
    }
    
       
//Ok, now instead of all the users, we want the suer from the previous page
$users = get_enrolled_users($contextmodule);; //returns an array of users

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

    $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid]);

    // Add score to array for AU.
    $sessionscores[] = $session->score;

    // Retrieve createdAt and format.
    $date = new DateTime($session->createdat, new DateTimeZone('US/Eastern'));
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $datestart = $date->format('D d M Y H:i:s');

    // Retrieve lastRequestTime and format.
    $date = new DateTime($session->lastrequesttime, new DateTimeZone('US/Eastern'));
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $datefinish = $date->format('D d M Y H:i:s');

    // The users sessions.
    $usersession = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

    // Add row data.
    $rowdata["Attempt"] = "Attempt " . $attempt;
    $rowdata["Started"] = $datestart;
    $rowdata["Finished"] = $datefinish;

    // AUs moveon specification.
    $aumoveon = $aurecord->moveon;

    // 0 is no 1 is yes, these are from CMI5 player
    $iscompleted = $session->iscompleted;
    $ispassed = $session->ispassed;
    $isfailed = $session->isfailed;
    $isterminated = $session->isterminated;
    $isabanadoned = $session->isabandoned;

    // If it's been attempted but no moveon value.
    if ($iscompleted == 1 ) {
        
            $austatus = "Completed";

            if($ispassed == 1){
                $austatus = "Completed and Passed";
            }
            if($isfailed == 1){
                $austatus = "Completed and Failed";
            }
    } 

        $scorecolumns[] = "Attempt " . $attempt;
        $scoreheaders[] = "Attempt " . $attempt;
        $scorerow["Attempt " . $attempt] = $usersession->score;
        
        //TODO
        //Later, this will take the rading type from settings, for now we will just manually assign highest
        $scorerow["Grading type"] = "Highest";

        // TODO
        // I thinnk we need to make a gradetype retrieval method and put in grade_helpers, since its an int
        // save some brain work

    $attempt++;
    
    $rowdata["Status"] = $austatus;

    $rowdata["Score"] = $usersession->score;
  
    $table->add_data_keyed($rowdata);

  }
  
  // Here is the grading type, highest, ave, etc.
  $scorecolumns[] = 'Grading type';
  $scoreheaders[] = 'Gradingtype';
  $scorecolumns[] = 'Overall Score';
  $scoreheaders[] = 'Overall Score';

  // Session score may be null or empty.
  if(!empty($sessionscores)){

  
  //TODO
  // and here we can later use an if or switch nd adjust on gradetype
$scorerow["Overall Score"] = max($sessionscores);
  }

  // Setup score table
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


// TODO
// I'm not sure I like the way this looks? Maybe adda div or something?
$table->add_separator();
$scoretable->finish_output();
$table->finish_output();




