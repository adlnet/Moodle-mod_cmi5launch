<?php
//namespace cmi5;

///Class to hold methods for working with cmi5
// @method - createTenant: used to create a new tenant
// @method - retrieveToken: used to retreive a bearer token with tenant id
// @property - 
class cmi5Connectors{

    //Need to make these global or properties - mb
    public static $tenantName = "";
    public static $tenantId = "";
    public static $bearerToken = "";
    public static $launchUrl = "";

    
    //To make a new instance and hold variables??
  //  global $returnedToken = $connectors->$GLOBALS.$bearerToken;
    
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

      //////
    //Function to create a course
    // @param $urlToSend - URL to import the course to
    // @param $id - tenant id in moodle
    // @param $token - tenant bearer token
    // @param $fileName - The filename of the course to be imported, to be added to url POST request 
    /////MB
    public function createCourse(/*$urlToSend,*/$id, $tenantToken, $fileName){

        global $DB;
        $settings = cmi5launch_settings($id);

        //echo"Importing a course! The course filename is " . $fileName;
        //retrieve and assign params
        $token = $tenantToken;
        $file = $fileName;

        //Build URL here
        //Can this file access the settings? 
        $url= "http://" . $settings['cmi5launchplayerurl'] . ":" . $settings['cmi5launchplayerport'] . "/api/v1/course" ;
       

        //the body of the request must be made as array first
        //This is where the filepath is right? How do we send?
       //Ok, the bopdy of the request is the file itself...so hmmm
        $data = $file;
  
        //sends the stream to the specified URL 
        $result = $this->sendRequestM($data, $url, $token);

  
        echo "<br>";
        echo "Okey pokey result is hopefully gonna work lets dump it here";
        var_dump($result);
        echo "<br>";

        if ($result === FALSE) 
            { echo"Something went wrong! TRY AGAIN";
                echo"<br>";
            echo "Dumping session trying to find prooblem";
                var_dump($_SESSION);
                echo "<br>";
        }
        else{
            echo"<br>";
            echo"<br>";
            echo "Course created. Response: is $result";
		  //to bring in functions from class cmi5Connector
		  //$connectors = new cmi5Connectors;
		  //to bring in functions from class cmi5_table_connectors
		  //$tableConnectors = new cmi5Tables;
		  //create instance of class functions
		  //$populateTable = $tableConnectors->getPopulateTable();
		  //Now we can popuate table and the id wont be null
		  //We can populate with theerturned course id
		  //If we chnage ow ppulate tbale works tl be good, it doesn't
		  //need a record really cause al ttakes is the record id? S ust pass an id? 
		  
		 
		  //Populate player table with record and tenant info for URL retrieval, and retrieve newly created record
   			//$tenantRecord = $populateTable($record, 'cmi5launch_player');
            var_dump($result);
            echo"<br>";

        }
            //decode returned response into array
            $returnedInfo = json_decode($result, true);
            
            //Return an array with tenant name and info
            return $result;
		  //what if result is returned and json decode happens later?
    }

    //////
    //Function to create a tenant
    // @param $urlToSend - URL retrieved from user in URL textbox
    // @param $user - username retrieved from user in username textbox - ideally this will be backend/hidden
    // @param $pass - password retrieved from user in password textbox - ideally this will be backend/hidden
    // @param $newTenantName - the name the new tenant will be, retreived from Tenant NAme textbox
    /////MB
    public function createTenant($urlToSend, $user, $pass, $newTenantName){ 
    
        echo"Are we making it here?";
        //retrieve and assign params
        $url = $urlToSend;
        $username = $user;
        $password = $pass;
        $tenant = $newTenantName;
    
        //the body of the request must be made as array first
        $data = array(
            'code' => $tenant);
    
        //sends the stream to the specified URL 
        $result = $this->sendRequestM($data, $url, $username, $password);

        echo "<br>";
        echo "What about here?";

        if ($result === FALSE) 
            { echo"Something went wrong!";
                echo"<br>";
                var_dump($_SESSION);
        }
        else{
            echo "Tenant created. Response: is $result";
            var_dump(json_decode($result, true));
        }
            //decode returned response into array
            $returnedInfo = json_decode($result, true);
            
            //Return an array with tenant name and info
            return $returnedInfo;
    }
    
    
     //Ok, next we need a function to retrieve bearer token, all in PHP
     //@param $urlToSend - URL retrieved from user in URL textbox
    // @param $user - username retrieved from user in username textbox - ideally this will be backend/hidden
    // @param $pass - password retrieved from user in password textbox - ideally this will be backend/hidden
    // @param $audience - the name the of the audience using the token, retreived from audience textbox
    // @param #tenantId - the id of the tenant, retreived from Tenant Id text box
    //Note - this is for trial, ideally the id will be stored in php variable in createTenant func and then supplied as needed
    /////MB
     function retrieveToken($urlToSend, $user, $pass, $audience, $tenantId){
    
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
        $token = $this->sendRequestM($data, $url, $username, $password);

        if ($token === FALSE) 
            { echo"Something went wrong!";
            echo"<br>";
            var_dump($_SESSION);
        }
        else{
            echo"The token is  + $token";
            return $token;
        }

}

