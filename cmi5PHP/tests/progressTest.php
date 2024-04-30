<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;

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
class cmi5_progressTest extends TestCase
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
        global $DB, $cmi5launch,  $cmi5launchid;

        // Delete the test record.
        deletetestcmi5launch($cmi5launchid);
        
    }

    protected function setUp(): void
    {
        global $sessionids, $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $cmi5launchsettings;

          $cmi5launchsettings = array("cmi5launchtenanttoken" => "Testtoken", "cmi5launchplayerurl" => "http://test/launch.php", "cmi5launchcustomacchp" => "http://testhomepage.com");


        // Override global variable and function so that it returns test data.
        $USER = new \stdClass();
        $USER->username = "testname";
        $USER->id = 10;

        $testcourseid = maketestcourse($cmi5launchid);

        // We need session objects to test the progress class
        // Make a fake session object.
        $sessionids = maketestsessions();

        echo "<br>";
        echo " what is sessions ids here right after being made? ";
        var_dump($sessionids);
        echo "<br>";

     
    }

    protected function tearDown(): void
    {
        // Restore overridden global variable.
        unset($GLOBALS['USER']);
        unset($GLOBALS['cmi5launchsettings']);
    }


    /**
     * Test of the cmi5launch_request_statements_from_lrs with a pass condition.
     * @return void
     */
    public function testcmi5launch_request_statements_from_lrs_pass()
    {
        global $DB, $cmi5launch, $cmi5launchid, $sessionids;

        // Amount of statements to make for testing.
        $amount = 4;

        // Make fake statements for testing.
        $statements = maketeststatements($amount);

        // Which means we expect the same AMOUNT as returned statements.
        // in an array 
        	// Make new statements, lets make five.
	$i = $amount;
        for ($i = 0; $i < 5; $i++) {
            // mock values to add to statements.
            // They will be nested under the registration id.
            $statementvalues = array(
                "registrationid" => array(
                    0 => array(
                        'id' => 'id' . $i,
                        'timestamp' => 'timestamp' . $i,
                        'actor' => 
                        array (
                            "firstname" => "firstname" . $i,
                            "lastname" => "lastname" . $i,
                        ),
                        'verb' => array (
                            "verb" => "verb" . $i,
                            "verbtype" => "verbtype" . $i,
                        ),
                        'object'  => array (
                            "verb" => "verb" . $i,
                            "verbtype" => "verbtype" . $i,
                        ),
                        'context'  => array (
                            "context" => "context" . $i,
                            "contexttype" => "contexttype" . $i,
                        ),
                        'result' => array (
                            "score" => "score" . $i,
                        ),
                        'stored' => 'stored' . $i,
                        'authority' => array (
                            "authority" => "authority" . $i,
                        ),
                        'version' => "version" . $i,
                                    )  )
            );
        }
      
        // Retrieve a sessionid.
        $sessionid = $sessionids[0];

            // Retrieve a session from the DB as an object
            $session = $DB->get_record('cmi5launch_sessions', array('id' => $sessionid), '*', MUST_EXIST);
         

            // Thi is the problem, the registration id is 0 so it keeps making array
            // 0 =>  value, that was VALUE not reater than pr equal to! Where is my head!!!
        $registrationid = "registrationid";

        // Mock data as it will be passed to stub
        $data = array(
            'registration' => $registrationid,
            'since' => $session->createdat,
        );


        // Takes a registrationid and a session object
        // IT retrtieves a stement form an LRS so we can mock out the send request ti lrs
        // and return some fakes statments
        
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(array('cmi5launch_send_request_to_lrs' ))
            ->getMock();

        // We will have the mock return a basic string, as it's not under test
        // the string just needs to be returned as is. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_lrs')
            ->with($data, $session->id)
            ->willReturn($statements);

        // Call the method under test.
        $result = $csc->cmi5launch_request_statements_from_lrs($registrationid,$session);

        // Check the result is as expected.
        $this->assertEquals($statementvalues, $result, "Expected result to match statementvalues but it doesn't it equals "); // . var_dump($result));
        $this->assertIsArray($result,"Expected retrieved object to be array" );
    }
}
    