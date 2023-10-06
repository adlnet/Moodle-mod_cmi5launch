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

use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\course;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;

use core_reportbuilder\local\report\column;
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
//require('header.php');
//require_login($course, false, $cm);
require_once("../../config.php");
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
//require_once($CFG->dirroot.'/mod//reportsettings_form.php');
require_once($CFG->dirroot.'/mod/cmi5launch/report/basic/classes/report.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');
global $cmi5launch;
define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();
$id = required_param('id', PARAM_INT);// Course Module ID, or ...
// $userid = required_param('user', PARAM_INT);// Course Module ID, or ...
//$currenttitle = required_param('autitle', PARAM_TEXT);// Course Module ID, or ...
//$cmi5idprevpage = required_param('currentcmi5id', PARAM_TEXT);// Course Module ID, or ...
//$auidprevpage = required_param('auid', PARAM_TEXT);// Course Module ID, or ...
// Retrieve the registration and AU ID from view.php.
$fromreportpage = base64_decode(required_param('AU_view', PARAM_TEXT) );
// Break it into array (AU is first index).
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



//////
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
// MB
// I have no idea what downlaod and mode are....
$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); // Report mode.

$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

////$scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);

$contextmodule = context_module::instance($cm->id);
// Can we gett herE?
$url = new moodle_url('/mod/cmi5launch/session_report.php');

//They are passed sd url params?
$url->param('id', $id);
//....this maybe got easier 
$PAGE->set_url($url);

require_login($course, false, $cm);
$PAGE->set_pagelayout('report');
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

require_once("$CFG->dirroot/lib/outputcomponents.php");
require_login($course, false, $cm);

global $cmi5launch, $USER;

        // Reload cmi5 course instance.
        $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
/*
        echo"<br>";
        echo " What is record here?";
        var_dump($record);
        echo "<br>";
  */      
// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/session_report.php', ['id' => $id]));


if (!empty($download)) {
    $noheader = true;
}
// Print the page header.
if (empty($noheader)) {
    // I think I understand. This string arument is looking at cmi5launch.php, thats what the second
    // param refers to
    $strreport = get_string('report', 'cmi5launch');
    
    // MB
    // We dont so attempts yet, but we do auas
  //  $strattempt = get_string('attempt', 'cmi5launch');

    $PAGE->set_title("$course->shortname: ".format_string($cm->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->activityheader->set_attrs([
        'hidecompletion' => true,
        'description' => ''
    ]);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', array('id' => $cm->id)));

    echo $OUTPUT->header();
}

// What is coming from other page?
echo"<br>";
echo " What is fromreportpage?";
var_dump($fromreportpage);
echo "<br>";
// Retrieve the registration and AU ID from view.php.


echo"<br>";
echo "USER ID IS: ";
var_dump($userid);
echo "<br>";


// Maybe the problem is the reistration id? It's only grabbing certain record, not user records
$registrationid = $getregistration($record->courseid, $cmi5launch->id);
/*
echo"<br>";
echo " Will this work??";
var_dump(json_decode($auidprevpage, true));
echo "<br>";
*/
// Yes it will!! Now we have the riht auid!!!!

/*
echo"<br>";
echo " What is user page?";
$user = $DB->get_record('user', ['id' => $userid]);
var_dump($user);
echo "<br>";
echo "and what would all enrolled user llook like?";
$users = get_enrolled_users($contextmodule);; //returns an array of users
var_dump($users);
echo "<br>";
*/

// Create table to display on page.

// This table holds the user and au names 
$table = new \flexible_table('mod-cmi5launch-report');

// This table holds the overall scofre, maybe shows score type selected
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

$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);


//The problem is this wants the 'course' id as in the moodle assigned ACTIVITY id, I thouggggght they were courses
// so like not 2 but 185
//so the 185 is course id noit id
$specificcourse= $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $userid]);
$aushelpers = new au_helpers;
// Retrieve AU ids.
$getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();


//NOW we have the correct AUS!!! THESE ids should have progress
$auids = (json_decode($auidprevpage, true) );

