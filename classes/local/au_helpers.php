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
use mod_cmi5launch\local\errorOver;

// include the errorOver funcs
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorOver.php');

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
        // The results come back as nested array under more then just AUs.
        // We only want the info pertaining to the AU.
       $resultchunked = "";
       set_error_handler('mod_cmi5launch\local\array_chunk_warning', E_WARNING);
        
       //Lets add a try catch, and see if it catches when fed wrong array info
        try {
            $resultchunked = array_chunk($returnedinfo["metadata"]["aus"], 1, );
        } 
   // If this doesnt work catch the exception or erro and throw our own!
        catch (\Exception $e) {
            
            // It's nt bein thrown!! OMG it's not being throuwn!!!!
            // lololololololol
            // But it is bein thorw but the array chunk over ride, this is the problem
            // So maybe catch the error? The warning huh
            // We need to throw HERE for it to be cauht
            // Whats happening is the ARRAY chunk is throwing trhe null exception
            // not the code under test
            // So, what does this mean? Do we want to just test output?????
            // I think so, because the exception handling is anothe system under test
            // What is happening here is the error is turned into exception, the exception is autothrown or thrown to be a null exception,
            // If the null exception is thrown THEN the catch operates, but because catch is operiting the tests are picking up IT, not the thing that causedd it!!!

            //They problme is the try/catch syntax I think


            // What is php unit is throwing ANOTHER exceptuion and thats what is
            // being cauhy and thats why me invented one is missed
            // The message returned is what I decded in erroverride, combined with below I can make it apecif for certain areas
            echo "Cannot retrieve AUs. Error found when trying to parse them from course creation: " .
                "Please check the connection to player or course format and try again. \n"
                . $e->getMessage() . "\n";

            //exit;
        }     
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
        // /SHould this be where the build in is? Or where this func is called?

        // We should not be able to get here but what if null is pulled from record and passed in?
        // SO in case it is given null
        if ($austatements == null) {
            throw new nullException('Cannot retrieve AU information. AU statements from DB are: ' . $austatements, 0);
            exit;
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
        $newid = "";
        // An array to hold the created ids.
        $auids = array();
        // Array of all items in new record, this will be useful for 
        // troubleshooting.
        $newrecorditems = array('id', 'attempt', 'auid', 'launchmethod', 'lmsid', 'url', 'type', 'title', 'moveon', 'auindex', 'parents', 'objectives', 'description', 'activitytype', 'masteryscore', 'completed', 'passed', 'inprogress', 'noattempt', 'satisfied', 'moodlecourseid');

        set_error_handler('mod_cmi5launch\local\sifting_data_warning', E_WARNING);
        
        //Check it's not null.
        if ($auobjectarray == null) {
            throw new nullException('Cannot save AU information. AU object array is: ' . $auobjectarray, 0);

        } else {
            // For each AU in array build a new record and save it.
            // Because of so many nested variables this needs to be done manually.
            foreach ($auobjectarray as $auobject) {

                // I suppose another thing that could o wrong is the retrieval or subsequent encoding of these items.
                // Sooo maybe wrap hte whole thing in a try/cath?
// Can we make an exception that points to whos is missing? Like title vs id? 
                // Now here we need an exception catchy thing, because theres no way to know WHAT wmight be good 
                try {
                    set_error_handler('mod_cmi5launch\local\sifting_data_warning', E_WARNING);
        
                    // Make a newrecord to save.
                    $newrecord = new \stdClass();

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
                    // And HERE we can add the moodlecourseid.
                    $newrecord->moodlecourseid = $cmi5launch->id;

                    // What could go wrong here? The record is not saved? The record is not saved correctly?
                    // Save the record and get the new id.
                    $newid = $DB->insert_record($table, $newrecord, true);
                    // Save new id to list to pass back.
                    $auids[] = $newid;

                    // Thats right, the error handler is throwing the exception, not the code under test, so catch ANY error
                    // A type error cn also be thrown here, throwable catches it, so we either need to set an exception handler as well
                    // or catch throwable
                    
                } catch (\Throwable $e) {
               
                    // Maybe we can construct the new errors here. This would allow the error personalization? And keep main code clean
                    // maybe the catch is just what is suppossed to happen when the 
                    //error is thrown, any additional stuff
                    //the current new record is
                    echo "Cannot save to DB. Stopped at record with ID number " . print_r($newid) . ".";

                    // ok so what is e?
                   // echo"Error stirn ---  " . $e->getMessage();
                    // Typecast tp array to grab the list item
                    $items = (array) $newrecord;
                    // This will almost work but it is the NEXT one after, so maybe I should make array and then grab the one after it
// Get the last ley of array
                    $lastkey = array_key_last($items);
                    // Heres thhe tricky part, the lastkey here is somewhere in the array we earlier made and the NEXT one would be the one that thre th error

                    // So now we can grab the key after the last one
                    $key = array_search($lastkey, $newrecorditems) + 1;

                    // Ok, NOW the missin element is key in newrecorditems
                    $missing = $newrecorditems[$key];

                    // It worked! Now in the test we can make say three mock statements, each one with a bad field, and then make sure the 
                    // error messae matches the bad output!!! Woot! 
                    // yup I'm a dummy....
                    echo " One of the fields is incorrect. Check data for field '$missing'. " . $e->getMessage() . "\n";
                    // exit;
                }
            }


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

        // If check is negative, the record does not exist. It should so throw error.
        // Moodle will throw the error, but we want to pass this message back ot user.
        if (!$check) {

            echo "<p>Error attempting to get AU data from DB. Check AU id. AU id is: " . $auid ."</p>";

            return false;
        } else {

            $auitem = $DB->get_record('cmi5launch_aus',  array('id' => $auid));

            $au = new au($auitem);
        }

        // Return our new list of AU.
        return $au;
    }

}
