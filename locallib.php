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
 * Internal library of functions for module cmi5launch
 *
 * All the cmi5launch specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/mod/cmi5launch/lib.php");

//Classes for connecting to CMI5 player
require_once("$CFG->dirroot/mod/cmi5launch/cmi5PHP/src/cmi5Connector.php");


/**
 * Send a statement that the activity was launched.
 * This is useful for debugging - if the 'launched' statement is present in the LRS, you know the activity was at least launched.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string/UUID $registrationid The cmi5 Registration UUID associated with the launch.
 * @return cmi5 LRS Response
 */
function cmi5_launched_statement($registrationid) {
    global $cmi5launch, $course, $CFG;
    $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

    $version = $cmi5launchsettings['cmi5launchlrsversion'];
    $url = $cmi5launchsettings['cmi5launchlrsendpoint'];
    $basiclogin = $cmi5launchsettings['cmi5launchlrslogin'];
    $basicpass = $cmi5launchsettings['cmi5launchlrspass'];

    $cmi5phputil = new \cmi5\Util();
    $statementid = $cmi5phputil->getUUID();

    $lrs = new \cmi5\RemoteLRS($url, $version, $basiclogin, $basicpass);

    $parentdefinition = array();
    if (isset($course->summary) && $course->summary !== "") {
        $parentdefinition["description"] = array(
            "en-US" => $course->summary
        );
    }

    if (isset($course->fullname) && $course->fullname !== "") {
        $parentdefinition["name"] = array(
            "en-US" => $course->fullname
        );
    }

    $statement = new \cmi5\Statement(
        array(
            'id' => $statementid,
            'actor' => cmi5launch_getactor($cmi5launch->id),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/launched',
                'display' => array(
                    'en-US' => 'launched'
                )
            ),

            'object' => array(
                'id' => $cmi5launch->cmi5activityid,
                'objectType' => "Activity"
            ),

            "context" => array(
                "registration" => $registrationid,
                "contextActivities" => array(
                    "parent" => array(
                        array(
                            "id" => $CFG->wwwroot.'/course/view.php?id='. $course->id,
                            "objectType" => "Activity",
                            "definition" => $parentdefinition
                        )
                    ),
                    "grouping"  => array(
                        array(
                            "id" => $CFG->wwwroot,
                            "objectType" => "Activity"
                        )
                    ),
                    "category"  => array(
                        array(
                            "id" => "https://moodle.org",
                            "objectType" => "Activity",
                            "definition" => array (
                                "type" => "http://id.cmi5api.com/activitytype/source"
                            )
                        )
                    )
                ),
                "language" => cmi5launch_get_moodle_langauge()
            ),
            "timestamp" => date(DATE_ATOM)
        )
    );

    $response = $lrs->saveStatement($statement);
    return $response;
}

/**
 * Builds a cmi5 launch link for the current module and a given registration
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string/UUID $registrationid The cmi5 Registration UUID associated with the launch.
 * @return string launch link including querystring.
 */
function cmi5launch_get_launch_url($registrationuuid, $auID) {
    global $cmi5launch, $CFG, $DB;
    $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
    $expiry = new DateTime('NOW');
    $xapiduration = $cmi5launchsettings['cmi5launchlrsduration'];
    $expiry->add(new DateInterval('PT'.$xapiduration.'M'));

    $url = trim($cmi5launchsettings['cmi5launchlrsendpoint']);
    
    // Call the function to get the credentials from the LRS.
    $basiclogin = trim($cmi5launchsettings['cmi5launchlrslogin']);
    $basicpass = trim($cmi5launchsettings['cmi5launchlrspass']);

    switch ($cmi5launchsettings['cmi5launchlrsauthentication']) {

        // Learning Locker 1.
        case "0":
            $creds = cmi5launch_get_creds_learninglocker($cmi5launchsettings['cmi5launchlrslogin'],
                $cmi5launchsettings['cmi5launchlrspass'],
                $url,
                $expiry,
                $registrationuuid
            );
            $basicauth = base64_encode($creds["contents"]["key"].":".$creds["contents"]["secret"]);
            break;

        // Watershed.
        case "2":
            $creds = cmi5launch_get_creds_watershed (
                $basiclogin,
                $basicpass,
                $url,
                $xapiduration * 60
            );
            $basicauth = base64_encode($creds["key"].":".$creds["secret"]);
            break;

        default:
            $basicauth = base64_encode($basiclogin.":".$basicpass);
            break;
    }
//to bring in functions from class cmi5Connector
$connectors = new cmi5Connectors;
//Get retrieve URL function
$retrieveUrl = $connectors->getRetrieveUrl();
//See here we are passing the auid. If we have session ids will we pass those instead
//or is that a whole new func, I think it may be
//$rtnstring = $retrieveUrl($cmi5launch->id, $auID); 

//return $rtnstring;
}

