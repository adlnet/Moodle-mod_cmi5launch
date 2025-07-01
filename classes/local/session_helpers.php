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
 * Helper class for sessions -MB
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

namespace mod_cmi5launch\local;

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\session;

/**
 * Class of helper functions for sessions.
 */
class session_helpers {
    /**
     * Returns the function to create a session.
     *
     * @return callable
     */
    public function cmi5launch_get_create_session() {
        return [$this, 'cmi5launch_create_session'];
    }
    /**
     * Returns the function to update a session.
     *
     * @return callable
     */
    public function cmi5launch_get_update_session() {
        return [$this, 'cmi5launch_update_sessions'];
    }
    /**
     * Returns the function to retrieve a session from the db.
     *
     * @return callable
     */
    public function cmi5launch_get_retrieve_sessions_from_db() {
        return [$this, 'cmi5launch_retrieve_sessions_from_db'];
    }
    /**
     * Gets updated session information from CMI5 player
     * @param Progress $progress - The progress class- to use it's functions.
     * @param Cmi5_connectors $cmi5 - The cmi5 connector class- to use it's functions.
     * @param int $sessionid - The session id.
     * @param int $cmi5launchid - The cmi5 instance id.
     * @param /User $user - The user object.
     * @return object - The session updated session pobject.
     */
    public function cmi5launch_update_sessions($progress, $cmi5, $sessionid, $cmi5launchid, $user) {

        global $CFG, $DB, $cmi5launch, $USER;

        // Set error and exception handler to catch and override the default PHP error messages, make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\progresslrs_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_progresslrs');

        $connector = new $cmi5();
        $progress = new $progress();
        $getsessioninfo = $connector->cmi5launch_get_session_info();
        $getprogress = $progress->cmi5launch_get_retrieve_statements();

        try {
            // Get the session from DB with session id.
            $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid, 'userid' => $user->id]);

            // Reload cmi5 instance.
            $record = $DB->get_record('cmi5launch', ['id' => $cmi5launchid]);

            // Reload user course instance.
            $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $record->courseid, 'userid' => $user->id]);
            // Get updates from the LRS as well.
            $session = $getprogress($userscourse->registrationid, $session);
            // Get updates from cmi5player.
            // This is sessioninfo from CMI5 player.
            $sessioninfo = json_decode($getsessioninfo($sessionid, $cmi5launchid), true);
            // Update session.
            foreach ($sessioninfo as $key => $value) {

                // We don't want to overwrite ids.
                // If the property exists and it's not id or sessionid, set it to lowercase and
                // encode value if it is array. (DB needs properties in lowercase, but player returns camelcase).

                // If it's an array, encode it so it can be saved to DB.
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if (is_string($key)) {
                    $key = mb_convert_case($key, MB_CASE_LOWER, "UTF-8");
                }

                if (property_exists($session, $key) && $key != 'id' && $key != 'sessionid') {

                    // If it's an array, encode it so it can be saved to DB.
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    if (is_string($key)) {
                        $key = mb_convert_case($key, MB_CASE_LOWER, "UTF-8");
                    }

                    $session->$key = $value;
                }
            }

            // Now update to table.
            $DB->update_record('cmi5launch_sessions', $session);

        } catch (\Throwable $e) {

            // Restore default handlers.
            restore_exception_handler();
            restore_error_handler();

            // If there is an error, return the error.
            throw new nullException(get_string('cmi5launchsessionupdateerror', 'cmi5launch'). $e->getMessage());

        }
        // Restore default handlers.
        restore_exception_handler();
        restore_error_handler();

        return $session;
    }

    /**
     * Creates a session record in DB.
     * @param mixed $sessionid - the session id
     * @param mixed $launchurl - the launch url
     * @param mixed $launchmethod - the launch method
     * @return void
     */
    public function cmi5launch_create_session($sessionid, $launchurl, $launchmethod) {

        global $DB, $CFG, $cmi5launch, $USER;

        // Set error and exception handler to catch and override the default PHP error messages, make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\progresslrs_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_progresslrs');

        // Put a try here to catch if anything goes wrong.
        try {

            $table = "cmi5launch_sessions";

            // Make a new record to save.
            $newrecord = new \stdClass();
            // Because of many nested properties, needs to be done manually.
            $newrecord->sessionid = $sessionid;
            $newrecord->launchurl = $launchurl;
            $newrecord->tenantname = $USER->username;
            $newrecord->launchmethod = $launchmethod;
            $newrecord->moodlecourseid = $cmi5launch->id;
            $newrecord->userid = $USER->id;

            // Save record to table.
            $newid = $DB->insert_record($table, $newrecord, true);

            // Instantiate progress and cmi5_connectors class to pass.
            $progress = new progress;
            $cmi5 = new cmi5_connectors;

            // Retrieve new info (if any) from CMI5 player and LRS on session.
            $session = $this->cmi5launch_update_sessions($progress, $cmi5, $sessionid, $cmi5launch->id, $USER);

            // Restore default handlers.
            restore_exception_handler();
            restore_error_handler();

            // Return value.
            return $newid;
        } catch (\Throwable $e) {

            // Restore default handlers.
            restore_exception_handler();
            restore_error_handler();

            // If there is an error, return the error.
            throw new nullException(get_string('cmi5launchsessioncreationerror', 'cmi5launch') . $e->getMessage());

        }
    }


    /**
     * Retrieves session from DB.
     * @param int $sessionid - the session id.
     * @return object session - the session object.
     */
    public function cmi5launch_retrieve_sessions_from_db($sessionid) {

        global $DB, $CFG;

        $check = $DB->record_exists('cmi5launch_sessions', ['sessionid' => $sessionid], '*', IGNORE_MISSING);

        // If check is negative, the record does not exist. Throw error.
        if (!$check) {

            echo get_string('cmi5launchsessionretrievederror', 'cmi5launch');
            echo "<pre>";
            var_dump($sessionid);
            echo "</pre>";

        } else {

            $sessionitem = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);

            $session = new session($sessionitem);

        }

        // Return new session object.
        return $session;
    }

}

