<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace cmi5Test;

use mod_cmi5launch\local\course;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session;
// use mod_cmi5launch\local\session_helpers;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../classes/local/au_helpers.php');
// require_once(__DIR__ . '/../../classes/local/session_helpers.php');


/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */

 // maybe put fake 'records here and the other one can call it? easier than and neater looking then just ploppin it down

/**
 * Make a fake record for testing.
 * @return mixed
 * @package mod_cmi5launch
 */
function maketestcmi5launch() {
    global $DB, $cmi5launch;
    // Ok for starters, what is cmilaunch

    // Make a cmi5launch record
    $cmi5launch = new \stdClass();
    $cmi5launch->id = 1;
    $cmi5launch->course = 1;
    $cmi5launch->name = 'Test cmi5launch';
    $cmi5launch->intro = 'Test cmi5launch intro';
    $cmi5launch->introformat = 1;
    $cmi5launch->cmi5activityid = 'testcmi5activityid';
    $cmi5launch->registrationid = 'testregistrationid';
    $cmi5launch->returnurl = 'testreturnurl';
    $cmi5launch->courseid = 1;
    $cmi5launch->cmi5verbid = 'testcmi5verbid';
    $cmi5launch->cmi5expiry = 1;
    $cmi5launch->overridedefaults = 1;
    $cmi5launch->cmi5multipleregs = 1;
    $cmi5launch->timecreated = 00;
    $cmi5launch->timemodified = 00;
    $cmi5launch->courseinfo = 'testcourseinfo';
    $cmi5launch->aus = '{testaus:1, testaus:2}';

    $newid = $DB->insert_record('cmi5launch', $cmi5launch);

    return $newid;

}
/**
 * Delete a fake record created for testing.
 * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
 * @return void
 * @package mod_cmi5launch
 */
function deletetestcmi5launch($createdid) {
    global $DB, $cmi5launch;
    // Delete the cmi5launch record
    $DB->delete_records('cmi5launch', ['id' => $createdid]);

}

/**
 * Delete a fake record(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestcmi5launch.
 * @return void
 * @package mod_cmi5launch
 */
function deletetestcmi5launch_usercourse($ids) {
    global $DB, $cmi5launch;

    // if id is int
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    foreach ($ids as $id) {
        // Delete the cmi5launch record
        $DB->delete_records('cmi5launch_usercourse', ['id' => $id]);
    }
}

/**
 * Delete a fake au(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestas.
 * @return void
 * @package mod_cmi5launch
 */
function deletetestcmi5launch_aus($ids) {
    global $DB, $cmi5launch;
    // if id is int
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    foreach ($ids as $id) {
        // Delete the cmi5launch record
        $DB->delete_records('cmi5launch_aus', ['id' => $id]);
    }

}

/**
 * Delete a fake session(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestas.
 * @return void
 * @package mod_cmi5launch
 */
function deletetestcmi5launch_sessions($ids) {
    global $DB, $cmi5launch;

    // if id is int
    if (!is_array($ids)) {
        $ids = [$ids];
    }

    foreach ($ids as $id) {
        // Delete the cmi5launch record
        $DB->delete_records('cmi5launch_sessions', ['sessionid' => $id]);
    }

}

  /**
   * Create a fake course for testing.
   * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
   * @return mixed $newid
   * @package mod_cmi5launch
   */
function maketestcourse ($createdid) {

    global $DB, $cmi5launch, $USER;

    // cmi5 launch is coming in null, looks like we need to make a fake one of that! Wonder if theres a way to make one just, first time testing?
    // Instead of before every test, like a way to prepare the test environment?
    // Reload cmi5 course instance.
    $record = $DB->get_record('cmi5launch', ['id' => $createdid]);

    // Make a new course record.
    $userscourse = new course($record);

    // Populate with data for testing.
    $userscourse->userid = $USER->id;
    $userscourse->returnurl = 'https://testmoodle.com';
    $userscourse->registrationid = 'testregistrationid';
    $userscourse->moodlecourseid = $createdid;

    // Save new record to DB.
    $newid = $DB->insert_record('cmi5launch_usercourse', $userscourse);

    return $newid;

}


  /**
   * Create a fake course for testing.
   * @param mixed $createdid - id that was created for testing by maketestcourse.
   * @return array $newauids - array of AU ids
   * @package mod_cmi5launch
   */
