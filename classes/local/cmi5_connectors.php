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
 * Class to hold ways to communicate with CMI5 player through its API's.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


 namespace mod_cmi5launch\local;
 
class cmi5_connectors {

    public function cmi5launch_get_create_tenant() {
        return [$this, 'cmi5launch_create_tenant'];
    }
    public function cmi5launch_get_retrieve_token() {
        return [$this, 'cmi5launch_retrieve_token'];
    }
    public function cmi5launch_get_retrieve_url() {
        return [$this, 'cmi5launch_retrieve_url'];
    }
    public function cmi5launch_get_create_course() {
        return [$this, 'cmi5launch_create_course'];
    }
    public function cmi5launch_get_session_info() {
        return [$this, 'cmi5launch_retrieve_session_info_from_player'];

    }
    public function cmi5launch_get_registration_with_post() {
        return [$this, 'cmi5launch_retrieve_registration_with_post'];
    }
    public function cmi5launch_get_registration_with_get() {
        return [$this, 'cmi5launch_retrieve_registration_with_get'];
    }
    public function cmi5launch_get_send_request_to_cmi5_player_post() {
        return [$this, 'cmi5launch_send_request_to_cmi5_player_post'];

    }

    /**
    * Function to create a course.
     * @param mixed $id - tenant id in Moodle.
     * @param mixed $tenanttoken - tenant bearer token.
     * @param mixed $filename -- The filename of the course to be imported, to be added to url POST request.
     * @return bool|string - Response from cmi5 player.
     */
    public function cmi5launch_create_course($id, $tenanttoken, $filename) {

        global $DB, $CFG;
        $settings = cmi5launch_settings($id);

        // Build URL to import course to.
        $url= $settings['cmi5launchplayerurl'] . "/api/v1/course" ;

        // To determine the headers. 
        $filetype = "zip";

        $databody = $filename->get_content();
        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post($databody, $url, $filetype, $tenanttoken);

        // Decode result because if it is not 200 then something went wrong
        $resulttest = json_decode($result, true);


        if ($resulttest === FALSE || array_key_exists("statusCode", $resulttest) && $resulttest["statusCode"] != 200) {

            echo "<br>";

            echo "Something went wrong creating the course. CMI5 Player returned " . var_dump($result);

            echo "<br>";
              
        }

        // Return an array with course info.
        return $result;
    }

    /**
     * Function to create a tenant.
     * @param $urltosend - URL retrieved from user in URL textbox.
     * @param $username - username.
     * @param $password - password.
     * @param $newtenantname - the name the new tenant will be, retreived from Tenant Name textbox.
     */

    public function cmi5launch_create_tenant($urltosend, $username, $password, $newtenantname){

        global $CFG;
    
        // The body of the request must be made as array first.
        $data = array(
            'code' => $newtenantname);

        // To determine the headers. 
        $filetype = "json";
        
        // Data needs to be JSON encoded.
        $data = json_encode($data);

        // Sends the stream to the specified URL 
        $result = $this->cmi5launch_send_request_to_cmi5_player_post($data, $urltosend, $filetype, $username, $password);

        if ($result === FALSE){

            echo "<br>";

            echo "Something went wrong creating the tenant. CMI5 Player returned " . $result;

            echo "<br>";
                }

        // Decode returned response into array.
        $returnedinfo = json_decode($result, true);

        // Return an array with tenant name and info.
        return $returnedinfo;
    }

    /** 
     * Function to retrieve registration from cmi5 player. 
     * This way uses the registration id and GET request.
     * Registration  is "code" in returned json body.
     * @param $registration - registration UUID
     * @param $id - launch id
     */
    public function cmi5launch_retrieve_registration_with_get($registration, $id) {

        $settings = cmi5launch_settings($id);

        $token = $settings['cmi5launchtenanttoken'];
        $playerUrl = $settings['cmi5launchplayerurl'];

        global $CFG;

        // Build URL for launch URL request.
        $url = $playerUrl . "/api/v1/registration/" . $registration ;

        // Sends the stream to the specified URL 
        $result = $this->cmi5launch_send_request_to_cmi5_player_get($token, $url);
        
        if ($result === FALSE) {
            echo "<br>";
            echo "Something went wrong retrieving registration id. CMI5 Player returned " . $result;
            echo "<br>";
        }


        return $result; 

    }
    

