<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\au_helpers;
/**
 * Tests for AuHelpers class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */
class ausHelpersTest extends TestCase
{
    private $auProperties, $emptyStatement, $mockStatementValues, $mockStatement2, $returnedAUids;

    public $auidForTest;

    protected function setUp(): void
    {
        // All the properties in an AU object.
        $this->auProperties = array(
            'id',
            'attempt',
            'url',
            'type',
            'lmsid',
            'grade',
            'scores',
            'title',
            'moveon',
            'auindex',
            'parents',
            'objectives',
            'description',
            'activitytype',
            'launchmethod',
            'masteryscore',
            'satisfied',
            'launchurl',
            'sessionid',
            'sessions',
            'progress',
            'noattempt',
            'completed',
            'passed',
            'inprogress',
        );

        $this->emptyStatement = array();

        // Based on created AU in program, but with some values removed.
        $this->mockStatement2 = array(
            "id" => "https://exampleau",
            "attempt" => NULL,
            "url" => "example.html?pages=1&complete=launch",
            "type" => "au example",
            "lmsid" => NULL,
            "grade" => NULL,
            "scores" => NULL,
            "title" => array( 0 => array(
                "lang" => "en-US",
                "text" => "Example AU")
            ),
            "moveOn"=> NULL,
            "auIndex" => NULL,
            "parents" => array(),
            "objectives" => NULL,
            "description" => array( 0 => array(
                "lang" => "en-US",
                "text" => "Example AU lesson description")
            ),
            'activitytype' => NULL,
            'launchmethod' => NULL,
            'masteryscore' => NULL,
            'satisfied' => NULL,
            'launchurl' => NULL,
            'sessionid' => NULL,
            'sessions' => NULL,
            'progress' => NULL,
            'noattempt' => NULL,
            'completed' => NULL,
            'passed' => NULL,
            'inprogress' => NULL,
            'launchMethod' => "AnyWindow",
            'lmsId' => "https://exampleau/ranomnum/au0",
            'moveOn' => "CompletedOrPassed",
            'auIndex' => 0,
            'activityType' => NULL,
            'masteryScore' => NULL
        );
        // Perhaps a good test would be to test the constructor with a statement that has all the properties set.
        $this->mockStatementValues = array(
            'id' => 'id',
            'attempt' => 'attempt',
            'url' => 'url',
            'type' => 'type',
            'lmsid' => 'lmsid',
            'grade' => 'grade',
            'scores' => 'scores',
            'title' => 'title',
            'moveon' => 'moveon',
            'auindex' => 'auindex',
            'parents' => 'parents',
            'objectives' => 'objectives',
            'description' => 'description',
            'activitytype' => 'activitytype',
            'launchmethod' => 'launchmethod',
            'masteryscore' => 'masteryscore',
            'satisfied' => 'satisfied',
            'launchurl' => 'launchurl',
            'sessionid' => 'sessionid',
            'sessions' => 'sessions',
            'progress' => 'progress',
            'noattempt' => 'noattempt',
            'completed' => 'completed',
            'passed' => 'passed',
            'inprogress' => 'inprogress',
        );
    
    }

    protected function tearDown(): void
    {
        //  $this->example = null;
    }