/**
 * Used with Learning Locker integration to fetch credentials from the LRS.
 * This process is not part of the xAPI specification or the cmi5 launch spec.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $basiclogin login/key for the LRS
 * @param string $basicpass pass/secret for the LRS
 * @param string $url LRS endpoint URL
 * @return array the response of the LRS (Note: not a cmi5 LRS Response object)
 */
function cmi5launch_get_creds_learninglocker($basiclogin, $basicpass, $url, $expiry, $registrationuuid) {
    global $cmi5launch;
    $actor = cmi5launch_getactor($cmi5launch->id);
    $data = array(
        'scope' => array ('all'),
        'expiry' => $expiry->format(DATE_ATOM),
        'historical' => false,
        'actors' => array(
            "objectType" => 'Person',
            "name" => array($actor->getName())
        ),
        'auth' => $actor,
        'activity' => array(
            $cmi5launch->cmi5activityid,
        ),
        'registration' => $registrationuuid
    );

    if (null !== $actor->getMbox()) {
        $data['actors']['mbox'] = array($actor->getMbox());
    } else if (null !== $actor->getMbox_sha1sum()) {
        $data['actors']['mbox_sha1sum'] = array($actor->getMbox_sha1sum());
    } else if (null !== $actor->getOpenid()) {
        $data['actors']['openid'] = array($actor->getOpenid());
    } else if (null !== $actor->getAccount()) {
        $data['actors']['account'] = array($actor->getAccount());
    }

    $streamopt = array(
        'ssl' => array(
            'verify-peer' => false,
            ),
        'http' => array(
            'method' => 'POST',
            'ignore_errors' => false,
            'header' => array(
                'Authorization: Basic ' . base64_encode(trim($basiclogin) . ':' .trim($basicpass)),
                'Content-Type: application/json',
                'Accept: application/json, */*; q=0.01',
            ),
            'content' => cmi5launch_myjson_encode($data),
        ),
    );

    $streamparams = array();

    $context = stream_context_create($streamopt);

    $stream = fopen(trim($url) . 'Basic/request'.'?'.http_build_query($streamparams, '', '&'), 'rb', false, $context);

    $returncode = explode(' ', $http_response_header[0]);
    $returncode = (int)$returncode[1];

    switch($returncode){
        case 200:
            $ret = stream_get_contents($stream);
            $meta = stream_get_meta_data($stream);

            if ($ret) {
                $ret = json_decode($ret, true);
            }
            break;
        default: // Error!
            $ret = null;
            $meta = $returncode;
            break;
    }

    return array(
        'contents' => $ret,
        'metadata' => $meta
    );
}

/**
 * By default, PHP escapes slashes when encoding into JSON. This cause problems for cmi5,
 * so this function unescapes the slashes after encoding.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param object or array $obj object or array encode to JSON
 * @return string/JSON JSON encoded object or array
 */
function cmi5launch_myjson_encode($obj) {
    return str_replace('\\/', '/', json_encode($obj));
}

/**
 * Save data to the state. Note: registration is not used as this is a general bucket of data against the activity/learner.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $data data to store as document
 * @param string $key id to store the document against
 * @param string $etag etag associated with the document last time it was fetched (may be Null if document is new)
 * @return cmi5 LRS Response
 */
function cmi5launch_get_global_parameters_and_save_state($data, $key, $etag) {
    global $cmi5launch;
    $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
    $lrs = new \cmi5\RemoteLRS(
        $cmi5launchsettings['cmi5launchlrsendpoint'],
        $cmi5launchsettings['cmi5launchlrsversion'],
        $cmi5launchsettings['cmi5launchlrslogin'],
        $cmi5launchsettings['cmi5launchlrspass']
    );

    return $lrs->saveState(
        new \cmi5\Activity(array("id" => trim($cmi5launch->cmi5activityid))),
        cmi5launch_getactor($cmi5launch->id),
        $key,
        cmi5launch_myjson_encode($data),
        array(
            'etag' => $etag,
            'contentType' => 'application/json'
        )
    );
}

/**
 * Save data to the agent profile.
 * Note: registration is not used as this is a general bucket of data against the activity/learner.
 * Note: fetches a new etag before storing. Will ALWAYS overwrite existing contents of the document.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $data data to store as document
 * @param string $key id to store the document against
 * @return cmi5 LRS Response
 */
