<?php
//Class to hold ways to communicate with CMI5 player through its API's -MB
//namespace cmi5;

class cmi5Connectors{

    public function getCreateTenant(){
        return [$this, 'createTenant'];
    }
    public function getRetrieveToken(){
        return [$this, 'retrieveToken'];
    }
    public function getRetrieveUrl(){
        return [$this, 'retrieveUrl'];
    }
    public function getCreateCourse(){
        return [$this, 'createCourse'];
    }
    public function getSessions(){
        return [$this, 'retrieveSessionInfo'];
    }
    public function getRegistrationPost(){
        return [$this, 'retrieveRegistrationPost'];
    }
    public function getRegistrationGet(){
        return [$this, 'retrieveRegistrationGet'];
    }

    //Function to create a course
    // @param $id - tenant id in Moodle
    // @param $token - tenant bearer token
    // @param $fileName - The filename of the course to be imported, to be added to url POST request 
    // @return  $result - Response from cmi5 player
    public function createCourse($id, $tenantToken, $fileName){

        global $DB, $CFG;
        $settings = cmi5launch_settings($id);

        //retrieve and assign params
        $token = $tenantToken;
        $file = $fileName;

        //Build URL to import course to
        $url= $settings['cmi5launchplayerurl'] . "/api/v1/course" ;
       
        //the body of the request must be made as array first
        $data = $file;
  
        //sends the stream to the specified URL 
        $result = $this->sendRequest($data, $url, $token);

        if ($result === FALSE) {

            if ($CFG->debugdeveloper) {
                echo "Something went wrong sending the request";
                echo "<br>";
                echo "Dumping session to troubleshoot.";
                var_dump($_SESSION);
                echo "<br>";
            }
	     } else {

			//Return an array with tenant name and info
			return $result;
		}
    }

    //////
    //Function to create a tenant
    // @param $urlToSend - URL retrieved from user in URL textbox
    // @param $user - username 
    // @param $pass - password 
    // @param $newTenantName - the name the new tenant will be, retreived from Tenant Name textbox
    /////
    public function createTenant($urlToSend, $user, $pass, $newTenantName){

        global $CFG;
        //retrieve and assign params
        $url = $urlToSend;
        $username = $user;
        $password = $pass;
        $tenant = $newTenantName;
    
        //the body of the request must be made as array first
        $data = array(
            'code' => $tenant);
    
        //sends the stream to the specified URL 
        $result = $this->sendRequest($data, $url, $username, $password);

        if ($result === FALSE){
            if ($CFG->debugdeveloper)  {
                    echo "Something went wrong!";
                    echo "<br>";
                    var_dump($_SESSION);
                }
        }
        
        //decode returned response into array
        $returnedInfo = json_decode($result, true);
            
        //Return an array with tenant name and info
        return $returnedInfo;
    }


    //Function to retreive registration from cmi5 player. This way uses
    //the registration id
    //Registration  is "code" in returned json body
    //@param $registration - registration UUID
    // @param $id - launch id
    function retrieveRegistrationGet($registration, $id) {

		$settings = cmi5launch_settings($id);

		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];

		global $CFG;

        //Build URL for launch URL request
        //Okay it looks like the reurnurk is same level as  
	    $url = $playerUrl . "/api/v1/registration/" . $registration ;

	   ///////////
	   $options = array(
		'http' => array(
		    'method'  => 'GET',
		    'header' => array('Authorization: Bearer ' . $token,  
			   "Content-Type: application/json\r\n" .
			   "Accept: application/json\r\n")
		)
	 );
		//the options are here placed into a stream to be sent
		$context  = stream_context_create($options);
		
		//sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
		$result = file_get_contents( $url, false, $context );
	 
