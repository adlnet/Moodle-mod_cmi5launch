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
    public function getRetrieveUrl2(){
        return [$this, 'retrieveUrl2'];
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
        $result = $this->sendRequest($data, $url, $username, $password);

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
        $token = cmi5Connectors::sendRequest($data, $url, $username, $password);

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
    public function retrieveUrl($actorName, $homepage, $returnUrl, $url, $bearerToken){

        echo "Retreive url function entered ";

        //retrieve and assign params
        $actor = $actorName;
        $homeUrl = $homepage;
        $retUrl = $returnUrl;
        $token = $bearerToken;
        $reqUrl = $url;
        echo "<br>";
        echo "actorname is {$actor} and the home URL is {$homeUrl}, now returnURL is {$retUrl}, the token is {$token}, and its going to {$reqUrl}";
        echo "<br>";

        //the body of the request must be made as array first
        $data = array(
            'actor' => array (
                'account' => array(
                    "homePage" => $homeUrl,
                    "name" => $actor,
                )
            ),
            'returnUrl' => $retUrl
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
        $launchResponse = file_get_contents( $reqUrl, false, $context );
        
        //return response
        return $launchResponse;
    }
     
    ///Function to retrieve a luanch URL for course
    //Same as function above except I'm going to attempt to use this in moodle
    //and pass moodles variables to it instead 
    //@param $actorName - the tenant name to be passed as name property actor->account 
    //@param $homepage - The URL that will be passed as the homepage property in actor->account
    //@param $returnUrl - The URL that will be passed as the returnUrl property in actor
    //@param $url - The URL to send request for launch URL to
    ////////
    public function retrieveUrl2($actorName, $homepage, $returnUrl, $url, $bearerToken){

        //Will this let me update DB tables from here? -MB
        global $DB, $CFG;
        /////////

        echo "Retreive url function entered ";

        
        //retrieve and assign params
        $actor = $actorName;
        $homeUrl = $homepage;
        $retUrl = $returnUrl;
        $token = $bearerToken;
        $reqUrl = $url;
        echo "<br>";
        echo "actorname is {$actor} and the home URL is {$homeUrl}, now returnURL is {$retUrl}, the token is {$token}, and its going to {$reqUrl}";
        echo "<br>";

        //the body of the request must be made as array first
        $data = array(
            'actor' => array (
                'account' => array(
                    "homePage" => $homeUrl,
                    "name" => $actor,
                )
            ),
            'returnUrl' => $retUrl
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
        $launchResponse = file_get_contents( $reqUrl, false, $context );
        
        //return response
        return $launchResponse;
    }


        ///Function to construct, send an URL, and save result
        //@param $dataBody - the data that will be used to construct the body of request as JSON 
        //@param $url - The URL the request will be sent to
        //@param $username - the username for basic auth
        //@param $password - the password for basic auth
        ///TODO - perhaps make an overload constructor that can take header info as an array, and method so it can work for GET/POST
        /////
        public function sendRequest($dataBody, $urlDest, $username, $password) {
            $data = $dataBody;
            $url = $urlDest;
            $user = $username;
            $pass = $password;
    
            echo"sendRequest has been fired";
    
        // use key 'http' even if you send the request to https://...
        //There can be multiple headers but as an array under the ONE header
        //content(body) must be JSON encoded here, as that is what CMI5 player accepts
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => array('Authorization: Basic '. base64_encode("$user:$pass"),  
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
 
 
}

    ?>