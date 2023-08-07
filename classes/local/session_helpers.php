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
 */

namespace mod_cmi5launch\local;

use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\session;

class session_helpers
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
		return [$this, 'cmi5launch_retrieve_aus_from_db'];
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

		$connector = new cmi5_connectors;
		$getSessionInfo = $connector->getSessions();

		//Get the session from DB with session id
		$session = $this->cmi5launch_retrieve_aus_from_db($sessionID);

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
		$newRecord = new \stdClass();
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
	function cmi5launch_retrieve_aus_from_db($sessionID)
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