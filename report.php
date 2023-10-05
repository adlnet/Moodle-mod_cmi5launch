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

// TODO MB.
// Teachers should be directed here when we implement grading.
//
//defined('MOODLE_INTERNAL') || die(); //Causing it to not display anything.

//echo("This is the report page.");

//So this report page is accesed by clicking the course title in the grader report and the magnifin glass
//to zoom in on certain things.

// So currently ittt all takes to same page, if glass is picked wee want only that students info, and consequestnyl,
// only students who should see themselves, only teachers see it all.

use core_reportbuilder\local\report\column;
use mod_cmi5launch\local\grade_helpers;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_login($course, false, $cm);
require_once("../../config.php");
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
//require_once($CFG->dirroot.'/mod//reportsettings_form.php');
require_once($CFG->dirroot.'/mod/cmi5launch/report/basic/classes/report.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();
//Wed how can i add jquery commands? it is not seeing my jquery????


$id = required_param('id', PARAM_INT);// Course Module ID, i think, like 417?

// MB
// I have no idea what downlaod and mode are....
$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); // Report mode.


$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
////$scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);
// Item number, may be != 0 for activities that allow more than one grade per user.
// itemnumber is from the moodle grade_items table, which holds info on the grade item itself such as course, mod type, activity title, etc
$itemnumber = optional_param('itemnumber', 0, PARAM_INT); 
// Graded user ID (optional) (not currenlty loged in user).
$userid = optional_param('userid', 0, PARAM_INT);
// The itemid is from the moooodle grade_grades table I believe, appears to correspond to a grade column (for like
// one cmi5launch or other activity part of a course)
$itemid = optional_param('itemid', 0, PARAM_INT);
// This is the gradeid, which is the id, in the same grade_grades table. So like a row entry, a particular users info
$gradeid = optional_param('gradeid', 0, PARAM_INT);
/////
//$actionlink = required_param('Grade analysis', PARAM_TEXT);// Course Module ID, or ...

$page= optional_param('page', 0, PARAM_INT);   // active page
///////////////
//$attemptid = required_param('attempt', PARAM_INT);
$page      = optional_param('page', 0, PARAM_INT);
$showall   = optional_param('showall', null, PARAM_BOOL);
$cmid      = optional_param('cmid', null, PARAM_INT);

$url = new moodle_url('/mod/quiz/review.php', array('attempt'=>$attemptid));
if ($page !== 0) {
    $url->param('page', $page);
} else if ($showall) {
    $url->param('showall', $showall);
}
$request_body = file_get_contents('php://input');


$contextmodule = context_module::instance($cm->id);
// MB - Generates and returns list of available Scorm report sub-plugins in their reportlib page 
//$reportlist = scorm_report_list($contextmodule);

// Will this allopw me to grage a certain user id?
//$userid = required_param('userid', PARAM_INT);// Course Module ID, or ...
// That seems to only work if I setit up as form in pervious page, which is a moodle page, can we take from url? 


//$url = new moodle_url('/mod/cmi5launch/report.php');

//$url->param('id', $id);

// MB what is this?
/*
if (empty($mode)) {
    $mode = reset($reportlist);
} else if (!in_array($mode, $reportlist)) {
    throw new \moodle_exception('erroraccessingreport', 'cmi5launch');
}
$url->param('mode', $mode);
*/
$PAGE->set_url($url);

require_login($course, false, $cm);
$PAGE->set_pagelayout('report');

// MB
// We will need to set and create a capability for teachers to view reports.
//require_capability('mod/scorm:viewreport', $contextmodule);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');

require_once("$CFG->dirroot/lib/outputcomponents.php");
require_login($course, false, $cm);

global $cmi5launch, $USER;

// Output starts here.
//echo $OUTPUT->header();

// Activate the secondary nav tab.
navigation_node::override_active_url(new moodle_url('/mod/cmi5launch/report.php', ['id' => $id]));

?>
    <script>
      
      function key_test(userscore) {
        
        //Onclick calls this
        if (event.keyCode === 13 || event.keyCode === 32) {

            mod_cmi5launch_open_report(userscore);
        }
    }

// Function to run when the experience is launched (on click).
function mod_cmi5launch_open_report(userscore) {

      // Set the form paramters.
      $('#AU_view').val(userscore);
        // Post it.
        $('#launchform').submit();
     
}
   
        </script>
<?php
// Hmmmm and this?
/*
if (count($reportlist) < 1) {
    throw new \moodle_exception('erroraccessingreport', 'cmi5launch');
}
*/

// Trigger a report viewed event.
// MB we will need to implement this
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

// Now are all my optional params coming through???
echo"<br>";
echo"what is id?";
var_dump($id);
echo"<br>";
echo"what is itemid?";
var_dump($itemid);
echo"<br>";
echo"<br>";
echo "what is itemnumber";
var_dump($itemnumber);
echo"<br>";
echo"<br>";
echo "what is gradeid";
var_dump($gradeid);
echo"<br>";
echo"<br>";
echo "what is userid";
var_dump($userid);
echo"<br>";




