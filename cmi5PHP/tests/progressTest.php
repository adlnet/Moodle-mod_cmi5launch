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
        // The same amount of statements to make for testing will match the amount
        // of statement values returned from mock.
        $amount = 5;

        // Make fake statements for testing.
        $statements = maketeststatements($amount);

        // Test registrationid to pass.
        $registrationid = "registrationid";

        // Statement values that should be returned from mock.
        $statementvalues = array();
 
        for($i = 0; $i < $amount; $i++)
        {
            // Mock statements that should be returned.
            $statementvalues[] = array(
                $registrationid => array(
                    // They are nested under 0 since array_chunk is used.
                    0 => array(
                        'timestamp' => 'timestamp' . $i,
                        'actor' => array (
                            "firstname" => "firstname" . $i,
                            "lastname" => "lastname" . $i,
                            "account" => array (
                                "homePage" => "homePage" . $i,
                                "name" => "name" . $i,
                            ),
                        ),
                        'verb' => array (
                            "id" => "verbid" . $i,
                            "display" => array(
                                "en" => "verbdisplay" . $i,
                            ),
                        ),
                        'object'  => array (
                            "id" => "objectid" . $i,
                            "definition" => array (
                                "name" => "name" . $i,
                                "description" => "description" . $i,
                                "type" => "type" . $i,
                            ),
                        ),
                        'context'  => array (
                            "context" => "context" . $i,
                            "contexttype" => "contexttype" . $i,
                            "contextparent" => "contextparent" . $i,
                        ),
                        "result" => array (
                            "result" => "result" . $i,
                            "score" => array (
                                "raw" => "raw" . $i,
                                "scaled" => "scaled" . $i,
                            ),
                        ),
                        'stored' => 'stored' . $i,
                        'authority' => array (
                            "authority" => "authority" . $i,

                        ),
                        'version' => "version" . $i,
                    ),
                )    
            ); 
        }
      
        // Retrieve a sessionid, we'll just use the first one.
        $sessionid = $sessionids[0];
    
        // Retrieve a session from the DB as an object.
        $session = $DB->get_record('cmi5launch_sessions', array('id' => $sessionid), '*', MUST_EXIST);

        // Mock data as it will be passed to stub.
        $data = array(
            'registration' => $registrationid,
            'since' => $session->createdat,
        );

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\progress')
            ->onlyMethods(array('cmi5launch_send_request_to_lrs' ))
            ->getMock();

        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_lrs')
            ->with($data, $session->id)
            ->willReturn($statements);

        // Call the method under test.
        $result = $csc->cmi5launch_request_statements_from_lrs($registrationid,$session);

        // We can check the count matches. We want as many statements back as we made.
        $this->assertCount($amount, $result, "Expected result to have $amount statements");
        
        // Check the result is as expected.
        $this->assertEquals($statementvalues, $result, "Expected result to match statementvalues ");
        $this->assertIsArray($result,"Expected retrieved object to be array" );
    }

    /**
     * Test of the cmi5launch_request_statements_from_lrs with a fail condition.
     * @return void
     */
    public function testcmi5launch_request_statements_from_lrs_fail(){

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
        $statementvalues = array();
 
 
        for($i = 0; $i < $amount; $i++)
        {
            // Mock statements that should be returned.
            $statementvalues[] = array(
                $registrationid => array(
                    // They are nested under 0 since array_chunk is used.
                    0 => array(
                        'timestamp' => 'timestamp' . $i,
                        'actor' => array (
                            "firstname" => "firstname" . $i,
                            "lastname" => "lastname" . $i,
                            "account" => array (
                                "homePage" => "homePage" . $i,
                                "name" => "name" . $i,
                            ),
                        ),
                        'verb' => array (
                            "id" => "verbid" . $i,
                            "display" => array(
                                "en" => "verbdisplay" . $i,
                            ),
                        ),
                        'object'  => array (
                            "id" => "objectid" . $i,
                            "definition" => array (
                                "name" => "name" . $i,
                                "description" => "description" . $i,
                                "type" => "type" . $i,
                            ),
                        ),
                        'context'  => array (
                            "context" => "context" . $i,
                            "contexttype" => "contexttype" . $i,
                            "contextparent" => "contextparent" . $i,
                        ),
                        "result" => array (
                            "result" => "result" . $i,
                            "score" => array (
                                "raw" => "raw" . $i,
                                "scaled" => "scaled" . $i,
                            ),
                        ),
                        'stored' => 'stored' . $i,
                        'authority' => array (
                            "authority" => "authority" . $i,

                        ),
                        'version' => "version" . $i,
                    ),
                )    
            ); 
        }
          
    }

}