$scorecolumns = array();
$scoreheaders = array();
// For each au id, find the one that matches our auid from previous pae, this is the record 
// we want
foreach ($auids as $key => $auid) {
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

//Are we getting the right sessions?
echo"<br>";
echo " What are the sessions?";
var_dump($sessions);
echo "<br>";
// Start Attempt at one
$attempt = 1;

//Array to hold row info
$rowdata = array();
//
$scorerow = array();
//An array to hold grades for max or mean scoring
$sessionscores = array();
// Set table up, this needs to be done before rows added
$table->setup();

// There may be more than one session
foreach ($sessions as $sessionid) {

    // Can we just retrieve the session from DB? Since we are writing a cron to udate
    // session
    $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid]);

    ///////
    // Retrieve new info (if any) from CMI5 player on session.
//$session = $updatesession($sessionid, $cmi5launch->id);

    // Get progress from LRS.
   // $session = $getprogress($registrationid, $cmi5launch->id, $session);

    // Ok, so above, when session is returned we know there is no bracket
    // so maybe it happens here? 
    // Add score to array for AU.
    $sessionscores[] = $session->score;

    // Update session in DB.
    //$DB->update_record('cmi5launch_sessions', $session);
    /////////////

    ///////
    // Retrieve createdAt and format.
    $date = new DateTime($session->createdat, new DateTimeZone('US/Eastern'));
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $datestart = $date->format('D d M Y H:i:s');

    // Retrieve lastRequestTime and format.
    $date = new DateTime($session->lastrequesttime, new DateTimeZone('US/Eastern'));
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $datefinish = $date->format('D d M Y H:i:s');
    ///////

    //The users sessions
    $usersession = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

    // $headers[] = $session;
    // $columns[] = $session;
   // $sessionprogress = ("<pre>" . implode("\n ", json_decode($session->progress)) . "</pre>");
    
    // we may not be able to display this, is it necessary even? 
    // But this table doesn't seem to like rows in rows

    // You know itttt doesn't say if it's right or wrong, is it even necessary? 
    //$rowdata["Session info"] = $session->progress;
    $rowdata["Attempt"] = "Attempt " . $attempt;
    $rowdata["Started"] = $datestart;
    $rowdata["Finished"] = $datefinish;

    // ....... ...............AUs moveon specification.
    $aumoveon = $aurecord->moveon;



    ////maybe a CASE here
    // 0 is no 1 is yes, these are from players
    $iscompleted = $session->iscompleted;
    $ispassed = $session->ispassed;
    $isfailed = $session->isfailed;
    $isterminated = $session->isterminated;
    $isabanadoned = $session->isabandoned;

    //$ausatisfied = $session->satisfied;
    

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
        //can we just fill space with blank
        $scorecolumns[] = "Attempt " . $attempt;
        $scoreheaders[] = "Attempt " . $attempt;
        $scorerow["Attempt " . $attempt] = $usersession->score;
        //TODO
        //Later, this will take the rading type from settings, for now we will just manually assign highest
        $scorerow["Grading type"] = "Highest";

    $attempt++;
    
    $rowdata["Status"] = $austatus;

    $rowdata["Score"] = $usersession->score;
  
    //$row[] = $rowdata;

    $table->add_data_keyed($rowdata);
  //  $table->add_separator();

  }
  
  //Here is the grading type, highest, ave, etc
  $scorecolumns[] = 'Grading type';
  $scoreheaders[] = 'Gradingtype';
  $scorecolumns[] = 'Overall Score';
  $scoreheaders[] = 'Overall Score';

  // Session score may be null or empty
  if(!empty($sessionscores)){

  
  //TODO
  // and here we can later use an if or switcvh nd adjust on gradetype
$scorerow["Overall Score"] = max($sessionscores);
  }
//$scorecolumns[] = 'Overall Score';
//$scoreheaders[] = 'Overall Score';
    
$scoretable->define_columns($scorecolumns);
$scoretable->define_headers($scoreheaders);
$scoretable->define_baseurl($PAGE->url);

$scoretable->setup();

$scoretable->add_data_keyed($scorerow);

//$i++;

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




