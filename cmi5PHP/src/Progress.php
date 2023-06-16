<?php
//Class to hold methods for tracking and displaying student progress

class progress{

	public function getRetrieveStatement()
	{
	    return [$this, 'retrieveStatement'];
	}

	public function getRequestCompleted()
	{
		return [$this, 'requestCompletedInfo'];
	}


	public function getRetrieveVerb()
	{
	    return [$this, 'retrieveVerbs'];
	}

	public function getRequestLRSInfo()
	{
	    return [$this, 'requestLRSinfo'];
	}

	/**
	 * Send request to LRS
	 * @param mixed $regId - registration id
	 * @param mixed $session - a session object 
	 * @return array
	 */
	public function requestLRSinfo($regId, $session /*$id*/){

		//Array to hold result
		$result = array();

		//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
		$data = array(
			'registration' => $regId,
			'since' => $session->createdAt
		);

		$statements = $this->sendRequestToLRS($data, $regId);
		//The results come back as nested array under more then statements. We only want statements, and we want them unique
		$statement = array_chunk($statements["statements"], 1);

		$length = count($statement);

		for ($i = 0; $i < $length; $i++){
		
		//This separates the larger statement into the separate sessions and verbs
			$current = ($statement[$i]);
		array_push($result, array ($regId => $current) );
		}
	
		return $result;
	}


	/**
	 * Builds and sends requests to LRS
	 * @param mixed $data
	 * @param mixed $id
	 * @return mixed
	 */
	public function sendRequestToLRS($data, $id)
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
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed - actor
	 */
	public function retrieveActor($info, $regid){

		$actor = $info[$regid][0]["actor"]["account"]["name"];
		return $actor;
	}

	/**
	 * Returns a verb retrieved from collected LRS data based on registration id
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed - verb
	 */
	public function retrieveVerbsOrig($resultChunked, $i){

		//Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present			
		$verbInfo = $resultChunked[0][0][$i]["statements"][0]["verb"];
		$display = array_key_exists("display", $verbInfo);

			//If it is null then there is no display, so go by verb id
			if(!$display ){
				//retrieve id
				$verbId = $resultChunked[$i][0]["verb"]["id"];

				//SPLITS id in two on 'verbs/', we want the end which is the actual verb
				$split = explode('verbs/', $verbId);
				$verb = $split[1];

			}else{
				//IF it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'
				$verbLang =  $resultChunked[$i][0]["verb"]["display"];
				//Retreive the language
				$lang = array_key_first($verbLang);
				//use it to retreive verb
				$verb = [$verbLang][0][$lang];
			}
			return $verb;
	}

	public function retrieveVerbs($resultChunked, $i){

		//Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present			
		$verbInfo = $resultChunked[$i][0]["verb"];
		$display = array_key_exists("display", $verbInfo);

			//If it is null then there is no display, so go by verb id
			if(!$display ){
				//retrieve id
				$verbId = $resultChunked[$i][0]["verb"]["id"];

				//SPLITS id in two on 'verbs/', we want the end which is the actual verb
				$split = explode('verbs/', $verbId);
				$verb = $split[1];

			}else{
				//IF it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'
				$verbLang =  $resultChunked[$i][0]["verb"]["display"];
				//Retreive the language
				$lang = array_key_first($verbLang);
				//use it to retreive verb
				$verb = [$verbLang][0][$lang];
			}
			return $verb;
	}