function cmi5launch_get_global_parameters_and_save_agentprofile($data, $key) {
    global $cmi5launch;
    $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

    $lrs = new \cmi5\RemoteLRS(
        $cmi5launchsettings['cmi5launchlrsendpoint'],
        $cmi5launchsettings['cmi5launchlrsversion'],
        $cmi5launchsettings['cmi5launchlrslogin'],
        $cmi5launchsettings['cmi5launchlrspass']
    );

    $getresponse = $lrs->retrieveAgentProfile(cmi5launch_getactor($cmi5launch->id), $key);

    $opts = array(
        'contentType' => 'application/json'
    );
    if ($getresponse->success) {
        $opts['etag'] = $getresponse->content->getEtag();
    }

    return $lrs->saveAgentProfile(
        cmi5launch_getactor($cmi5launch->id),
        $key,
        cmi5launch_myjson_encode($data),
        $opts
    );
}

/**
 * Get data from the state. Note: registration is not used as this is a general bucket of data against the activity/learner.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $key id to store the document against
 * @return cmi5 LRS Response containing the response code and data or error message
 */
function cmi5launch_get_global_parameters_and_get_state($key) {
    global $cmi5launch;
    $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

    $lrs = new \cmi5\RemoteLRS(
        $cmi5launchsettings['cmi5launchlrsendpoint'],
        $cmi5launchsettings['cmi5launchlrsversion'],
        $cmi5launchsettings['cmi5launchlrslogin'],
        $cmi5launchsettings['cmi5launchlrspass']
    );

 
    return $lrs->retrieveState(
        new \cmi5\Activity(array("id" => trim($cmi5launch->cmi5activityid))),
        cmi5launch_getactor($cmi5launch->id),
        $key
    );
}


/**
 * Get the current lanaguage of the current user and return it as an RFC 5646 language tag
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @return string RFC 5646 language tag
 */

function cmi5launch_get_moodle_langauge() {
    $lang = current_language();
    $langarr = explode('_', $lang);
    if (count($langarr) == 2) {
        return $langarr[0].'-'.strtoupper($langarr[1]);
    } else {
        return $lang;
    }
}


/**
 * Used with Watershed integration to fetch credentials from the LRS.
 * This process is not part of the xAPI specification or the cmi5 launch spec.
 *
 * @package  mod_cmi5launch
 * @category cmi5
 * @param string $login login for Watershed
 * @param string $pass pass for Watershed
 * @param string $endpoint LRS endpoint URL
 * @param int $expiry number of seconds the credentials are required for
 * @return array the response of the LRS (Note: not a cmi5 LRS Response object)
 */
function cmi5launch_get_creds_watershed($login, $pass, $endpoint, $expiry) {
    global $CFG, $DB;

    // Process input parameters.
    $auth = 'Basic '.base64_encode($login.':'.$pass);

    $explodedendpoint = explode ('/', $endpoint);
    $wsserver = $explodedendpoint[0].'//'.$explodedendpoint[2];
    $orgid = $explodedendpoint[5];

    // Create a session.
    $createsessionresponse = cmi5launch_send_api_request(
        $auth,
        "POST",
        $wsserver . "/api/organizations/" . $orgid . "/activity-providers/self/sessions",
        [
            "content" => json_encode([
                "expireSeconds" => $expiry,
                "scope" => "xapi:all"
            ])
        ]
    );

    if ($createsessionresponse["status"] === 200) {
        return [
            "key" => $createsessionresponse["content"]->key,
            "secret" => $createsessionresponse["content"]->secret
        ];
    } else {
        $reason = get_string('apCreationFailed', 'cmi5launch')
        ." Status: ". $createsessionresponse["status"].". Response: ".$createsessionresponse["content"]->message;
        throw new moodle_exception($reason, 'cmi5launch', '');
    }
}