function maketestaus ($testcourseid) {
    global $DB, $cmi5launch, $USER;

    // Make new AUs, lets make five.
    $aus = [];
    for ($i = 0; $i < 5; $i++) {

        $toaddtostring = strval($i);
        // Add i to each value so the AUs are unique.
        $mockvalues = [
        'id' => 'id' . $i,
        'attempt' => $i,
        'url' => 'url' . $i,
        'type' => 'type' . $i,
        'lmsId' => 'lmsid' . $i,
        'grade' => 'grade' . $i,
        'scores' => 'scores' . $i,
        'title' => [0 => ['text' => 'The title text ' . $i]],
        'moveon' => 'moveon' . $i,
        'auindex' => $i,
        'parents' => 'parents' . $i,
        'objectives' => 'objectives' . $i,
        'description' => [0 => ['text' => $i]],
        'activitytype' => 'activitytype' . $i,
        'launchmethod' => 'method' . $i,
        'masteryscore' => $i,
        'satisfied' => $i,
        'launchurl' => 'launchurl' . $i,
        'sessions' => 'sessions' . $i,
        'progress' => 'progress' . $i,
        'noattempt' => $i,
        'completed' => $i,
        'passed' => $i,
        'inprogress' => $i,
        'moveOn' => 'moveOn' . $i,
        'activityType' => 'activityType' . $i,
        'moodlecourseid' => 'moodlecourseid' . $i,
        'userid' => 'userid' . $i,
        ];

        // maybe if we put the sessions here then we can skip the whole resaving thing
        $testcoursesessionids = maketestsessions();
        $mockvalues['sessions'] = $testcoursesessionids;

        $newau = new au($mockvalues);

        $aus[] = $newau;
    }

    // Now save the fake aus to the test database
    $auhelper = new au_helpers();
    $saveau = $auhelper->get_cmi5launch_save_aus();

    // Save AUs to test DB and save IDs.
    $newauids = $saveau($aus);

    // Delete testcoursesessionids.
    deletetestcmi5launch_sessions($testcoursesessionids);
    // Return array of AU ids
    return $newauids;

}

    /**
     * Create fake statement values for testing.
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvalues($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => '2024-' . $i . '0-00T00:00:00.000Z' ,
        'actor' => [
         "firstname" => "firstname" . $i,
         "lastname" => "lastname" . $i,
         "account" => [
          "homePage" => "homePage" . $i,
          "name" => "actorname" . $i,
         ],
                    ],
                    'verb' => [
                        "id" => "verbs/verbid" . $i,
                        "display" => [
                            "en" => ("verbdisplay" . $i),
                        ],
                    ],
                    'object'  => [
                        "id" => "objectid" . $i,
                        "definition" => [
                            "name" => [

                                "en" => 'objectname' . $i],
                            "description" => "description" . $i,
                            "type" => "type" . $i,
                        ],
                    ],
                    'context'  => [
                        "context" => "context" . $i,
                        "contexttype" => "contexttype" . $i,
                        "contextparent" => "contextparent" . $i,
                        "extensions" => [
                            "extensions" => "extensions" . $i,
                            "sessionid" => "code" . $i,
                        ],
                    ],
                    "result" => [
                        "result" => "result" . $i,
                        "score" => [
                            "raw" => 80 + $i,
                            "scaled" => $i,
                        ],
                    ],
                    'stored' => 'stored' . $i,
                    'authority' => [
                        "authority" => "authority" . $i,

                    ],
                    'version' => "version" . $i,
                    'code' => 'code' . $i,
                    'progress' => 'progress' . $i,
                    'score' => 'score' . $i,
        ],
        ],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has no 'display' key in the verb.
     *  It also has a scaled score for testing in testcmi5launch_retrieve_score_scaled_score
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluesnodisplay($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => 'timestamp' . $i,
        'actor' => [
         "firstname" => "firstname" . $i,
         "lastname" => "lastname" . $i,
         "account" => [
          "homePage" => "homePage" . $i,
          "name" => "name" . $i,
         ],
                    ],
                    'verb' => [
                        "id" => "verbs/verbid" . $i,
                    ],
                    'version' => "version" . $i,

                    "result" => [
                        "result" => "result" . $i,
                        "score" => [
                            "scaled" => $i,
                        ],
        ],
        ],
        ]];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has no object key.
     * // It also has no result and is used in testcmi5launch_retrieve_result_no_result
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluesnoobject($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => 'timestamp' . $i, ],
        ],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has no object definition key but does have an id.
     * // This also has no timestamp so it can be used for testing in testcmi5launch_retrieve_timestamp_no_time
     * // And no score in 'result' to be use in testin testcmi5launch_retrieve_score_no_score.
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluesnoobjectdef($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'object'  => [
         "id" => "objectid" . $i],

                    "result" => [],
        ],
        ]];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has no object/def/name key..
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluesnoobjectname($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => 'timestamp' . $i, ],
                    'object'  => [
                        "id" => "objectid" . $i,
                        "definition" => [

                            "description" => "description" . $i,
                            "type" => "type" . $i,
                        ],

        ],
        ],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has an object but nothing in it    .
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluesnoobjectid($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => 'timestamp' . $i,
        'object'  => [],
                    ],
        ],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}


    /**
     * Create fake statement values for testing.
     * This one has no matching lanuage string.
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluenomatchinglang($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => 'timestamp' . $i,
        'object'  => [
         "id" => "objectid" . $i,
         "definition" => [
          "name" => [
                                "fr" => "fr" . $i,
                                "es" => "name" . $i,
          ],
          "description" => "description" . $i,
          "type" => "type" . $i,
         ],

        ],
        ] ],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}

    /**
     * Create fake statement values for testing.
     * This one has a matching lanuage value.
     * @param mixed $amountomake - amount of statements to make.
     * @return array $statements - array of statements
     * @package mod_cmi5launch
     */
