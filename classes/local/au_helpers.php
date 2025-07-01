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
 * Helper class for AUs
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */
namespace mod_cmi5launch\local;

defined('MOODLE_INTERNAL') || die();

use mod_cmi5launch\local\au;

global $CFG;
// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

/**
 * Class au_helpers
 *
 * This class contains helper functions for handling AUs in the cmi5launch module.
 * It provides methods to retrieve, create, save, and update AUs.
 *
 * @package mod_cmi5launch\local
 */
class au_helpers {

    /**
     * Returns this class's function that retrieve AUs.
     * @return callable
     */
    public function get_cmi5launch_retrieve_aus() {
        return [$this, 'cmi5launch_retrieve_aus'];
    }
    /**
     * Returns this class's function that creates AUs.
     * @return callable
     */
    public function get_cmi5launch_create_aus() {
        return [$this, 'cmi5launch_create_aus'];
    }
    /**
     * Returns this class's function that saves AUs.
     * @return callable
     */
    public function get_cmi5launch_save_aus() {
        return [$this, 'cmi5launch_save_aus'];
    }
    /**
     * Returns this class's function that retrieves AUs from the DB.
     * @return callable
     */
    public function get_cmi5launch_retrieve_aus_from_db() {
        return [$this, 'cmi5launch_retrieve_aus_from_db'];
    }
    /**
     * Returns this class's function that updates AUs from the grades.
     * @return callable
     */
    public function get_cmi5launch_update_au_for_user_grades() {
        return [$this, 'cmi5launch_update_au_for_user_grades'];
    }
    /**
     * Parses and retrieves AUs from the returned info from CMI5 player.
     * @param mixed $returnedinfo
     * @return array
     */
    public function cmi5launch_retrieve_aus($returnedinfo) {

        $resultchunked = "";

        // Use our own more specific error handler, to give better info to user.
        set_error_handler('mod_cmi5launch\local\array_chunk_warning', E_WARNING);

        // The results come back as nested array under more then just AUs.
        // We only want the info pertaining to the AU. However, if the wrong info is passed array_chunk will throw an exception.
        try {
            $resultchunked = array_chunk($returnedinfo["metadata"]["aus"], 1, );
        } catch (\Exception $e) {

            echo ( get_string('cmi5launchaucannotretrieve', 'cmi5launch') . "\n"
                . $e->getMessage() . "\n");
        }

        // Restore the error handler.
        restore_error_handler();

        return $resultchunked;
    }


    /**
     * It is fed an array of statements that then assigns the values to
     * several aus, and returns them as au objects.
     * @param mixed $austatements
     * @return array<au>
     */
    public function cmi5launch_create_aus($austatements) {
        // Needs to return our new AU objects.
        $newaus = [];

        // We should not be able to get here but what if null is pulled from record and passed in?
        // So in case it is given null.
        if ($austatements == null) {

            throw new nullException(get_string('cmi5launchaucannotretrievedb', 'cmi5launch') . $austatements, 0);

        } else {
            foreach ($austatements as $int => $info) {

                // The aus come back decoded from DB nestled in an array.
                // So they are the first key, which is '0'.
                $statement = $info[0];

                $au = new au($statement);

                // Assign the newly created au to the return array.
                $newaus[] = $au;
            }

            // Return our new list of AU.
            return $newaus;
        }
    }

