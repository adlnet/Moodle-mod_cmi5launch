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

	public function getUpdateVerb()
	{
		return [$this, 'updateVerbs'];
	}

	public function getAUsFromDB()
	{
		return [$this, 'getFromDB'];
	}


	function updateVerbs($aus, $verbList)
	{

		//This gets the aus separate and then compares to update them
		//Their verbs and such
		foreach ($aus as $key) {

			//Retrieve individual AU as array
			$au = (array) ($aus[$key]);

		}
	}
	function retrieveAus($returnedInfo)
	{

		//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
		$resultChunked = array_chunk($returnedInfo["metadata"]["aus"], 1, );
		//The info has now been broken into chunks
		//Return the AU with the chuncks, but start at 0 because array_chunk returns an array, all will be 
		//nestled under 0

		//$newAus = $this->createAUs($resultChunked[0]);

		//return $newAus;
		//it was returning aus then encoding them then creating them again
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
	 * //can we take an AU and just if this matches then this matches and save to table?
	 * ok if this is called in lib it needs to take an array
	 * @param mixed $id - base id to make sure record is saved to correct actor
	 * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
	 * @param mixed $retUrl - Tenants return url for when course window closes.
	 * @return mixed
	 */
	function saveAUs($auObjectArray, $recordIn)
	{

		global $DB, $CFG, $cmi5launch;

		//$record;
		$table = "cmi5launch_aus";

		//Lets make an array to hold the created ids
		$auIDs = array();
		//Retrieve record id, this will be added to auindex to make unique id
//		$recordId = $record->id;

		//Ok the foreach is clever, but there are two many nested values for this to work, we will need to do this manually
		foreach ($auObjectArray as $auObject) {

		
			//Make a newRecord to save
			$newRecord = new stdClass();
						//Because of many nested properties, needs to be done manually
						$newRecord->auid = $auObject->id;
						$newRecord->launchmethod = $auObject->launchMethod;
						//$newRecord->lmsid = $auObject->lmsid;
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
			
				//The record is ok, I think one of the ARGS in record isn't, some of them are nested arrays! Can I "toString" them?
			$newId = $DB->insert_record($table, $newRecord, true);

			$auIDs[] = $newId;
		}
		//Return record id to it's original value
		//now return the au id list (Because record is global)
		//$record->id = $recordId;
		return $auIDs;
	}







	/**
	 * //can we take an AU and just if this matches then this matches and save to table?
	 * ok if this is called in lib it needs to take an array
	 * @param mixed $id - base id to make sure record is saved to correct actor
	 * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
	 * @param mixed $retUrl - Tenants return url for when course window closes.
	 * @return mixed
	 */
	function saveAUsOld($auObjectArray, $recordIn)
	{

		//Could this be a pointer issue?
		//Like is it overwriting record and thats casuing the duope?
		//$record = $recordIn;
		echo "<br>";
		echo "Well dang are we even entering this new func";
		echo "<br>";

		global $DB, $CFG, $cmi5launch;

		$record;
		$table = "cmi5launch_aus";

		//Lets make an array to hold the created ids
		$auIDs = array();
		//Retrieve record id, this will be added to auindex to make unique id
		$recordId = $record->id;

		//Ok the foreach is clever, but there are two many nested values for this to work, we will need to do this manually
		foreach ($auObjectArray as $auObject) {

			//Because of many nested properties, needs to be done manually
			$record->auid = $auObject->id;
			$record->launchmethod = $auObject->launchMethod;
			$record->lmsid = $auObject->lmsId;
			$record->url = $auObject->url;
			$record->type = $auObject->type;
			$title = json_decode(json_encode($auObject->title), true);
			$record->title = $title[0]['text'];
			$record->moveon = $auObject->moveOn;
			$record->auindex = $auObject->auIndex;
			$record->parents = json_encode($auObject->parents, true);
			$record->objectives = $auObject->objectives;
			$desc = json_decode(json_encode($auObject->description), true);
			$record->description = $desc[0]['text'];
			$record->activitytype = $auObject->activityType;
			$record->masteryscore = $auObject->masteryScore;
			$record->completed = $auObject->completed;
			$record->passed = $auObject->passed;
			$record->inprogress = $auObject->inProgress;
			$record->noattempt = $auObject->noAttempt;
			echo "<br>";
			echo "Ok, lets see if everything is right?";
			echo "<br>";
			//What if we made id id+au??? could that solve the issue?
		//	$record->id = ($recordId + $record->auindex);
			echo "<br>";
			echo "Ok, break it down, what is recordId ";
			var_dump($recordId);
			echo "<br>";
			echo "<br>";
			echo "Ok, break it down, what is and auindex in record? ";
			var_dump($record->auindex);
			echo "<br>";
			echo "together they make record->>>id is: ";
			var_dump($record->id);
			echo "<br>";
			echo "record->>>courseid is: ";
			var_dump($record->courseid);
			echo "<br>";
			echo "record->>>tenantname is: ";
			var_dump($record->tenantname);
			echo "<br>";
			echo "record->>>currentgrade is: ";
			var_dump($record->currentgrade);
			echo "<br>";
			echo "record->>>launchmethod is: ";
			var_dump($record->launchmethod);
			echo "<br>";
			echo "record->>>reegistrationid is: ";
			var_dump($record->registrationid);
			echo "<br>";
			echo "record->>>moodleid is: ";
			var_dump($record->moodleid);
			echo "<br>";
			echo "record->>>sessionid is: ";
			var_dump($record->sessionid); //this will be added to later
			echo "<br>";
			echo "record->>>returnurl is: ";
			var_dump($record->returnurl);
			echo "<br>";
			echo "record->>>au id is: ";
			var_dump($record->lmsid);
			echo "<br>";
			echo "<br>";
			echo "record->>>LMSid is: ";
			var_dump($record->lmsid);
			echo "<br>";
			echo "<br>";
			echo "record->>> url is: ";
			var_dump($record->url);
			echo "<br>";
			echo "<br>";
			echo "record->>>  type is: ";
			var_dump($record->type);
			echo "<br>";
			echo "<br>";
			echo "record->>> title is: ";
			var_dump($record->title);
			echo "<br>";
			echo "<br>";
			echo "<br>";
			echo "record->>> moveon is: ";
			var_dump($record->moveon);
			echo "<br>";
			echo "<br>";
			echo "record->>> AU INDEX is: ";
			var_dump($record->auindex);
			echo "<br>";
			echo "<br>";
			echo "record->>> parents is: ";
			var_dump($record->parents);
			echo "<br>";
			echo "record->>> description is: ";
			var_dump($record->description);
			echo "<br>";
			echo "record->>> activitytype is: ";
			var_dump($record->activitytype);
			echo "<br>";
			echo "record->>> masteryscore is: ";
			var_dump($record->masteryscore);
			echo "<br>";
			echo "record->>> completed is: ";
			var_dump($record->completed);
			echo "<br>";
			echo "record->>> passed is: ";
			var_dump($record->passed);
			echo "<br>";
			echo "record->>> inprogress is: ";
			var_dump($record->inprogress);
			echo "<br>";
			echo "record->>> noattempt is: ";
			var_dump($record->noattempt);
			echo "<br>";

			//Make sure record doesn't exist before attempting to create
			//Why is this returning recordds??
		/*	$check = $DB->record_exists($table, ['id' => $record->id], '*', IGNORE_MISSING);
			echo "<br>";
			echo "Ok, what check??????????????";
			var_dump($check);
			echo "<br>";

			//If false, record doesn't exist, so create  it
			if (!$check) {
*/
				echo "<br>";
				echo "No surproise record didnt exist. Record currently BEFORE:: ";
				var_dump($record);
				echo "<br>";

				//The record is ok, I think one of the ARGS in record isn't, some of them are nested arrays! Can I "toString" them?
				$DB->import_record($table, $record, true);

//			}
			/*else {
			// If it does exist, update it
			//Wait, this should NEVER exist
			echo"<br>";
			echo "Record currently after:: ";
			var_dump($record);
			echo "<br>";
			//Update record in table with newly retrieved tenant data
			$DB->update_record($table, $record, true);
			}*/

			//llllets make an array list of created auid's and have these be retrieved
			//now add that on
			$auIDs[] = $record->id;
		}
		//Return record id to it's original value
		//now return the au id list (Because record is global)
		//$record->id = $recordId;
		return $auIDs;
	}

	function getFromDB($auID)
	{

		global $DB;

		$check = $DB->record_exists( 'cmi5launch_aus', ['id' => $auID], '*', IGNORE_MISSING);

	
		//If check is negative, the record doesnot exist. IT should so throw error
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