function maketeststatementsvaluematchinglang($amounttomake, $registrationid) {
    for($i = 0; $i < $amounttomake; $i++)
    {
        // Mock statements that should be returned.
        $statementvalues[] = [
        $registrationid => [
        // They are nested under 0 since array_chunk is used.
        0 => [
        'timestamp' => '2024-' . $i . '0-00T00:00:00.000Z',
        'object'  => [
         "id" => "objectid" . $i,
         "definition" => [
          "name" => [
                                "en" => "en-us" . $i,
                                "es" => "name" . $i,
          ],
          "description" => "description" . $i,
          "type" => "type" . $i,
         ],

        ],
        ]],
        ];
    }

    // Return the statement values.
    return $statementvalues;
}

/**
 * Create fake statements for testing.
 * @param mixed $amountomake - amount of statements to make.
 * @return array $statements - array of statements
 * @package mod_cmi5launch
 */
function maketeststatements($amounttomake) {
    // Iterate through amount to make, and use i to make different 'registration ids'
    //
    // Array to hold statements.
    $statements = [];
    $statement = [
    "more" => "Other stuff",
    "statements" => []];

    // so the problem is that the statement is coming nested,
    // even when I make one statement it still is under a 0.
    for ($i = 0; $i < $amounttomake; $i++) {
        // Mock values to make statements.
        // Array to hold the statements.

        // Maybe I need to change the stucture so the $i is first and next to statements instead of now as its nested
        $newstatement = [
        'timestamp' => '2024-' . $i . '0-00T00:00:00.000Z' ,
        'actor' => [
        "firstname" => "firstname" . $i,
        "lastname" => "lastname" . $i,
        "account" => [
        "homePage" => "homePage" . $i,
        "name" => "actorname" . $i,
        ],
        ],
        'verb' => [
        "id" => "verbs/verbid" . $i,
        "display" => [
                    "en" => ("verbdisplay" . $i),
        ],
        ],
        'object'  => [
        "id" => "objectid" . $i,
        "definition" => [
                    "name" => [

                        "en" => 'objectname' . $i],
                    "description" => "description" . $i,
                    "type" => "type" . $i,
        ],
        ],
        'context'  => [
        "context" => "context" . $i,
        "contexttype" => "contexttype" . $i,
        "contextparent" => "contextparent" . $i,
        "extensions" => [
                    "extensions" => "extensions" . $i,
                    "sessionid" => "code" . $i,
        ],
        ],
        "result" => [
        "result" => "result" . $i,
        "score" => [
                    "raw" => 80 + $i,
                    "scaled" => $i,
        ],
        ],
        'stored' => 'stored' . $i,
        'authority' => [
        "authority" => "authority" . $i,

        ],
        'version' => "version" . $i,
        'code' => 'code' . $i,
        'progress' => 'progress' . $i,
        'score' => 'score' . $i,
        ];

        $statements[] = $newstatement;
    }

    $statement['statements'] = $statements;
    // Return array of statements
    return $statement;
}

  /**
   * Create fake sessions for testing.
   * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
   * @return array $sessionid
   * @package mod_cmi5launch
   */