//New snippet I found
echo"<br>";
echo"what is request_body?";
var_dump($request_body);

echo"<br>";
echo"what is page thinabo?";
var_dump($page);
echo"<br>";
echo" what is showall?:'";
var_dump($showall);
echo"<br>";

echo "<br>";
echo "what is this thinggg?";
var_dump($actionlink);
echo "<br>";
// Ok, can we ge treport?
echo"<br>";
echo"what is report?";
var_dump($report);
echo"<br>";

echo"<br>";
echo"what is page2?";
var_dump($page2);
echo"<br>";


/// what is this?
// return tracking object
$gpr = new grade_plugin_return(
    array(
        'type' => 'report',
        'plugin' => 'grader',
        'course' => $course,
        'page' => $page
    )
);

//What is gpr
echo"<br>";
echo"what is gpr?";
var_dump($gpr);
echo"<br>";
//Could this woprk on next pager????

///LEts see what we captured
echo"<br>";     
echo"what is userid?";
var_dump($userid);
echo"<br>";
$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); // Report mode.

//what are thes eoptionalparams
echo"<br>";
echo"what is download?";
var_dump($download);
echo"<br>";
echo"what is mode?";
var_dump($mode);
echo"<br>";
//What is session const here
echo"<br>";
echo"what is server const?";
var_dump($_SERVER);
echo"<br>";
//What is session const here
echo"<br>";
echo"what is session const?";
var_dump($_SESSION);
echo"<br>";

// what about $_GET
echo"<br>";
echo"what is get const?";
var_dump($_GET);
echo"<br>";

echo"<br>";
echo"what is cookie const?";
var_dump($_COOKIE);
echo"<br>";

// what about $_GET
echo"<br>";
echo"what is POST const?";
var_dump($_POST);
echo"<br>";
$url = new moodle_url('/mod/cmi5launch/report.php');

/*
$url->param('id', $id);
if (empty($mode)) {
    $mode = reset($reportlist);
} else if (!in_array($mode, $reportlist)) {
    throw new \moodle_exception('erroraccessingreport', 'scorm');
}
*/
$url->param('mode', $mode);
// What is url->param
echo"<br>";
echo"what is url param?";
var_dump($url->param);
echo"<br>";
// Create table to display on page.

// What about a table in a table? 
// This table holds the user and au names 
$outertable = new \flexible_table('mod-cmi5launch-report');

$columns[] = 'AU Title';
$headers[] = get_string('autitle', 'cmi5launch');


//SEe here we are getting all users, but it may only be one user, lets see what we can do if wecan grad user id from previous screen
$users = get_enrolled_users($contextmodule);; //returns an array of users


// Here? We should update here right!
// And all users should be checked cause the teacher is looking at all their students
    
$gradehelpers = new grade_helpers;

$updategrades = $gradehelpers->get_cmi5launch_check_user_grades_for_updates();

$updategrades($cmi5launch);

foreach($users as $user){
    $headers[] = $user->username;
    $columns[] = $user->username;
}

        // Reload cmi5 course instance.
        $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

        // i think this is the problem, the aus from here don't match the aus from cmi5launch_course

        // Retrieve AU ids for this course.
        // Yes cause this is the OVERALL au info, their base info, not the users AU versions
        $aus = json_decode($record->aus, true);

// we can use array_chunk to separate AUS. lets eparate on id
// ITs annoying but I don't think theres getting around it nested....
$aus2 = array_chunk($aus, 13, true);

// Each row needs to be separate, unless row is meant to be multi indexed array
$rowdata = array(); 
$i = 0;
     //   $columns[] = 'AU Title';

    // $columns[] = 'AU Title';

  //  $headers[] = get_string('autitle', 'cmi5launch');

    $outertable->define_columns($columns);
$outertable->define_headers($headers);
$outertable->define_baseurl($PAGE->url);

$rowdata = array(); 
$outertable->setup();

