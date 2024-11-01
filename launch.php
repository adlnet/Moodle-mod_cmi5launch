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
 * Launches the experience with the requested registration number.
 * The cmi5 player does the actual playing. This file enables handling of launch url from the player and data saving.
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\local;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\customException;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_once("$CFG->dirroot/lib/outputcomponents.php");

// Include the errorover (error override) funcs.
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

require_login($course, false, $cm);

global $CFG, $cmi5launch, $USER, $DB;

function abandonCourse($session, $au, $actorname) {

    $settings = cmi5launch_settings($session->id);
    $agent = array(
        'account' => array(
            "homePage" => $settings['cmi5launchcustomacchp'],
            "name" => $actorname,
        ),
    );

    $statement = array(
         'actor' => $agent,
        'verb' => array(
            "id" => "https://w3id.org/xapi/adl/verbs/abandoned",
            "display" => array(
                "en-US" => "abandoned"
            )
        ),
        'object' => array(
            'objectType' => 'Activity',
            'id' => $au->lmsid
        ),
        
        "timestamp" => date("c"),
    );

    echo "<h1>Data</h1>";
    echo "<h1>clear state</h1>";
    clearLrsState($session, $au->lmsid, $agent);
    
    var_dump($statement);

    $statement_json = json_encode($statement);


    

    // Assign passed in function to variable.
    $stream = 'cmi5launch_stream_and_send';
       // Make sure LRS settings are there.
       try {
           // Url to request statements from.
           $url = $settings['cmi5launchlrsendpoint'] . "statements";
          

           // LRS username and password.
           $user = $settings['cmi5launchlrslogin'];
           $pass = $settings['cmi5launchlrspass'];
           echo "<h1>user</h1>";
           var_dump($user);
           echo "<h1>pass</h1>";
           var_dump($pass);

       }
       catch (\Throwable $e) {

          // Throw exception if settings are missing.
          Throw new nullException('Unable to retrieve LRS settings. Caught exception: '. $e->getMessage() . " Check LRS settings are correct.");
       }

       // Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
       set_error_handler('mod_cmi5launch\local\progresslrsreq_warning', E_WARNING);
       set_exception_handler('mod_cmi5launch\local\exception_progresslrsreq');

       // Use key 'http' even if you send the request to https://...
       // There can be multiple headers but as an array under the ONE header.
       // Content(body) must be JSON encoded here, as that is what CMI5 player accepts.
       $options = array(
           'http' => array(
               'method' => 'POST',
               'header' => array(
                   'Authorization: Basic ' . base64_encode("$user:$pass"),
                   "Content-Type: application/json\r\n" .
                   "X-Experience-API-Version:1.0.3",
               ),
               'content' => $statement_json
           ),
       );

      try {
          //By calling the function this way, it enables encapsulation of the function and allows for testing.
               //It is an extra step, but necessary for required PHP Unit testing.
               echo "<h1>called</h1>";
               $result = call_user_func($stream, $options, $url);
            

           // Decode result.
           $resultdecoded = json_decode($result, true);
           var_dump($resultdecoded);
           // Restore default hadlers.
           restore_exception_handler();
           restore_error_handler();
                 
           return $resultdecoded;
       
       } catch (\Throwable $e) {
           
           // Restore default hadlers.
           restore_exception_handler();
           restore_error_handler();

           throw new nullException('Unable to communicate with LRS. Caught exception: ' . $e->getMessage() . " Check LRS is up, username and password are correct, and LRS endpoint is correct.", 0);
       }
}

