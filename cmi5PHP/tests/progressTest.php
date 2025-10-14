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

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\nullException;
use mod_cmi5launch\local\session;

require_once( "cmi5TestHelpers.php");

/**
 * Tests for progress class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \mod_cmi5launch\local\progress
 * @covers \mod_cmi5launch\local\progress::cmi5launch_request_statements_from_lrs
 * @covers \mod_cmi5launch\local\progress::cmi5launch_send_request_to_lrs
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_actor
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_verbs
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_object_name
 * @covers \mod_cmi5launch\local\progress::cmi5launch_statement_retrieval_error
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_timestamp
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_score
 * @covers \mod_cmi5launch\local\progress::cmi5launch_retrieve_statements
 *
 *  * @package mod_cmi5launch
 * @package mod_cmi5launch
 */
class progressTest extends TestCase {

    // Use setupbefore and after class sparingly. In this case, we don't want to use it to connect tests, but rather to
    // 'prep' the test db with values the tests can run against.
    public static function setUpBeforeClass(): void {
        global $DB, $cmi5launch, $cmi5launchid;

        // Mke a fake cmi5 launch record.
        $cmi5launchid = maketestcmi5launch();

    }

    public static function tearDownAfterClass(): void {
        global $DB, $cmi5launch,  $cmi5launchid;

        // Delete the test record.
        deletetestcmi5launch($cmi5launchid);

    }

    protected function setUp(): void {
        global $sessionids, $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = [
            "cmi5launchlrsendpoint" => "Test LRS point",
            "cmi5launchlrslogin" => "Test LRS login",
            "cmi5launchlrspass" => "Test LRS password",
            "cmi5launchtenanttoken" => "Testtoken",
            "cmi5launchplayerurl" => "http://test/launch.php",
            "cmi5launchcustomacchp" => "http://testhomepage.com",
            "grademethod" => 1,
        ];

        // Override global variable and function so that it returns test data.
        $USER = new \stdClass();
        $USER->username = "testname";
        $USER->id = 10;

        $testcourseid = maketestcourse($cmi5launchid);

        // We need session objects to test the progress class
        // Make a fake session object.
        $sessionids = maketestsessions();
    }

    protected function tearDown(): void {
        global $sessionids, $testcourseid, $cmi5launchsettings;
        // Restore overridden global variable.
        unset($USER);
        unset($cmi5launchsettings);
        deletetestcmi5launch_usercourse($testcourseid);
        deletetestcmi5launch_sessions($sessionids);
    }

