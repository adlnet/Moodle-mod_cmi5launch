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
 *  A class to help with grading functions such as LRS querying and grade calculation.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

namespace mod_cmi5launch\local;

defined('MOODLE_INTERNAL') || die();

use mod_cmi5launch\local\session_helpers;
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');
require_once($CFG->dirroot . '/mod/cmi5launch/constants.php');

class grade_helpers {

    public function get_cmi5launch_update_au_for_user_grades() {
        return [$this, 'cmi5launch_update_au_for_user_grades'];
    }

    public function get_cmi5launch_check_user_grades_for_updates() {
        return [$this, 'cmi5launch_check_user_grades_for_updates'];
    }

    public function get_cmi5launch_highest_grade() {
        return [$this, 'cmi5launch_highest_grade'];
    }

    public function get_cmi5launch_average_grade() {
        return [$this, 'cmi5launch_average_grade'];
    }

    public function cmi5launch_get_grade_method_array() {
        return [$this, 'cmi5launch_grade_method_array'];
    }

    public function cmi5launch_fetch_attempts_array() {
        return [$this, 'cmi5launch_get_attempts_array'];
    }

    public function cmi5launch_fetch_what_grade_array() {
        return [$this, 'cmi5launch_get_what_grade_array'];
    }


    /**
     * Returns an array of the array of what grade options are availabe.
     *
     * @return array an array of what grade options
     */
    function cmi5launch_get_what_grade_array() {
        return  [MOD_CMI5LAUNCH_HIGHEST_ATTEMPT => get_string('mod_cmi5launch_highest_attempt', 'cmi5launch'),
                    MOD_CMI5LAUNCH_AVERAGE_ATTEMPT => get_string('mod_cmi5launch_average_attempt', 'cmi5launch'),
                    MOD_CMI5LAUNCH_FIRST_ATTEMPT => get_string('mod_cmi5launch_first_attempt', 'cmi5launch'),
                    MOD_CMI5LAUNCH_LAST_ATTEMPT => get_string('mod_cmi5launch_last_attempt', 'cmi5launch')];
    }


    /**
     * Returns an array of the array of what grade options
     *
     * @return array an array of what grade options
     */
    function cmi5launch_grade_method_array() {
        return  [
                    MOD_CMI5LAUNCH_GRADE_HIGHEST => get_string('mod_cmi5launch_grade_highest', 'cmi5launch'),
                    MOD_CMI5LAUNCH_GRADE_AVERAGE => get_string('mod_cmi5launch_grade_average', 'cmi5launch'),
        ];
    }

        /**
         * Returns an array of the array of attempt options
         *
         * @return array an array of attempt options
         */
    function cmi5launch_get_attempts_array() {
        $attempts = [0 => get_string('cmi5launchnolimit', 'cmi5launch'),
                        1 => get_string('cmi5launchattempt1', 'cmi5launch')];

        for ($i = 2; $i <= 6; $i++) {
            $attempts[$i] = get_string('cmi5launchattemptsx', 'cmi5launch', $i);
        }

        return $attempts;
    }



    /**
     * Takes in an array of scores and returns the average grade.
     * @param mixed $scores - either an array of numbers or string of numbers to be converted to array
     *                      - or a singular int.
     * @return int
     */
    public function cmi5launch_average_grade($scores) {

        global $cmi5launch, $USER, $DB;

        // If it comes in as string, convert to array.
        if (is_string($scores)) {

            $scores = json_decode($scores, true);
        }

        // If it isn't an array it (array_sum) doesn't work.
        if (!$scores == null && is_array($scores)) {

            // Find the average of the scores.
            $averagegrade = (array_sum($scores) / count($scores));

        } else if (!$scores == null && !is_array($scores)) {

            // If it's an int, it's a single value so average is itself.
            $averagegrade = $scores;

        } else {
            $averagegrade = 0;

        }

        // Now apply intval.
        $averagegrade = intval($averagegrade);

        return $averagegrade;
    }

    /**
     * Takes in an array of scores and returns the highest grade.
     * @param mixed $scores
     * @return int
     */
    public function cmi5launch_highest_grade($scores) {

        global $cmi5launch, $USER, $DB;

        // Highest equals 0 to start.
        $highestgrade = 0;

        // First check if scores is a string, if a string we need it to be array.
        if (is_string($scores)) {
            $scores = json_decode($scores, true);
        }

        // if it is an object print so i know what it is
        if (is_object($scores)) {
            var_dump($scores);
        }
        if (!$scores == null && is_array($scores)) {

            // Find the highest grade.
            $highestgrade = max($scores);

        } else if ($scores > $highestgrade && !is_array($scores) && is_numeric($scores)) {

            // If it's an int, it's a single value so highest is itself.
            $highestgrade = $scores;
        }

        // Now apply intval.
        $highestgrade = intval($highestgrade);

        return $highestgrade;
    }


