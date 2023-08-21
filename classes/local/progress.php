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
 * //Class to retrieve progress statements from LRS
 * //Holds methods for tracking and displaying student progress
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cmi5launch\local;

class progress{

	public function cmi5launch_get_retrieve_statements()
	{
	    return [$this, 'cmi5launch_retrieve_statements'];
	}

	public function cmi5launch_get_request_completion_info()
	{
		return [$this, 'cmi5launch_request_completion_info'];
	}

	public function cmi5launch_get_request_statements_from_lrs()
	{
	    return [$this, 'cmi5launch_request_statements_from_lrs'];
	}

	//Changing this func as a test
	/**
	 * Send request to LRS
	 * @param mixed $regId - registration id
	 * @param mixed $session - a session object 
	 * @return array
	 */
	public function cmi5launch_request_statements_from_lrs2($registrationid, $session /*$id*/){

		//Array to hold result
		$result = array();

		//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
		$data = array(
			'registration' => $registrationid,
			'since' => $session->createdAt
		);

		
		$statements = $this->cmi5launch_send_request_to_lrs($data, $registrationid);

		echo "what does this object look like?";
		var_dump($statements);
		echo "<br>";
		echo "<br>";
		echo "How about array_search?";
		
		//so apparently the key to nested arrays is foreach and is_array
		foreach($statements as $key => $value){
			if(is_array($value)){
				echo"found array";
				echo "<br>";
				if(array_search("object", $value)){
					echo "found it";
				}
				if(is_array($value)){
					foreach($value as $key => $value){
						if(is_array($value)){
							if(array_search("object", $value)){
								echo "found it";
							}
						}
					}
				}
			}
		}
		var_dump(array_search("object", $statements));
		echo "<br>";

		echo "What about array_keys";
		var_dump(array_keys($statements, "object"));
		echo "End";
	/*
		//The results come back as nested array under more then statements. We only want statements, and we want them unique
		$statement = array_chunk($statements["statements"], 1);

		$length = count($statement);

		for ($i = 0; $i < $length; $i++){
		
		//This separates the larger statement into the separate sessions and verbs
			$current = ($statement[$i]);
		array_push($result, array ($registrationid => $current) );
		}
	
		return $result; */
	}
	
	/**
	 * Send request to LRS
	 * @param mixed $regId - registration id
	 * @param mixed $session - a session object 
	 * @return array
	 */
	public function cmi5launch_request_statements_from_lrs($registrationid, $session /*$id*/){

		//Array to hold result
		$result = array();

		//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
		$data = array(
			'registration' => $registrationid,
			'since' => $session->createdAt
		);

		
		$statements = $this->cmi5launch_send_request_to_lrs($data, $registrationid);

		//Ok, here, what are statements here?
		/*echo`<br>`;
		echo "what does this object look like?";
		var_dump($statements);
		echo "<br>";
		echo "<br>";
		echo "and how does it look as plain json?";
		echo json_encode($statements);
		echo "<br>";
		echo "<br>";
	*/
		//The results come back as nested array under more then statements. We only want statements, and we want them unique
		$statement = array_chunk($statements["statements"], 1) ;////!?//

		//Ok, here, what are statements here?
		/*echo`<br>`;
		echo "what does this object look like AFTER CHUNKING?";
		var_dump($statement);
		echo "<br>";
		echo "<br>";
		echo "and how does it look as plain json AFTER HCUNKING?";
		echo json_encode($statement);
		echo "<br>";
		echo "<br>";
		*/
		$length = count($statement);

		for ($i = 0; $i < $length; $i++){
		
		//This separates the larger statement into the separate sessions and verbs
			$current = ($statement[$i]);
		array_push($result, array ($registrationid => $current) );
		}
	
			//Ok, here, what are statements here?
		/*	echo`<br>`;
			echo "what does this object look like as a result?";
			var_dump($result);
			echo "<br>";
			echo "<br>";
			echo "and how does it look like as a result plain json";
			echo json_encode($result);
			echo "<br>";
			echo "<br>";
			*/

		return $result;
	}


