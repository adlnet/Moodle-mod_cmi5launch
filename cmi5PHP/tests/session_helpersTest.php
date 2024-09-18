<?php
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
 *  */
class session_helpersTest extends TestCase
{
	// Use setupbefore and after class sparingly. In this case, we don't want to use it to connect tests, but rather to
	// 'prep' the test db with values the tests can run against. 
	public static function setUpBeforeClass(): void
	{
		global $DB, $cmi5launch, $cmi5launchid;

		// Mke a fake cmi5 launch record.
		$cmi5launchid = maketestcmi5launch();


	}

	public static function tearDownAfterClass(): void
	{
		global $DB, $cmi5launch, $cmi5launchid;

		// Delete the test record.
		deletetestcmi5launch($cmi5launchid);

	}

	protected function setUp(): void
	{
		global $sessionids, $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $cmi5launchsettings;

		$cmi5launchsettings = array(
			"cmi5launchlrsendpoint" => "Test LRS point",
			"cmi5launchlrslogin" => "Test LRS login",
			"cmi5launchlrspass" => "Test LRS password",
			"cmi5launchtenanttoken" => "Testtoken",
			"cmi5launchplayerurl" => "http://test/launch.php",
			"cmi5launchcustomacchp" => "http://testhomepage.com",
			"grademethod" => 1
		);

		// Override global variable and function so that it returns test data.
		$USER = new \stdClass();
		$USER->username = "testname";
		$USER->id = 10;

		$testcourseid = maketestcourse($cmi5launchid);

		// We need session objects to test the progress class
		// Make a fake session object.
		$sessionids = maketestsessions();
	}

	protected function tearDown(): void
	{
		global $sessionids;
		// Restore overridden global variable.
		unset($GLOBALS['USER']);
		unset($GLOBALS['cmi5launchsettings']);

		deletetestcmi5launch_sessions($sessionids);
	}


	/**
	 * Test of the cmi5launch_update_sessions with a pass condition.
	 * @return void
	 */
	public function testcmi5launch_update_sessions()
	{
		global $DB, $cmi5launch, $cmi5launchid, $sessionids, $USER;

		// We just need one session to test this.
		$sessionid = $sessionids[0];

		// Retrieve the session object.
		// Get the session from DB with session id.
		$session = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

		// So expected session should be the same as session with the other two changes. 
		$sessionexpected = $session;
		$sessionexpected->score = 100;
		$sessionexpected->iscompleted = 1;
		$sessionexpected->ispassed = 1;
		$sessionexpected->launchmethod = "ownWindow";
		$sessionexpected->isterminated = 1;
		$sessionexpected->launchurl = "http://test.com";

		// New session_helpers from mod_cmi5launch
		$helpers = new \mod_cmi5launch\local\session_helpers();


		$progress = new \cmi5Test\progress();
		$cmi5 = new \cmi5Test\cmi5_connectors();

		// Result of the function.
		$helpers->cmi5launch_update_sessions($progress, $cmi5, $sessionid, $cmi5launchid, $USER);

		// So the func doesn't return  anything, but we can check the session object in the db.
		// Get the session from DB with session id.
		$result = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

		// Result should be a session object.
		$this->assertIsObject($result, "Result should be an object.");

		// Check that the result is the same as the expected session.
		$this->assertEquals($sessionexpected, $result, "Result should be the same as the expected session.");
	}


	/**
	 * Test of the cmi5launch_update_sessions with an exception.
	 * @return void
	 */
	public function testcmi5launch_update_sessions_excep()
	{
		global $DB, $cmi5launch, $cmi5launchid, $sessionids, $USER;

		// We just need one session to test this.
		$sessionid = $sessionids[0];

		// Retrieve the session object.
		// Get the session from DB with session id.
		$session = $DB->get_record('cmi5launch_sessions', array('sessionid' => $sessionid));

		$exceptionmessage = 'Error in updating session. Report this error to system administrator: Attempt to assign property "isterminated" on false';
		// So expected session should be the same as session with the other two changes. 
		$sessionexpected = $session;
		$sessionexpected->score = 100;
		$sessionexpected->iscompleted = 1;
		$sessionexpected->ispassed = 1;
		$sessionexpected->launchmethod = "ownWindow";
		$sessionexpected->isterminated = 1;
		$sessionexpected->launchurl = "http://test.com";

		// New session_helpers from mod_cmi5launch
		$helpers = new \mod_cmi5launch\local\session_helpers();

		$progress = new \cmi5Test\progress();
		$cmi5 = new \cmi5Test\cmi5_connectors();

		// Wait, i bet this is being thrown in the cmi5 connectors error message func and so we need to catch
		// the correct output not an exception
		$output = "<p>Error attempting to get session data from DB. Check session id.";
		//  $this->assertStringStartsWith($output);
		$this->expectExceptionMessage($exceptionmessage);
		$this->expectException(nullException::class);
		// Pass null so that an exception is thrown.
		// Result of the function.
		$helpers->cmi5launch_update_sessions($progress, $cmi5, null, $cmi5launchid, $USER);


	}


