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
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cmi5launch\local;

defined('MOODLE_INTERNAL') || die();

class progress
{

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
     * Send request to LRS and receive statements
     * @param mixed $regId - registration id
     * @param mixed $session - a session object 
     * @return array
     */
    public function cmi5launch_request_statements_from_lrs($registrationid, $session /*$id*/)
    {

        // Array to hold result.
        $result = array();

        // When searching by reg id, which is the option available to Moodle, many results are returned, so iterating through them is necessary.
        $data = array(
            'registration' => $registrationid,
            'since' => $session->createdat
        );

        $statements = $this->cmi5launch_send_request_to_lrs($data, $session->id);

        // The results come back as nested array under more then statements. We only want statements, and we want them unique.
        $statement = array_chunk($statements["statements"], 1);

        $length = count($statement);

        for ($i = 0; $i < $length; $i++) {

            // This separates the larger statement into the separate sessions and verbs.
            $current = ($statement[$i]);
            array_push($result, array($registrationid => $current));
        }

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

        // Url to request statements from.
        $url = $settings['cmi5launchlrsendpoint'] . "statements";
        // Build query with data above.
        $url = $url . '?' . http_build_query($data, "", '&', PHP_QUERY_RFC1738);

        // LRS username and password.
        $user = $settings['cmi5launchlrslogin'];
        $pass = $settings['cmi5launchlrspass'];

        // Use key 'http' even if you send the request to https://...
        // There can be multiple headers but as an array under the ONE header.
        // Content(body) must be JSON encoded here, as that is what CMI5 player accepts.
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => array(
                    'Authorization: Basic ' . base64_encode("$user:$pass"),
                    "Content-Type: application/json\r\n" .
                    "X-Experience-API-Version:1.0.3",
                )
            )
        );
        // The options are here placed into a stream to be sent.
        $context = stream_context_create($options);

        // Sends the stream to the specified URL and stores results.
        // The false is use_include_path, which we dont want in this case, we want to go to the url.
        $result = file_get_contents($url, false, $context);

        $resultDecoded = json_decode($result, true);


        return $resultDecoded;
    }

    /**
     * Returns an actor (name) retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $i - the registration id
     * @return mixed - actor
     */
    public function cmi5launch_retrieve_actor($resultarray, $registrationid)
    {

        $actor = $resultarray[$registrationid][0]["actor"]["account"]["name"];
        return $actor;
    }

    /**
     * Returns a verb retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return mixed - verb
     */
    public function cmi5launch_retrieve_verbs($resultarray, $registrationid)
    {
        // Some verbs do not have an easy to display 'language' option, we need to check if 'display' is present.	
        $verbInfo = $resultarray[$registrationid][0]["verb"];
        $display = array_key_exists("display", $verbInfo);

        //If it is null then there is no display, so go by verb id.
        if (!$display) {
            // Retrieve id.
            $verbId = $resultarray[$registrationid][0]["verb"]["id"];

            // Splits id in two on 'verbs/', we want the end which is the actual verb
            $split = explode('verbs/', $verbId);
            $verb = $split[1];

        } else {
            // If it is not null then there is a language easy to read version of verb display, such as 'en' or 'en-us'.
            $verbLang = $resultarray[$registrationid][0]["verb"]["display"];
            // Retrieve the language.
            $lang = array_key_first($verbLang);
            // Use it to retrieve verb.
            $verb = [$verbLang][0][$lang];
        }
        return $verb;
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
    public function cmi5launch_retrieve_object_name($resultarray, $registrationid)
    {
        global $CFG;

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

                // If both name and id are missing throw error.
                $this->cmi5launch_statement_retrieval_error("Object name and id ");
            }

        } else {

            $this->cmi5launch_statement_retrieval_error("Object ");
        }
    }

    /**
     *  Error message for statment retrieval to mark if something is missing
     * @param mixed $missingitem - the missing item(s)
     * @return void
     */
    public function cmi5launch_statement_retrieval_error($missingitem) {

                    global $CFG;

                    // If admin debugging is enabled.
                    if ($CFG->debugdeveloper) {

                        // Print that it is missing.
                        echo "<br>";
                        echo $missingitem . "is missing from this statement.";
                        echo "<br>";
                    }
    }

    /**
     * Returns a timestamp retrieved from collected LRS data based on registration id
     * @param mixed $resultarray - data retrieved from LRS, usually an array
     * @param mixed $registrationid - the registration id
     * @return string - date/time
     */
    public function cmi5launch_retrieve_timestamp($resultarray, $registrationid)
    {
        //Verify this statement has a 'timestamp' param
        if (array_key_exists("timestamp", $resultarray[$registrationid][0])) {

            $date = new \DateTime($resultarray[$registrationid][0]["timestamp"], new \DateTimeZone('US/Eastern'));

            $date->setTimezone(new \DateTimeZone('America/New_York'));

            $date = $date->format('d-m-Y' . " " . 'h:i a');

            return $date;

        } else {

            $this->cmi5launch_statement_retrieval_error("Timestamp ");
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
        public function cmi5launch_retrieve_score($resultarray, $registrationid)
        {
            global $CFG;

            // Variable to hold score.
            $score = null;

            // Verify this statement has a 'result' param.
            if (array_key_exists("result", $resultarray[$registrationid][0])) {
                
                //If it exists, retrieve it.
                $resultInfo = $resultarray[$registrationid][0]["result"];

                $score = array_key_exists("score", $resultInfo);

                //If it is null then the item in question doesn't exist in this statement
                if ($score) {

                    $score = $resultarray[$registrationid][0]["result"]["score"];

                    // Raw score preferred to scaled.
                    if ($score["raw"]) {

                        $returnscore = $score["raw"];
                        return $returnscore;
                    } else if ($score["scaled"]) {

                        $returnscore = round($score["scaled"], 2);
                        return $returnscore;
                    }
                }
            } else {

                // If admin debugging is enabled.
                if ($CFG->debugdeveloper) {

                    // Print that it is missing.
                    echo "<br>";
                    echo 'No score in statement with id ' .$resultarray[$registrationid][0]['id'];
                    echo "<br>";
                }
            }
        }


        /**
         * Retrieves xAPI statements from LRS
         * @param mixed $registrationid - the registration id.
         * @param mixed $session - session item to be updated.
         * @return array<string>
         */
        public function cmi5launch_retrieve_statements($registrationid, $session)
        {
            // Array to hold verbs and be returned.
            $progressupdate = array();

            // Array to hold score and be returned.
            $returnscore = 0;

            $resultDecoded = $this->cmi5launch_request_statements_from_lrs($registrationid, $session);
            
            // We need to sort the statements by finding their session id
            // parse through array 'ext' to find the one holding session id.
            foreach ($resultDecoded as $singlestatement) {

                $code = $session->code;
                $currentsessionid = "";
                $ext = $singlestatement[$registrationid][0]["context"]["extensions"];
                foreach ($ext as $key => $value) {

                    // If key contains "sessionid" in string.
                    if (str_contains($key, "sessionid")) {
                        $currentsessionid = $value;
                    }
                }

                // Now if code equals currentsessionid, this is a statement pertaining to this session.
                if ($code === $currentsessionid) {

                    $actor = $this->cmi5launch_retrieve_actor($singlestatement, $registrationid);
                    $verb = $this->cmi5launch_retrieve_verbs($singlestatement, $registrationid);
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
        }   
    }