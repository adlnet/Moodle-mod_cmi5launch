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

		//Testing-MB
		//Ok, is there a problem with the session? or registrationp? Why would that change from one program to another
		echo"<br>";
		echo("What is our registrationid?");
		var_dump($registrationid);
		echo"<br>";
		echo("What is our session->createdAt?");
		var_dump($session->createdAt);
		echo"<br>";
		
		$statements = $this->cmi5launch_send_request_to_lrs($data, $registrationid);

		//Testing-MB
		//It would seem they are NOT coming back, what are statements here??
		echo"<br>";
		echo("Are statements coming back?");
		var_dump($statements);
		echo"<br>";


		//The results come back as nested array under more then statements. We only want statements, and we want them unique
		$statement = array_chunk($statements["statements"], 1);

		$length = count($statement);

		for ($i = 0; $i < $length; $i++){
		
		//This separates the larger statement into the separate sessions and verbs
			$current = ($statement[$i]);
		array_push($result, array ($registrationid => $current) );
		}

		//Testing-MB
		//IS anything being returned?
		echo"<br>";
		echo("Are results coming back?");
		var_dump($result);
		echo"<br>";

	
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

		//Testing-MB
		//I wonder if the url has something to do with it?
		echo"<br>";
		echo("What is our url at this point?");
		echo"<br>";
		var_dump($url);
		echo"<br>";
		
		//LRS username and password
		$user = $settings['cmi5launchlrslogin'];
		$pass = $settings['cmi5launchlrspass'];

		//Testing-MB
		//I woulllllllllllllllllllllllllllllllllllllllld bet money this is it! the suer settings are wrong?
		echo"<br>";
		var_dump($user);
		echo"<br>";
		//See we have it set to be TENANT name, but it's not! The tenant name should be sent to player, not lrs?
		//wait, no this SHOULD be right, what is pass as well
		var_dump($pass);
		echo"<br>";

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

		//Testing-MB
		//So the issue must stem from talking to lrs, what is our result here? what is it sending  back?
		echo"<br>";
		echo("What is our result?");
		var_dump($result);
		echo"<br>";

		$resultDecoded = json_decode($result, true);

		//Testing-MB
		//And if result is ok, is the problem resultDecoded?
		echo"<br>";
		echo("What is our resultDecoded?");
		var_dump($resultDecoded);
		echo"<br>";
		
		return $resultDecoded;
	}
	
	/**
	 * Returns an actor (name) retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed - actor
	 */
	public function cmi5launch_retrieve_actor($resultarray, $registrationid){

		$actor = $resultarray[$registrationid][0]["actor"]["account"]["name"];
		return $actor;
	}

		/**
	 * Returns a verb retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed - verb
	 */
	public function cmi5launch_retrieve_verbs($resultarray, $registrationid){

		//Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present			
		$verbInfo = $resultarray[$registrationid][0]["verb"];
		$display = array_key_exists("display", $verbInfo);

			//If it is null then there is no display, so go by verb id
			if(!$display ){
				//retrieve id
				$verbId = $resultarray[$registrationid][0]["verb"]["id"];

				//SPLITS id in two on 'verbs/', we want the end which is the actual verb
				$split = explode('verbs/', $verbId);
				$verb = $split[1];

			}else{
				//IF it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'
				$verbLang =  $resultarray[$registrationid][0]["verb"]["display"];
				//Retreive the language
				$lang = array_key_first($verbLang);
				//use it to retreive verb
				$verb = [$verbLang][0][$lang];
			}
			return $verb;
	}

	/**
	 * Returns a name (the au title) retrieved from collected LRS data based on registration id
	 * @param mixed $resultarray - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed - object name
	 */
	public function cmi5launch_retrieve_name($resultarray, $registrationid){
		//THIS is the SECOND chunk, this is the problem
		$objectInfo = $resultarray[$registrationid][0]["object"];
		$definition = array_key_exists("definition", $objectInfo);
		//If it is null then there is no "definition", so go by object id
		if(!$definition ){
			//retrieve id
			$object = $resultarray[$registrationid][0]["object"]["id"];
			//I have noticed that in the LRS when it can't find a name it references the WHOLE id as in "actor did WHOLEID", so I will do the same here
		}else{
			//IF it is not null then there is a language easy to read version of object definition, such as 'en' or 'en-us'
			$objectLang =  $resultarray[$registrationid][0]["object"]["definition"]["name"];
			//Retreive the language
			$lang = array_key_first($objectLang);
			//use it to retreive verb
			$object = [$objectLang][0][$lang];
		}
		return $object;
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
		
		
		$date = new \DateTime($resultarray[$registrationid][0]["timestamp"], new \DateTimeZone('US/Eastern'));
		
		$date->setTimezone(new \DateTimeZone('America/New_York'));

		$date = $date->format('d-m-Y' . " ".  'h:i a');

		return $date;
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
		if (array_key_exists("result", $resultarray[$registrationid][0] ) )
		{
			//If it exists, grab it
			$resultInfo = $resultarray[$registrationid][0]["result"];
		
			$score = array_key_exists("score", $resultInfo);

		}
		
			//If it is null then the item in question doesn't exist in this statement
		if ($score) {

			$score = $resultarray[$registrationid][0]["result"]["score"];

			//Raw score preferred to scaled
			if($score["raw"]){
		
				$returnScore = $score["raw"];
				return $returnScore;
			}
			elseif($score["scaled"]){
		
				$returnScore = round($score["scaled"], 2) ;
				return $returnScore;
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

		//Testing-MB
		echo"<br>";
		echo("Since it's annoying lets also test here, are statements being returnes?");
		var_dump($resultDecoded);
		echo"<br>";
			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go with it

		foreach($resultDecoded as $singleStatment){

			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go and compare to saved session 'code'
			$code = $session->code;
			$currentSessID = "";
			$ext = $singleStatment[$registrationid][0]["context"]["extensions"];
				foreach ($ext as $key => $value) {
				
					//if key contains "sessionid" in string
					if(str_contains($key, "sessionid")){
						$currentSessID= $value;
					}
				}

			//Now if code equals currentSessID, this is a statement pertaining to this session
			if($code == $currentSessID){

				//Testing-MB
				echo"<br>";
				echo("AH! what about here? does code equal currensesid? Are we in here?");
				echo"<br>";
				$actor = $this->cmi5launch_retrieve_actor($singleStatment, $registrationid);
				$verb = $this->cmi5launch_retrieve_verbs($singleStatment, $registrationid);
				$object = $this->cmi5launch_retrieve_name($singleStatment, $registrationid);
				$date = $this->cmi5launch_retrieve_timestamp($singleStatment, $registrationid);
				$score = $this->cmi5launch_retrieve_score($singleStatment, $registrationid);
				
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
?>