	/**
	 * Test of the cmi5launch_create_session with a pass condition.
	 * @return void
	 */
	public function testcmi5launch_create_session()
	{
		global $DB, $cmi5launch, $cmi5launchid, $sessionids, $USER;

		
		// Fake values. 
		$sessionid = '100';
		$launchurl = "http://test.com";
		$launchmethod = "ownWindow";
		$tenantname = $USER->username;

		// We need to make a fake session for the mocked function to return.
		 // Make a new record to save.
		 $mockedsession = new \stdClass();
		 // Because of many nested properties, needs to be done manually.
		 $mockedsession->sessionid = $sessionid;
		 $mockedsession->launchurl = $launchurl;
		 $mockedsession->tenantname = $USER->username;
		 $mockedsession->launchmethod = $launchmethod;
		 // I think here is where we eed to implement : moodlecourseid
		 $mockedsession->moodlecourseid = $cmi5launch->id;
		 // And userid!
		 $mockedsession->userid = $USER->id;
		
		// New session_helpers from mod_cmi5launch
		$helpers = new \mod_cmi5launch\local\session_helpers();

		// Create a mock since we need to mock the update session function.
		$mock = $this->getMockBuilder($helpers::class)
                     ->onlyMethods(['cmi5launch_update_sessions']) // Specify only the allowed method
                     ->getMock();

		// Result of the function.
		$resultid = $mock->cmi5launch_create_session($sessionid, $launchurl, $launchmethod);

		// Returns a new id.
		$this->assertIsInt($resultid, "Result should be an int.");

		// Now retrieve it back from the db and make sure it matches.
		// So the func doesn't return  anything, but we can check the session object in the db.
		// Get the session from DB with session id.
		$result = $DB->get_record('cmi5launch_sessions', array('id' => $resultid));

		// Result should be a session object.
		$this->assertIsObject($result, "Result should be an object.");

		// Maybe we can just assert the newrecord fields that are different.
		// Check results sessioid, launchurl, tenantname, launchmethod.

		// Check that the result is the same as the expected session.
		$this->assertEquals($sessionid, $result->sessionid, "Result should be the same as the expected session.");
		$this->assertEquals($launchurl, $result->launchurl, "Result should be the same as the expected session.");
		$this->assertEquals($tenantname, $result->tenantname, "Result should be the same as the expected session.");
		$this->assertEquals($launchmethod, $result->launchmethod, "Result should be the same as the expected session.");
	}


	/**
	 * Test of the cmi5launch_create_session with a fail condition.
	 * Catches an exception.
	 * @return void
	 */
	public function testcmi5launch_create_session_excep()
	{
		global $DB, $cmi5launch, $cmi5launchid, $sessionids, $USER;

		// LEt's make user not have id?
		$USER = null;
		// Fake values. 
		$sessionid = 100;
		$launchurl = "http://test.com";
		$launchmethod = "ownWindow";

		// New session_helpers from mod_cmi5launch
		$helpers = new \mod_cmi5launch\local\session_helpers();

		$exceptionmessage = 'Error in creating session. Report this error to system administrator: ';

		$this->expectExceptionMessage($exceptionmessage);
		$this->expectException(nullException::class);

		// Pass null so that an exception is thrown.

		// Result of the function.

		// Result of the function.
		$resultid = $helpers->cmi5launch_create_session($sessionid, $launchurl, $launchmethod);

	}



}