    /** 
     * Function to retrieve registration from cmi5 player.
     * This way uses the course id and actor name.
     * As this is a POST request it returns a new code everytime it is called.
     * Registration  is "code" in returned json body.
     * @param $courseid - course id - The course ID in the CMI5 player.
     * @param $id - the course id in MOODLE.
     */ 
    public function cmi5launch_retrieve_registration_with_post($courseid, $id) {

        global $USER;

        $settings = cmi5launch_settings($id);

        $actor = $USER->username;
        $token = $settings['cmi5launchtenanttoken'];
        $playerurl = $settings['cmi5launchplayerurl'];
        $homepage = $settings['cmi5launchcustomacchp'];
        global $CFG;
      
        // Build URL for launch URL request.
        $url = $playerurl . "/api/v1/registration" ;

        // The body of the request must be made as array first.
        $data = array(
            'courseId' => $courseid,
            'actor' => array(
                'account' => array(
                    "homePage" => $homepage,
                    "name" => $actor
                )
            )
        );
        
        // Data needs to be JSON encoded.
        $data = json_encode($data);
        // To determine the headers. 
        $filetype = "json";

        // Sends the stream to the specified URL 
        $result = $this->cmi5launch_send_request_to_cmi5_player_post($data, $url, $filetype, $token);


        // Catch errors
        if ($result === FALSE) {
            echo "<br>";
            echo "Something went wrong retrieving registration id. CMI5 Player returned " . $result;
            echo "<br>";
        }

        $registrationInfo = json_decode($result, true);
        
        // The returned 'registration info' is a large json object.
        // Code is the registration id we want.
        $registration = $registrationInfo["code"];
        
        return $registration;
    }

    /**
     * Function to retrieve a token from cmi5 player.
     * @param $url - URL to send request to
     * @param $username - username
     * @param $password - password
     * @param $audience - the name the of the audience using the token,
     * @param #tenantid - the id of the tenant
     */
    public function cmi5launch_retrieve_token($url, $username, $password, $audience, $tenantid)
    {

        global $CFG;

        // The body of the request must be made as array first.
        $data = array(
            'tenantId' => $tenantid,
            'audience' => $audience
        );
        $filetype = "json";
       
        // Data needs to be JSON encoded.
        $data = json_encode($data);

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post($data, $url, $filetype, $username, $password);
        
        if ($result === FALSE) {

            echo "<br>";
            echo "Something went wrong retrieving the bearer token. CMI5 Player returned " . $result;
            echo "<br>";

        } else {
            return $result;
        }
    }
    