	/**
	 * Builds and sends requests to LRS
	 * @param mixed $data
	 * @param mixed $id
	 * @return mixed
	 */
	public function cmi5launch_send_request_to_lrs($data, $id)
	{
		$settings = cmi5launch_settings($id);

		//Url to request statements from
		$url = $settings['cmi5launchlrsendpoint'] . "statements";
		//Build query with data above
		$url = $url . '?' . http_build_query($data,"", '&',  PHP_QUERY_RFC1738);

		
		//LRS username and password
		$user = $settings['cmi5launchlrslogin'];
		$pass = $settings['cmi5launchlrspass'];


		// use key 'http' even if you send the request to https://...
		//There can be multiple headers but as an array under the ONE header
		//content(body) must be JSON encoded here, as that is what CMI5 player accepts
		$options = array(
			'http' => array(
				'method'  => 'GET',
				'header' => array('Authorization: Basic '. base64_encode("$user:$pass"),  
				"Content-Type: application/json\r\n" .
				"X-Experience-API-Version:1.0.3",
				)
			)
		);
		//the options are here placed into a stream to be sent
		$context  = stream_context_create($options);

		//sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
		$result = file_get_contents( $url, false, $context );

		$resultDecoded = json_decode($result, true);


		return $resultDecoded;
	}
	
	/**
	 * Returns an actor (name) retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed - actor
	 */
	public function cmi5launch_retrieve_actor($resultarray, $registrationid){

		// variable to hold actor.
		$actor = "";

		if (array_key_exists("actor", $resultarray[$registrationid][0])) { //Print that it exists and it's value
			

			if (array_key_exists("account", $resultarray[$registrationid][0]["actor"])) {

				if (array_key_exists("name", $resultarray[$registrationid][0]["actor"]["account"])) {

					$actor = $resultarray[$registrationid][0]["actor"]["account"]["name"];

				} else {

					$this->cmi5launch_statement_retrieval_error("Actor name");

				}
			} else {
				$this->cmi5launch_statement_retrieval_error("Actor account");
			}
		}
			else { //Print that it doesn't exist
				
				$this->cmi5launch_statement_retrieval_error("Actor object");
	

		
		
	}

	return $actor;
	}

	//What if this class had it's own error function? That just inserts a variable that's missing? that would be easier 
	//then 600 if statements. Then it can be if debug, call my werror function
	//should it be if debug call this? OR just call this and have if debug in it? I think the second option is best
	public function cmi5launch_statement_retrieval_error($missingvariable)
	{
		Global $CFG;
		//If admin debugging is enabled
		if($CFG->debugdeveloper){
			//If the variable is missing
			if($missingvariable){
				//Print that it is missing
				echo"<br>";
				echo "Error: " . $missingvariable . " missing from statement";
				echo "<br>";
			}
		}
	}

		/**
	 * Returns a verb retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed - verb
	 */
	public function cmi5launch_retrieve_verbs($resultarray, $registrationid){

		global $CFG;
		$verb = "";
		//Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present			
		//$verbInfo = $resultarray[$registrationid][0]["verb"];
		//$display = array_key_exists("display", $verbInfo);

		if (array_key_exists("verb", $resultarray[$registrationid][0])) { //Print that it exists and it's value

			if(array_key_exists("display", $resultarray[$registrationid][0]["verb"])){
				
			// Retrieve the name of the verb,
				//However, there may be more than one languages string to choose from. First we want to 
				//select the language that matches the language of the course, then if not available, the first key.
				$verbArray = $resultarray[$registrationid][0]["verb"]["display"];

				// System language setting
				$language = $CFG->lang;
				if (array_key_exists($language, $verbArray)) {
					$verb = $verbArray[$language];
				} else {
					$defaultLanguage = array_key_first($verbArray);
					$verb = $verbArray[$defaultLanguage];
				}
				return $verb;
				
			} elseif(array_key_exists("id", $resultarray[$registrationid][0]["verb"])) {
			//If it is null then there is no display section, default to verb id
			
				//retrieve id
				$verbId = $resultarray[$registrationid][0]["verb"]["id"];

				//SPLITS id in two on 'verbs/', we want the end which is the actual verb
				$split = explode('verbs/', $verbId);
				$verb = $split[1];
				return $verb;
			}
			else{
				$this->cmi5launch_statement_retrieval_error("Verb id and display missing ");
			}
		
		}
		else {
			$this->cmi5launch_statement_retrieval_error("Verb object ");
		}
	}

