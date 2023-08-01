<?php

//namespace cmi5;
class Session_Helpers
{

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

	/**
	 * Gets updated session information from CMI5 player
	 * @param mixed $sessionID - the session id
	 * @param mixed $cmi5Id - cmi5 instance id
	 * @return session
	 */
	function updateSessions($sessionID, $cmi5Id)
	{
		global $CFG, $DB;
		require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");
		$connector = new cmi5Connectors;
		$getSessionInfo = $connector->getSessions();

		//Get the session from DB with session id
		$session = $this->getFromDB($sessionID);

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
	

	/**
	 * Creates a session record in DB
	 * @param mixed $sessId - the session id
	 * @param mixed $launchurl - the launch url
	 * @param mixed $launchMethod - the launch method
	 * @return void
	 */
	function createSession($sessId, $launchurl, $launchMethod)
	{
		global $DB, $CFG, $cmi5launch, $USER;
	
		//$record;
		$table = "cmi5launch_sessions";

		//Make a newRecord to save
		$newRecord = new stdClass();
		//Because of many nested properties, needs to be done manually
		$newRecord->sessionid = $sessId;
		$newRecord->launchurl = $launchurl;
		$newRecord->tenantname = $USER->username;
		$newRecord->launchmethod = $launchMethod;

		//Save
		$DB->insert_record($table, $newRecord, true);
	}

	/**
	 * Retrieves session from DB
	 * @param mixed $sessionID - the session id
	 * @return session
	 */
	function getFromDB($sessionID)
	{
		global $DB, $CFG;

		$check = $DB->record_exists('cmi5launch_sessions', ['sessionid' => $sessionID], '*', IGNORE_MISSING);

		//If check is negative, the record does not exist. Throw error
		if (!$check) {

			echo "<p>Error attempting to get session data from DB. Check session id.</p>";
			echo "<pre>";
			var_dump($sessionID);
			echo "</pre>";
		
		} else {

			$sessionItem = $DB->get_record('cmi5launch_sessions',  array('sessionid' => $sessionID));

			$session = new session($sessionItem);
			
		}

		//Return new session object!
		return $session;
	}
}

?>