    /**
     * Takes a list of AUs and a record and saves to the DB.
     * @param mixed $auobjectarray
     * @return array
     */
    public function cmi5launch_save_aus($auobjectarray) {
        // Add userid to the record.
        global $DB, $USER, $cmi5launch;
        $table = "cmi5launch_aus";

        // An array to hold the created ids.
        $auids = [];

        // Variables for error over and exception handling.
        // Array of all items in new record, this will be useful for troubleshooting.
        $newrecorditems = ['id', 'attempt', 'auid', 'launchmethod', 'lmsid', 'url', 'type', 'title', 'moveon', 
            'auindex', 'parents', 'objectives', 'description', 'activitytype', 'masteryscore', 'completed', 'passed', 'inprogress',
            'noattempt', 'satisfied', 'moodlecourseid'];
        $currentrecord = 1;
        $newid = "";
        $newrecord = "";

        // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\sifting_data_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_au');

        // Check it's not null.
        if ($auobjectarray == null) {

              // Restore default hadlers.
              restore_exception_handler();
              restore_error_handler();

            throw new nullException(get_string('cmi5launchaucannotsave', 'cmi5launch'), 0);

        } else {

            // For each AU in array build a new record and save it.
            // Because of so many nested variables this needs to be done manually.
            foreach ($auobjectarray as $auobject) {

                // A try statement to catch any errors that may be thrown.
                try {
                    // Make a newrecord to save.
                    $newrecord = new \stdClass();

                    // Assign the values to the new record.
                    $newrecord->userid = $USER->id;
                    $newrecord->attempt = $auobject->attempt;
                    $newrecord->auid = $auobject->id;
                    $newrecord->launchmethod = $auobject->launchMethod;
                    $newrecord->lmsid = json_decode(json_encode($auobject->lmsId, true));
                    $newrecord->url = $auobject->url;
                    $newrecord->type = $auobject->type;

                    // Apparently this convoluted method is necessary due to nature of php unit tests -MB.
                    $title = json_decode(json_encode($auobject->title), true);
                    $newrecord->title = $title[0]['text'];
                    $newrecord->moveon = $auobject->moveOn;
                    $newrecord->auindex = $auobject->auIndex;
                    $newrecord->parents = json_encode($auobject->parents, true);
                    $newrecord->objectives = json_encode($auobject->objectives);
                    $desc = json_decode(json_encode($auobject->description), true);
                    $newrecord->description = $desc[0]['text'];
                    $newrecord->activitytype = $auobject->activityType;
                    $newrecord->masteryscore = $auobject->masteryscore;
                    $newrecord->completed = $auobject->completed;
                    $newrecord->passed = $auobject->passed;
                    $newrecord->inprogress = $auobject->inprogress;
                    $newrecord->noattempt = $auobject->noattempt;
                    $newrecord->satisfied = $auobject->satisfied;
                    $newrecord->moodlecourseid = $cmi5launch->id;

                    // Save the record and get the new id.
                    $newid = $DB->insert_record($table, $newrecord, true);

                    // Save new id to list to pass back.
                    $auids[] = $newid;

                    // This is for troubleshooting, so we know where the error is.
                    $currentrecord++;

                    // The set exception handler catches exceptions that SLIP by.
                } catch (\Throwable $e) {

                    echo (get_string('cmi5launchaucannotsavedb', 'cmi5launch') . ($currentrecord) . ".");

                    // This is the tricky part, we need to find out which field is missing.
                    // But because the error is thrown ON the field, we need to do some manuevering to find out which field is missing.
                    // Typecast to array to grab the list item.
                    $items = (array) $newrecord;

                    // Get the last ley of array
                    $lastkey = array_key_last($items);

                    // Here's the tricky part, the lastkey here is somewhere in the array we earlier made and the NEXT one would be the one that threw the error.
                    // So now we can grab the key after the last one.
                    $key = array_search($lastkey, $newrecorditems) + 1;

                    // Ok, NOW the missin element is key in newrecorditems.
                    $missing = $newrecorditems[$key];

                    // Now use the found missing value to give feedback to user.
                    echo (get_string('cmi5launchaucannotsavefield', 'cmi5launch') . "'" . $missing  . "'. ". $e->getMessage() . " \n");
                    // Restore default hadlers.
                    restore_exception_handler();
                    restore_error_handler();
                }
            }

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            return $auids;
        }
    }


    /**
     * Retrieves AU info from DB, converts to AU object, and returns it.
     * @param mixed $auid
     * @return au|bool
     */
    public function cmi5launch_retrieve_aus_from_db($auid) {

        global $DB;

        $check = $DB->record_exists('cmi5launch_aus', ['id' => $auid], '*', IGNORE_MISSING);

        // If check is negative, the record does not exist. It should also throw error.
        // Moodle will throw the error, but we want to pass this message back t0 user.
        if (!$check) {

            throw new nullException( get_string('cmi5launchaudatadb', 'cmi5launch'). $auid . "</p>", 0);

        } else {

            $auitem = $DB->get_record('cmi5launch_aus', ['id' => $auid]);

            $au = new au($auitem);

            // Return our new list of AU.
            return $au;
        }

    }
}