	/**
	 * Returns a name (the au title) retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed - object name
	 */
	public function cmi5launch_retrieve_object_name($resultarray, $registrationid)
	{

		Global $CFG;
		//First find the object, it should always be second level of statement (so third level array).
		if (array_key_exists("object", $resultarray[$registrationid][0])) { //Print that it exists and it's value
			
			if (array_key_exists("definition", $resultarray[$registrationid][0]["object"])) {
				
				//if 'definition' exists, check if 'name' does
				if (array_key_exists("name", $resultarray[$registrationid][0]["object"]["definition"])) {
					
					//retrieve the name
					//However, there may be more than one languages string to choose from. First we want to 
					//select the language that matches the language of the course, then if not available, the first key.
					$objectArray = $resultarray[$registrationid][0]["object"]["definition"]["name"];

					// System language setting
					$language = $CFG->lang;
					if (array_key_exists($language, $objectArray)) {
						$object = $objectArray[$language];
					} else {
						$defaultLanguage = array_key_first($objectArray);
						$object = $objectArray[$defaultLanguage];
					}
					return $object;
				}
					
					//If name is missing check for id
				}elseif(array_key_exists("id", $resultarray[$registrationid][0]["object"])){
					
						//retrieve id
						$object = $resultarray[$registrationid][0]["object"]["id"];	
						return $object;			
				}else
					//if both name and id are missing throw error
					$this->cmi5launch_statement_retrieval_error("Object name and id ");
			
	
		} else { //Print that it doesn't exist

			$this->cmi5launch_statement_retrieval_error("Object ");
			//$objectInfo = $resultarray[$registrationid][0]["object"];
		}

		
	}

	/**
	 * TODO MB - This is able to get all results for later grading
	 * Result params when returned with statements can have 5 fields (not including extensions)
	 * Success - a true/false to provide for a pass/fail of Activity
	 * Completion - a true/false to provide for completion of Activity
	 * Score - takes a Score object
	 * Response - a string value that can contain anything, such as an answer to a question
	 * Duration - length of time taken for experience
	 * 
	 * We are concerned with  the top three for Moodle reporting purposes
	 * 
	 * Summary of cmi5launch_retrieve_result
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed
	 */
	public function cmi5launch_retrieve_result($resultarray, $registrationid){

		//Verify this statement has a 'result' param
		if (array_key_exists("result", $resultarray ) )
		{
			//If it exists, grab it
			$resultInfo = $resultarray[$registrationid][0]["result"];
		
			//Check which keys exist in 'result'
			$success = array_key_exists("success", $resultInfo);
			$completion = array_key_exists("completion", $resultInfo);
			$score = array_key_exists("score", $resultInfo);
			//Andy seeemed interested in durations?
			$duration = array_key_exists("score", $resultInfo);
			$response = array_key_exists("response", $resultInfo);
	
		}
		
		//How should we save and return these infos? A key value array maybe?
			//If it is null then the item in question doesn't exist in this statement
			if($success){
				//no need to make new variable, save over
				$success = $resultarray[$registrationid][0]["result"]["success"];
				
				//now that we have success, save to db. This means we need an object right? Can we update afield?
				//even if we could we need id to find it...
			}else{
			}

			//Maybe it would be better to just have a 'cmi5launch_retrieve_score' for now
	}

