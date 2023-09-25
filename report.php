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
//This is scorms reports page.
use core_reportbuilder\local\report\column;

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


$id = required_param('id', PARAM_INT);// Course Module ID, or ...

// MB
// I have no idea what downlaod and mode are....
$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); // Report mode.

$cm = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
////$scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);

$contextmodule = context_module::instance($cm->id);
// MB - Generates and returns list of available Scorm report sub-plugins in their reportlib page 
//$reportlist = scorm_report_list($contextmodule);

$url = new moodle_url('/mod/cmi5launch/report.php');

$url->param('id', $id);

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
          $('#launchform_registration').val(userscore);
            // Post it.
            $('#launchform').submit();
            <?php
        //redirect('session_report.php?id='. userscore);
        ?>
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

// Create table to display on page.

// What about a table in a table? 
// This table holds the user and au names 
$outertable = new \flexible_table('mod-cmi5launch-report');

$columns[] = 'AU Title';
$headers[] = get_string('autitle', 'cmi5launch');

$users = get_enrolled_users($contextmodule);; //returns an array of users

foreach($users as $user){
    $headers[] = $user->username;
    $columns[] = $user->username;
}

// inner table or table that will be on new pages
    /*
    $headers[] = get_string('started', 'cmi5launch');
    $columns[] = 'finish';
    $headers[] = get_string('last', 'cmi5launch');
    $columns[] = 'score';
    $headers[] = get_string('score', 'cmi5launch');
    */


    //And we can make funcs to query the table and return the data we want! maybe in our report lib?
//$table = new \flexible_table('mod-cmi5launch-report');

    // hmmmm
        // inner table or table that will be on new pages
    /*
    $headers[] = get_string('started', 'cmi5launch');
    $columns[] = 'finish';
    $headers[] = get_string('last', 'cmi5launch');
    $columns[] = 'score';
    $headers[] = get_string('score', 'cmi5launch');
    *
    $columns[] = 'attempt';
    $headers[] = get_string('attempt', 'cmi5launch');
    $columns[] = 'start';
    $headers[] = get_string('started', 'cmi5launch');
    $columns[] = 'finish';
    $headers[] = get_string('last', 'cmi5launch');
    $columns[] = 'score';
    $headers[] = get_string('score', 'cmi5launch');
*/


       // $table->define_columns($columns);
       // $table->define_headers($headers);
       // $table->define_baseurl($PAGE->url);


       // $table->column_class('picture', 'picture');
        //$table->column_class('fullname', 'bold');
        //$table->column_class('score', 'bold');

        //$table->set_attribute('cellspacing', '0');
        //$table->set_attribute('id', 'attempts');
        //$table->set_attribute('class', 'generaltable generalbox');

        // Reload cmi5 course instance.
        $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));

        // i think this is the problem, the aus from here don't match the aus from cmi5launch_course

        // Retrieve AU ids for this course.
        // Yes cause this is the OVERALL au info, their base info, not the users AU versions
        $aus = json_decode($record->aus, true);

     //  How about the aus num,?
     echo"<br>";
        echo"what is record?"; 
        var_dump($record);
        echo"<br>";

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
//$outertable->is_collapsible(true);

// start working!!!
//echo"<br>";
//echo"what is COLUMNS";
//var_dump($columns);
//echo"<br>";
/*
$useridtosend = "1";
//can this just be a string that is populated later?
$button = " <a tabindex=\"0\" id='userScore'
onkeyup=\"key_test('" . $useridtosend . "')\"
onclick=\"redirect?('" . $useridtosend . "')\" style='cursor: pointer;'>"
. get_string("This is where userscore goes") . "</a>";
*/
$rowdata = array(); 
$outertable->setup();
// IS there a way to do this without a GROUP of arrays, just one long thing?
    foreach ($aus2[0] as $au) {
        
        $row = array();
// So like make a new array for each row? Theres got to be some better way,
// why wont it takes arrays


//$headers[] = get_string('autitle', 'cmi5launch');
    //$i = 0;
   
    echo"<br>";
    echo"what is au?";
    var_dump($au);
    echo"<br>";
    
    //REtrieve the current au id, this is always unique and will help with retreiving the 
    // student grades
    $currentcmi5id = $au[0]['id'];

    echo"<br>";
    echo"what is current auid?";
    var_dump($currentcmi5id);
    echo"<br>";
    // Then current title is 
    $currenttitle = $au[0]['title'][0]['text'];
    /*
    echo"<br>";
    echo"what is current title?";
    var_dump($currenttitle);
    echo"<br>";
    */

    //Makes more sense to feed row an array of data
    // Nope it doent like arrays
    // We need a freakin double array!! The array[i] is  string value apparenlty
    //array_push($rowdata, $currenttitle);
    
    // Ok so apparently the key needs to be column title?
    //$rowdata[] =array ("AU Title" => $currenttitle);
    // Well not two dimensional ,just key pair>
    $rowdata["AU Title"] =  ($currenttitle);
    foreach($users as $user){
        $username = $user->username;
        //$headers[] = $username;
    //$columns[] = $username;
        
     //   echo $user->username;
       // echo"<br>";
       $userrecord =$DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);
         // echo"what is user record?";
       $usergrades = json_decode($userrecord->ausgrades, true);
      
       //These are the AUS we want to send on if clicked, the more specific ids.
       $currentauids = $userrecord->aus;
       
       echo"<br>";
       echo"what is currenntauids?";
         var_dump($currentauids);
        echo"<br>";
       
       echo"<br>";
       echo"what is user record?";
         var_dump($userrecord);
        echo"<br>";
       // Getting closer? But is the id correct, they all really have an 80?
       
       echo"<br>";
        echo"what is usergrades?";
        var_dump($usergrades);
        echo"<br>";
        //well maybe the problem is the usergrades are o????
        
        // Now compare the usergreades array keys to name of current autitle, if t 
        // it matches then display, that's what userscore is
        
        // Ok, what is this iterates through all user info and uses this to stop on right one
        // could we grab auid that way?
        if(array_key_exists($currenttitle, $usergrades)){
            
            $userscore = $usergrades[$currenttitle];

                echo"<br>";
        echo"what is user score?";
        var_dump($userscore);
        echo"<br>";

            $url = ('report.php?id='.$cm->id);
            //Can we make the userscore a link?
           // $userscorelink = html_writer::link("google.com", $userscore);
           
           
           // Remove []
            $toremove = array("[", "]");
            if(str_contains($userscore, "[")){
                $userscore = str_replace($toremove, "", $userscore);
            }
           // $userscore = $usergrades[$currenttitle];
        }
        else{
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
   $useridtosend = $user->id;
   $userscoreasstring = strval($userscore);
           //can this just be a string that is populated later?
       //  $button = "<a onclick=mod_cmi5launch_open_report("
        //   . $useridtosend . ")"
          // . $userscoreasstring . "</a>";
          
        
           $button = "<a tabindex=\"0\" id='newreport'
        onkeyup=\"key_test('" . $useridtosend . "')\" onclick=\"mod_cmi5launch_open_report('"
        . $useridtosend . "')\" style='cursor: pointer;'>"
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

        <input id="auid" name="auid" type="hidden" value="<?php echo $currentauids ?>">
    </form>
<?php

if (empty($noheader)) {
    echo $OUTPUT->footer();
    // 
}
