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

use core_reportbuilder\local\report\column;
use mod_cmi5launch\local\au_helpers;
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
$userid = required_param('user', PARAM_INT);// Course Module ID, or ...
$currenttitle = required_param('autitle', PARAM_TEXT);// Course Module ID, or ...
$auidprevpage = required_param('auid', PARAM_TEXT);// Course Module ID, or ...


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

$url->param('id', $id);
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

        echo"<br>";
        echo " What is record here?";
        var_dump($record);
        echo "<br>";
        
// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/session_report.php', ['id' => $id]));

//echo "CONGRATS!";

$userdata = null;
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
    $strattempt = get_string('attempt', 'cmi5launch');

    $PAGE->set_title("$course->shortname: ".format_string($cm->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->activityheader->set_attrs([
        'hidecompletion' => true,
        'description' => ''
    ]);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/cmi5launch/report.php', array('id' => $cm->id)));

    echo $OUTPUT->header();
}

// Create table to display on page.

// This table holds the user and au names 
$table = new \flexible_table('mod-cmi5launch-report');

$columns[] = 'AU Title';
$headers[] = $currenttitle;
$headers[] = get_string('started', 'cmi5launch');
$columns[] = 'finish';
$headers[] = get_string('last', 'cmi5launch');
$columns[] = 'score';
$headers[] = get_string('score', 'cmi5launch');

$columns[] = 'attempt';
$headers[] = get_string('attempt', 'cmi5launch');
$columns[] = 'start';
$headers[] = get_string('started', 'cmi5launch');
$columns[] = 'finish';
$headers[] = get_string('last', 'cmi5launch');
$columns[] = 'score';
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
    $auids = (json_decode($specificcourse->aus) );

   
    // For each au id, find the one that matches our auid from previous pae, this is the record 
    // we want
    foreach ($auids as $key => $auid) {
        $au = $getaus($auid);
       
        echo"<br>";
        echo "DID IT WORK WHAT care the auid";
        var_dump($auids);
        echo "<br>";

        $au = $DB->get_record('cmi5launch_aus', ['id' => $auid, 'auid' => $auidprevpage]);
        
        echo"<br>";
        echo "DID IT WORK WHAT came back?";
        var_dump($au);
        echo "<br>";
        echo"<br>";
        echo "what is currenttitle we want tomatch?";
        var_dump($auidprevpage);
        echo "<br>";
        
        // When it is not null this is our aurecord
        // 
        if (!$au == null || false) {
        echo "Entering?";


            $aurecord = $au;
        }
    }
    

    echo"<br>";
echo "DID IT WORK WHAT IS AU record?";
var_dump($aurecord);
echo "<br>";

       // Now we pull up the au record from the DB and the sessions will be
    //   $aurecord =$DB->get_record('cmi5launch_aus', ['courseid'  => $course->id, 'userid'  => $userid, 'auid' => $auid]);
// Is it not getting ocurse>?
/*
echo"<br>";
echo " What is course id here?";
var_dump($course->id);
echo "<br>";
echo"<br>";
echo " What is userid  here?";
var_dump($userid);
echo "<br>";
echo"<br>";
echo " What is auid here?";
var_dump($auid);
echo "<br>";
*/
       //Ok, now instead of all the users, we want the suer from the previous page
$users = get_enrolled_users($contextmodule);; //returns an array of users

// Retrieve AU ids for this course.
$sessions = json_decode($aurecord->sessions, true);

//There may be more than one session
foreach($sessions as $sessionid){
    

//The users sessions
$usersession =$DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));
echo"<br>";
echo " What is session here?";
var_dump($usersession);
echo "<br>";
    $headers[] = $session;
    $columns[] = $session;
}

// Is it not getting ocurse>?
echo"<br>";
echo " What is sessions here?";
var_dump($sessions);
echo "<br>";
foreach($users as $user){
    $headers[] = $user->username;
    $columns[] = $user->username;
}
