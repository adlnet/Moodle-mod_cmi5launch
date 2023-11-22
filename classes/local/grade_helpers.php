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
 * Class to handle Sessions.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\local;
defined('MOODLE_INTERNAL') || die();

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
    
use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\course;
use mod_cmi5launch\local\session_helpers;


/**
 * A class to help with grading functions such as LRS querying and grade calculation
 */
class grade_helpers
{
    public function get_cmi5launch_check_user_grades_for_updates()
    {
        return [$this, 'cmi5launch_check_user_grades_for_updates'];
    }


    public function get_cmi5launch_highest_grade()
    {
        return [$this, 'cmi5launch_highest_grade'];
    }

    public function get_cmi5launch_average_grade()
    {
        return [$this, 'cmi5launch_average_grade'];
    }

/**
 * Takes in an array of scores and returns the average grade
 * @param mixed $scores
 * @return int
 */
function cmi5launch_average_grade($scores)
{

    global $cmi5launch, $USER, $DB;

      //So if it is an array it doesn't work, can we check its a string and if NOT then json decode
      if (!$scores == null) {


        //same issue as in the other....
        // This is ridiculous, but I have to write my own sum? func to get around the php/moodle error

        //Brilliant!
        //array_sum($scores) / count($scores)
        // Find the average of the scores

        // what is scores here
  
        $averagegrade = (array_sum($scores) / count($scores));

        // so lets make an if clause that checks for them and removes them if found, that way it can handle both
        if(str_contains($averagegrade, "[")){
            $averagegrade = str_replace("[", "", $averagegrade);
        }
        // Now lets apply intval
        $averagegrade = intval($averagegrade);
        //what is it now
      
    }else{
        $averagegrade = 0;
    }


    return $averagegrade;

}
/**
 * Takes in an array of scores and returns the highest grade
 * @param mixed $scores
 * @return int
 */
function cmi5launch_highest_grade($scores)
{

    global $cmi5launch, $USER, $DB;

    // This is ridiculous, but I have to write my own MAX func to get around the php/moodle error
        // Highest equals 0 to start
        $highestgrade = 0;

      //So if it is an array it doesn't work, can we check its a string and if NOT then json decode
      //Is this the problme
      if (!$scores == null && is_array($scores)) {

        // This is ridiculous, but I have to write my own MAX func to get around the php/moodle error
        // Highest equals 0 to start
            $highestgrade = 0;
        foreach($scores as $key => $value){
            
            if($value > $highestgrade){
                $highestgrade = $value;
            }
        
        }
        //I UNDERSTAND!!! Sometimes it's an array and sometimes not,.
        // Depending on if they have more than one grade or not. Huh, so that's why I never noticed the error before
        // 
        //heres the problem i think, coming in as array here?
        /*  
        echo "<br>";
            echo "scores  ";
            var_dump($scores);
            echo "<br>";
            $top_score = array_search(max($scores), $scores); // john
            echo "<br>";
            echo "NOW WHAT IS TOP SCORE  ";
            var_dump($top_score);
            echo "<br>";
       */
            // Add score to array of scores
        //So...aparently know a named array doesn't work, it has to be just data, ie (1, 2, 3)
        //$highestgrade = (max($scores) );

        // so lets make an if clause that checks for them and removes them if found, that way it can handle both
     /*   if(str_contains($highestgrade, "[")){
            $highestgrade = str_replace("[", "", $highestgrade);
        }
        // Now lets apply intval
        $highestgrade = intval($highestgrade);
        //what is it now
      */
    }elseif($scores > $highestgrade && !is_array($scores)){ 
        $highestgrade = $scores;

    } else {
        $highestgrade = 0;
        }

        //This is coming in array? That's why str_contains is throwing an error
   // Format answer before returning     
   if(str_contains($highestgrade, "[")){
            $highestgrade = str_replace("[", "", $highestgrade);
        }
        // Now lets apply intval
        $highestgrade = intval($highestgrade);

    return $highestgrade;

}