    // Retrieve Aus parses and returns AUs from large statements from the CMI5 player
    // So to test, maybe make a statement and ensure the test value is returned? 
    // Arbitrarily pick a word and put in right place? See if it is returned?
    public function testcmi5launch_retrieve_aus()
    {
    //It's not just returning it, it's splitting it into chuncks~!

        //Fake values to return
        $mockStatement = array(
            "createdAt" => "2023-06-26T18:36:15.000Z",
            "id"=> 000,
            "lmsId"=> "https://example",
            "metadata" => array( 
                "aus" => array  ( 
                    0 => array (
                        "activityType" => null,
                        "auIndex" => 0,
                        "description" => array ( 
                            "lang"=> "en-US",
                            "text"=> "Testing."
                        ),
                        "id"=> "https://au",
                        "launchMethod" => "AnyWindow",
                        "lmsId"  => "https://au/0",
                        "masteryScore" => null,
                        "moveOn"=> "CompletedOrPassed",
                        "objectives"=> null,
                        "parents"=> "",
                        "title"=> array (
                            "lang"=> "en-US",
                            "text" => "Introduction to Testing"
                        ),
                        "type" => "au",
                        "url" => "index.html?pages=1&complete=launch",
                    ),
                1 =>  array (
                    "activityType" => null,
                    "auIndex" => 1,
                    "description" => array(
                        "lang" => "en-US",
                        "text" => "Testing Testing."
                    ),
                    "id" => "https://example",
                    "launchMethod" => "AnyWindow",
                    "lmsId" => "https://example/au/1",
                    "masteryScore" => null,
                    "moveOn" => "CompletedOrPassed",
                    "objectives"=> null,
                    "parents" => "",
                    "title" => array(
                        "lang" => "en-US",
                        "text" => "Testing Materials"
                    ),
                    "type" => "au",
                    "url"=> "index.html?pages=2&complete=launch"
                    ),
                )
            )
        );
 
        //This is the value that should be returned, basically, an array holding all the aus separately
        $shouldBeReturned = array (
        //First au, nestled in array
        0 => array  ( 
            0 => array (
                "activityType" => null,
                "auIndex" => 0,
                "description" => array ( 
                    "lang"=> "en-US",
                    "text"=> "Testing."
                ),
                "id"=> "https://au",
                "launchMethod" => "AnyWindow",
                "lmsId"  => "https://au/0",
                "masteryScore" => null,
                "moveOn"=> "CompletedOrPassed",
                "objectives"=> null,
                "parents"=> "",
                "title"=> array (
                    "lang"=> "en-US",
                    "text" => "Introduction to Testing"
                ),
                "type" => "au",
                "url" => "index.html?pages=1&complete=launch",
            )
        ),
        //second au nestled in array    
        1 =>  array (
            0 => array (
                "activityType" => null,
                "auIndex" => 1,
                "description" => array(
                    "lang" => "en-US",
                    "text" => "Testing Testing."
                ),
                "id" => "https://example",
                "launchMethod" => "AnyWindow",
                "lmsId" => "https://example/au/1",
                "masteryScore" => null,
                "moveOn" => "CompletedOrPassed",
                "objectives"=> null,
                "parents" => "",
                "title" => array(
                    "lang" => "en-US",
                    "text" => "Testing Materials"
                ),
                "type" => "au",
                "url"=> "index.html?pages=2&complete=launch"
                ),
            )
        
        );

       $helper = new au_helpers();
        //So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $retrieved = $helper->cmi5launch_retrieve_aus($mockStatement);

   
        // It should retrieve the mock aus
        $this->assertEquals($shouldBeReturned, $retrieved, "Expected retrieved statement to be equal to mock statement");
        //This is being flaged as risky?
        //Is there a different way to test this?
        //Maybe we 'expect' two properties since 2 aus  were passed in?
        //I mean we aren't testing 'chunked?' so....?

        //It DOES return as array
        $this->assertIsArray($retrieved, "Expected retrieved statement to be an array");
        //And it returns two in array? Since we passed in two?
        $this->assertCount(2, $retrieved, "Expected retrieved statement to have two aus");
        //TODO MB
        //Those seem to pass, so take away line 206?
    }

    //Test function that is fed an array of statments and returns an array of aus onjects
    public function testcmi5launch_create_aus()
    {
        // Should be enough to pass the mock statement values here, make an array of them first
        $testStatements = array();
        
        //Lets create 4 aus statement
        for ($i = 0; $i < 4; $i++) {
            $testStatements[$i][] = $this->mockStatementValues;
        }
    
        $helper = new au_helpers();
        //So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $auList = $helper->cmi5launch_create_aus($testStatements);
        
        //There should be a total of 4 Aus in this array
        $this->assertCount(4, $auList, "Expected retrieved statement to have four aus");
        //And they should all be au objects
        foreach ($auList as $au) {
            $this->assertInstanceOf(au::class, $au, "Expected retrieved statement to be an array of aus");
        }
    }

    //This one is going to be tricky, it saves to a DB! I know test php can have TEST DBs, but is that setup here?
    //And how to freaking test THAT?
    //Well, actually we don't need to test it goes to the DB, THAT was the job of the person who invented insert_record
    //We just need tothat it saves the correct values and CALLS insert_record
    //Technically this function returns ids, so we can make a stub which just returns ids
    //This will test it is called without messing with the DB
    public function testcmi5launch_save_aus()
    {

        // The func should return auids created by the DB when AU's were saved in array format.
        $helper = new au_helpers();

        //Lets create 4 aus statement
        for ($i = 0; $i < 3; $i++) {
            $testAus[$i][] = $this->mockStatement2;
        }

        //So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $returnedAUids = $helper->cmi5launch_save_aus($helper->cmi5launch_create_aus($testAus));

        // First make sure array is returned
        $this->assertIsArray($returnedAUids, "Expected retrieved statement to be an array");

        // The array should have the same count of ids as AU's passed in
        $this->assertCount(3, $returnedAUids, "Expected retrieved statement to have three aus");
        // Now iterate through the returned array and ensure ids were passed back, numeric ids
        foreach ($returnedAUids as $auId) {

            // what is id?
          //  echo"auId: $auId";
            $this->assertIsNumeric($auId, "Expected array to have numeric values");
        }
        global $auidForTest;
        //Save to use in next test?
        $auidForTest = $returnedAUids;
        //Do I need to test fail?


    }   

    public function testcmi5launch_retrieve_aus_from_db()
    {
        // Access the global array of ids from above test
        global $auidForTest;

       // global $auidForTest;
        $helper = new au_helpers();

        // It takes singular ids, so we will iterate through them
        foreach ($auidForTest as $auId) {
            
            $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($auId);
            
            // And the return should be an au object
            $this->assertInstanceOf(au::class, $returnedAu, "Expected retrieved object to be an au object");
        }

        // And if it fails it should fail gracefully
        $badid = 0;
        $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($badid);
        
        // And the return should be a false value
        $this->assertNotTrue($returnedAu, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString("<p>Error attempting to get AU data from DB. Check AU id. AU id is: " . $badid . "</p>");

    }   
}