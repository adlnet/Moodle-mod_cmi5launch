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
 * Returns an array of reports to which are currently readable.
 * @package    mod_scorm
 * @author     Ankit Kumar Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
// Dang if this is gonna be called AAIN we need to just make it a func in either AU_helpers
// Or make a grade helper, but lets see if it's waht we need first.
// Should we take in AU id and use that? 
function cmi5launch_calculate_au_grade($auid)
{
    $connectors = new cmi5_connectors;
    $aushelpers = new au_helpers;
    $getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
    $getregistrationinfo = $connectors->cmi5launch_get_registration_with_get();
    $au = $getaus($auid);

    // Verify object is an au object.
    if (!is_a($au, 'mod_cmi5launch\local\au', false)) {

        $reason = "Excepted AU, found ";
        var_dump($au);
        throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
    }

    // Retrieve AU's lmsID.
    $aulmsid = $au->lmsid;

    // Query CMI5 player for updated registration info.
    //$registrationinfofromcmi5 = $getregistrationinfo($registrationid, $cmi5launch->id);
    // Take only info about AUs out of registrationinfofromcmi5.
  //  $ausfromcmi5 = array_chunk($registrationinfofromcmi5["metadata"]["moveOn"]["children"], 1, true);

    //We will make a func here for this, but right now, can we take
    // the au id and use it to get and save score to course?
    ///Ooooh yes, lets make an array to add to!

    //Wait, if this is PER au, then there should only be one grade, unless they do it multiple times dangit
    // if we decode its a dang array and if we dont its a string!!!!caugh!!!
    $auscores[($au->title)] = ($au->scores);
    // TODO now we can get the AU's satisifed FROM the CMI5 player.
    // TODO (for that matter couldn't we make it, notattempetd, satisifed, not satisfied??).
   /* foreach ($ausfromcmi5 as $key => $auinfo) {

        // Arra4ry to hold scores for AU.
        $sessionscores = array();

        if ($auinfo[$key]["lmsId"] == $aulmsid) {

            // Grab it's 'satisfied' info.
            $ausatisfied = $auinfo[$key]["satisfied"];
        }
    }

    // If the 'sessions' in this AU are null we know this hasn't even been attempted.
    if ($au->sessions == null ) {

        $austatus = "Not attempted";

    } else {

        // Retrieve AUs moveon specification.
        $aumoveon = $au->moveon;

        // If it's been attempted but no moveon value.
        if ($aumoveon == "NotApplicable") {
            $austatus = "viewed";
        } else { // IF it DOES have a moveon value.

            // If satisifed is returned true.
            if ($ausatisfied == "true") {

                $austatus = "Satisfied";
                // Also update AU.
                $au->satisfied = "true";
            } else {

                // If not, its in progress.
                $austatus = "In Progress";
                // Also update AU.
                $au->satisfied = "false";
            }
        };
        */
        // Ensure sessions are up to date.
        // Retrieve session ids.
        $sessionids = json_decode($au->sessions);

        // Iterate through each session by id.
        foreach ($sessionids as $key => $sessionid) {

            // Retrieve new info (if any) from CMI5 player on session.
            $session = $updatesession($sessionid, $cmi5launch->id);

            // Get progress from LRS.
          //  $session = $getprogress($registrationid, $cmi5launch->id, $session);
            //array to hold current session
            $currentattempt = array();
            // First set it's attempt number, start at 1
            // We need sto store all created at, and score, IN the one, then the one in the session scores

            //   $currentsession[] = ("1"=> ($session->createdAt));
            // Ok, so above, when session is returned we know there is no bracket
            // so maybe it happens here? 
            // Add score to array for AU.

            //We need to make this loop so i starts at one and increemtns
          // $sessionscores[] = ($i => $currentsession);


     
            // Update session in DB.
          // $DB->update_record('cmi5launch_sessions', $session);
        }

         // Save the session scores to AU, it is ok to overwrite.
         $au->scores = json_encode($sessionscores, JSON_NUMERIC_CHECK );
       
    
    

        // Create array of info to place in table.
        $auinfo = array();

        // Assign au name, progress, and index.
        $auinfo[] = $au->title;
        $auinfo[] = ($austatus);

        // Ok, now we need to retrieve the sessions and find the average score.
        $grade = 0;

    if ($au->moveon == "CompletedOrPassed" || "Passed") {

        // Currently it takes the highest grade out of sessions for grade.
        // Later this can be changed by linking it to plugin options.
        // However, since CMI5 player does not count any sessions after the first for scoring, by averaging we are adding unnessary.
        // 0', and artificailly lowering the grade.
        // Also, should we query for 'passed' or 'completed'? statements here?
        // Or can we have the cmi5player update our AU's moveon to 'passed' or 'completed'?

        if (!$sessionscores == null) {
            // If the grade is empty, we need to pass a null or NA.
            $grade = max($sessionscores);
            $au->grade = $grade;
            if ($grade == 0) {
                $auinfo[] = ("Passed");
            } else {
                $auinfo[] = ($grade);

                // MB, 
                // Could this be a good place to check and call update grades?
                // Or is that better done where  the session is updated? Cause that would be a constant check right
                //What is auinfo here, can we pass THIS to grade?
        
                // This may work, it has quiz and satisified! What if our update grades goes whereever this does?
                // Except! The freaing things has very specific params....
                // Dagum! So maybe can we grab this info ourselves? With these paramrs? 
                // Yeah grabbing the score will work, at elast for nwo

              //  cmi5launch_update_grades($cmi5launch, $USER->id);

            }

        } else {
            $auinfo[] = ("Not Applicable");
        }
    } else {

        if (!$sessionscores == null) {
            // If the grade is empty, we need to pass a null or NA.
            $grade = max($sessionscores);
            $au->grade = $grade;
            $auinfo[] = ($grade);

        } else {
            $auinfo[] = ("Not Attempted");
        }
    }



    // Or make a grade helper, but lets see if it's waht we need first.
// Should we take in AU id and use that? 
function cmi5launch_calculate_user_au_grade($auid)
{
    $connectors = new cmi5_connectors;
    $aushelpers = new au_helpers;
    $getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
    $getregistrationinfo = $connectors->cmi5launch_get_registration_with_get();
    $au = $getaus($auid);

    // Verify object is an au object.
    if (!is_a($au, 'mod_cmi5launch\local\au', false)) {

        $reason = "Excepted AU, found ";
        var_dump($au);
        throw new moodle_exception($reason, 'cmi5launch', '', $warnings[$reason]);
    }

    // Retrieve AU's lmsID.
    $aulmsid = $au->lmsid;

    // Query CMI5 player for updated registration info.
    //$registrationinfofromcmi5 = $getregistrationinfo($registrationid, $cmi5launch->id);
    // Take only info about AUs out of registrationinfofromcmi5.
  //  $ausfromcmi5 = array_chunk($registrationinfofromcmi5["metadata"]["moveOn"]["children"], 1, true);

    //We will make a func here for this, but right now, can we take
    // the au id and use it to get and save score to course?
    ///Ooooh yes, lets make an array to add to!

    //Wait, if this is PER au, then there should only be one grade, unless they do it multiple times dangit
    // if we decode its a dang array and if we dont its a string!!!!caugh!!!
    $auscores[($au->title)] = ($au->scores);
    // TODO now we can get the AU's satisifed FROM the CMI5 player.
    // TODO (for that matter couldn't we make it, notattempetd, satisifed, not satisfied??).
   /* foreach ($ausfromcmi5 as $key => $auinfo) {

        // Arra4ry to hold scores for AU.
        $sessionscores = array();

        if ($auinfo[$key]["lmsId"] == $aulmsid) {

            // Grab it's 'satisfied' info.
            $ausatisfied = $auinfo[$key]["satisfied"];
        }
    }

    // If the 'sessions' in this AU are null we know this hasn't even been attempted.
    if ($au->sessions == null ) {

        $austatus = "Not attempted";

    } else {

        // Retrieve AUs moveon specification.
        $aumoveon = $au->moveon;

        // If it's been attempted but no moveon value.
        if ($aumoveon == "NotApplicable") {
            $austatus = "viewed";
        } else { // IF it DOES have a moveon value.

            // If satisifed is returned true.
            if ($ausatisfied == "true") {

                $austatus = "Satisfied";
                // Also update AU.
                $au->satisfied = "true";
            } else {

                // If not, its in progress.
                $austatus = "In Progress";
                // Also update AU.
                $au->satisfied = "false";
            }
        };
        */
        // Ensure sessions are up to date.
        // Retrieve session ids.
        $sessionids = json_decode($au->sessions);

        // Iterate through each session by id.
        foreach ($sessionids as $key => $sessionid) {

            // Retrieve new info (if any) from CMI5 player on session.
            $session = $updatesession($sessionid, $cmi5launch->id);

            // Get progress from LRS.
          //  $session = $getprogress($registrationid, $cmi5launch->id, $session);
            //array to hold current session
            $currentattempt = array();
            // First set it's attempt number, start at 1
            // We need sto store all created at, and score, IN the one, then the one in the session scores

                $currentsession[] = ("1"=> ($session->createdAt));
            // Ok, so above, when session is returned we know there is no bracket
            // so maybe it happens here? 
            // Add score to array for AU.

            //We need to make this loop so i starts at one and increemtns
            $sessionscores[] = ($i => $currentsession);


     
            // Update session in DB.
          // $DB->update_record('cmi5launch_sessions', $session);
        }

         // Save the session scores to AU, it is ok to overwrite.
         $au->scores = json_encode($sessionscores, JSON_NUMERIC_CHECK );
       
    
    };

        // Create array of info to place in table.
        $auinfo = array();

        // Assign au name, progress, and index.
        $auinfo[] = $au->title;
        $auinfo[] = ($austatus);

        // Ok, now we need to retrieve the sessions and find the average score.
        $grade = 0;

    if ($au->moveon == "CompletedOrPassed" || "Passed") {

        // Currently it takes the highest grade out of sessions for grade.
        // Later this can be changed by linking it to plugin options.
        // However, since CMI5 player does not count any sessions after the first for scoring, by averaging we are adding unnessary.
        // 0', and artificailly lowering the grade.
        // Also, should we query for 'passed' or 'completed'? statements here?
        // Or can we have the cmi5player update our AU's moveon to 'passed' or 'completed'?

        if (!$sessionscores == null) {
            // If the grade is empty, we need to pass a null or NA.
            $grade = max($sessionscores);
            $au->grade = $grade;
            if ($grade == 0) {
                $auinfo[] = ("Passed");
            } else {
                $auinfo[] = ($grade);

                // MB, 
                // Could this be a good place to check and call update grades?
                // Or is that better done where  the session is updated? Cause that would be a constant check right
                //What is auinfo here, can we pass THIS to grade?
        
                // This may work, it has quiz and satisified! What if our update grades goes whereever this does?
                // Except! The freaing things has very specific params....
                // Dagum! So maybe can we grab this info ourselves? With these paramrs? 
                // Yeah grabbing the score will work, at elast for nwo

              //  cmi5launch_update_grades($cmi5launch, $USER->id);

            }

        } else {
            $auinfo[] = ("Not Applicable");
        }
    } else {

        if (!$sessionscores == null) {
            // If the grade is empty, we need to pass a null or NA.
            $grade = max($sessionscores);
            $au->grade = $grade;
            $auinfo[] = ($grade);

        } else {
            $auinfo[] = ("Not Attempted");
        }
    }
}
/* Generates and returns list of available Scorm report sub-plugins
 *
 * @param context context level to check caps against
 * @return array list of valid reports present
 */