    /**
     * Test of the cmi5launch_request_statements_from_lrs with a pass condition.
     * @return void
     */
    public function testcmi5launch_request_statements_from_lrs_pass() {
        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Amount of statements to make for testing.
        // The same amount of statements to make for testing will match the amount
        // of statement values returned from mock.
        $amount = 5;

        // Make fake statements for testing.
        $statements = maketeststatements($amount);

        // Test registrationid to pass.
        $registrationid = "registrationid";

        // Statement values that should be returned from mock.
        $statementvalues = maketeststatementsvalues($amount, $registrationid);

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid], '*', MUST_EXIST);

        // Mock data as it will be passed to stub.
        $data = [
            'registration' => $registrationid,
            'since' => $session->createdat,
        ];

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(['cmi5launch_send_request_to_lrs' ])
            ->getMock();

        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_lrs')
            ->with($testfunction, $data, $session->id)
            ->willReturn($statements);

        // Call the method under test.
        $result = $csc->cmi5launch_request_statements_from_lrs($registrationid, $session);

        // We can check the count matches. We want as many statements back as we made.
        $this->assertCount($amount, $result, "Expected result to have $amount statements");

        // Check the result is as expected.
        $this->assertEquals($statementvalues, $result, "Expected result to match statementvalues ");
        $this->assertIsArray($result, "Expected retrieved object to be array" );
    }

    /**
     * Test of the cmi5launch_request_statements_from_lrs with a fail condition.
     * @return void
     */
    public function testcmi5launch_request_statements_from_lrs_excep() {

        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Test registrationid to pass.
        $registrationid = "registrationid";

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid, 'moodlecourseid' => $cmi5launch->id], '*', MUST_EXIST);

        // Mock data as it will be passed to stub.
        $data = [
            'registration' => $registrationid,
            'since' => $session->createdat,
        ];

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(['cmi5launch_send_request_to_lrs' ])
            ->getMock();

            // If this returns null it should trrigger an error in the function.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_lrs')
            ->with($testfunction, $data, $session->id)
            ->willReturn(null);

        // The expected is built by the two messages knowing 'title' is an empty array.
        $expected = "Error retrieving statements from LRS. Caught exception: ";

        // Catch the exception.
        $this->expectException(nullException::class);
        $this->expectExceptionMessage($expected);

        // Call the method under test.
        $result = $csc->cmi5launch_request_statements_from_lrs($registrationid, $session);

    }
        /**
         * Test of the cmi5launch_send_request_to_lrs with a pass condition.
         * @return void
         */
    public function testcmi5launch_send_request_to_lrs() {

        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Mock data as it will be passed to stub.
        $data = [
        'registration' => "registrationid",
        'since' => "Test time",
        ];

        // Retrieve settings like they will be in SUT.
        $settings = cmi5launch_settings($cmi5launch->id);
        // Url to request statements from.
        $url = $settings['cmi5launchlrsendpoint'] . "statements";
        // Build query with data above.
        $url = $url . '?' . http_build_query($data, "", '&', PHP_QUERY_RFC1738);

        // LRS username and password.
        $user = $settings['cmi5launchlrslogin'];
        $pass = $settings['cmi5launchlrspass'];

        // Test registrationid to pass.
        $registrationid = "registrationid";

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid], '*', MUST_EXIST);

        // Mock data as it will be passed to stub.
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

         // Function that will be called in function under test.
         $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass_lrs';

           // Make a fake statement to return from the mock.
        $statement = maketeststatements(1);

        // The result we expect back is the statment, decoded. So it's original form.
        $expected = $statement;

        // New proggress
        $progress = new \mod_cmi5launch\local\progress();
        // Call the method under test.
        $result = $progress->cmi5launch_send_request_to_lrs($testfunction, $data, $session->id);

        // Check the result is as expected.
        $this->assertEquals($expected, $result, "Expected result to match statementvalues ");
        // The return should also be an array, since it is decoded with the 'tue' flag.
        $this->assertIsArray($result, "Expected retrieved object to be array" );
    }

    /**
     * Test of the cmi5launch_send_request_to_lrs with a fail condition.
     * Throws an exception at the first try/catch where it is retrievin settings.
     * @return void
     */
    public function testcmi5launch_send_request_to_lrs_fail_settings() {

        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Make data that is not array to throw error in settings try/catch
        $data = ("Test string to throw error");

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid], '*', MUST_EXIST);

         // Function that will be called in function under test.
         $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass_lrs';

         // New proggress
         $progress = new \mod_cmi5launch\local\progress();
           // The expected is built bby the two messages knowing 'title' is an empty array.
           $expected = "Unable to retrieve LRS settings. Caught exception: ";

           $this->expectExceptionMessage($expected);
            // Catch the exception.
            $this->expectException(nullException::class);

        // Call the method under test.
        $result = $progress->cmi5launch_send_request_to_lrs($testfunction, $data, $session->id);

    }

              /**
               * Test of the cmi5launch_send_request_to_lrs with a fail condition.
               * Throws an exception at the second try/catch, where it tries to communicte with LRS.
               * @return void
               */
    public function testcmi5launch_send_request_to_lrs_fail_excep() {

        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Mock data as it will be passed to stub.
        $data = [
        'registration' => "registrationid",
        'since' => "Test time",
        ];

        // Retrieve settings like they will be in SUT.
        $settings = cmi5launch_settings($cmi5launch->id);
        // Url to request statements from.
        $url = $settings['cmi5launchlrsendpoint'] . "statements";
        // Build query with data above.
        $url = $url . '?' . http_build_query($data, "", '&', PHP_QUERY_RFC1738);

        // LRS username and password.
        $user = $settings['cmi5launchlrslogin'];
        $pass = $settings['cmi5launchlrspass'];

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', ['sessionid' => $sessionid], '*', MUST_EXIST);

        // Mock data as it will be passed to stub.
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

         // Function that will be called in function under test.
         $testfunction = 'cmi5Test\cmi5launch_stream_and_send_excep_lrs';

           // The expected is built bby the two messages knowing 'title' is an empty array.
           $expected = 'Unable to communicate with LRS. Caught exception: ';

           // Catch the exception.
           $this->expectException(nullException::class);
           $this->expectExceptionMessage($expected);

           // New proggress
           $progress = new \mod_cmi5launch\local\progress();
           // Call the method under test.
           $result = $progress->cmi5launch_send_request_to_lrs($testfunction, $data, $session->id);

    }
    /**
     * Test of the cmi5launch_retrieve_actor with a pass condition.
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_actor_pass() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "actorname0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_actor($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }
      /**
       * Test of the cmi5launch_retrieve_actor with a fail condition.
       * Fail at retrieving the actors name.
       * @return void
       */
    public function testcmi5launch_retrieve_actor_fail() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "(Actor name not retrieved)";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        // Pass a string that is not in the statement to cause an error.
        $result = $progress->cmi5launch_retrieve_actor($statement[0], "purple");

        $expectedoutput = 'Unable to retrieve actor name from LRS. Caught exception: Undefined array key "purple"';
        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");
        $this->expectOutputString($expectedoutput);
    }
     /**
      * Test of the cmi5launch_retrieve_verb with a pass condition.
      * (This one tests if verb has display option).
      * Successfully retrieves actors name.
      * @return void
      */
    public function testcmi5launch_retrieve_verb_pass() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "verbdisplay0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_verb($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }
    /**
     * Test of the cmi5launch_retrieve_verb with a pass condition.
     * (This one tests if verb doesn't have a display option).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_verb_pass_no_display() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnodisplay(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "verbid0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_verb($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

          /**
           * Test of the cmi5launch_retrieve_actor with a fail condition.
           * Fail at retrieving the verb .
           * @return void
           */
    public function testcmi5launch_retrieve_verb_fail() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "(Verb not retrieved)";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        // Pass a string that is not in the statement to cause an error.
        $result = $progress->cmi5launch_retrieve_verb($statement[0], "purple");

        $expectedoutput = 'Unable to retrieve verb from LRS. Caught exception: Undefined array key "purple"';

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");
        $this->expectOutputString($expectedoutput);

    }

    /**
     * Test of the cmi5launch_retrieve_object with a pass condition.
     * (This one tests if object isn't there).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_object_pass_no_object_key() {
        // Branch one

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobject(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "(Object name not retrieved/there is no object in this statement)";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

    /**
     * Test of the cmi5launch_retrieve_object with a pass condition.
     * (This one tests if object doesn't have a definition key and no id ).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_object_pass_no_object_def_key() {
        // Branch 2
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobjectid(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "(Object name not retrieved/there is no object in this statement)";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }
    /**
     * Test of the cmi5launch_retrieve_object with a pass condition.
     * (This one tests if object doesnt have a definition key,
     * but does have an object id key ).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_object_pass_object_id_exists() {
        // Branch 3
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobjectdef(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "objectid0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

    /**
     * Test of the cmi5launch_retrieve_object with a pass condition.
     * (This one tests if object has everything and  languae is specified matches the cfg language (should be 'en')).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_object_pass_matching_lang() {
        // Branch 5
        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluematchinglang(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "en-us0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

    /**
     * Test of the cmi5launch_retrieve_object with a pass condition.
     * (This one tests if object has everything and languae doesnt match the cfg language).
     * Successfully retrieves actors name.
     * @return void
     */
    public function testcmi5launch_retrieve_object_pass_no_matching_lang() {

        // Branch 4.

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluenomatchinglang(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "fr0";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }
        /**
         * Test of the cmi5launch_retrieve_object with a pass condition.
         * (This one tests if the exceptions are caught and thrown correctly.
         * Successfully retrieves actors name.
         * @return void
         */
    public function testcmi5launch_retrieve_object_excep() {
        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = "String to throw error";

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $expected = "(Object name not retrieved)";
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_object_name($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

        // There will also be an output message.
        $expectedoutput = 'Unable to retrieve object name from LRS. Caught exception: Cannot access offset of type string on string';
        $this->expectOutputString($expectedoutput);
    }

     /**
      * Test of the cmi5launch_retrieve_timestamp with a pass condition.
      * Successfully retrieves actors name.
      * @return void
      */
    public function testcmi5launch_retrieve_timestamp() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // The actor name will be 'name1' Because 1 was passed but index starts at '0' in value making function.
        $faketime = "2024-00-00T00:00:00.000Z";

        // Turn expected into a date so it matches what's leaving the function.
        $expected = userdate(strtotime($faketime), '%a %d %b %Y %H:%M:%S');


        // We need tochange the timezone so it matches the non hardcoded file.

    
        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_timestamp($statement[0], $registrationid);
        // echo" result . " . $result;
        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

     /**
      * Test of the cmi5launch_retrieve_timestamp with a pass condition.
      * Tests else branch if timestamp is not present.
      * Successfully retrieves actors name.
      * @return void
      */
    public function testcmi5launch_retrieve_timestamp_no_time() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobjectdef(1, $registrationid);

        $expected = "(Timestamp not retrieved or not present in statement)";

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_timestamp($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }


     /**
      * Test of the cmi5launch_retrieve_timestamp with a fail condition.
      * Tests error try/catch.
      * Successfully retrieves actors name.
      * @return void
      */
    public function testcmi5launch_retrieve_timestamp_excep() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = "String to throw error";

        $expected = "(Timestamp not retrieved)";

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_timestamp($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a string");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

        // There will also be an output message.
        $expectedoutput = 'Unable to retrieve timestamp from LRS. Caught exception: Cannot access offset of type string on string';
        $this->expectOutputString($expectedoutput);
    }


     /**
      * Test of the cmi5launch_retrieve_score with a pass condition.
      * Successfully retrieves a score
      *  // This one retrieves raw score
      * .
      * @return void
      */
    public function testcmi5launch_retrieve_score_raw_score() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvalues(1, $registrationid);

        // Make the statement have a score.
        $statement[0][$registrationid][0]['result']['score']['raw'] = 10;
        $expected = 10;

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_score($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsInt($result, "Expected result to be a integer");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

         /**
          * Test of the cmi5launch_retrieve_score with a pass condition.
          * Successfully retrieves a scaled score
          * .
          * @return void
          */
    public function testcmi5launch_retrieve_score_scaled_score() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnodisplay(1, $registrationid);

        // Make the statement have a score.
        // Give it a float to test rounding.
        $statement[0][$registrationid][0]['result']['score']['scaled'] = 10.504;

        $expected = 10.5;

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_score($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsFloat($result, "Expected result to be a integer");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

    /**
     * Test of the cmi5launch_retrieve_score with a pass condition.
     * Successfully returns a message there is no score in the statement.
     * .
     * @return void
     */
    public function testcmi5launch_retrieve_score_no_score() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobjectdef(1, $registrationid);

        $expected = "(Score not retrieved or not present in statement)";

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_score($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a integer");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }


    /**
     * Test of the cmi5launch_retrieve_score with a pass condition.
     * Successfully returns a message there is no result in the statement.
     * .
     * @return void
     */
    public function testcmi5launch_retrieve_score_no_result() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = maketeststatementsvaluesnoobject(1, $registrationid);

        $expected = "(Score not retrieved or not present in statement)";

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_score($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a integer");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

    }

        /**
         * Test of the cmi5launch_retrieve_score with a fail condition.
         * Successfully returns an exception.
         * .
         * @return void
         */
    public function testcmi5launch_retrieve_score_excep() {

        global $CFG;

        // Fake registration id.
        $registrationid = "registrationid";
        // Make a test statement to draw name from.
        $statement = "String to throw error";

        $expected = "(Score not retrieved)";

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Because we have a new function to make these values in a test environment, the
        // statement comes wrapped in an array, so access first level of array.
        $result = $progress->cmi5launch_retrieve_score($statement[0], $registrationid);

        // The result should be a string.
        $this->assertIsString($result, "Expected result to be a integer");
        // and should equal expected value.
        $this->assertEquals($expected, $result, "Expected result to match expected value");

        // There will also be an output message.
        $expectedoutput = 'Unable to retrieve score from LRS. Caught exception: Cannot access offset of type string on string';
        $this->expectOutputString($expectedoutput);
    }

           /**
            * Test of the cmi5launch_retrieve_statements with a pass condition.
            * Successfully returns a session object.
            * .
            * @return void
            */
    public function testcmi5launch_retrieve_statements() {

            global $CFG, $DB, $sessionids;

            // Retrieve a sessionid, we'll just use the first one.
            $sessionid = $sessionids[1];

            // Retrieve a session from the DB as an object.
            $session = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);
            //
            // add a code to session
            $session->code = "code0";
            // add a score to be graded
            $session->score = 80;
            // add a progress to be graded

            // Fake registration id.
            $registrationid = "registrationid";
            // Make test statements to pass through from the mock function;
            $statement = (maketeststatementsvalues(1, $registrationid));

              // Make an array to add information to the session.
              $newprogress = json_encode(["actorname0 verbdisplay0 objectname0 on 29-11-2023 07:00 pm"]);
              $newscore = (80);

            // Copy the session so that we can tweak it and make how it should be then
            // Compare with sessin object returned from the function.
            $newsession = $session;
            // Now update the session to match the expected session.
            // Add the progress and score to the session.
            $newsession->progress = $newprogress;
            $newsession->score = $newscore;

        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(['cmi5launch_request_statements_from_lrs'])
            ->getMock();

        // Mock returns statements, as it would be from the LRS.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_request_statements_from_lrs')
            ->with($registrationid, $session)
            ->willReturn($statement);

            // Progress class and SUT.
            $progress = new \mod_cmi5launch\local\progress();
            // Call the method under test.
            $result = $mockedclass->cmi5launch_retrieve_statements($registrationid, $session);

            // The result should be a session.
            $this->assertIsObject($result, "Expected result to be a session");
            // and should equal expected value.
            $this->assertEquals($newsession, $result, "Expected result to match expected value");

    }

    /**
     * Test of the cmi5launch_retrieve_statements with a condition.
     * Successfully catches an exception thrown when trying to retrieve extension info.
     * .
     * @return void
     */
    public function testcmi5launch_retrieve_statements_excep_ext() {

        global $CFG, $DB, $sessionids;

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[1];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);
        //
        // add a code to session
        $session->code = "code0";
        // add a score to be graded
        $session->score = 80;
        // add a progress to be graded

        // Fake registration id.
        $registrationid = "registrationid";
        // Make test statements to pass through from the mock function;
        $statement = maketeststatementsvaluesnoobject(1, $registrationid);

          // Make an array to add information to the session.
          $newprogress = json_encode(["actorname0 verbdisplay0 objectname0 on 29-11-2023 07:00 pm"]);
          $newscore = (80);

        // Copy the session so that we can tweak it and make how it should be then
        // Compare with sessin object returned from the function.
        $newsession = $session;
        // Now update the session to match the expected session.
        // Add the progress and score to the session.
        $newsession->progress = $newprogress;
        $newsession->score = $newscore;

        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(['cmi5launch_request_statements_from_lrs'])
            ->getMock();

        // Mock returns statements, as it would be from the LRS.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_request_statements_from_lrs')
            ->with($registrationid, $session)
            ->willReturn($statement);

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_statements($registrationid, $session);

        // There will also be an output message.
        $expectedoutput = 'Unable to retrieve session id from LRS. Caught exception: Undefined array key "context". There may not be an extension key in statement.';
        $this->expectOutputString($expectedoutput);

    }

       /**
        * Test of the cmi5launch_retrieve_statements with a condition.
        * Successfully catches an exception thrown.
        * .
        * @return void
        */
    public function testcmi5launch_retrieve_statements_excep() {

        global $CFG, $DB, $sessionids;

        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[1];

        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions',  ['sessionid' => $sessionid]);
        //
        // add a code to session
        $session->code = "code0";
        // add a score to be graded
        $session->score = 80;
        // add a progress to be graded

        // Fake registration id.
        $registrationid = "registrationid";
        // Make test statements to pass through from the mock function;
        $statement = "String to throw error";

          // Make an array to add information to the session.
          $newprogress = json_encode(["actorname0 verbdisplay0 objectname0 on 29-11-2023 07:00 pm"]);
          $newscore = (80);

        // Copy the session so that we can tweak it and make how it should be then
        // Compare with sessin object returned from the function.
        $newsession = $session;
        // Now update the session to match the expected session.
        // Add the progress and score to the session.
        $newsession->progress = $newprogress;
        $newsession->score = $newscore;

        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(['cmi5launch_request_statements_from_lrs'])
            ->getMock();

        // Mock returns statements, as it would be from the LRS.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_request_statements_from_lrs')
            ->with($registrationid, $session)
            ->willReturn($statement);

        // Progress class and SUT.
        $progress = new \mod_cmi5launch\local\progress();
        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_statements($registrationid, $session);

        // There will also be an output message.
        $expectedoutput = 'Unable to retrieve statements from LRS. Caught exception: foreach() argument must be of type array|object, string given';
        $this->expectOutputString($expectedoutput);

    }
}