function clearLrsState($session, $activityId, $agent) {
    // Retrieve LRS settings from the session
    $settings = cmi5launch_settings($session->id);

    // Define the LRS endpoint for the state API
    $url = $settings['cmi5launchlrsendpoint'] . "activities/state";
    
    // LRS credentials
    $user = $settings['cmi5launchlrslogin'];
    $pass = $settings['cmi5launchlrspass'];

    // Build query parameters for the DELETE request
    $url .= "?activityId=" . urlencode($activityId) . "&agent=" . urlencode(json_encode($agent));

    // Set up the HTTP headers and options, as in your original style
    $options = array(
        'http' => array(
            'method' => 'DELETE',
            'header' => array(
                'Authorization: Basic ' . base64_encode("$user:$pass"),
                "Content-Type: application/json\r\n" .
                "X-Experience-API-Version:1.0.3",
            ),
        )
    );

    // Prepare the callable function to handle the HTTP request
    $stream = 'cmi5launch_stream_and_send';

    // Set error and exception handlers
    set_error_handler('mod_cmi5launch\local\progresslrsreq_warning', E_WARNING);
    set_exception_handler('mod_cmi5launch\local\exception_progresslrsreq');

    try {
        // Call the function encapsulated for testing
        echo "<h1>Deleting State Data</h1>";
        $result = call_user_func($stream, $options, $url);

        // Decode and check response for debugging
        $resultdecoded = json_decode($result, true);
        var_dump($resultdecoded);

        // Restore handlers
        restore_exception_handler();
        restore_error_handler();

        if ($resultdecoded === null && json_last_error() === JSON_ERROR_NONE) {
            // Successfully deleted (assuming a 204 or empty response)
            return "State data cleared successfully!";
        } else {
            return "Failed to clear state data. Response: " . var_export($resultdecoded, true);
        }
    } catch (\Throwable $e) {
        // Restore handlers on error
        restore_exception_handler();
        restore_error_handler();

        throw new nullException('Unable to communicate with LRS to delete state data. Caught exception: ' . $e->getMessage(), 0);
    }
}
// MB - currently not utilizing events, but may in future.
/*
// Trigger Activity launched event.
$event = \mod_cmi5launch\event\activity_launched::create(array(
    'objectid' => $cmi5launch->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('cmi5launch', $cmi5launch);
$event->trigger();
*/

// External class and funcs to use.
$auhelper = new au_helpers;
$connectors = new cmi5_connectors;
$sessionhelper = new session_helpers;

$savesession = $sessionhelper->cmi5launch_get_create_session();
$cmi5launchretrieveurl = $connectors->cmi5launch_get_retrieve_url();
$retrieveaus = $auhelper->get_cmi5launch_retrieve_aus_from_db();

// Retrieve the registration id from previous page.
$id = required_param('launchform_registration', PARAM_TEXT);

// Reload cmi5 instance.
$record = $DB->get_record('cmi5launch', array('id' => $cmi5launch->id));
// Retrieve user's course record.
$userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid'  => $record->courseid, 'userid'  => $USER->id]);

// To hold launch url.
$location = "";

$restart = filter_var($_GET['restart'], FILTER_VALIDATE_BOOLEAN);

// Most of the functions below have their own error handling. We will encapsulate here in case there are any php errors, such
// as json_decode not working, etc.
// Set error and exception handler to catch and override the default PHP error messages, to make messages more user friendly.
set_error_handler('mod_cmi5launch\local\custom_warning', E_WARNING);
//set_exception_handler('mod_cmi5launch\local\customException');



try {

    // Retrieve AUs.
    $au = $retrieveaus($id);
    // Retrieve the au index.
    $auindex = $au->auindex;

    if ($restart)
    {
        $sessionids = json_decode($au->sessions);
        $sessionId = end($sessionids);
        $session = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionId));
        
        abandonCourse($session, $au, $USER->username);
    }

    // Pass in the au index to retrieve a launchurl and session id.
    $urldecoded = $cmi5launchretrieveurl($cmi5launch->id, $auindex);

    // Retrieve and store session id in the aus table.
    $sessionid = intval($urldecoded['id']);

    // Check if there are previous sessions.
    if (!$au->sessions == null) {
        // We don't want to overwrite so retrieve the sessions before changing them.
        $sessionlist = json_decode($au->sessions);
        // Now add the new session number.
        $sessionlist[] = $sessionid;

    } else {
        // If it is null just start fresh.
        $sessionlist = array();
        $sessionlist[] = $sessionid;
    }

    // Save sessions.
    $au->sessions = json_encode($sessionlist);

    // The record needs to updated in DB.
    $updated = $DB->update_record('cmi5launch_aus', $au, true);

    // Retrieve the launch url.
    $location = $urldecoded['url'];
    // And launch method.
    $launchmethod = $urldecoded['launchMethod'];

} catch (\Throwable $e) {
    // Restore default handlers.
    restore_exception_handler();
    restore_error_handler();

    // If there is an error, return the error.
    throw new customException("Error in launching experience. Report this error to system administrator: ". $e->getMessage());
}
    // Create and save session object to session table.
    $savesession($sessionid, $location, $launchmethod);

    // Last thing check for updates.
    cmi5launch_update_grades($cmi5launch, $USER->id);

    header("Location: " . $location);



// Restore default handlers.
restore_exception_handler();
restore_error_handler();

exit;
