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
 */

namespace mod_cmi5launch\local;

use mod_cmi5launch\local\au;
use mod_cmi5launch\local\errorover;

global $CFG;
// Include the errorover (error override) funcs.
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

defined('MOODLE_INTERNAL') || die();

class au_helpers {
    public function get_cmi5launch_retrieve_aus() {
        return [$this, 'cmi5launch_retrieve_aus'];
    }
    public function get_cmi5launch_create_aus() {
        return [$this, 'cmi5launch_create_aus'];
    }
    public function get_cmi5launch_save_aus() {
        return [$this, 'cmi5launch_save_aus'];
    }
    public function get_cmi5launch_retrieve_aus_from_db() {
        return [$this, 'cmi5launch_retrieve_aus_from_db'];
    }

    /**
     * Parses and retrieves AUs from the returned info from CMI5 player.
     * @param mixed $returnedinfo
     * @return array
     */
    public function cmi5launch_retrieve_aus($returnedinfo) {
        
       $resultchunked = "";
       
     
       // Use our own more specific error handler, to give better info tto user.
       set_error_handler('mod_cmi5launch\local\array_chunk_warning', E_WARNING);
        
        // The results come back as nested array under more then just AUs.
        // We only want the info pertaining to the AU. However, if the wrong info is passed array_chunk will through an exception.
        try {
            $resultchunked = array_chunk($returnedinfo["metadata"]["aus"], 1, );
        } 
        catch (\Exception $e) {

            echo "Cannot retrieve AUs. Error found when trying to parse them from course creation: " .
                "Please check the connection to player or course format and try again. \n"
                . $e->getMessage() . "\n";

            //exit;
        } 

        // Restore the error handler.
        restore_error_handler();
        
        return $resultchunked;
    }


    /**
     * So it should be fed an array of statements that then assigns the values to
     * several aus, and then returns them as au objects.
     * @param mixed $austatements
     * @return array<au>
     */
    public function cmi5launch_create_aus($austatements)
    {
        // Needs to return our new AU objects.
        $newaus = array();

        // We should not be able to get here but what if null is pulled from record and passed in?
        // So in case it is given null.
        if ($austatements == null) {
            
            throw new nullException('Cannot retrieve AU information. AU statements from DB are: ' . $austatements, 0);

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
     * Takes a list of AUs and record and saves to the DB.
     * @param mixed $auobjectarray
     * @return array
     */
    public function cmi5launch_save_aus($auobjectarray) {
        // Add userid to the record.
        global $DB, $USER, $cmi5launch;
        $table = "cmi5launch_aus";
        
        // An array to hold the created ids.
        $auids = array();

        // Variables for error over and exception handling.
        // Array of all items in new record, this will be useful for troubleshooting.
        $newrecorditems = array('id', 'attempt', 'auid', 'launchmethod', 'lmsid', 'url', 'type', 'title', 'moveon', 'auindex', 'parents', 'objectives', 'description', 'activitytype', 'masteryscore', 'completed', 'passed', 'inprogress', 'noattempt', 'satisfied', 'moodlecourseid');
        $currentrecord = 1;
        $newid = "";
        $newrecord = "";

        // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
        set_error_handler('mod_cmi5launch\local\sifting_data_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_au');
        
        //Check it's not null.
        if ($auobjectarray == null) {

            throw new nullException('Cannot save AU information. AU object array is: null' , 0);

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
               
                    // The set exception handler catches exceptionas that SLIP by,
                    // so maybe DONT make it throwable and  catch type errror
                } catch (\Throwable $e) {
               
                
                    echo "Cannot save to DB. Stopped at record with ID number " . ($currentrecord) . ".";

                    // This is the tricky part, we need to find out which field is missing. But because the error is thrown ON the field, we need to do some
                    // manuevering to find out which field is missing.
                    // Typecast to array to grab the list item. 
                    $items = (array) $newrecord;

                    // Get the last ley of array
                    $lastkey = array_key_last($items);
                    
                    // Heres thhe tricky part, the lastkey here is somewhere in the array we earlier made and the NEXT one would be the one that threw the error.
                    // So now we can grab the key after the last one.
                    $key = array_search($lastkey, $newrecorditems) + 1;

                    // Ok, NOW the missin element is key in newrecorditems.
                    $missing = $newrecorditems[$key];

                    // Now use the found missing value to give feedback to user.
                    echo " One of the fields is incorrect. Check data for field '$missing'. " . $e->getMessage() . "\n";
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

        $check = $DB->record_exists( 'cmi5launch_aus', ['id' => $auid], '*', IGNORE_MISSING);


        // If check is negative, the record does not exist. It should also throw error.
        // Moodle will throw the error, but we want to pass this message back ot user.
        if (!$check) {

            throw new nullException("Error attempting to get AU data from DB. Check AU id. AU id is: " . $auid ."</p>", 0);

        } else {

            $auitem = $DB->get_record('cmi5launch_aus',  array('id' => $auid));

            $au = new au($auitem);

            // Return our new list of AU.
            return $au;
        }

        
    }

}