// IS there a way to do this without a GROUP of arrays, just one long thing?
    foreach ($aus2[0] as $au) {
       
    
    foreach($users as $user){
       
            //REtrieve the current au id, this is always unique and will help with retreiving the 
    // student grades
    $infofornextpage[] = $au[0]['id'];

    // Then current title is 
    $currenttitle= $au[0]['title'][0]['text'];
    $infofornextpage[] = $currenttitle;
    $rowdata["AU Title"] =  ($currenttitle);
    $infofornextpage = array();
    $row = array();
        $username = $user->username;

       // II see, this may be because if the user hasn't done anything in class yet? 
       // Well here's the problem! IT's getting the global or signed in user
       $userrecord =$DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $user->id]);
       
       // Userrecord may be null if user has not aprticipated in course yet
       if($userrecord == null){
           $userscore = " ";
       } else {

            // echo"what is user record?";
            $usergrades = json_decode($userrecord->ausgrades, true);

            // This isnt working cause the whole screen runs before button are clicked, we may need to pass them somewho differntly. or change how the button access them
            // like if button clicked it...does something
            // Like each butttton can have a code, and that code retrieves ITS's opbjects?
            // like an id, and this is gneretead programmatically?
            // fudge
            //These are the AUS we want to send on if clicked, the more specific ids.
            $currentauids = $userrecord->aus;

            $infofornextpage[] = $currentauids;


            //well maybe the problem is the usergrades are o????

            // Now compare the usergreades array keys to name of current autitle, if t 
            // it matches then display, that's what userscore is

            // Ok, what is this iterates through all user info and uses this to stop on right one
            // could we grab auid that way?
            if (!$usergrades == null) {
                if (array_key_exists($currenttitle, $usergrades)) {

                    $userscore = $usergrades[$currenttitle];


                    $url = ('report.php?id=' . $cm->id);
                    //Can we make the userscore a link?
                    // $userscorelink = html_writer::link("google.com", $userscore);


                    // Remove []
                    $toremove = array("[", "]");
                    if (str_contains($userscore, "[")) {
                        $userscore = str_replace($toremove, "", $userscore);
                    }
                    // $userscore = $usergrades[$currenttitle];
                }
            } else {
                $userscore = "N/A";
            }
            //Now we retrieve the users grade for each au

            // Nevermind, we want individual not overall $userscore = cmi5launch_highest_grade($cmi5launch, $user->id);
            //$headers[] = $user->username;
            //$columns[] = $user->username;
            //Oh! we need columns for each user
            // array_push($rowdata, $userScore);

            // Ok so apparently the key needs to be column title?
            // $rowdata[] = array ($username => $userscore);
        }
   //Why isnt user id going oveR?
   echo"<br>";
    echo"what is user id?";
    var_dump($user->id);
    echo"<br>";
   $infofornextpage[] = $user->id;

   $userscoreasstring = strval($userscore);
           //can this just be a string that is populated later?
       //  $button = "<a onclick=mod_cmi5launch_open_report("
        //   . $useridtosend . ")"
          // . $userscoreasstring . "</a>";
          echo"<br>";
          echo"what is send to page?";
          var_dump($infofornextpage);
          echo"<br>";
         // Encode to send to next page
         // bas encode enables it to travel! //now just decod eon other page?
         $sendtopage = base64_encode(json_encode($infofornextpage, JSON_HEX_QUOT));
    // Ok thisis it, it's not good as an encoded array
    // It's the "" I think, since this encodes it it meses up with all the qoutation makts
    // lets try using this flag - JSON_HEX_QUOT
        //$sendtopage = "problem";
        echo"<br>";
        echo"what is send to page?";
        var_dump($sendtopage);
        echo"<br>";
          //So here like lets make a button id?
          // or is that necessary, the 'sendtonext page string will be diff each time, we just need an arrayt like in th
          // other paes
           $button = "<a tabindex=\"0\" id='newreport'
           onkeyup=\"key_test('" . $sendtopage . "')\" onclick=\"mod_cmi5launch_open_report('"
        . $sendtopage . "')\" style='cursor: pointer;'>"
        . $userscoreasstring . "</a>";
          
           // View.phpo or wherever we send them now is very similar or the same with diff ino, to what the student will see, cause it just one perosn right? 
            // I don't imagone the teacher needsmtp see all students doing stuff at once?
   $rowdata[$username] = ($button);
    }
  //  $row[$i] = $rowdata;
  
    $row[] = $rowdata;
   //$i++;
    $outertable->add_data_keyed($rowdata);
   /*
    echo`<br>`;
echo " What row here???? ";
var_dump($rowdata);
echo"<br>";
*/
//$outertable->add_data($rowdata);
}

//$outertable->add_data_keyed($row);
//$outertable->format_and_add_array_of_rows($row);


 //$table->get_page_start();
  //$table->get_page_size();
  $outertable->get_page_start();
  $outertable->get_page_size();
   //  This feeds the table.
//$outertable->data = $row;
//$table->finish_output();
$outertable->finish_output();

// I would like to build this link and then use redirect? OR do I need to make a javascript?
/* "<a tabindex=\"0\" id='cmi5relaunch_attempt'
            onkeyup=\"key_test('" . $infoForNextPage . "')\"
            onclick=\"mod_cmi5launch_launchexperience('" . $infoForNextPage . "')\" style='cursor: pointer;'>"
            . get_string('cmi5launchviewlaunchlink', 'cmi5launch') . "</a>";
            */
// WEll ,if a link is pressed:
    // redirect('report.php?id='.$cm->id);
//echo html_writer::table($table);
?>

<form id="launchform" action="session_report.php" method="get" target="_blank">
        <input id="id" name="id" type="hidden" value="<?php echo $id ?>">
        <input id="user" name="user" type="hidden" value="<?php echo $useridtosend ?>">
        <input id="autitle" name="autitle" type="hidden" value="<?php echo $currenttitle ?>">
        <input id="currentcmi5id" name="currentcmi5id" type="hidden" value="<?php echo $currentcmi5id ?>">
        <input id="AU_view" name="AU_view" type="hidden" value="default">
        <input id="auid" name="auid" type="hidden" value="<?php echo $currentauids ?>">
    </form>
<?php

if (empty($noheader)) {
    echo $OUTPUT->footer();
    // 
}
