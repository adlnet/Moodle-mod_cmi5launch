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
 * Class to retrieve progress statements from LRS.
 * Holds methods for tracking and displaying student progress.
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */
namespace mod_cmi5launch\local;

/**
 * Class to retrieve progress statements from LRS.
 * Holds methods for tracking and displaying student progress.
 */
class progress {

    /**
     * Returns the function that retrieves statements.
     *
     * @return callable
     */
    public function cmi5launch_get_retrieve_statements() {
        return [$this, 'cmi5launch_retrieve_statements'];
    }

    /**
     * Returns the function that gets completion info.
     *
     * @return callable
     */
    public function cmi5launch_get_request_completion_info() {
        return [$this, 'cmi5launch_request_completion_info'];
    }
    /**
     * Returns the function that retrieves statements from the LRS.
     *
     * @return callable
     */
    public function cmi5launch_get_request_statements_from_lrs() {
        return [$this, 'cmi5launch_request_statements_from_lrs'];
    }

    /**
     * Send request to LRS and receive statements.
     * @param mixed $registrationid - registration id
     * @param mixed $session - a session object
     * @return array
     */
    public function cmi5launch_request_statements_from_lrs($registrationid, $session) {

        // Set error and exception handler to catch and override the default PHP error messages, to make more user friendly.
        set_error_handler('mod_cmi5launch\local\progresslrs_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_progresslrs');

        // Array to hold result.
        $result = [];

        // When searching by reg id, which is the option available to Moodle,
        // many results are returned, so iterating through them is necessary.
        $data = [
            'registration' => $registrationid,
            'since' => $session->createdat,
        ];

        // Try and retrieve statements.
        try {
            $statements = $this->cmi5launch_send_request_to_lrs('cmi5launch_stream_and_send', $data, $session->id);

            // The results come back as nested array under more then statements. We only want statements, and we want them unique.
            $statement = array_chunk($statements["statements"], 1);

            $length = count($statement);

            for ($i = 0; $i < $length; $i++) {

                // This separates the larger statement into the separate sessions and verbs.
                $current = ($statement[$i]);
                array_push($result, [$registrationid => $current]);
            }

            // Restore default handlers.
            restore_exception_handler();
            restore_error_handler();

            return $result;
        } catch (\Throwable $e) {

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            // If there is an error, return the error.
            throw new nullException( get_string('cmi5launchlrsstatementretrievalerror', 'cmi5launch'). $e->getMessage());

        }
    }