/*
@method sendAPIRequest Sends a request to the API.
@param {String} [$auth] Auth string
@param {String} [$method] Method of the request e.g. POST.
@param {String} [$url] URL to request
@param {Array} [$options] Array of optional properties.
    @param {String} [content] Content of the request (should be JSON).
@return {Array} Details of the response
    @return {String} [metadata] Raw metadata of the response
    @return {String} [content] Raw content of the response
    @return {Integer} [status] HTTP status code of the response e.g. 201
*/
function cmi5launch_send_api_request($auth, $method, $url) {
    $options = func_num_args() === 4 ? func_get_arg(3) : array();

    if (!isset($options['contentType'])) {
        $options['contentType'] = 'application/json';
    }

    $http = array(
        // We don't expect redirects.
        'max_redirects' => 0,
        // This is here for some proxy handling.
        'request_fulluri' => 1,
        // Switching this to false causes non-2xx/3xx status codes to throw exceptions.
        // but we need to handle the "error" status codes ourselves in some cases.
        'ignore_errors' => true,
        'method' => $method,
        'header' => array()
    );

    array_push($http['header'], 'Authorization: ' . $auth);

    if (($method === 'PUT' || $method === 'POST') && isset($options['content'])) {
        $http['content'] = $options['content'];
        array_push($http['header'], 'Content-length: ' . strlen($options['content']));
        array_push($http['header'], 'Content-Type: ' . $options['contentType']);
    }

    $context = stream_context_create(array( 'http' => $http ));

    $fp = fopen($url, 'rb', false, $context);
    
    $content = "";
    
    if (! $fp) {
        return array (
            "metadata" => null,
            "content" => $content,
            "status" => 0
        );
    }
    $metadata = stream_get_meta_data($fp);
    $content  = stream_get_contents($fp);
    $responsecode = (int)explode(' ', $metadata["wrapper_data"][0])[1];

    fclose($fp);

    if ($options['contentType'] == 'application/json') {
        $content = json_decode($content);
    }

    return array (
        "metadata" => $metadata,
        "content" => $content,
        "status" => $responsecode
    );
}

//Grade stuff from SCORM

//Move these to top where they belong if they are what we need
define('GRADE_AUS_CMI5', '0');
define('GRADE_HIGHEST_CMI5', '1');
define('GRADE_AVERAGE_CMI5', '2');
define('GRADE_SUM_CMI5', '3');

define('HIGHEST_ATTEMPT_CMI5', '0');
define('AVERAGE_ATTEMPT_CMI5', '1');
define('FIRST_ATTEMPT_CMI5', '2');
define('LAST_ATTEMPT_CMI5', '3');

define('CMI5_FORCEATTEMPT_NO', 0);
define('CMI5_FORCEATTEMPT_ONCOMPLETE', 1);
define('CMI5_FORCEATTEMPT_ALWAYS', 2);

define('CMI5_UPDATE_NEVER', '0');
define('CMI5_UPDATE_EVERYDAY', '2');
define('CMI5_UPDATE_EVERYTIME', '3');

/**
 * Returns an array of the array of update frequency options
 *
 * @return array an array of update frequency options
 */
function cmi5_get_updatefreq_array() {
    return array(CMI5_UPDATE_NEVER => get_string('never'),
    CMI5_UPDATE_EVERYDAY => get_string('everyday', 'cmi5launch'),
    CMI5_UPDATE_EVERYTIME => get_string('everytime', 'cmi5launch'));
}

/**
 * Returns an array of the array of what grade options
 *
 * @return array an array of what grade options
 */
function cmi5_get_grade_method_array() {
    return array (GRADE_AUS_CMI5 => get_string('GRADE_CMI5_AUS', 'cmi5launch'),
                  GRADE_HIGHEST_CMI5 => get_string('GRADE_HIGHEST_CMI5', 'cmi5launch'),
                  GRADE_AVERAGE_CMI5 => get_string('GRADE_AVERAGE_CMI5', 'cmi5launch'),
                  GRADE_SUM_CMI5 => get_string('GRADE_SUM_CMI5', 'cmi5launch'));
}



/**
 * Returns an array of the array of attempt options
 *
 * @return array an array of attempt options
 */
function cmi5_get_attempts_array() {
    $attempts = array(0 => get_string('nolimit', 'cmi5launch'),
                      1 => get_string('attempt1', 'cmi5launch'));

    for ($i = 2; $i <= 6; $i++) {
        $attempts[$i] = get_string('attemptsx', 'cmi5launch', $i);
    }

    return $attempts;
}

/**
 * Returns an array of the array of what grade options
 *
 * @return array an array of what grade options
 */
function cmi5_get_what_grade_array() {
    return array (HIGHEST_ATTEMPT_CMI5 => get_string('HIGHEST_ATTEMPT_CMI5', 'cmi5launch'),
                  AVERAGE_ATTEMPT_CMI5 => get_string('AVERAGE_ATTEMPT_CMI5', 'cmi5launch'),
                  FIRST_ATTEMPT_CMI5 => get_string('FIRST_ATTEMPT_CMI5', 'cmi5launch'),
                  LAST_ATTEMPT_CMI5 => get_string('last_attempt_cmi5', 'cmi5launch'));
}

/**
 * Returns an array of the force attempt options
 *
 * @return array an array of attempt options
 */
function cmi5_get_forceattempt_array() {
    return array(CMI5_FORCEATTEMPT_NO => get_string('no'),
                 CMI5_FORCEATTEMPT_ONCOMPLETE => get_string('forceattemptoncomplete', 'cmi5launch'),
                 CMI5_FORCEATTEMPT_ALWAYS => get_string('forceattemptalways', 'cmi5launch'));
}