    /**
     * Parses and retrieves AUs from the returned info from CMI5 player.
     * @param array $users - an array of enrolled users
     * @return array
     */
    public function cmi5launch_check_user_grades_for_updates($user)
    {

        // Bring in functions and classes.
        $progress = new progress;
        $aushelpers = new au_helpers;
        $connectors = new cmi5_connectors;
        $sessionhelpers = new session_helpers;

        // Functions from other classes.
        $saveaus = $aushelpers->get_cmi5launch_save_aus();
        $createaus = $aushelpers->get_cmi5launch_create_aus();
        $getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
        // Classes and functions.
        $auhelper = new au_helpers;
        $sessionhelper = new session_helpers;
        $progress = new progress;
        $getprogress = $progress->cmi5launch_get_retrieve_statements();
        $updatesession = $sessionhelper->cmi5launch_get_update_session();
        $retrieveaus = $auhelper->get_cmi5launch_retrieve_aus_from_db();

        global $cmi5launch, $USER, $DB;

        /// $record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));\
        // maybe just have it do one user?
        ////////    foreach ($users as $user) {
        // II see, this may be because if the user hasn't done anything in class yet? 

        //Is it getting the wron course? Why is there more than oine?

        //There needs to be an EXISTS here!

        $exists = $DB->record_exists('cmi5launch_course', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);
        if (!$exists == false) {

            // Well here's the problem! IT's getting the global or signed in user
            $userscourse = $DB->get_record('cmi5launch_course', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);

            // Maybe best to holds all session ids in one HUGGE array 
            $allsessids = array();
            // Userrecord may be null if user has not aprticipated in course yet
            if (!$userscourse == null) {
                // right not returning rades her just updating record  $userscorerseid" UNIQ= " ";
                // Retrieve registration id.
                $registrationid = $userscourse->registrationid;

                // Retrieve AU ids.
                $auids = (json_decode($userscourse->aus));
                // ok so we want to update both sessions and auid, can we do this with nested au>session?
                // statment?
                // Go through each Au, each Au will be responsible for updating its own session
                // The user has Au, the Aus have sessions. Each user will pull out their Aus, and update each one
                // Each AU will update its sessions, and then update its own grades

               
                // Array to hold Au scores!
                $auscores = array();

                foreach ($auids as $key => $auid) {
                     //Array to hold session scores for update
                $sessiongrades = array();
                    $au = $getaus($auid);

                    // This uses the auid to pull the right record from the aus table
                    $aurecord = $DB->get_record('cmi5launch_aus', ['id' => $auid]);

                    // When it is not null this is our aurecord
                    if (!$aurecord == null || false) {

                        // Retrieve the registration id.
                        $registrationid = $aurecord->registrationid;
                        //Now retrieve the AU's sessions
                        // Retrieve session ids for this course.
                        // $sessions = json_decode($aurecord->sessions, true);


                        //Ahhh the problem is this is passing in null, sometimes there ar eno sessions!!
                        // There may be more than one session
                        if (!$aurecord->sessions == null) {
                            // Retrieve session ids for this course.
                            $sessions = json_decode($aurecord->sessions, true);
                            foreach ($sessions as $sessionid) {

                                // Can we just retrieve the session from DB? Since we are writing a cron to udate
                                // session
                                $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid]);

                                //ok, now here is where we want to query the LRS for sessions updates 
                                // Retrieve new info (if any) from CMI5 player on session.
                                $session = $updatesession($sessionid, $cmi5launch->id);

                                //And here is also gets the progress stuff from lrs
                                // Get progress from LRS.
                            //    $session = $getprogress($registrationid, $cmi5launch->id, $session);

                                // Update session in DB.
                              //  $DB->update_record('cmi5launch_sessions', $session);

                                //Now if the session is complete, we want to update the AU
                                // Also if terminated
                                // The idea is these come in order, so the last one is the current status, so update on each one,
                                // overwrite as you go, and the last one if ifnal.
                                if ($session->iscompleted == 1) {
                                    // 0 is no 1 is yes, these are from players
                                    $aurecord->completed = 1;
                                }
                                if ($session->ispassed == 1) {
                                    // 0 is no 1 is yes, these are from players
                                    $aurecord->passed = 1;
                                }

                                //Add the session grade to array
                                $sessiongrades[] = $session->score;

                                // Update session in DB.
                               // $DB->update_record('cmi5launch_sessions', $session);
                            }
                            // Save the session scores to AU, it is ok to overwrite.
                            $aurecord->scores = json_encode($sessiongrades);

                            // can we access the gradetype here?
                            $gradetype = cmi5launch_retrieve_gradetype();

                            //YES! So know we can update AU grade!
                            // Determine gradetype and use it to save overall grade to 
                            // Thats the problem we are not entering switch
                          
                          /*  echo "<br>";
                            echo "What is gradetype??";
                            var_dump($gradetype);
                            echo "<br>";
*/

                            switch($gradetype){
                                case "Highest":

                                    $aurecord->grade = $this->cmi5launch_highest_grade($sessiongrades);
                                    break;
                                case "Average":
                                    $aurecord->grade = $this->cmi5launch_average_grade($sessiongrades);
                                    break;
                                default:
                                echo "Gradetype not found.";
                            }
                       
                            //
                            // ok so that handled sessions, now we need to update AU rihgt?
                            $aurecord = $DB->update_record('cmi5launch_aus', $aurecord);
                            $auscores[($au->title)] = ($au->scores);
                        } 
                    }
                    
                }
                $userscourse->ausgrades = json_encode($auscores);
                $updated = $DB->update_record("cmi5launch_course", $userscourse);

            } else {
             //   $sessiongrades[] = 0;
            }
            //REturn scores
            return $sessiongrades;
        } else {

            // Do nothing, there is no record for this user in this course
            return false;

        }
    }
}