function maketestsessions () {
    global $DB, $cmi5launch, $USER;

    // Make new sessions, lets make five.
    $sessions = [];
    $sessionid = [];

    for ($i = 0; $i < 5; $i++) {

        // For some bizarre reason, retrieve sessions from db goes on SESSION id not ID?!?!??
        // THIS IS the problem?
        $mockvalues = [
        'id' => $i,
        // Can't save string to    DB.
        'sessionid' => $i,
        'userid' => 'userid' . $i,
        'moodlecourseid' => 'moodlecourseid' . $i,
        'registrationscoursesausid' => 'registrationscoursesausid' . $i,
        'tenantname' => 'tenantname' . $i,
        'createdat' => 'createdat' . $i,
        'updatedat' => 'updatedat' . $i,
        'code' => 'code' . $i,
        'launchtokenid' => 'launchtokenid' . $i,
        'lastrequesttime' => 'lastrequesttime' . $i,
        'launchmode' => 'launchmode' . $i,
        'masteryscore' => 'masteryscore' . $i,
        'score' => 'score' . $i,
        'islaunched' => 'islaunched' . $i,
        'isinitialized' => 'isinitialized' . $i,
        'duration' => 'duration' . $i,
        'iscompleted' => 'iscompleted' . $i,
        'ispassed' => 'ispassed' . $i,
        'isfailed' => 'isfailed' . $i,
        'isterminated' => 'isterminated' . $i,
        'isabandoned' => 'isabandoned' . $i,
        'progress' => 'progress' . $i,
        'launchmethod' => 'launchmethod' . $i,
        'launchurl' => 'launchurl' . $i,
        ];
        $sessions[] = new session($mockvalues);
        $sessionid[] = ( $i);
    }

    // Retrieve the launch url.
    $launchurl = 'testurl';
    // And launch method.
    $launchmethod = 'testmethod';

    // Now save the fake sessions to the test database
    // Ok so tomorrow make this so there are sessions actually creatd.
    $sessionhelper = new session_helpers();
    // $createsession = $sessionhelper->cmi5launch_create_session();
    // For each session id in the list, create a session.
    foreach ($sessions as $session) {
        // LEts test if it can retrieve here
        $newid = $sessionhelper->cmi5launch_create_session($session->id, $launchurl, $launchmethod);
        $newids[] = $newid;
        // $sessionid[] = $session;

    }
    // Save AUs to test DB and save IDs.
    // $newauids = $createsession($aus);

    // Return array of session ids
    return $sessionid;

}

    /**
     * Helper func that assigns the sessions made to the aus for testing purposes.
     * @param mixed $auids
     * @param mixed $sessionids
     * @return array
     * @package mod_cmi5launch
     */