	/**
	 * Returns an object (au title?) retrieved from collected LRS data based on registration id
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed - object name
	 */
	public function retrieveObject($resultChunked, $i){
		//THIS is the SECOND chunk, this is the problem
		$objectInfo = $resultChunked[$i][0]["object"];
		$definition = array_key_exists("definition", $objectInfo);
		//If it is null then there is no "definition", so go by object id
		if(!$definition ){
			//retrieve id
			$object = $resultChunked[$i][0]["object"]["id"];
			//I have noticed that in the LRS when it can't find a name it references the WHOLE id as in "actor did WHOLEID", so I will do the same here
		}else{
			//IF it is not null then there is a language easy to read version of object definition, such as 'en' or 'en-us'
			$objectLang =  $resultChunked[$i][0]["object"]["definition"]["name"];
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
	 * Summary of retrieveResult
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return mixed
	 */
	public function retrieveResult($resultChunked, $i){

		//Verify this statement has a 'result' param
		if (array_key_exists("result", $resultChunked ) )
		{
			//If it exists, grab it
			$resultInfo = $resultChunked[$i][0]["result"];
		
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
				$success = $resultChunked[$i][0]["result"]["success"];
				
				//now that we have success, save to db. This means we need an object right? Can we update afield?
				//even if we could we need id to find it...
			}else{
			}

			//Maybe it would be better to just have a 'retrieveScore' for now
	}

	/**
	 * Returns a timestamp retrieved from collected LRS data based on registration id
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $i - the registration id
	 * @return string - date/time
	 */
	public function retrieveTimestamp($resultChunked, $i){
		
		$date = new DateTime($resultChunked[$i][0]["timestamp"], new DateTimeZone('US/Eastern'));
		
		$date->setTimezone(new DateTimeZone('America/New_York'));

		$date = $date->format('d-m-Y' . " ".  'h:i a');

		return $date;
	}

	/**
	 * 
	 * Summary of retrieveScore
	 * @param mixed $resultChunked - data retrieved from LRS, usually an array
	 * @param mixed $registrationid - the registration id
	 * @return mixed
	 */
	//Ok, if we change so session id goes through, can we update DB in this func	
	public function retrieveScore($resultChunked, $registrationid){

		//variable to hold score
		$score = null;

		//Verify this statement has a 'result' param
		if (array_key_exists("result", $resultChunked[$registrationid][0] ) )
		{
			//If it exists, grab it
			$resultInfo = $resultChunked[$registrationid][0]["result"];
		
			$score = array_key_exists("score", $resultInfo);

		}
		
			//If it is null then the item in question doesn't exist in this statement
		if ($score) {

			$score = $resultChunked[$registrationid][0]["result"]["score"];

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
	 * Summary of retrieveStatement
	 * //Retrieves statements from LRS
	 * @param mixed $regId
	 * @param mixed $id
	 * @param mixed $lmsId
	 * @return array<string>
	 */

	public function retrieveStatement($regId, $id, $session)
	{
		//Array to hold verbs and be returned
		$progressUpdate = array();
		//Array to hold score and be returned
		$returnScore = 0;

		$resultDecoded = $this->requestLRSinfo($regId, $session);

			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go with it

		foreach($resultDecoded as $singleStatment){

			//We need to sort the statements by finding their session id
			//parse through array 'ext' to find the one holding session id, 
			//grab id and go and compare to saved session 'code'
			$code = $session->code;
			$currentSessID = "";
			$ext = $singleStatment[$regId][0]["context"]["extensions"];
				foreach ($ext as $key => $value) {
				
					//if key contains "sessionid" in string
					if(str_contains($key, "sessionid")){
						$currentSessID= $value;
					}
				}

			//Now if code equals currentSessID, this is a statement pertaining to this session
			if($code == $currentSessID){
			
				$actor = $this->retrieveActor($singleStatment, $regId);
				$verb = $this->retrieveVerbs($singleStatment, $regId);
				$object = $this->retrieveObject($singleStatment, $regId);
				$date = $this->retrieveTimestamp($singleStatment, $regId);
				$score = $this->retrieveScore($singleStatment, $regId);
				
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

	/**
	 * Summary of prettyProgress
	 * I dont know if we need this anymore -TODO
	 * @param mixed $arrayOfStatements
	 * @return array<string>
	 */
	public function prettyProgress($arrayOfStatements){
		$length = count($arrayOfStatements);
		
		//Why is iteration unreachable? It's reachable in the other test file
		for($i = 0; $i < $length; $i++){

			$actor = $this->retrieveActor($arrayOfStatements, $i);
			$verb = $this->retrieveVerbs($arrayOfStatements, $i);
			$object = $this->retrieveObject($arrayOfStatements, $i);
			$date = $this->retrieveTimestamp($arrayOfStatements, $i);

			//Maybe make this an overloaded func that can print this and /or just verbs
			//Like if you pass in verbs it gives only verbs
			//Wait....this is the above smh
			//BUT, this is the only one with the resultChunked info, so lets pass back shtuff
			//and let it have the string stuff added later, then easy to parse
			//Could even pas as actor=>actor
			//OR object=> actor, verb, date. Then we can sort it by au!
			$progressUpdate[] = "$actor $verb $object on $date";
	
		}		
	
		return $progressUpdate;
	}
}
?>