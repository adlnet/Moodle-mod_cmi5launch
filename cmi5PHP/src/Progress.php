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

	public function getCompletion()
	{
	    return [$this, 'evaluateCompleted'];
	}
	public function getRetrieveProgress()
	{
	    return [$this, 'retrieveProgress'];
	}
	public function getRetrieveVerb()
	{
	    return [$this, 'retrieveVerbs'];
	}

	public function getRequestLRSInfo()
	{
	    return [$this, 'requestLRSinfo'];
	}

	public function evaluateCompleted($auMoveon, $verbs){

		//Return this bool to let cqaller know if it is moveon worthy
		$moveOn = true | false;
//Firstprint the verbs so I can make sure this wokrs
		echo "<br>";
		echo "Hi there@ Hello, I'm a list of verbs";
		echo "<br>";
		var_dump($verbs);
		echo "<br>";

		//These will keep track of completed and passed being found
		$completedFound = true | false;
		$passedFound = true | false;

		$completedFound = array_search("completed", $verbs);
		$passedFound = array_search("passed", $verbs);

		//These bools track what verb has been sent. This helps because we may need more than one verb
		//maybe these should be in outerloop, or see what verbs come here
		//in outer loop then use a array of verbs HERE and finally pass out completed.
		//
		switch ($auMoveon) {
			case ("Passed"):
			    
				//Only moves on if verb is 'passed'
				
				if($passedFound == true){
					$moveOn = true;
					return $moveOn;
				}
				else{
					$moveOn = false;
					return $moveOn;
				}
			    
			case ("Completed"):
				//Only moves on if verb is 'completed'

				if($completedFound == true){
					$moveOn = true;
					return $moveOn;
				}
				else{
					$moveOn = false;
					return $moveOn;
				}	
			case ("CompletedAndPassed"):
				//Only moves on if verb(s) is/are 'completed AND PASSED'
				
				if($passedFound == true && $completedFound == true){
					$moveOn = true;
					return $moveOn;
				}
				else{
					$moveOn = false;
					return $moveOn;
				}


				case ("CompletedOrPassed"):
				//Only moves on if verb(s) is/are 'completed' OR 'passes'
				if($passedFound == true || $completedFound == true){
					$moveOn = true;
					return $moveOn;
				}
				else{
					$moveOn = false;
					return $moveOn;
				}
		 }
	}


	public function retrieveProgress($registrationdatafromlrs, $id)
	{
		//We can return this to calling page
		//or even save in a DB, there IS a completed.pass in cmi5_urls table
		$progressList = array();
		
		//Retrieve list of registration ids
		$registrationIds = $this->sortRegistration($registrationdatafromlrs);

		//use the registration ids to request progress updates from
		//LRS
		foreach ($registrationIds as $regid) {

			/*
			$statement = $this->retrieveStatement($regid, $id);
			$verb= $this->retrieveVerbs($regid, $id);
			
			//<May not need this? Perhaps regid is enouvh
			$auId = $this->retrieveObject($regid, $id);
			*/

			//Ok so this retrieves statment
			$progressList[] = $this->retrieveStatement($regid, $id);
			
			//$progressList[] = "$auId => $verb";
		}

		return $progressList;
		//So if verb is completed, then auID is completed
		//if verb is anything else in proresS?
		//and of course if no verb then
	}


	//This will take the LRS info and retrieve the regisid, later we may want dates?
	public function sortRegistration($lrsInfo){

		//To hold regID
		$regIds = array();
		foreach ($lrsInfo as $regId =>$info) {
			$regIds[] = $regId;
		}
		return $regIds;
	}
	

	public function checkCompleted($aus, $regId){
		
		$foundStatement = array();
		
		foreach($regId as $id){
			echo "Give me the regid here!!!";
			echo($id);
			echo "<br>";

			foreach ($aus as $key => $item) {

				echo "Give me the  au id here!!!";
			echo( $au['id'] );
			echo "<br>";

				//Retrieve individual AU as array
				$au = (array) ($aus[$key]);
				$auId = $au['id'];
				$auMoveon = $au['moveOn'];
	
				//ToDO - this is the version of completed the LRS seems to use, but there can be more than
				//one version fo a verb. Needs to be investigated?
				$completedVerb = "http://adlnet.gov/expapi/verbs/completed";
				//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
				//Lets try to ask for 'completed' verb
				$data = array(
					'registration' => $id,
					'verb' => $completedVerb
					/*'activity'=>$auId*/
				);
				$result = $this->sendRequestToLRS($data, $id);
				echo "<br>";
				echo "Ok, how is this? ONE?";
				echo var_dump($result);
				if (!$result['more']){
					echo"true, its NOT empty";
				}
				else{
					echo "false, its empty";
				}
				//echo"What is " . $result['statements'][0];
				echo "<br>";

					//If break is used you can use it to get out of mopre than one loop!
				if ($result['more']="" && empty($result['statements']) ){
					//It found something!
					$foundStatement[] = $result;
				}
			}
		}
		/*
		echo "<br>";
				echo "HEY WHAT IS THIS";
				echo var_dump($foundStatement);
				echo "<br>";
			*/
			//IF empty there was nothing! Boo! return no passed found or false
			if(count($foundStatement)==0){
			return false;
			}
			else{
				//Something was found! Woot!
			return true;
			}
	}
	public function checkPassed($aus, $regId){

		$foundStatement = array();

		foreach($regId as $id){
			foreach ($aus as $key => $item) {
				//Retrieve individual AU as array
				$au = (array) ($aus[$key]);
				$auId = $au['id'];
				$auMoveon = $au['moveOn'];
	
				//ToDO - this is the version of completed the LRS seems to use, but there can be more than
				//one version fo a verb. Needs to be investigated?
				$passedVerb = "http://adlnet.gov/expapi/verbs/passed`";
				//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
				//Lets try to ask for 'completed' verb
				$data = array(
					'registration' => $id,
					'verb' => $passedVerb,
					'activity'=>$auId
				);
				$result = $this->sendRequestToLRS($data, $id);
				echo "<br>";
				echo "Ok, how is this? TWO?";
				echo var_dump($result);
				echo "<br>";
					//If break is used you can use it to get out of mopre than one loop!
				if (!$result['more']="" && !$result['statements']="" ){
					//It found something!
					$foundStatement[] = $result;
				}
			}
		}
			//IF empty there was nothing! Boo! return no passed found or false
			if(empty($foundStatement)){
			return false;
			}
			else{
				//Something was found! Woot!
			return true;
			}
	}

	public function requestCompletedInfo($aus, $registrationIds, $id){

		$regId = $this->sortRegistration($registrationIds);

		$completedFound = $this->checkCompleted($aus, $regId);
		
		$passedFound = $this->checkPassed($aus, $regId);

	}

	public function requestLRSinfo($regId, $id){

		//Array to hold result
		$result = array();

		//Somes times when this is called, ffor instance from AUview.php
		//IT's a single reg id not an array. Below needs an array, so check and create array if need be
		if (!is_array($regId)) {
			//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
			$data = array(
				'registration' => $regId
			);

			$statements = $this->sendRequestToLRS($data, $regId);
			//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
			$statement = array_chunk($statements["statements"], 1);

			$length = count($statement);

			for ($i = 0; $i < $length; $i++){
			
			//This separates the larger statment into the separete sessions and verbs
				$current = ($statement[$i]);
			array_push($result, array ($regId => $current) );
			}
		}else{
			foreach ($regId as $id => $info) {

	
				//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
				$data = array(
					'registration' => $id
				);

				$statements = $this->sendRequestToLRS($data, $id);
				//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments

				$statement = array_chunk($statements["statements"], 1);

				$length = count($statement);

				for ($i = 0; $i < $length; $i++) {

					//This separates the larger statment into the separete sessions and verbs
					$current = ($statement[$i]);
					array_push($result, array($id => $current));
				}
			}
		

	}

		return $result;
	}

	public function sendRequestToLRS($data, $id)
	{

		
	//Now were do we get ID, will view.php have it?
	$settings = cmi5launch_settings($id);

	//Url to request statements fr
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

	/*
	echo "<br>";
		echo "********************What is result here RIGHT AFTER LRS RETURN, CAN IT BE CLUMPED HRE";
		var_dump($result);
			echo"<br>";
	
			*/
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

	//THis is what AUVIEW calls successully to get info
	//
	//And is not currently being successful
	//sooooo
	/** */
	public function retrieveStatement($regId, $id)
	{


		//Array to hold verbs and be returned
		$progressUpdate = array();

		//Array to hold verbs and be returned
		$verbs = array();

		$resultDecoded = $this->requestLRSinfo($regId, $id);


		//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
		//Well, i think because it is checked for statements before? Maybe this can go?
		//$resultChunked = array_chunk($resultDecoded, 1);
		//NO THIS isnt the answer! I remember there was a way to do this right? We want to et past the 0, I have a way somewhere,
		

		//LEts try without resultChunked with JUST orig resutls decoded
		//Because it would be one less nest of a '0'
		//Also we can't just take the first object, byt making a new array assigned to value of 0,,
		//because WHAT if there are moer than one regid? IT would then need 0, 1, 2 etc

		//Ok, so then this should be resultDecoded, not chunked? Uh lets just change it to chunked on 522 and save changingg
		$length = count($resultDecoded);

		//If length is resultDecod, it should be amount of regids

		//Why is iteration unreachable? It's reachable in the other test file
		//Maybe better to make this a foreach? Cause it may be diff lengts?

		//Maybe DO use this form, cause then we can use the 'i' number to select the WHOLE regid array, and THAT can be parsed accordingly
		for ($i = 0; $i < $length; $i++) {

			//Now we want to have a second iteration through the regid array BECAUSE there may be more than one verb per array
			//so maybe an if then//
			//or maybe just make the progress array and make array of it too,

			$currentRegid = $resultDecoded[$i];
//Maybe not needed, as each regid IS doing it's own thing, even same regid mutlples as diff

		//	foreach ($currentRegid as $regid => $regInfo) {

		//i is each separate statment
            //We don't know the regid, but need it because it's the first array key, 
            //sosimply retrieve the key itself.
            //current regid
            $regid = array_key_first($currentRegid);
				//Now to parse the diff verbs, maybe array chunk on 'id'?
				$actor = $this->retrieveActor($currentRegid, $regid);
				$verb = $this->retrieveVerbs($currentRegid, $regid);
				$object = $this->retrieveObject($currentRegid, $regid);
				$date = $this->retrieveTimestamp($currentRegid, $regid);

				//Maybe make this an overloaded func that can print this and /or just verbs
				//Like if you pass in verbs it gives only verbs
				//Wait....this is the above smh
				//BUT, this is the only one with the resultChunked info, so lets pass back shtuff
				//and let it have the string stuff added later, then easy to parse
				//Could even pas as actor=>actor
				//OR object=> actor, verb, date. Then we can sort it by au!
				$progressUpdate[] = "$actor $verb $object on $date";
			//}
		
			
		}
		return $progressUpdate;
	}

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
			
			echo"<br>";
			echo "What is an au at this stage?";
			var_dump($progressUpdate);
		echo"<br>";

		}		
	
		return $progressUpdate;
	}
}
?>