        if ($result === FALSE){

            if ($CFG->debugdeveloper)  {
                echo "Something went wrong!";
                echo "<br>";
                var_dump($_SESSION);
                }
        }
        else{
  
               $registrationInfo = json_decode($result, true);
    //The returned 'registration info' is a large json 
    //code is the registration id we want   
			$registration = $registrationInfo["code"];
			
            //Why would I return the code when the code is needed to fwetch?
//			return $registration;

            return $registrationInfo; //much better!
        }
    }


    //Function to retreive registration from cmi5 player. This way uses
    //the course id and actor name
    //As this is a POST request it returns a new code everytime it is called
    //Registration  is "code" in returned json body
    //@param $courseID
    // @param $id 
    function retrieveRegistrationPost($courseId, $id){

        global $USER;

		$settings = cmi5launch_settings($id);

        //Switch this out for user not tenant
//        $actor = $settings['cmi5launchtenantname'];
        $actor = $USER->username;
        $token = $settings['cmi5launchtenanttoken'];
        $playerUrl = $settings['cmi5launchplayerurl'];
        $homepage = $settings['cmi5launchcustomacchp'];
        global $CFG;
      
        //Build URL for launch URL request
        //Okay it looks like the return url is same level as  
	    $url = $playerUrl . "/api/v1/registration" ;

        //the body of the request must be made as array first
        $data = array(
            'courseId' => $courseId,
            'actor' => array(
                'account' => array(
                    "homePage" => $homepage,
                    "name" => $actor
                )
            )
        );
        
	   $options = array(
		'http' => array(
		    'method'  => 'POST',
		    'header' => array('Authorization: Bearer ' . $token,  
			   "Content-Type: application/json\r\n" .
			   "Accept: application/json\r\n"),
		    'content' => json_encode($data)
		)
	 );

		//the options are here placed into a stream to be sent
		$context  = stream_context_create($options);
		
		//sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
		$result = file_get_contents( $url, false, $context );
		

        if ($result === FALSE){

            if ($CFG->debugdeveloper)  {
                echo "Something went wrong!";
                echo "<br>";
                var_dump($_SESSION);
                }
        }
        else{

               $registrationInfo = json_decode($result, true);
          
               //The returned 'registration info' is a large json 
		    //code is the registration id we want   
			$registration = $registrationInfo["code"];
			
			return $registration;
        }
    }

     //@param $urlToSend - URL to send request to
    // @param $user - username
    // @param $pass - password
    // @param $audience - the name the of the audience using the token,
    // @param #tenantId - the id of the tenant
     function retrieveToken($urlToSend, $user, $pass, $audience, $tenantId){

        global $CFG;
        //retrieve and assign params
        $url = $urlToSend;
        $username = $user;
        $password = $pass;
        $tokenUser = $audience;
        $id = $tenantId;
    
        //the body of the request must be made as array first
        $data = array(
            'tenantId' => $id,
            'audience' => $tokenUser
        );
    
        //sends the stream to the specified URL 
        $token = $this->sendRequest($data, $url, $username, $password);

        if ($token === FALSE){

            if ($CFG->debugdeveloper)  {
                echo "Something went wrong!";
                echo "<br>";
                var_dump($_SESSION);
                }
        }
        else{
            return $token;
        }

    }

    ///Function to retrieve a launch URL for an AU
    //@param $id -Actor id to find correct info for url request
    //@param $auID -AU id to pass to cmi5 for url request
    //@return $url - The launch URL returned from cmi5 player
    ////////
    public function retrieveUrl($id, $auIndex){
		//TODO, this needs to be changed to have an if its one old call, if its not, new call
        //MB
        global $DB, $USER;

		//Retrieve actor record, this enables correct actor info for URL storage
		$record = $DB->get_record("cmi5launch", array('id' => $id));

        //Here's the trouble, still getting reggistration id from master RECORD
		$settings = cmi5launch_settings($id);
     
        $usersCourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

		$registrationID = $usersCourse->registrationid;
		
        $homepage = $settings['cmi5launchcustomacchp'];
        $returnUrl =$usersCourse->returnurl;
		//MB
        //We need to change this to actor name, not tenant
        $actor= $USER->username;
        //$actor= $settings['cmi5launchtenantname'];
		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];
		$courseId = $usersCourse->courseid;

        //Build URL for launch URL request
	    $url = $playerUrl . "/api/v1/course/" . $courseId  ."/launch-url/" . $auIndex;

        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => $homepage,
                    "name" => $actor,
                ),
            ),
            'returnUrl' => $returnUrl,
            'reg' => $registrationID
        );

		// use key 'http' even if you send the request to https://...
        //There can be multiple headers but as an array under the ONE header
        //content(body) must be JSON encoded here, as that is what CMI5 player accepts
        //JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly
     	   $options = array(
            'http' => array(
                'method'  => 'POST',
                'ignore_errors' => true,
                'header' => array("Authorization: Bearer ". $token,  
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"),
                'content' => json_encode($data, JSON_UNESCAPED_SLASHES)

            )	
        );

        //the options are here placed into a stream to be sent
        $context  = stream_context_create(($options));

        //sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
        $launchResponse = file_get_contents( $url, false, $context );

        //here may be the problem, what is being sent back?
        echo"<br>";
        echo" This is swhat is bein sent back>:";
        var_dump($launchResponse);
        ECHO"<br>";
        //Only return the URL
		$urlDecoded = json_decode($launchResponse, true);

        return $urlDecoded;
    }


        ///Function to construct, send an URL, and save result
        //@param $dataBody - the data that will be used to construct the body of request as JSON 
        //@param $url - The URL the request will be sent to
        //@param ...$tenantInfo is a variable length param. If one is passed, it is $token, if two it is $username and $password
        ///@return - $result is the response from cmi5 player
        /////
        public function sendRequest($dataBody, $urlDest, ...$tenantInfo) {
            $data = $dataBody;
            $url = $urlDest;
            $tenantInformation = $tenantInfo;
    
                //If number of args is greater than one it is for retrieving tenant info and args are username and password
                if(count($tenantInformation) > 1 ){
                
                    $username = $tenantInformation[0];
                    $password = $tenantInformation[1];

                    // use key 'http' even if you send the request to https://...
                    //There can be multiple headers but as an array under the ONE header
                    //content(body) must be JSON encoded here, as that is what CMI5 player accepts
                    $options = array(
                        'http' => array(
                            'method'  => 'POST',
                            'header' => array('Authorization: Basic '. base64_encode("$username:$password"),  
                                "Content-Type: application/json\r\n" .
                                "Accept: application/json\r\n"),
                            'content' => json_encode($data)
                        )
                    );
                    //the options are here placed into a stream to be sent
                    $context  = stream_context_create($options);
                
                    //sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
                    $result = file_get_contents( $url, false, $context );
                
                    //return response
                    return $result;
                }
            //Else the args are what we need for posting a course
          	  else{

				//First arg will be token
                	$token = $tenantInformation[0];
	            	$file_contents = $data->get_content();

                // use key 'http' even if you send the request to https://...
                //There can be multiple headers but as an array under the ONE header
                //content(body) must be JSON encoded here, as that is what CMI5 player accepts
                //JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly
                $options = array(
                    'http' => array(
                        'method'  => 'POST',
                        'ignore_errors' => true,
                        'header' => array("Authorization: Bearer ". $token,  
                            "Content-Type: application/zip\r\n"), 
                        'content' => $file_contents
                    )
                );

                 //the options are here placed into a stream to be sent
                 $context  = stream_context_create(($options));
    
                 //sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
                 $result = file_get_contents( $url, false, $context );

      	      return $result;
                }
    }


    /**
    *Retrieve session info from cmi5player
    * @param mixed $sessionId - the session id to retrieve
     * @param mixed $id - cmi5 id
     * @return mixed
     */
    public function retrieveSessionInfo($sessionId, $id){

        global $DB;

		$settings = cmi5launch_settings($id);
		
		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];

        //Build URL for launch URL request
	    $url = $playerUrl . "/api/v1/session/" . $sessionId;

		// use key 'http' even if you send the request to https://...
        //There can be multiple headers but as an array under the ONE header
        //content(body) must be JSON encoded here, as that is what CMI5 player accepts
        //JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly
     	   $options = array(
            'http' => array(
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => array("Authorization: Bearer ". $token,  
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n")
            )	
        );

        //the options are here placed into a stream to be sent
        $context  = stream_context_create(($options));

        //sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
        $launchResponse = file_get_contents( $url, false, $context );

		$sessionDecoded = json_decode($launchResponse, true);

        return $sessionDecoded;
    }

}
   
   ?>