    /**
     * Function to retrieve a launch URL for an AU.
     * @param $id - courses's ID in MOODLE to retrieve corect record.
     * @param $auindex -AU's index to send to request for launch url.
     */
    public function cmi5launch_retrieve_url($id, $auindex) {

        global $DB, $USER;

        // Retrieve actor record, this enables correct actor info for URL storage.
        $record = $DB->get_record("cmi5launch", array('id' => $id));

		$settings = cmi5launch_settings($id);
     
        $userscourse = $DB->get_record('cmi5launch_course', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

		$registrationid = $userscourse->registrationid;
		
        $homepage = $settings['cmi5launchcustomacchp'];
        $returnurl =$userscourse->returnurl;
        $actor= $USER->username;
		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];
		$courseid = $userscourse->courseid;

        // Build URL for launch URL request.
	    $url = $playerUrl . "/api/v1/course/" . $courseid  ."/launch-url/" . $auindex;

        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => $homepage,
                    "name" => $actor,
                ),
            ),
            'returnUrl' => $returnurl,
            'reg' => $registrationid
        );
        
        // To determine the headers. 
        $filetype = "json";

        // Data needs to be JSON encoded.
        $data = json_encode($data);
        // Sends the stream to the specified URL 
        $result = $this->cmi5launch_send_request_to_cmi5_player_post($data, $url, $filetype, $token);

        // Catch errors
        if ($result === FALSE) {
            echo "<br>";
            echo "Something went wrong retrieving launch url. CMI5 Player returned " . $result;
            echo "<br>";
        }
    
        // Only return the URL.
        $urlDecoded = json_decode($result, true);

        return $urlDecoded;
    }

    /**
     * Function to construct, send an URL, and save result.
     * @param $databody - the data that will be used to construct the body of request as JSON.
     * @param $url - The URL the request will be sent to.
     * @param ...$tokenorpassword is a variable length param. If one is passed, it is $token, if two it is $username and $password.
     * @return - $result is the response from cmi5 player.
     */
        // If I add an arg to take type, that will solve the issue where some are zip or json
    public function cmi5launch_send_request_to_cmi5_player_post($databody, $url, $filetype, ...$tokenorpassword) {
        
        // Determine content type to be used in header.
        // It is also the same as accepted type.
        $contenttype = $filetype;
        if($contenttype == "zip"){
            $contenttype = "application/zip\r\n";
        } elseif ("json") {
            $contenttype = "application/json\r\n";
        }

        //If number of args is greater than one it is for retrieving tenant info and args are username and password
        if(count($tokenorpassword) == 2 ){
            echo " Wait is this the prob? Are we in the right thing?";
            $username = $tokenorpassword[0];
            $password = $tokenorpassword[1];

            // Use key 'http' even if you send the request to https://...
            // There can be multiple headers but as an array under the ONE header
            // content(body) must be JSON encoded here, as that is what CMI5 player accepts
            $options = array(
                'http' => array(
                    'method'  => 'POST',
                    'header' => array('Authorization: Basic '. base64_encode("$username:$password"),  
                        "Content-Type: " .$contenttype .
                        "Accept: " . $contenttype),
                    'content' => ($databody)
                )
            );

            // The options are here placed into a stream to be sent.
            $context  = stream_context_create($options);
        
            // Sends the stream to the specified URL and stores results.
            // The false is use_include_path, which we dont want in this case, we want to go to the url.
            $result = file_get_contents( $url, false, $context );
        }
        
        // Else the args are what we need for posting a course.
        else{
          //  echo " So what about this???";

            // First arg will be token.
            $token = $tokenorpassword[0];

            // Use key 'http' even if you send the request to https://...
            // There can be multiple headers but as an array under the ONE header
            // content(body) must be JSON encoded here, as that is what CMI5 player accepts
            // JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly
            $options = array(
                'http' => array(
                    'method'  => 'POST',
                    'ignore_errors' => true,
                    'header' => array("Authorization: Bearer ". $token,  
                        "Content-Type: " .$contenttype .
                        "Accept: " . $contenttype),
                    'content' => ($databody)
                )
            );

            // The options are placed into a stream to be sent.
            $context  = stream_context_create(($options));

            // Sends the stream to the specified URL and stores results.
            // The false is use_include_path, which we dont want in this case, we want to go to the url.
            $result = file_get_contents( $url, false, $context );
    
        }

        // Return response
        return $result;
}
    /**
     * Function to construct, send an URL, and save result.
     * @param $databody - the data that will be used to construct the body of request as JSON.
     * @param $url - The URL the request will be sent to.
     * @param ...$tokenorpassword is a variable length param. If one is passed, it is $token, if two it is $username and $password.
     * @return - $result is the response from cmi5 player.
     */
        // If I add an arg to take type, that will solve the issue where some are zip or json
    public function cmi5launch_send_request_to_cmi5_player_get($token, $url)
    {
        // use key 'http' even if you send the request to https://...
        // There can be multiple headers but as an array under the ONE header
        // content(body) must be JSON encoded here, as that is what CMI5 player accepts
        // JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly
        $options = array(
            'http' => array(
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => array("Authorization: Bearer ". $token,  
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n")
            )	
        );

        
        // The options are here placed into a stream to be sent
        $context  = stream_context_create(($options));

        // Sends the stream to the specified URL and stores results (the false is use_include_path, which we dont want in this case, we want to go to the url)
        $launchresponse = file_get_contents( $url, false, $context );

		$sessionDecoded = json_decode($launchresponse, true);
            
        return $sessionDecoded;
    }

    /**
    * Retrieve session info from cmi5player
    * @param mixed $sessionid - the session id to retrieve
     * @param mixed $id - cmi5 id
     * @return mixed $sessionDecoded - the session info from cmi5 player.
     */

    public function cmi5launch_retrieve_session_info_from_player($sessionid, $id){
        global $DB;

		$settings = cmi5launch_settings($id);
		
		$token = $settings['cmi5launchtenanttoken'];
		$playerUrl = $settings['cmi5launchplayerurl'];

        // Build URL for launch URL request.
	    $url = $playerUrl . "/api/v1/session/" . $sessionid;

        // Sends the stream to the specified URL 
        $result = $this->cmi5launch_send_request_to_cmi5_player_get($token, $url);
        
        if ($result === FALSE) {
            echo "<br>";
            echo "Something went wrong retrieving session info. CMI5 Player returned " . $result;
            echo "<br>";
        }

        return $result;
    }

}
   
   ?>
