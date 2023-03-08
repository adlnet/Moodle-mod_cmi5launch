<?php
//Class to hold methods for tracking and displaying student progress

class progress{

	public function getRetrieveStatement()
	{
	    return [$this, 'retrieveStatement'];
	}

	public function retrieveStatement($regId, $id){

		//Now were do we get ID, will view.php have it?
		$settings = cmi5launch_settings($id);

		//Array to hold verbs and be returned
		$progressUpdate = array();

		//When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary
		$data = array(
		'registration' => $regId
		);

		//Url to request statements from 
		//MB - I think we should do LRS endpoint and add 'statements'
		//BECAUSE it could not be LL.
		//Here it is LOL, the login!? No! We want the ADDY!!!The ENDPOINT!!

		$url = $settings['cmi5launchlrsendpoint'] . "statements";
		//Build query with data above
		$url = $url . '?' . http_build_query($data,"", '&',  PHP_QUERY_RFC1738);
		//$url = $url . '?' . urlencode($data);

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

		//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
		$resultChunked = array_chunk($resultDecoded["statements"], 1);

		$length = count($resultChunked);
		
		//Why is iteration unreachable? It's reachable in the other test file
		for($i = 0; $i < $length; $i++){

			//var_dump($resultChunked[$i]);

			$actor = $resultChunked[$i][0]["actor"]["account"]["name"];
		//	echo"Actor is : " . $actor;

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
				//	echo"Verb is : " . $verb;
				}else{
					//IF it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'
					$verbLang =  $resultChunked[$i][0]["verb"]["display"];
					//Retreive the language
					$lang = array_key_first($verbLang);
					//use it to retreive verb
					$verb = [$verbLang][0][$lang];
//					echo"Verb is : " . $verb;
				}
			
		//Ok, so now it has actor and verb, but we want EACH ONE to display right?
		//Barnacles! How best to do that? Maybe return actor/verb. OH wait! forgot
		//the dang activity *facepalm*
		
			//Some activities are the same as verbs. sometimes they do not have an easy to display 'language' option, we need to check if 'display' is present
			//Objects seem to always have 'id' and 'objecttype', but do not always have 'definition'. Def is what has the easy to read version because within it
			// is 'type' and 'name' and within 'name; is the en opr lang and easy to read vers. 
			//See if definition AND it's sub name are there, otherwise might as qwell go with id.///EXCEPT we CANT cause it doesn't do NESTED ids....
			echo"<br>";
			echo"I forot, what this is resultChunked: ";
			var_dump($resultChunked);
			echo"<br>";

			//THIS is the SECOND chunk, this is the problem
			$objectInfo = $resultChunked[$i][0]["object"];
			$definition = array_key_exists("definition", $objectInfo);
			//If it is null then there is no "definition", so go by object id
			if(!$definition ){
				//retrieve id
				$object = $resultChunked[$i][0]["object"]["id"];
				//I have noticed that in the LRS when it can't find a name it references the WHOLE id as in "actor did WHOLEID", so I will do the same here
//				echo"Object is : " . $object;
			}else{
				//IF it is not null then there is a language easy to read version of object definition, such as 'en' or 'en-us'
				$objectLang =  $resultChunked[$i][0]["object"]["definition"]["name"];
				//Retreive the language
				$lang = array_key_first($objectLang);
				//use it to retreive verb
				$object = [$objectLang][0][$lang];
//				echo"object is : " . $object;
			}
		
			//Now the timstamp.... Do we want to include timestamp? I reckon so teacher tracks betteR???? hmmmmm

			// Specified date/time in your computer's time zone.
			//$date = new DateTimeImmutable($resultChunked[$i][0]["timestamp"]);
			
			$date = new DateTime($resultChunked[$i][0]["timestamp"], new DateTimeZone('US/Eastern'));
			//$date = $date->format('d-m-Y' . " ".  'h:i a');
			
			//$date = date_format(
			//	date_create($resultChunked[$i][0]["timestamp"]),
			//	'D, d M Y H:i:s');
				$date->setTimezone(new DateTimeZone('America/New_York'));

//			var_dump($date);

			$date = $date->format('d-m-Y' . " ".  'h:i a');

			//echo"<br>";
			
			//I need a better way to do date and time
			//We need to make it a date object!!! ...hmmm w
			//$timestamp=$resultChunked[$i][0]["timestamp"];
			//$date = $timestamp->format('d-m-Y' . " ".  'h:i a');
			//echo gmdate("Y-m-d\TH:i:s\Z", $timestamp);

			///WAIT!!!
			// I can return a STRING SMH
			//Okay, now lets put them all in an array, pass the array out as return,
			//Then moodle can unpack the array into a nice sentence on the screen
			//$progressUpdate = array ($actor, $verb, $object, $date);
			$progressUpdate[] = "$actor $verb $object on $date";
			
			/*
			echo"<br>";
			echo"" . var_dump($progressUpdate);
			echo"<br>";
			*/

			
		}
		//We originally had progressUpdate as array because
		//we want it ti return ALL the verbs, so lets try arraying
		//it again

		return $progressUpdate;
	}		


}
?>