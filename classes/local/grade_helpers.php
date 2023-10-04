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
    public function get_cmi5launch_check_user_grades_for_updates() {
        return [$this, 'cmi5launch_check_user_grades_for_updates'];
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
                $auids = (json_decode($userscourse->aus) );
                // ok so we want to update both sessions and auid, can we do this with nested au>session?
                // statment?
                // Go through each Au, each Au will be responsible for updating its own session
                // The user has Au, the Aus have sessions. Each user will pull out their Aus, and update each one
                // Each AU will update its sessions, and then update its own grades
                
                //Array to hold session scores for update
                $sessiongrades = array();
                foreach ($auids as $key => $auid) {
                    //$au = $getaus($auid);
            
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
                            $session = $getprogress($registrationid, $cmi5launch->id, $session);

                            // Update session in DB.
                            $DB->update_record('cmi5launch_sessions', $session);

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

                        }
                        //Update au score
                        $aurecord->scores = json_encode($sessiongrades);
                        // ok so that handled sessions, now we need to update AU rihgt?
                    } else{
                        //$sessiongrades[] = 0;
                    }
                    //So like query LRS, update DB. But whats most efficient way to??
                    // I think we can trim session table down, like it doesn't need to know au is complete right?

                    //Now, update AU in DB
                    $au = $DB->update_record('cmi5launch_aus', $aurecord);

              
                    }

                }
            }
            else{
                $sessiongrades[] = 0;
            }
            //REturn scores
            return $sessiongrades;
        }
    }
//}