function assign_sessions_to_aus($auids, $sessionids) {

    // What they are saying is the SESSIONS arent in the table so lets chekc why that may be
    global $DB;
    // HElper function to assign sessions to aus
    $auhelpers = new au_helpers();
    $retrieveaus = $auhelpers->get_cmi5launch_retrieve_aus_from_db();
    $saveaus = $auhelpers->get_cmi5launch_save_aus();

    // Array to holdd newly created AUs
    $newaus = [];
    // First populate the aus with the sessions
    foreach ($auids as $auid){

        $check = $DB->record_exists( 'cmi5launch_aus', ['id' => $auid], '*', IGNORE_MISSING);

        if (!$check) {
            // If check is negative, the record does not exist. Throw error.
            echo "<p>Error attempting to get AU data from DB. Check AU id.</p>";
        }
        // Assiging the sessions to the aus
        $au = $retrieveaus($auid);

        // I bet an argument one string being array is in the au itself

        // Tomorrow: no that isnt it. Something else is throwin the problem.
        // Now the AU will have properties and we want to assign the sessionid array to the 'sessions'  property
        $au->sessions = json_encode($sessionids);

        // Save the AU back to the DB.
        $success = $DB->update_record('cmi5launch_aus', $au);

        $newaus[] = $au;
    }

}

        /**
         * Heeelper func that assigns the aus made to the course(s) for testing purposes.
         * @param mixed $auids
         * @param mixed $sessionids
         * @return void
         * @package mod_cmi5launch
         */
function assign_aus_to_courses($courseids, $auids) {

    global $DB;
    // HElper function to assign sessions to aus
    // Mooonday- there are no course helpers so just call the course, assign the auids
    // and then save ther course with DB calls

    // Retreive the courses
    // Imust have copied below from the func above, it doesn't seem relevant
    $auhelpers = new au_helpers();
    $retrieveaus = $auhelpers->get_cmi5launch_retrieve_aus_from_db();
    $saveaus = $auhelpers->get_cmi5launch_save_aus();

    // Array to holdd newly created AUs
    $newaus = [];

    // if its an int
    if (!is_array($courseids)){
        $courseids = [$courseids];
    }
    // First populate the aus with the sessions
    foreach ($courseids as $courseid){

        // Get the course
        $record = $DB->get_record('cmi5launch_usercourse', ['id' => $courseid]);

        // somewhere between thie au ids above and being encoded below its effin uop
        // Assigning the sessions to the aus
        // Its like its not assigning it.

        // AHA What is auids here?

        $record->aus = json_encode($auids);

        // Save the course back to the db.
        $success = $DB->update_record('cmi5launch_usercourse', $record);
    }

}
   // So now all the test has to do is inject THIS which will return as we please
function cmi5launch_test_stream_and_send_pass_lrs($options, $url) {
    // Make a fake statement to return from the mock.
    $statement = maketeststatements(1);

    // Lets pass in the 'return' value as the option.
    // Encode because it would be a string comiiiiing from LRS.
    return json_encode($statement);
}

     // So now all the test has to do is inject THIS which will return as we please
function cmi5launch_test_stream_and_send_excep_lrs($options, $url) {
    // Make a fake statement to return from the mock.
    $statement = maketeststatements(1);

    // Lets pass in the 'return' value as the option.
    // Encode because it would be a string comiiiiing from LRS.
    return json_encode($statement);
}

   // So now all the test has to do is inject THIS which will return as we please
function cmi5launch_test_stream_and_send_pass($options, $url) {
    $returnvalue = json_encode([
    "statusCode" => 200,
    "Response" => "Successful Post",
    ]);
    // Lets pass in the 'return' value as the option.
    return $returnvalue;
}

   // So now all the test has to do is inject THIS which will return as we please
function cmi5launch_test_stream_and_send_fail($options, $url) {
    // It should be a string! The player returns strings
    // Error message for stubbed method to return.
    $errormessage = json_encode([
    "statusCode" => "404",
    "error" => "Not Found",
    "message" => "testmessage" ]);

    // Lets pass in the 'return' value as the option.
    return $errormessage;
}
      // should I have it throw an error? would that work, or would that take the erorr out of SUT?
     // So now all the test has to do is inject THIS which will return as we please
function cmi5launch_test_stream_and_send_excep($options, $url) {
       throw new \Exception('test error');
}

     // Ok lets make a new strea_helpers to override the other and enable testing
     // New class
class session_helpers {

    public function cmi5launch_get_create_session() {
        return [$this, 'cmi5launch_create_session'];
    }

    public function cmi5launch_get_update_session() {
        return [$this, 'cmi5launch_update_sessions'];
    }

