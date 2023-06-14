<?php
class Au_Helpers
{

	public function getRetrieveAUs()
	{
		return [$this, 'retrieveAUs'];
	}
	public function getCreateAUs()
	{
		return [$this, 'createAUs'];
	}
	public function getSaveAUs()
	{
		return [$this, 'saveAUs'];
	}

	public function getAUsFromDB()
	{
		return [$this, 'getFromDB'];
	}


	/**
	 * PArses and retrieves AUs from the returned info from CMI5
	 * @param mixed $returnedInfo
	 * @return array
	 */
	function retrieveAus($returnedInfo)
	{
		//The results come back as nested array under more then just AUs. We only want the info pertaining to AU
		$resultChunked = array_chunk($returnedInfo["metadata"]["aus"], 1, );

		return $resultChunked;
	}

	/**
	 *So it should be fed an array of statements that then assigns the values to 
	 *several aus, and then returns them as au objects!
	 * @param mixed $auStatements
	 * @return array<au>
	 */
	function createAUs($auStatements)
	{
		//Needs to return our new AU objects
		$newAus = array();

		//for ($i = 0; $i < count($auStatements); $i++) {
		foreach ($auStatements as $int => $info) {

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
	 * @param mixed $auObjectArray
	 * @return array
	 */
	function saveAUs($auObjectArray)
	{
		//Add userid to the record
		global $DB, $USER;
		//$record;
		$table = "cmi5launch_aus";

		//Lets make an array to hold the created ids
		$auIDs = array();

		//for each AU in array build a new record and save it. Because of so many nested variables this needs to be done manually
		foreach ($auObjectArray as $auObject) {
			//Make a newRecord to save
			$newRecord = new stdClass();
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
	 * @param mixed $auID
	 * @return au
	 */
	function getFromDB($auID)
	{
		global $DB;

		$check = $DB->record_exists( 'cmi5launch_aus', ['id' => $auID], '*', IGNORE_MISSING);
	
		//If check is negative, the record doesnot exist. It should so throw error
		if(!$check){

			echo "<p>Error attempting to get AU data from DB. Check AU id.</p>";
			echo "<pre>";
		   var_dump($auID);
			echo "</pre>";
		}
		else{
			$auItem = $DB->get_record('cmi5launch_aus',  array('id' => $auID));
			
			$au = new au($auItem);
		}

		//Return our new list of AU!
		return $au;
	}

}
?>