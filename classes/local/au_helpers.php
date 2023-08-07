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

class au_helpers
{
	public function get_cmi5launch_retrieve_aus()
	{
		return [$this, 'cmi5launch_retrieve_aus'];
	}
	public function get_cmi5launch_create_aus()
	{
		return [$this, 'cmi5launch_create_aus'];
	}
	public function get_cmi5launch_save_aus()
	{
		return [$this, 'cmi5launch_save_aus'];
	}

	public function get_cmi5launch_retrieve_aus_from_db()
	{
		return [$this, 'cmi5launch_retrieve_aus_from_db'];
	}


	/**
	 * Parses and retrieves AUs from the returned info from CMI5
	 * @param mixed $returnedinfo
	 * @return array
	 */
	function cmi5launch_retrieve_aus($returnedinfo)
	{
		//The results come back as nested array under more then just AUs. We only want the info pertaining to AU
		$resultChunked = array_chunk($returnedinfo["metadata"]["aus"], 1, );

		return $resultChunked;
	}

	/**
	 *So it should be fed an array of statements that then assigns the values to 
	 *several aus, and then returns them as au objects!
	 * @param mixed $austatements
	 * @return array<au>
	 */
	function cmi5launch_create_aus($austatements)
	{
		//Needs to return our new AU objects
		$newAus = array();

		//for ($i = 0; $i < count($auStatements); $i++) {
		foreach ($austatements as $int => $info) {

			//The aus come back decoded from DB nestled in an array, so they are the first key,
			//which is '0'
			$statement = $info[0];

			//Maybe just combine 45 and 48? TODO
			$au = new au($statement);

			//assign the newly created au to the return array
			$newAus[] = $au;
		}

		//Return our new list of AU!
		return $newAus;
	}


	/**
	 * Takes a list of AUs and record and saves to DB
	 * @param mixed $auobjectarray
	 * @return array
	 */
	function cmi5launch_save_aus($auobjectarray)
	{
		//Add userid to the record
		global $DB, $USER;
		//$record;
		$table = "cmi5launch_aus";

		//Lets make an array to hold the created ids
		$auIDs = array();

		//for each AU in array build a new record and save it. Because of so many nested variables this needs to be done manually
		foreach ($auobjectarray as $auObject) {
			//Make a newRecord to save
			$newRecord = new \stdClass();
			$newRecord->userid = $USER->id;
			$newRecord->auid = $auObject->id;
			$newRecord->launchmethod = $auObject->launchMethod;
			$newRecord->lmsid = json_decode(json_encode($auObject->lmsId, true) );
			$newRecord->url = $auObject->url;
			$newRecord->type = $auObject->type;
			$title = json_decode(json_encode($auObject->title), true);
			$newRecord->title = $title[0]['text'];
			$newRecord->moveon = $auObject->moveOn;
			$newRecord->auindex = $auObject->auIndex;
			$newRecord->parents = json_encode($auObject->parents, true);
			$newRecord->objectives = $auObject->objectives;
			$desc = json_decode(json_encode($auObject->description), true);
			$newRecord->description = $desc[0]['text'];
			$newRecord->activitytype = $auObject->activityType;
			$newRecord->masteryscore = $auObject->masteryscore;
			$newRecord->completed = $auObject->completed;
			$newRecord->passed = $auObject->passed;
			$newRecord->inprogress = $auObject->inprogress;
			$newRecord->noattempt = $auObject->noattempt;
			$newRecord->satisfied = $auObject->satisfied;

			//Save the record and get the new id
			$newId = $DB->insert_record($table, $newRecord, true);
			//Save new id to list to pass back
			$auIDs[] = $newId;
		}

		return $auIDs;
	}

	/**
	 * Retrieves a list of AU's from DB and makes them AU objects
	 * @param mixed $auid
	 * @return au
	 */
	function cmi5launch_retrieve_aus_from_db($auid)
	{
		global $DB;

		$check = $DB->record_exists( 'cmi5launch_aus', ['id' => $auid], '*', IGNORE_MISSING);
	
		//If check is negative, the record doesnot exist. It should so throw error
		if(!$check){

			echo "<p>Error attempting to get AU data from DB. Check AU id.</p>";
			echo "<pre>";
		   var_dump($auid);
			echo "</pre>";
		}
		else{
			$auItem = $DB->get_record('cmi5launch_aus',  array('id' => $auid));
			
			$au = new au($auItem);
		}

		//Return our new list of AU!
		return $au;
	}

}
?>