    /**
     * Parses and retrieves AUs and their sessions from the returned info from CMI5 player and LRS and updates them.
     * @param array $user - the user whose grades are being updated.
     * @return array
     */
    public function cmi5launch_check_user_grades_for_updates($user, ) {

        global $cmi5launch, $USER, $DB;

        // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\grade_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_grade');

        // Check if record already exists.
        $exists = $DB->record_exists('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);

        try {
            // If it exists, we want to update it.
            if (!$exists == false) {

                // Retrieve the record.
                $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $user->id]);

                $auids = json_decode($userscourse->aus);

                // Bring in functions and classes.
                $sessionhelper = new session_helpers;

                $returnedinfo = $this->cmi5launch_update_au_for_user_grades($sessionhelper, $auids, $user);
                // Array to hold AU scores.
                $auscores = $returnedinfo[0];
                $overallgrade = $returnedinfo[1];

                // Update course record.
                $userscourse->ausgrades = json_encode($auscores);
                $DB->update_record("cmi5launch_usercourse", $userscourse);
                // Restore default hadlers.
                restore_exception_handler();
                restore_error_handler();
                // Return scores.
                return $overallgrade;

            } else {

                $nograde = [0 => get_string('cmi5launchnogradeerror', 'cmi5launch')];
                // Do nothing, there is no record for this user in this course.
                // Restore default hadlers.
                restore_exception_handler();
                restore_error_handler();
                return $nograde;

            }
        } catch (\Throwable $e) {

            // If there is an error, return the error.
            echo(get_string('cmi5launchgradeerror', 'cmi5launch') . $e->getMessage());
            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();
            return $e;
        }
    }

    /*
     *  Go through each Au, each Au will be responsible for updating its own session.
     * @param mixed $auid
     * @return au|bool
     */
    public function cmi5launch_update_au_for_user_grades($sessionhelpers, $auids, $user) {
        global $cmi5launch, $USER, $DB;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Instantiate progress and cmi5_connectors class to pass.
        $progress = new progress;
        $cmi5 = new cmi5_connectors;

        // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\grade_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_grade');

        // Array to hold AU scores.
        $auscores = [];
        $overallgrade = [];
        try {
            // Bring in functions and classes.
            $sessionhelper = $sessionhelpers;

            // Functions from other classes.
            $updatesession = $sessionhelper->cmi5launch_get_update_session();

            // Go through each Au, each Au will be responsible for updating its own session.
            foreach ($auids as $key => $auid) {

                // Array to hold session scores for update.
                $sessiongrades = [];

                // This uses the auid to pull the right record from the aus table.
                $aurecord = $DB->get_record('cmi5launch_aus', ['id' => $auid]);

                // When it is null it is because the user has not launched the AU yet.
                if (!$aurecord == null || false) {

                    // Check if there are sessions.
                    if (!$aurecord->sessions == null) {

                        // Retrieve session ids for this course. There may be more than one session.
                        $sessions = json_decode($aurecord->sessions, true);

                        // Iterate through each session.
                        foreach ($sessions as $sessionid) {

                            // Using current session id, retrieve session from DB.
                            $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid, 'userid' => $user->id, "moodlecourseid" => $cmi5launch->id]);

                            // Retrieve new info (if any) from CMI5 player and LRS on session.
                            $session = $updatesession($progress, $cmi5, $sessionid, $cmi5launch->id, $user);

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

                            // Add the session grade to array.
                            $sessiongrades[] = $session->score;
                        }
                        // Save the session scores to AU, it is ok to overwrite.
                        $aurecord->scores = json_encode($sessiongrades, JSON_NUMERIC_CHECK);

                        // Determine gradetype and use it to save overall grade to AU.
                        $gradetype = $cmi5launchsettings["grademethod"];

                        switch ($gradetype) {

                            // GRADE_AUS_CMI5 = 0.
                            // GRADE_HIGHEST_CMI5 = 1.
                            // GRADE_AVERAGE_CMI5 =  2.
                            // GRADE_SUM_CMI5 = 3.

                            case 1:
                                $aurecord->grade = $this->cmi5launch_highest_grade($sessiongrades);
                                break;
                            case 2:
                                $aurecord->grade = $this->cmi5launch_average_grade($sessiongrades);
                                break;
                            default:

                                echo(get_string('cmi5launchgradetypenotfound', 'cmi5launch'));
                        }

                        // Save AU scores to corresponding title.
                        $auscores[$aurecord->lmsid] = [$aurecord->title => $aurecord->scores];

                        // Save an overall grade \to be passed out to grade_update.
                        $overallgrade = $aurecord->grade;

                        // Save Au title and their scores to AU.
                        // Save updates to DB.
                        $aurecord = $DB->update_record('cmi5launch_aus', $aurecord);

                    }
                }
            }

            // Array to hold answer.
            $toreturn = [0 => $auscores, 1 => $overallgrade];

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            return $toreturn;
        } catch (\Throwable $e) {

            // Restore default handlers.
            restore_exception_handler();
            restore_error_handler();

            // If there is an error, return the error.
            throw new nullException(get_string('cmi5launchgradeerror', 'cmi5launch') . $e->getMessage());
        }
    }
}