	/**
	 * Returns a timestamp retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return string - date/time
	 */
	public function cmi5launch_retrieve_timestamp($resultarray, $registrationid){
		
		if (array_key_exists("timestamp", $resultarray[$registrationid][0])) { //Print that it exists and it's value
			
			$date = new \DateTime($resultarray[$registrationid][0]["timestamp"], new \DateTimeZone('US/Eastern'));
		
			$date->setTimezone(new \DateTimeZone('America/New_York'));

			$date = $date->format('d-m-Y' . " ".  'h:i a');

		return $date;
		
		} else { //Print that it doesn't exist
			$this->cmi5launch_statement_retrieval_error("Timestamp ");
		}
		
	}

	/**
	 * 
	 * Summary of cmi5launch_retrieve_score
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed
	 */
	//Ok, if we change so session id goes through, can we update DB in this func	
	public function cmi5launch_retrieve_score($resultarray, $registrationid){

		//variable to hold score
		$score = null;

		//Verify this statement has a 'result' param
		// Note there is no catch error here. There may not be a score and that's ok.
		if (array_key_exists("result", $resultarray[$registrationid][0])) {


			if (array_key_exists("score", $resultarray[$registrationid][0]["result"])) {

				$score = $resultarray[$registrationid][0]["result"]["score"];

				//Raw score preferred to scaled
				if ($score["raw"]) {

					$returnScore = $score["raw"];
					return $returnScore;
				} elseif ($score["scaled"]) {

					$returnScore = round($score["scaled"], 2);
					return $returnScore;
				}

			}

		}
		else{
			Global $CFG;
			//If admin debugging is enabled
			if($CFG->debugdeveloper){

					//Print that it is missing
					echo"<br>";
					echo "No score in this statement.";
					echo "<br>";
				}
			}
	}
	
	
	/**
	 * Summary of cmi5launch_retrieve_statements
	 * //Retrieves statements from LRS
	 * @param mixed $registrationid
	 * @param mixed $id
	 * @param mixed $lmsId
	 * @return array<string>
	 */

	public function cmi5launch_retrieve_statements($registrationid, $id, $session)
	{
		//Array to hold verbs and be returned
		$progressUpdate = array();
		//Array to hold score and be returned
		$returnScore = 0;

		$resultDecoded = $this->cmi5launch_request_statements_from_lrs($registrationid, $session);

			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go with it

		foreach($resultDecoded as $singleStatement){

			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go and compare to saved session 'code'
			$code = $session->code;
			$currentSessID = "";
			$ext = $singleStatement[$registrationid][0]["context"]["extensions"];
				foreach ($ext as $key => $value) {
				
					//if key contains "sessionid" in string
					if(str_contains($key, "sessionid")){
						$currentSessID= $value;
					}
				}

				//Ok, so HERE is where we have statments only pertaining to THIS sess and THIS regid
				//So these are the statements we can look at the 2nd level for 'object' and 'verb', etc
				//with array_search, or array_key_exists

			//Now if code equals currentSessID, this is a statement pertaining to this session
			if($code == $currentSessID){

				$actor = $this->cmi5launch_retrieve_actor($singleStatement, $registrationid);
				$verb = $this->cmi5launch_retrieve_verbs($singleStatement, $registrationid);
				
					//This is so hard to parse, can we make it json?
		/*echo"start json";
		echo"<br>";
		echo json_encode($singleStatement);
		echo"<br>";
		echo"end json";
		*/
				$object = $this->cmi5launch_retrieve_object_name($singleStatement, $registrationid);
				$date = $this->cmi5launch_retrieve_timestamp($singleStatement, $registrationid);
				$score = $this->cmi5launch_retrieve_score($singleStatement, $registrationid);
				
				//If a session has more than one score, we only want the highest
				if(!$score == null && $score > $returnScore){
					$returnScore = $score;
				}
				//Update to return
				$progressUpdate[] = "$actor $verb $object on $date";
			
			}
				
		}
			$session->progress = json_encode($progressUpdate);
			$session->score = $returnScore;
		
			return $session;
	}

}