function scorm_report_list($context) {
    global $CFG;
    static $reportlist;
    if (!empty($reportlist)) {
        return $reportlist;
    }
    $installed = core_component::get_plugin_list('scormreport');
    foreach ($installed as $reportname => $notused) {

        // Moodle 2.8+ style of autoloaded classes.
        $classname = "scormreport_$reportname\\report";
        if (class_exists($classname)) {
            $report = new $classname();

            if ($report->canview($context)) {
                $reportlist[] = $reportname;
            }
            continue;
        }

        // Legacy style of naming classes.
        $pluginfile = $CFG->dirroot.'/mod/scorm/report/'.$reportname.'/report.php';
        if (is_readable($pluginfile)) {
            debugging("Please use autoloaded classnames for your plugin. Refer MDL-46469 for details", DEBUG_DEVELOPER);
            include_once($pluginfile);
            $reportclassname = "scorm_{$reportname}_report";
            if (class_exists($reportclassname)) {
                $report = new $reportclassname();

                if ($report->canview($context)) {
                    $reportlist[] = $reportname;
                }
            }
        }
    }
    return $reportlist;
}
/**
 * Returns The maximum numbers of Questions associated with an Scorm Pack
 *
 * @param int Scorm ID
 * @return int an integer representing the question count
 */
function get_scorm_question_count($scormid) {
    global $DB;
    $count = 0;
    $params = array();
    $select = "scormid = ? AND ";
    $select .= $DB->sql_like("element", "?", false);
    $params[] = $scormid;
    $params[] = "cmi.interactions_%.id";
    $rs = $DB->get_recordset_select("scorm_scoes_track", $select, $params, 'element');
    $keywords = array("cmi.interactions_", ".id");
    if ($rs->valid()) {
        foreach ($rs as $record) {
            $num = trim(str_ireplace($keywords, '', $record->element));
            if (is_numeric($num) && $num > $count) {
                $count = $num;
            }
        }
        // Done as interactions start at 0 (do only if we have something to report).
        $count++;
    }
    $rs->close(); // Closing recordset.
    return $count;
}