    public function cmi5launch_get_retrieve_sessions_from_db() {
        return [$this, 'cmi5launch_retrieve_sessions_from_db'];
    }

    /**
     * Gets updated session information from CMI5 player
     * @param mixed $sessionid - the session id
     * @param mixed $cmi5id - cmi5 instance id
     * @return
     */
    public function cmi5launch_update_sessions($sessionid, $cmi5id, $user) {

        // Ok, lets make sure we are calling THIS one
        // And not the other one
        // echo "Calling from duplicate class!";
        $returnvalue = new \stdClass();
            $returnvalue->iscompleted = 1;
            $returnvalue->ispassed = 1;
            $returnvalue->isterminated = 1;

        $returnvalue->score = 80;

           // $returnvalue->score = 80;
        return $returnvalue;
    }

    /**
     * Creates a session record in DB.
     * @param mixed $sessionid - the session id
     * @param mixed $launchurl - the launch url
     * @param mixed $launchmethod - the launch method
     * @return void
     */
    public function cmi5launch_create_session($sessionid, $launchurl, $launchmethod) {

        global $DB, $CFG, $cmi5launch, $USER;

        $table = "cmi5launch_sessions";

        // Make a new record to save.
        $newrecord = new \stdClass();
        // Because of many nested properties, needs to be done manually.
        $newrecord->sessionid = $sessionid;
        $newrecord->launchurl = $launchurl;
        $newrecord->tenantname = $USER->username;
        $newrecord->launchmethod = $launchmethod;
        // I think here is where we eed to implement : moodlecourseid
        $newrecord->moodlecourseid = $cmi5launch->id;
        // And userid!
        $newrecord->userid = $USER->id;

        // Save record to table.
        $newid = $DB->insert_record($table, $newrecord, true);

        // Return value
        return $newid;

    }

    /**
     * Retrieves session from DB
     * @param mixed $sessionid - the session id
     * @return session
     */
    public function cmi5launch_retrieve_sessions_from_db($sessionid) {

        global $DB, $CFG;

        $check = $DB->record_exists('cmi5launch_sessions', ['sessionid' => $sessionid], '*', IGNORE_MISSING);

        // If check is negative, the record does not exist. Throw error.
        if (!$check) {

            echo "<p>Error attempting to get session data from DB. Check session id.</p>";
            echo "<pre>";
            var_dump($sessionid);
            echo "</pre>";

        } else {

            $sessionitem = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);

            $session = new session($sessionitem);

        }

        // Return new session object.
        return $session;
    }
}
    // MAke a new  progress class for overriding
    // New class
class progress {

    public function cmi5launch_get_retrieve_statements() {
        return [$this, 'cmi5launch_retrieve_statements'];
    }

    public function cmi5launch_retrieve_statements($registrationid, $session) {

        global $DB, $CFG, $cmi5launch, $USER;
        // We need session objects to test the progress class
        // Make a fake session object.
        $sessionlrs = $session;

        // Change the session to have some new values from LRS.
        $sessionlrs->isterminated = 1;
        $sessionlrs->launchurl = "http://test.com";

        return $sessionlrs;

    }

}

    // MAke a new  cmi5 connectors class for overriding
    // New class
class cmi5_connectors {
    public function cmi5launch_get_session_info() {
        return [$this, 'cmi5launch_retrieve_session_info_from_player'];
    }

    public function cmi5launch_retrieve_session_info_from_player($sessionid, $id) {

        global $DB, $CFG, $cmi5launch, $USER;
        // We need session objects to test the progress class
        // Make a fake session object.
        $sessionids = maketestsessions();

        // We just need one session to test this.
        $sessionid = $sessionids[0];

        // Get the session from DB with session id.
        $sessioncmi5 = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);

        // Change the session from cmi5 to have some new values.
        $sessioncmi5->score = 100;
        $sessioncmi5->iscompleted = 1;
        $sessioncmi5->ispassed = 1;
        $sessioncmi5->launchmethod = "ownWindow";
        // Change the session to have some new values from to match lrs values.
        $sessioncmi5->isterminated = 1;
        $sessioncmi5->launchurl = "http://test.com";

        return json_encode($sessioncmi5);
    }

}

