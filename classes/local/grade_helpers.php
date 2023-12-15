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
 *  A class to help with grading functions such as LRS querying and grade calculation
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\local;

defined('MOODLE_INTERNAL') || die();

use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\progress;
use mod_cmi5launch\local\session_helpers;

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
    // What is scores? 
    //why is it erasing scores>?
        echo "<br>";
        echo " Average rade immedietlaye passed into func is :";
        var_dump($scores);
        echo "<br>";
        // If it isn't an array it (array_sum) doesn't work, check if it's a string and if NOT then json decode
        if (!$scores == null && is_array($scores)) {

            echo "1";
            // Find the average of the scores
            $averagegrade = (array_sum($scores) / count($scores));

            // If string it sometimes has brackets, check for them and remove them if found.
          /*  if (str_contains($averagegrade, "[")) {
                $averagegrade = str_replace("[", "", $averagegrade);
            }
            */
            // Apply intval if string
            $averagegrade = intval($averagegrade);
        
        } elseif  (!$scores == null && !is_array($scores)) {
            echo "2";
            
            // If it's an int, it's a single value so average is itself.
            $averagegrade = $scores;

              // If string it sometimes has brackets, check for them and remove them if found.
          /*   if (str_contains($averagegrade, "[")) {
                $averagegrade = str_replace("[", "", $averagegrade);
            }*/
        }
        else {
            echo "3";
            $averagegrade = 0;
        }

          // Remove [] from userscore if they are there.
          $toremove = array("[", "]");
          if ($averagegrade != null && str_contains($averagegrade, "[")) {
            $averagegrade = str_replace($toremove, "", $averagegrade);
          }

           // Now apply intval.
           $averagegrade = intval($averagegrade);
        // and cast it to int

        //What is average grade is a string??
        echo "<br>";
        echo " Average rade before return is :";
        var_dump($averagegrade);
        echo "<br>";
    return $averagegrade;

}

/**
 * Takes in an array of scores and returns the highest grade.
 * @param mixed $scores
 * @return int
 */
function cmi5launch_highest_grade($scores)
{
    global $cmi5launch, $USER, $DB;
        
    // Highest equals 0 to start
    $highestgrade = 0;

    //So if it is an array it doesn't work, can we check its a string and if NOT then json decode
    //Is this the problme
    if (!$scores == null && is_array($scores)) {

        // MB- This is ridiculous, but I have to write my own MAX func to get around the php/moodle error until it is resolved.
        // Highest equals 0 to start
        $highestgrade = 0;
        
        foreach($scores as $key => $value){
            
            if($value > $highestgrade){
                $highestgrade = $value;
            }
        }

    } elseif ($scores > $highestgrade && !is_array($scores)){ 
        
        $highestgrade = $scores; 

    } else {
        $highestgrade = 0;
        }

    // Sometimes there will be brackets, check for them and remove them if found.  
   if (str_contains($highestgrade, "[")) {
            $highestgrade = str_replace("[", "", $highestgrade);
        }
        // Now apply intval.
        $highestgrade = intval($highestgrade);

    return $highestgrade;

}

    /**
     * Parses and retrieves AUs and their sessions from the returned info from CMI5 player and LRS.
     * 
     * @param array $user - the user whose grades are being updated
     * @return array
     */
    public function cmi5launch_check_user_grades_for_updates($user)
    {

        // Bring in functions and classes.
        $sessionhelper = new session_helpers;

        // Functions from other classes.
        $updatesession = $sessionhelper->cmi5launch_get_update_session();

        global $cmi5launch, $USER, $DB;

        // Check if record already exists.
        $exists = $DB->record_exists('cmi5launch_course', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);
        
        // If it exists, we want to update it.
        if (!$exists == false) {

            // Retrieve the record.
            $userscourse = $DB->get_record('cmi5launch_course', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);

            // User record may be null if user has not participated in course yet.
            if (!$userscourse == null) {
             
                // Retrieve AU ids.
                $auids = (json_decode($userscourse->aus));
                
                // Array to hold Au scores.
                $auscores = array();

                // Go through each Au, each Au will be responsible for updating its own session.
                foreach ($auids as $key => $auid) {
                
                    // Array to hold session scores for update.
                    $sessiongrades = array();
            
                    // This uses the auid to pull the right record from the aus table.
                    $aurecord = $DB->get_record('cmi5launch_aus', ['id' => $auid]);

                    //
                    /*
                    echo "<br>";
                    echo "Aurecord is :";
                    var_dump($aurecord);
                    echo "<br>";
                    */
                    // When it is null it is because the user has not launched the AU yet.
                    if (!$aurecord == null || false) {

                        // Check if there are sessions.
                        if (!$aurecord->sessions == null) {

                            // Retrieve session ids for this course. (There may be more than one session.)
                            $sessions = json_decode($aurecord->sessions, true);
                            
                            // Iterate through each session.
                            foreach ($sessions as $sessionid) {

                                // Using current session id, retriev esession from DB. 
                            
                                $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid]);
                            /*
                                echo "<br>";
                                echo "what is sSession is before update sessionssss :";
                                var_dump($session);
                                echo "<br>";
                              */
                                // Retrieve new info (if any) from CMI5 player and LRS on session.
                                $session = $updatesession($sessionid, $cmi5launch->id, $user);
                
                                // Now if the session is complete, passed, or terminated, we want to update the AU.
                                // These come in order, so the last one is the current status, so update on each one,
                                // overwrite as you go, and the last one if final.
                                if ($session->iscompleted == 1) {
                                    // 0 is no 1 is yes, these are from players
                                    $aurecord->completed = 1;
                                }
                                if ($session->ispassed == 1) {
                                    // 0 is no 1 is yes, these are from players
                                    $aurecord->passed = 1;
                                }
                                if ($session->isterminated == 1) {
                                    // 0 is no 1 is yes, these are from players
                                    $aurecord->terminated = 1;
                                }

                                //Add the session grade to array
                                $sessiongrades[] = $session->score;
                            }
                            // Save the session scores to AU, it is ok to overwrite.
                            $aurecord->scores = json_encode($sessiongrades, JSON_NUMERIC_CHECK);

                            // Determine gradetype and use it to save overall grade to AU.
                            $gradetype = cmi5launch_retrieve_gradetype();
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
                       
                            // Save Au title and their scores to AU
                            $auscores[($aurecord->title)] = ($aurecord->scores);
                            // Save updates to DB.
                            $aurecord = $DB->update_record('cmi5launch_aus', $aurecord);
                        } 
                    }
                }

                echo "<br>";
                echo "Auscores before return is :";
                var_dump($auscores);
                echo "<br>";
                // Update course record.
                $userscourse->ausgrades = json_encode($auscores);
                $DB->update_record("cmi5launch_course", $userscourse);

            } 
            // Return scores.
            return $sessiongrades;
        
        } else {
 
            // Do nothing, there is no record for this user in this course
            return false;

        }
    }
}