    /**
     * Builds and sends requests to LRS
     * @param callable $cmi5launchstreamandsend  Function to stream and send data.
     * @param array $data - Data to send in request.
     * @param mixed $id  - The id of the cmi5launch instance.
     * @return mixed - The response from the LRS.
     * @throws nullException - If there is an error retrieving the LRS settings or sending the request.
     */
    public function cmi5launch_send_request_to_lrs($cmi5launchstreamandsend, $data, $id) {

        $settings = cmi5launch_settings($id);

        // Assign passed in function to variable.
        $stream = $cmi5launchstreamandsend;
        // Make sure LRS settings are there.
        try {
            // Url to request statements from.
            $url = $settings['cmi5launchlrsendpoint'] . "statements";
            // Build query with data above.
            $url = $url . '?' . http_build_query($data, "", '&', PHP_QUERY_RFC1738);

            // LRS username and password.
            $user = $settings['cmi5launchlrslogin'];
            $pass = $settings['cmi5launchlrspass'];
        } catch (\Throwable $e) {

            // Throw exception if settings are missing.
            Throw new nullException(get_string('cmi5launchlrssettingsretrievalerror', 'cmi5launch')
                . $e->getMessage() . get_string('cmi5launchlrssettingscorrect', 'cmi5launch'));
        }

        // Set error and exception handler to catch and override the default PHP error messages, to make more user friendly.
        set_error_handler('mod_cmi5launch\local\progresslrsreq_warning', E_WARNING);
        set_exception_handler('mod_cmi5launch\local\exception_progresslrsreq');

        // Use key 'http' even if you send the request to https://...
        // There can be multiple headers but as an array under the ONE header.
        // Content(body) must be JSON encoded here, as that is what CMI5 player accepts.
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Authorization: Basic ' . base64_encode("$user:$pass"),
                    "Content-Type: application/json\r\n" .
                    "X-Experience-API-Version:1.0.3",
                ],
            ],
        ];

        try {
            // By calling the function this way, it enables encapsulation of the function and allows for testing.
            // It is an extra step, but necessary for required PHP Unit testing.
            $result = call_user_func($stream, $options, $url);

            // Decode result.
            $resultdecoded = json_decode($result, true);

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            return $resultdecoded;

        } catch (\Throwable $e) {

            // Restore default hadlers.
            restore_exception_handler();
            restore_error_handler();

            throw new nullException(get_string('cmi5launchlrscommunicationerror', 'cmi5launch') 
                . $e->getMessage() . get_string('cmi5launchlrschecksettings', 'cmi5launch'), 0);
        }
    }

    /**
     * Returns an actor (name) retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return mixed - The actor name.
     */
    public function cmi5launch_retrieve_actor($resultarray, $registrationid) {

        // If it fails to parse array it should throw an error, this shouldn't stop execution.
        // Catch so we can send a better message to user.
        try {
            // Actor should be in statement.
            $actor = $resultarray[$registrationid][0]["actor"]["account"]["name"];

            return $actor;

        } catch (\Throwable $e) {

            // If there is an error, echo the error.

            echo(get_string('cmi5launchactorretrievalerror', 'cmi5launch'). $e->getMessage());

            return (get_string('cmi5launchactornotretrieved', 'cmi5launch'));
        }
    }

    /**
     * Returns a verb retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return mixed - verb
     */
    public function cmi5launch_retrieve_verb($resultarray, $registrationid) {

        // Encase the whole thing in a try catch block to catch any errors.
        // If an array key isn't there it will throw a warning. It will not stop execution.
        // But catching it will enable us to send better error messages.

        try {
            // Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present.
            $verbinfo = $resultarray[$registrationid][0]["verb"];
            $display = array_key_exists("display", $verbinfo);

            // If it is null then there is no display, so go by verb id.
            if (!$display) {
                // Retrieve id.
                $verbid = $resultarray[$registrationid][0]["verb"]["id"];

                // Splits id in two on 'verbs/', we want the end which is the actual verb.
                $split = explode('verbs/', $verbid);
                $verb = $split[1];

            } else {
                // If it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'.
                $verblang = $resultarray[$registrationid][0]["verb"]["display"];
                // Retrieve the language.
                $lang = array_key_first($verblang);
                // Use it to retrieve verb.
                $verb = [$verblang][0][$lang];
            }

            return $verb;
        } catch (\Throwable $e) {
            // If there is an error, echo the error.
            echo(get_string('cmi5launchverbretrievalerror', 'cmi5launch'). $e->getMessage());
            return (get_string('cmi5launchverbnotretrieved', 'cmi5launch'));
        }
    }

    /**
     * Returns a name (the au title) retrieved from collected LRS data based on registration id
     * Statements are returned in an array, with the registration id as the key.
     * Often they are nested, and sometimes in differnt order, so to avoid errors we need to check for each piece as a key.
     * Then if found, use that key to navigate.
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return mixed - object name
     */
    public function cmi5launch_retrieve_object_name($resultarray, $registrationid) {

        global $CFG;
        // Encase the whole thing in a try catch block to catch any errors.
        // If an array key isn't there it will throw a warning.
        // It will not stop execution but catching it will enable us to send better error messages.
        try {

            // First find the object, it should always be second level of statement (so third level array).
            if (array_key_exists("object", $resultarray[$registrationid][0])) {

                if (array_key_exists("definition", $resultarray[$registrationid][0]["object"])) {

                    // If 'definition' exists, check if 'name' does.
                    if (array_key_exists("name", $resultarray[$registrationid][0]["object"]["definition"])) {

                        // Retrieve the name.
                        $objectarray = $resultarray[$registrationid][0]["object"]["definition"]["name"];

                        // There may be more than one languages string to choose from. First we want to
                        // select the language that matches the language of the course, then if not available, the first key.
                        // System language setting.
                        $language = $CFG->lang;

                        if (array_key_exists($language, $objectarray)) {
                            $object = $objectarray[$language];

                        } else {
                            $defaultlanguage = array_key_first($objectarray);
                            $object = $objectarray[$defaultlanguage];

                        }
                        return $object;
                    }

                } else if (array_key_exists("id", $resultarray[$registrationid][0]["object"])) {

                    // If name is missing check for id.
                    // Retrieve id.
                    $object = $resultarray[$registrationid][0]["object"]["id"];

                    return $object;

                } else {

                    return (get_string('cmi5launchobjectnotpresent', 'cmi5launch'));
                }

            } else {

                return (get_string('cmi5launchobjectnotpresent', 'cmi5launch'));
            }
        } catch (\Throwable $e) {
            // If there is an error, echo the error.
            echo(get_string('cmi5launchobjectretrievalerror', 'cmi5launch') . $e->getMessage());
            return get_string('cmi5launchobjectnotretrieved', 'cmi5launch');
        }
    }

    /**
     * Returns a timestamp retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return string - date/time
     */
    public function cmi5launch_retrieve_timestamp($resultarray, $registrationid) {

        // Encase the whole thing in a try catch block to catch any errors.
        // If an array key isn't there it will throw a warning.
        // It will not stop execution but catching it will enable us to send better error messages.
        try {
            // Verify this statement has a 'timestamp' param.
            if (array_key_exists("timestamp", $resultarray[$registrationid][0])) {

                $timestamp = strtotime($resultarray[$registrationid][0]["timestamp"]);
                $date = userdate($timestamp, '%a %d %b %Y %H:%M:%S');


                return $date;

            } else {

                return get_string('cmi5launchtimestampnotpresent', 'cmi5launch');
            }
        } catch (\Throwable $e) {
            // If there is an error, echo the error.
            echo (get_string('cmi5launchtimestampretrievalerror', 'cmi5launch'). $e->getMessage());
            return get_string('cmi5launchtimestampnotretrieved', 'cmi5launch');
        }
    }

        /**
         * Returns an actor's score retrieved from collected LRS data based on registration id
         * Statements are returned in an array, with the registration id as the key.
         * Often they are nested, and sometimes in differnt order, so to avoid errors we need to check for each piece as a key.
         * Then if found, use that key to navigate.
         * @param mixed $resultarray - data retrieved from LRS, usually an array
         * @param mixed $registrationid - the registration id
         * @return mixed
         */
    public function cmi5launch_retrieve_score($resultarray, $registrationid) {

        global $CFG;

        // Variable to hold score.
        $score = null;

        // Encase the whole thing in a try catch block to catch any errors.
        // If an array key isn't there it will throw a warning.
        // It will not stop execution but catching it will enable us to send better error messages.
        try {
            // Verify this statement has a 'result' param.
            if (array_key_exists("result", $resultarray[$registrationid][0])) {

                // If it exists, retrieve it.
                $resultinfo = $resultarray[$registrationid][0]["result"];

                // If it is null then the item in question doesn't exist in this statement.
                if (array_key_exists("score", $resultinfo)) {

                    $score = $resultarray[$registrationid][0]["result"]["score"];
                    // Raw score preferred to scaled.
                    if (array_key_exists("raw", $score)) {

                        $returnscore = $score["raw"];

                        return $returnscore;

                    } else if (array_key_exists("scaled", $score))  {

                        $returnscore = round($score["scaled"], 2);

                        return $returnscore;
                    }
                } else {

                    return get_string('cmi5launchscorenotpresent', 'cmi5launch');
                }
            } else {

                return get_string('cmi5launchscorenotpresent', 'cmi5launch');
            }
        } catch (\Throwable $e) {

            // If there is an error, echo the error.
            echo(get_string('cmi5launchscoreretrievalerror', 'cmi5launch'). $e->getMessage());

            return get_string('cmi5launchscorenotretrieved', 'cmi5launch');
        }
    }


    /**
     * Retrieves xAPI statements from LRS.
     * @param mixed $registrationid - the registration id.
     * @param mixed $session - session item to be updated.
     * @return array<string>
     */
    public function cmi5launch_retrieve_statements($registrationid, $session) {

        // Array to hold verbs and be returned.
        $progressupdate = [];

        // Array to hold score and be returned.
        $returnscore = 0;

        // Wrap in a try catch block to catch any errors.
        try {
            $resultdecoded = $this->cmi5launch_request_statements_from_lrs($registrationid, $session);

            // We need to sort the statements by finding their session id
            // parse through array 'ext' to find the one holding session id.
            foreach ($resultdecoded as $singlestatement) {

                $code = $session->code;
                $currentsessionid = "";

                // There should always be an extension but in case.
                try {
                    $ext = $singlestatement[$registrationid][0]["context"]["extensions"];

                    foreach ($ext as $key => $value) {

                        // If key contains "sessionid" in string.
                        if (str_contains($key, "sessionid")) {

                            $currentsessionid = $value;
                        }
                    }
                } catch (\Throwable $e) {
                    // If there is an error, echo the error.
                    echo(get_string('cmi5launchsessionidretrievalerror', 'cmi5launch')
                        . $e->getMessage() . ". There may not be an extension key in statement.");
                }

                // Now if code equals currentsessionid, this is a statement pertaining to this session.
                if ($code === $currentsessionid) {

                    $actor = $this->cmi5launch_retrieve_actor($singlestatement, $registrationid);
                    $verb = $this->cmi5launch_retrieve_verb($singlestatement, $registrationid);
                    $object = $this->cmi5launch_retrieve_object_name($singlestatement, $registrationid);
                    $date = $this->cmi5launch_retrieve_timestamp($singlestatement, $registrationid);
                    $score = $this->cmi5launch_retrieve_score($singlestatement, $registrationid);

                    // If a session has more than one score, we only want the highest.
                    if (!$score == null && $score > $returnscore) {

                        $returnscore = $score;
                    }

                    // Update to return.
                    $progressupdate[] = "$actor $verb $object on $date";

                }

            }

            $session->progress = json_encode($progressupdate);

            $session->score = $returnscore;

            return $session;

        } catch (\Throwable $e) {

            // If there is an error, echo the error.
            echo (get_string('cmi5launchstatementsretrievalerror', 'cmi5launch') . $e->getMessage());

            return get_string('cmi5launchstatementsnotretrieved', 'cmi5launch');
        }
    }


}
