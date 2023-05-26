<?php
class Session_Helpers
{

	
	public function getRetrieveAUs()
	{
		return [$this, 'retrieveAUs'];
	}
	public function getCreateAUs()
	{
		return [$this, 'createAUs'];
	}
	public function getSaveSession()
	{
		return [$this, 'createSession'];
	}

	public function getUpdateSession()
	{
		return [$this, 'updateSessions'];
	}

	public function getSessionFromDB()
	{
		return [$this, 'getFromDB'];
	}

	//Lets call session updatefrom cmi5

	function updateSessions($sessionID, $cmi5Id)
	{
		global $CFG, $DB;
		require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");
		$connector = new cmi5Connectors;
	
		//Get the session from DB with session id
		$session = $this->getFromDB($sessionID);

		$getSessionInfo = $connector->getSessions();

		//This is sessioninfo from CMI5 player
		$sessionInfo =	$getSessionInfo($sessionID, $cmi5Id);

		//Update session
		foreach($sessionInfo as $key => $value){
			//We don't want to overwrite id
			if (property_exists($session, $key ) && $key != 'id' )  {
				//If it's an array encode it so it can be saved to DB
				if (is_Array($value)) {
					$value = json_encode($value);
				}

					$session->$key = $value;
			}
		}
		
		//Now update to table
		$DB->update_record('cmi5launch_sessions', $session);

		return $session;
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
	function createSession($sessId, $launchurl, $launchMethod)
	{

		echo "<br>";
		echo "Well dang are we even entering this new func";
		echo "<br>";

		//OMG!!! Lets make a function that call the CMI5 player api (get session info!!!)
		//And like we can call it twice or make anew func,
		//but check if like these are filled in and if not, populate from cmi5 player~
		//Two may be better as this one could soleby be to create session with basics and after cmi5 called it 
		//will pop the rest. maybe on the return paggge? (which is view.php)
		//Because that will also tie in to done or not, so lets renme this to createSession
		//and the other can updateFromCmi5

		global $DB, $CFG, $cmi5launch;

		echo "<br>";
		echo "wait a dang minute!!! IS the issue the sessId not being here?";
		echo "<br>";
		echo "What is sessID?";
		var_dump($sessId);
		echo "<br>";
		echo "<br>";

		//$record;
		$table = "cmi5launch_sessions";
		$settings = cmi5launch_settings($cmi5launch->id);
		//Lets make an array to hold the created ids
		$sessionIDs = array();
		//Retrieve record id, this will be added to auindex to make unique id
//		$recordId = $record->id;

//See this is diff, we don't need to do this, just save the id and lauchurl, later
//we can call a func to retrieve sess info
$tenantname = $settings['cmi5launchtenantname'];

			//Make a newRecord to save
			$newRecord = new stdClass();
			//Because of many nested properties, needs to be done manually
			$newRecord->sessionid = $sessId;
			$newRecord->launchurl = $launchurl;
			
			$newRecord->tenantname = $settings['cmi5launchtenantname'];
			$newRecord->launchmethod = $launchMethod;
			
			echo "<br>";
			echo "What is our new record item???";
			var_dump(($newRecord));
			echo "<br>";

			//The record is ok, I think one of the ARGS in record isn't, some of them are nested arrays! Can I "toString" them?
			$DB->insert_record($table, $newRecord, true);

		//Return record id to it's original value
		//now return the au id list (Because record is global)
		//$record->id = $recordId;
		//return $newId;
	}


	function getFromDB($sessionID)
	{
		global $DB, $CFG;
		//require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/sessionHelpers.php");
		//$cmi5_connectors = new cmi5Connectors;
		//$getSession = $cmi5_connectors->getSessionInfo();

		$check = $DB->record_exists('cmi5launch_sessions', ['sessionid' => $sessionID], '*', IGNORE_MISSING);


		//If check is negative, the record doesnot exist. IT should so throw error
		if (!$check) {

			echo "<p>Error attempting to get session data from DB. Check session id.</p>";
			echo "<pre>";
			var_dump($sessionID);
			echo "</pre>";
		} else {

			$sessionItem = $DB->get_record('cmi5launch_sessions',  array('sessionid' => $sessionID));
			//THIS IS ONLY to get fromDB, not update sesion!!

			//$sessionItem = $DB->get_record('cmi5launch_sessions', array('id' => $sessionID));

			//Ok, maybe here is where we query the cmi5 player!

			//$infoFromPlayer = $getSession($sessionID, $cmiId);

			//YES! ok, 

			//Maybe the session func should take two objects? and combine to one session?
			//or it could just take the session id? and get ALL the info from player?
			//Or heck, the player RETURNS that anyway, so just give what the player returns to construct, 
			//make sure it is all good, and save back over record (update_record)
			$session = new session($sessionItem);
			
			///$au->lmsId = //

		}

		//Return our new list of AU!
		return $session;
	}
}

?>