    ///Function to retrieve a luanch URL for course
    //@param $actorName - the tenant name to be passed as name property actor->account 
    //@param $homepage - The URL that will be passed as the homepage property in actor->account
    //@param $returnUrl - The URL that will be passed as the returnUrl property in actor
    //@param $url - The URL to send request for launch URL to
    ////////
    //Trying somehting new, maybe just pass in id instead of above params?
    public function retrieveUrl($id){
		global $DB;
        echo "Retreive url function entered ";

	   //Maybe better to retreive them here as noted instead of in process_new_packae
	   //in lib.php??

	   // Retrieve record, this enables correect actor info for URL retrieval
	  // $record = $DB->get_record('cmi5launch', array('id' => $id));
	//I dont think this is the right time to pull get->record


		//to bring in functions from class cmi5_table_connectors
		//$tableConnectors = new cmi5Tables;
		//create instance of class functions
		//$populateTable = $tableConnectors->getPopulateTable();
		//Populate player table with record and tenant info for URL retrieval, and retrieve newly created record
  		//$tenantRecord = $populateTable($record, 'cmi5launch_player');
		
		
		//Retrieve actor record, this enables correct actor info for URL storage
		$record = $DB->get_record("cmi5launch", array('id' => $id));


		$settings = cmi5launch_settings($id);
		//TODO - I am hardcoding these for now, want to check with others as to best way to collect this info
        //such as from cmi5 mod install page, or cmi5 course uploadpage??? -MB
        $homepage ="http://myLMSexample.com";
        $returnUrl="http://127.0.0.1:63398.com";
	   $actor= $settings['cmi5launchtenantname'];
		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];
		$playerPort = $settings['cmi5launchplayerport'];
		$courseId = $record->courseid;

	$url = "http://" . $playerUrl . ":" . $playerPort . "/api/v1/course/" . $courseId  ."/launch-url/0";

        echo "<br>";
        echo "actorname is {$actor} and the home URL is {$homepage}, now returnURL is {$returnUrl}, the token is {$token}, and its going to {$url}";
        echo "<br>";

        //the body of the request must be made as array first
        $data = array(
            'actor' => array (
                'account' => array(
                    "homePage" => $homepage,
                    "name" => $actor,
                )
            ),
            'returnUrl' => $returnUrl
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

	   	//to bring in functions from class cmi5Connector
		$connectors = new cmi5Tables;
		//create instance of class functions
		$saveUrl = $connectors->getSaveURL();
		//Save the returned info to the correct table
		$saveUrl($id, $launchResponse);
		
        //return response
        return $launchResponse;
    }


        ///Function to construct, send an URL, and save result
        //@param $dataBody - the data that will be used to construct the body of request as JSON 
        //@param $url - The URL the request will be sent to
        //@param ...$tenantInfo is a variable length param. If one is passed, it is $token, if two it is $username and $password
        ///
        /////
        public function sendRequestM($dataBody, $urlDest, ...$tenantInfo) {
            $data = $dataBody;
            $url = $urlDest;
            $tenantInformation = $tenantInfo;
    
            echo"sendRequest has been fired";
    
                //If number of args is greater than one it is for retrieving tenant info and args are username and password
                if(count($tenantInformation) > 1 ){

                    echo "<br>";
                    echo "More than one arg entered!";
                    echo "<br>";
                
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

                echo "<br>";
                echo "One arg entered!";
                
                echo "<br>";
                //First arg will be token
                $token = $tenantInformation[0];
            echo "What is path and filename? Will this work?";
            echo "<br>";
            $filePath = $data->get_filepath();
            //$filePath = $data["filepath"];
            echo "Dern filepath:    " . $filePath;
            echo "<br>";
            $fileName = $data->get_filename();
           // $fileName = $data["filename"]; 
           echo "Dern filename:    " . $fileName;
            echo "<br>";

                //Can we change data here and make it contents???
            $file_contents = $data->get_content();
//dernnnnnnn
//NONE OF THIS IS WORKING? IS FILES an option??
            echo "IS FILES HERE?????";            
var_dump($_FILES);
//maybe this ay like in little proga,?
//can't find the path, can we retreive form temp
          //  $file_contents = file_get_contents("/var/www/moodledata/filedir" . $filePath . $fileName);
                //Ok, the problem does not seem to be the the token, it looks good

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
                //lets try adding a coma instead of dot after content-type, but it shoudl be ok with dot right?
                //Forget that! We forgot to JSON encode data!
                //Still not working, how about we remove the accept? IT's not
                //in basic call
                //Nooo, ok, well before we move to another test project
                //Maybe it can't find file?, if sooo then maybe lets manually put file path in 
                //JUST to see what happens
                echo "<br>";echo "<br>";echo "<br>";echo "<br>";
            echo "How about options? here?     >> ";
          //  var_dump($options);
            echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
       

                 //the options are here placed into a stream to be sent
                 $context  = stream_context_create(($options));
    
                 //MB .. So if the token seems ok, could it be cotext?? 
                 //which means it may also be OPTINO!!

    
                 echo "<br>";echo "<br>";echo "<br>";echo "<br>";
                 echo "How about context? here?>>    ";
                var_dump($context);
                 echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
      
                 ///Ok, lets make sure the args are all right!
                 echo "<br>";echo "<br>";echo "<br>";echo "<br>";
                 echo "ARGS about to be sent. Ffirst is URL:  >> ";
                 var_dump($url);
                 echo "<br>";echo "<br>";
                 echo "ARGS about to be sent. Second is context:  >> ";
                 var_dump($context);
                 echo "<br>";echo "<br>";
                echo "I a not sure what to do here....is it a file still? Can we dump it??";
            //var_dump($data);
                 echo "<br>";echo "<br>";echo "<br>";
                 
                 //sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
                 $result = file_get_contents( $url, false, $context );

            return $result;
                }
    }
}

    ?>