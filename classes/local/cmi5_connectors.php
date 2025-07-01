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
 * @package mod_cmi5launch
 */

 namespace mod_cmi5launch\local;
 defined('MOODLE_INTERNAL') || die();
 require_once($CFG->dirroot . '/mod/cmi5launch/constants.php');
// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');
require_once($CFG->dirroot . '/mod/cmi5launch/lib.php');

/**
 * Class for communicating with the CMI5 player through its API's.
 */
class cmi5_connectors {

    /**
     * Returns the class's function that creates a tenant.
     * @return callable - The function that creates a tenant.
     */
    public function cmi5launch_get_create_tenant() {
        return [$this, 'cmi5launch_create_tenant'];
    }
    /**
     * Returns the class's function that retreives a token from the player.
     * @return callable - The function that retrieves the token.
     */
    public function cmi5launch_get_retrieve_token() {
        return [$this, 'cmi5launch_retrieve_token'];
    }
    /**
     * Returns the class's function that retrieves a launch url.
     * @return callable - The function that retrieves the url.
     */
    public function cmi5launch_get_retrieve_url() {
        return [$this, 'cmi5launch_retrieve_url'];
    }
    /**
     * Returns the class's function that creates a course.
     * @return callable - The function that creates the curse.
     */
    public function cmi5launch_get_create_course() {
        return [$this, 'cmi5launch_create_course'];
    }
    /**
     * Returns the class's function that retrieves a session's information.
     * @return callable - The function that retrieves the information.
     */
    public function cmi5launch_get_session_info() {
        return [$this, 'cmi5launch_retrieve_session_info_from_player'];
    }
    /**
     * Returns the class's function that get's a registration code (new) with a POST request..
     * @return callable - The function that retrieves the code.
     */
    public function cmi5launch_get_registration_with_post() {
        return [$this, 'cmi5launch_retrieve_registration_with_post'];
    }
    /**
     * Returns the class's function that retrieves an existing registration code with a GET request..
     * @return callable - The function that retrieves the code.
     */
    public function cmi5launch_get_registration_with_get() {
        return [$this, 'cmi5launch_retrieve_registration_with_get'];
    }
    /**
     * Returns the class's function that sends a request via POST to the player.
     * @return callable - The function that sends the POST.
     */
    public function cmi5launch_get_send_request_to_cmi5_player_post() {
        return [$this, 'cmi5launch_send_request_to_cmi5_player_post'];
    }
    /**
     * Returns the class's function that sends a request via GET to the player.
     * @return callable - The function that sends the GET.
     */
    public function cmi5launch_get_send_request_to_cmi5_player_get() {
        return [$this, 'cmi5launch_send_request_to_cmi5_player_get'];
    }
    /**
     * Returns the class's function that encapsulates a class specific error message.
     * @return callable - The function that encapsulates the class specific error message.
     */
    public function cmi5launch_get_connectors_error_message() {
        return [$this, 'cmi5launch_connectors_error_message'];
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
        $url = $settings['cmi5launchplayerurl'] . CMI5LAUNCH_PLAYER_COURSE_URL;

        // To determine the headers.
        $filetype = "zip";

        $databody = $filename->get_content();

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post('cmi5launch_stream_and_send',
            $databody, $url, $filetype, $tenanttoken);

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            // Check result and display message if not 200.
            $resulttest = $this->cmi5launch_connectors_error_message($result,
                get_string('cmi5launchcourseerror', 'cmi5launch'));

            if ($resulttest == true) {
                // Return an array with course info.
                return $result;

            } else {
                // This should never be false, it should throw an exception if it is, so we can just return the result.
                // But catch all else that might go wrong.
                throw new playerException(get_string('cmi5launchcourseerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){
            throw new playerException(get_string('cmi5launchcourseerror', 'cmi5launch'). $e);
        }
    }

    /**
     * Function to create a tenant.
     * @param string $newtenantname  The name the new tenant will be, retreived from Tenant Name textbox.
     * @return bool|string - Response from cmi5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_create_tenant($newtenantname) {

        global $CFG, $cmi5launchid;

        $settings = cmi5launch_settings($cmi5launchid);

        $username = $settings['cmi5launchbasicname'];
        $playerurl = $settings['cmi5launchplayerurl'];
        $password = $settings['cmi5launchbasepass'];

        // Build URL for launch URL request.
        $url = $playerurl . CMI5LAUNCH_PLAYER_TENANT_URL;

        // The body of the request must be made as array first.
        $data = [
            'code' => $newtenantname];

        // To determine the headers.
        $filetype = "json";

        // Data needs to be JSON encoded.
        $data = json_encode($data);

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post('cmi5launch_stream_and_send',
            $data, $url, $filetype, $username, $password);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result, "creating the tenant");

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {

                return $result;
            } else {

                throw new playerException(get_string('cmi5launchtenanterror', 'cmi5launch'));
            }
        } catch (\Throwable $e){

            throw new playerException(get_string('cmi5launchtenantuncaughterror', 'cmi5launch') . $e);
        }

    }

    /**
     * Function to retrieve registration from cmi5 player.
     * This way uses the registration ID and GET request.
     * Registration  is "code" in returned json body.
     * @param string $registration - Registration UUID.
     * @param int $id - CMI5 launch id.
     * @return bool|string - Response from cmi5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_retrieve_registration_with_get($registration, $id) {

        $settings = cmi5launch_settings($id);

        $token = $settings['cmi5launchtenanttoken'];
        $playerurl = $settings['cmi5launchplayerurl'];

        global $CFG;

        // Build URL for launch URL request.
        $url = $playerurl . "/api/v1/registration/" . $registration;

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_get('cmi5launch_stream_and_send', $token, $url);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result,
            get_string('cmi5launchregistrationerror', 'cmi5launch'));

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {
                return $result;
            } else {

                throw new playerException(get_string('cmi5launchregistrationinfoerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){

            throw new playerException( get_string('cmi5launchregistrationuncaughterror', 'cmi5launch'). $e);
        }

    }

    /**
     * Function to retrieve registration from cmi5 player.
     * This way uses the course id and actor name.
     * As this is a POST request it returns a new code everytime it is called.
     * Registration  is "code" in returned json body.
     * @param int $courseid - The course ID in the CMI5 player.
     * @param int $id - The course id in MOODLE.
     * @return string - The registration code from the CMI5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function
    cmi5launch_retrieve_registration_with_post($courseid, $id) {

        global $USER;

        $settings = cmi5launch_settings($id);

        $actor = $USER->username;
        $token = $settings['cmi5launchtenanttoken'];
        $playerurl = $settings['cmi5launchplayerurl'];
        $homepage = $settings['cmi5launchcustomacchp'];
        global $CFG;

        // Build URL for launch URL request.
        $url = $playerurl . CMI5LAUNCH_PLAYER_REGISTRATION_URL;

        // The body of the request must be made as array first.
        $data = [
            'courseId' => $courseid,
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => $homepage,
                    "name" => $actor,
                ],
            ],
        ];

        // Data needs to be JSON encoded.
        $data = json_encode($data);
        // To determine the headers.
        $filetype = "json";

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post('cmi5launch_stream_and_send',
            $data, $url, $filetype, $token);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result,
            get_string('cmi5launchregistrationerror', 'cmi5launch'));

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {

                $registrationinfo = json_decode($result, true);

                // The returned 'registration info' is a large json object.
                // Code is the registration id we want.
                $registration = $registrationinfo["code"];

                return $registration;
            } else {
                throw new playerException(get_string('cmi5launchregistrationinfoerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){

            throw new playerException(get_string('cmi5launchregistrationuncaughterror', 'cmi5launch'). $e);
        }

    }

    /**
     * Function to retrieve a token from cmi5 player.
     * @param string $audience - The name the of the audience using the token.
     * @param int $tenantid - The id of the tenant.
     * @return string - The token from the CMI5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_retrieve_token($audience, $tenantid) {

        global $CFG, $cmi5launchid;

        $settings = cmi5launch_settings($cmi5launchid);

        // $actor = $USER->username;
        $username = $settings['cmi5launchbasicname'];
        $playerurl = $settings['cmi5launchplayerurl'];
        $password = $settings['cmi5launchbasepass'];

        // Build URL for launch URL request.
        $url = $playerurl . CMI5LAUNCH_PLAYER_AUTH_URL;

        // The body of the request must be made as array first.
        $data = [
            'tenantId' => $tenantid,
            'audience' => $audience,
        ];
        $filetype = "json";

        // Data needs to be JSON encoded.
        $data = json_encode($data);

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post('cmi5launch_stream_and_send',
            $data, $url, $filetype, $username, $password);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result,
            get_string('cmi5launchtokenerror', 'cmi5launch'));

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {

                $resultdecoded = json_decode($result, true);
                $token = $resultdecoded['token'];

                return $token;

            } else {
                throw new playerException(get_string('cmi5launchtokenerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){
            throw new playerException(get_string('cmi5launchtokenuncaughtterror', 'cmi5launch'). $e);
        }
    }

    /**
     * Function to retrieve a launch URL for an AU.
     * @param int $id - Courses's ID in MOODLE to retrieve corect record.
     * @param int $auindex - AU's index to send in the request for launch url.
     * @return array - The launch URL and other info from the CMI5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_retrieve_url($id, $auindex) {

        global $DB, $USER;

        // Retrieve actor record, this enables correct actor info for URL storage.
        $record = $DB->get_record("cmi5launch", ['id' => $id]);

        $settings = cmi5launch_settings($id);

        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

        $registrationid = $userscourse->registrationid;

        $homepage = $settings['cmi5launchcustomacchp'];
        $returnurl = $userscourse->returnurl;
        $actor = $USER->username;
        $token = $settings['cmi5launchtenanttoken'];
        $playerurl = $settings['cmi5launchplayerurl'];
        $courseid = $userscourse->courseid;

        // Build URL for launch URL request.
        $url = $playerurl . CMI5LAUNCH_PLAYER_COURSE_URL . "/" . $courseid  . CMI5LAUNCH_LAUNCH_URL . $auindex;

        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => $homepage,
                    "name" => $actor,
                ],
            ],
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        ];

        // To determine the headers.
        $filetype = "json";

        // Data needs to be JSON encoded.
        $data = json_encode($data);
        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_post('cmi5launch_stream_and_send', $data, $url, $filetype, $token);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result, get_string('cmi5launchurlerror', 'cmi5launch'));

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {
                // Only return the URL.
                $urldecoded = json_decode($result, true);

                return $urldecoded;
            } else {
                throw new playerException(get_string('cmi5launchurlerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){
            throw new playerException(get_string('cmi5launchurluncaughterror', 'cmi5launch') . $e);
        }

    }

    /**
     * Function to construct, send an URL, and save result as POST message to player.
     * @param callable $cmi5launchstreamandsend - tThe function that will be used to send the request.
     * @param array $databody - The data that will be used to construct the body of request as JSON.
     * @param string $url - The URL the request will be sent to.
     * @param string $filetype - The type of file being sent, either zip or json.
     * @param string ...$tokenorpassword is a variable length param. If one is passed, it is $token, if two it is $username and $password.
     * @return mixed $result - Is the response from cmi5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_send_request_to_cmi5_player_post($cmi5launchstreamandsend, $databody, $url, $filetype, ...$tokenorpassword) {

         // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
         set_error_handler('mod_cmi5launch\local\sifting_data_warning', E_WARNING);
         set_exception_handler('mod_cmi5launch\local\exception_au');

        try {
            // I think this whole thing should be try/catch cause there are several things that can go wrong.
            // Assign passed in function to variable.
            $stream = $cmi5launchstreamandsend;
            // Determine content type to be used in header.
            // It is also the same as accepted type.
            $contenttype = $filetype;
            if ($contenttype == "zip") {
                $contenttype = "application/zip\r\n";
            } else if ("json") {
                $contenttype = "application/json\r\n";
            }

            // If number of args is greater than one it is for retrieving tenant info and args are username and password.
            if (count($tokenorpassword) == 2) {

                $username = $tokenorpassword[0];
                $password = $tokenorpassword[1];

                // Use key 'http' even if you send the request to https://...
                // There can be multiple headers but as an array under the ONE header.
                // Content(body) must be JSON encoded here, as that is what CMI5 player accepts.
                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => [
                            'Authorization: Basic ' . base64_encode("$username:$password"),
                            "Content-Type: " . $contenttype .
                            "Accept: " . $contenttype,
                        ],
                        'content' => ($databody),
                    ],
                ];

                // By calling the function this way, it enables encapsulation of the function and allows for testing.
                // It is an extra step, but necessary for required PHP Unit testing.
                $result = call_user_func($stream, $options, $url);

                // Else the args are what we need for posting a course.
            } else {

                // First arg will be token.
                $token = $tokenorpassword[0];

                // Use key 'http' even if you send the request to https://...
                // There can be multiple headers but as an array under the ONE header content(body) must be JSON encoded here.
                // As that is what CMI5 player accepts.
                // JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly.
                $options = [
                    'http' => [
                        'method' => 'POST',
                        'ignore_errors' => true,
                        'header' => [
                            "Authorization: Bearer " . $token,
                            "Content-Type: " . $contenttype .
                            "Accept: " . $contenttype,
                        ],
                        'content' => ($databody),
                    ],
                ];

                // By calling the function this way, it enables encapsulation of the function and allows for testing.
                // It is an extra step, but necessary for required PHP Unit testing.
                $result = call_user_func($stream, $options, $url);
            }

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            // Return response.
            return $result;

        }catch(\Throwable $e) {

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();
            
            throw new playerException( get_string('cmi5launchposterror', 'cmi5launch') .   $e);
        }

    }

    /**
     * Function to construct and send GET request to CMI5 player.
     * @param callable $cmi5launchstreamandsend - The function that will be used to send the request.
     * @param string $token - The token that will be used to authenticate the request.
     * @param string $url - The URL the request will be sent to.
     * @return mixed - $sessionDecoded is the response from cmi5 player.
     * @throws playerException - If the request fails or returns an error.
     */
    public function cmi5launch_send_request_to_cmi5_player_get($cmi5launchstreamandsend, $token, $url) {

        $stream = $cmi5launchstreamandsend;

        // Use key 'http' even if you send the request to https://...
        // There can be multiple headers but as an array under the ONE header content(body) must be JSON encoded here.
        // As that is what CMI5 player accepts.
        // JSON_UNESCAPED_SLASHES used so http addresses are displayed correctly.
        $options = [
            'http' => [
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => ["Authorization: Bearer ". $token,
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"],
            ],
        ];

        try {
            // By calling the function this way, it enables encapsulation of the function and allows for testing.
            // It is an extra step, but necessary for required PHP Unit testing.
            $result = call_user_func($stream, $options, $url);

            // Return response.
            return $result;

        } catch (\Throwable $e) {

            throw new playerException(get_string('cmi5launchgeterror', 'cmi5launch'). $e);
        }
    }

    /**
     * Retrieve session info from cmi5player
     * @param mixed $sessionid - the session id to retrieve
     * @param mixed $id - cmi5 id
     * @return mixed $sessionDecoded - the session info from cmi5 player.
     */
    public function cmi5launch_retrieve_session_info_from_player($sessionid, $id) {

        global $DB;

        $settings = cmi5launch_settings($id);

        $token = $settings['cmi5launchtenanttoken'];
        $playerurl = $settings['cmi5launchplayerurl'];

        // Build URL for launch URL request.
        $url = $playerurl . CMI5LAUNCH_PLAYER_SESSION_URL . $sessionid;

        // Sends the stream to the specified URL.
        $result = $this->cmi5launch_send_request_to_cmi5_player_get('cmi5launch_stream_and_send',
            $token, $url);

        // Check result and display message if not 200.
        $resulttest = $this->cmi5launch_connectors_error_message($result,
            get_string('cmi5launchsessioninfoerror', 'cmi5launch') );

        // Now this will never return false, it will throw an exception if it fails, so we can just return the result.
        try {
            if ($resulttest == true) {

                return $result;

            } else {
                throw new playerException(get_string('cmi5launchsessioninfoerror', 'cmi5launch'));
            }
        } catch (\Throwable $e){

            throw new playerException( get_string("cmi5launchsessioninfouncaughterror", 'cmi5launch') . $e);
        }
    }


    /**
     * An error message catcher.
     * Function to test returns from cmi5 player and display error message if found to be false or not 200.
     * @param mixed $resulttotest - The result to test.
     * @param string $type - The type missing to be added to the error message.
     * @return bool
     */
    public function cmi5launch_connectors_error_message($resulttotest, $type) {

        // Decode result because if it is not 200 then something went wrong.
        // If it's a string, decode it.
        if (is_string($resulttotest)) {
            $resulttest = json_decode($resulttotest, true);
        } else {
            $resulttest = $resulttotest;
        }

        // I think splitting these to return two seperate messages depending on whether player is running is better.
        // Player cannot return an error if not running,
        if ($resulttest === false ){

            $errormessage = $type . get_string('cmi5launchcommerror', 'cmi5launch');

            throw new playerException($errormessage);
        }
        else if( array_key_exists("statusCode", $resulttest) && $resulttest["statusCode"] != 200) {

            $errormessage = $type . get_string('cmi5launchreturned', 'cmi5launch'). $resulttest["statusCode"] . get_string('cmi5launchwith', 'cmi5launch') . " '"
            . $resulttest["message"] . "'.";

            throw new playerException($errormessage);

        } else {
              // No errors, continue.
            return true;
        }
    }


}
