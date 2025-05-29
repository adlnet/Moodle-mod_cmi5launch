<?php
namespace cmi5Test;
use mod_cmi5launch\local\nullException;
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
    private  $mockstatementvalues, $mockStatement2, $mockStatementExcept, $mockStatementExcept2, $returnedAUids;

    public $auidForTest;

    protected function setUp(): void
    {

        // Based on created AU in program.
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
            "parents" => array(),
            "objectives" => NULL,
            "description" => array( 0 => array(
                "lang" => "en-US",
                "text" => "Example AU lesson description")
            ),
            'satisfied' => NULL,
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

        // Based on created AU in program, but with some values changed.
        // For instance title is an empty array
        $this->mockStatementExcept = array(
            "id" => "https://exampleau",
            "attempt" => NULL,
            "url" => "example.html?pages=1&complete=launch",
            "type" => "au example",
            "grade" => NULL,
            "scores" => NULL,
            "title" => array()
            ,
            "parents" => array(),
            "objectives" => NULL,
            "description" => array( 0 => array(
                "lang" => "en-US",
                "text" => "Example AU lesson description")
            ),
            'satisfied' => NULL,
            'sessions' => NULL,
            'progress' => NULL,
            'noattempt' => NULL,
            'completed' => NULL,
            'passed' => NULL,
            'inprogress' => NULL,
            'launchMethod' => "AnyWindow",
            'lmsId' => "https://exampleau/ranomnum/au0",
            'auIndex' => 0,
            'activityType' => NULL,
            'masteryScore' => NULL
        );
        // Based on created AU in program, but with some values changed. Here title is a string.
        $this->mockStatementExcept2 = array(
            "id" => "https://exampleau",
            "attempt" => NULL,
            "url" => "example.html?pages=1&complete=launch",
            "type" => "au example",
            "lmsid" => NULL,
            "grade" => NULL,
            "scores" => NULL,
            "title" => array( "title "),
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
        $this->mockstatementvalues = array(
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
         
    }


    // Retrieve Aus parses and returns AUs from large statements from the CMI5 player.
    // So to test, we will make a statement and ensure the test value is returned.
    public function testcmi5launch_retrieve_aus()
    {
        // Fake values to return.
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
 
        // This is the value that should be returned, basically, an array holding all the aus separately.
        $shouldbereturned = array (

        //First au, nestled in array.
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
        // Second au nestled in array.    
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
        
        // So now with this fake 'statement', lets ensure it pulls the correct value which is "correct retrieval".
        $retrieved = $helper->cmi5launch_retrieve_aus($mockStatement);

        // It should retrieve the mock aus
        $this->assertEquals($shouldbereturned, $retrieved, "Expected retrieved statement to be equal to mock statement");

        // It returns as array.
        $this->assertIsArray($retrieved, "Expected retrieved statement to be an array");
        // And it returns two in array?
        $this->assertCount(2, $retrieved, "Expected retrieved statement to have two aus");
    }

    //Lets try testing so it throws excrption if thhe array doesn't have the right keys

    // We cannot test 'caught' exception because it is thrown by the overriden error handler, not the SUT.
    // To test the exception we need to test the right output (from the exception) is generated,
    public function testcmi5launch_retrieve_aus_exception()
    {
        // Note this is an incorrect statement, cut off before 'aus'.
        $mockStatement = array(
            "createdAt" => "2023-06-26T18:36:15.000Z",
            "id"=> 000,
            "lmsId"=> "https://example",
            "metadata" => array( 
            ));

        // This is the expected output message.
        $expectedMessage = "Cannot retrieve AUs. Error found when trying to parse them from course creation: " .
                "Please check the connection to player or course format and try again. \n"
                . 'Cannot parse array. Error: Undefined array key "aus"' . "\n";
       $helper = new au_helpers();
      
        // Call function under test.
        $retrieved = $helper->cmi5launch_retrieve_aus($mockStatement);

        // If the right message is displayed the try/catch wworked!
        $this->expectOutputString($expectedMessage);
    }


    // Test function that is fed an array of statments and returns an array of aus objects.
    public function testcmi5launch_create_aus()
    {
        // Should be enough to pass the mock statement values here, make an array of them first.
        $teststatements = array();
        
        //Lets create 4 aus statement.
        for ($i = 0; $i < 4; $i++) {
            $teststatements[$i][] = $this->mockstatementvalues;
        }
    
        $helper = new au_helpers();
        // So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $auList = $helper->cmi5launch_create_aus($teststatements);
        
        // There should be a total of 4 Aus in this array.
        $this->assertCount(4, $auList, "Expected retrieved statement to have four aus");
        // And they should all be au objects.
        foreach ($auList as $au) {
            $this->assertInstanceOf(au::class, $au, "Expected retrieved statement to be an array of aus");
        }
    }

    // Test function creat aus null exception.
    public function testcmi5launch_create_aus_exception()
    {

        // If we pass a null value in, it should throw an exception immedietely
        $teststatements = null;
        
        $this->expectException(nullException::class);
        $this->expectExceptionMessage('Cannot retrieve AU information. AU statements from DB are: ' . null);
    
        $helper = new au_helpers();

        // Pass null to SUT.
        $helper->cmi5launch_create_aus($teststatements);
    }

  

    // test saving aus with exceptions. 
    public function testcmi5launch_save_aus_exceptions()
    {
        // Make a global variable to hold the id's to pretend to be cmi5launch instance id.
        global $cmi5launch;

        $cmi5launch = new \stdClass();
        $cmi5launch->id = 1;

        // The func should return auids created by the DB when AU's were saved in array format.
        $helper = new au_helpers();

       
        // Pass in a statement with something wrong. This one has a null title.
        $testAus[0][] = $this->mockStatementExcept;
        
        // Because this exception is thrown by the error handler, not the SUT, test the output to ensure right exception was thrown.
        $expected = "Cannot save to DB. Stopped at record with ID number " . 1 . "."
        . " One of the fields is incorrect. Check data for field 'title'. Error: Undefined array key 0 \n";
        
        // Call function under test.
        $returnedAUids = $helper->cmi5launch_save_aus($helper->cmi5launch_create_aus($testAus));
            // If the right message is displayed the try/catch wworked!
        $this->expectOutputString($expected);
    }   


    // Test saving aus with exceptions, a different exception.
    public function testcmi5launch_save_aus_exceptions_2()
    {
        // Make a global variable to hold the id's to pretend to be cmi5launch instance id.
        global $cmi5launch;

        $cmi5launch = new \stdClass();
        $cmi5launch->id = 1;

        // The func should return auids created by the DB when AU's were saved in array format.
        $helper = new au_helpers();

        // This first statement is correct. We want to test that it gets the SECOND statement number,
        $testAus[0][] = $this->mockStatement2;
        // This one has 'title' as string instead of array.
        $testAus[1][] = $this->mockStatementExcept2;

        // The expected is built by the two messages knowing 'title' is a string.
        $expected = "Cannot save to DB. Stopped at record with ID number " . 2 . "."
        . " One of the fields is incorrect. Check data for field 'title'. Cannot access offset of type string on string \n";

        // Call the function to throw the exception.
        $returnedAUids = $helper->cmi5launch_save_aus($helper->cmi5launch_create_aus($testAus));
        
        // Because this exception is thrown by the error handler, not the SUT, test the output to ensure right exception was thrown.
        $this->expectOutputString($expected);
    
    }  


    // Test saving aus with exceptions, a (null) exception.
    public function testcmi5launch_save_aus_exceptions_test_null()
    {

        // Make a global variable to hold the id's to pretend to be cmi5launch instance id.
        global $cmi5launch;

        $cmi5launch = new \stdClass();
        $cmi5launch->id = 1;

        $helper = new au_helpers();

        // A test statement with a null value.
        $testAus = null;

        // Catch the exception.
        $this->expectException(nullException::class);
        $this->expectExceptionMessage('Cannot save AU information. AU object array is: null' . null);

        // The expected is built bby the two messages knowing 'title' is an empty array.
        $expected = "Cannot save to DB. Stopped at record with ID number " . 1 . "."
        . " One of the fields is incorrect. Check data for field 'title'. Error: Undefined array key 0\n";
        //So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $returnedAUids = $helper->cmi5launch_save_aus($testAus);
            
    }   

    // Test saving aus, this function returns ids, so we can make a stub which just returns ids.
    // This will test it is called without messing with the DB.
    public function testcmi5launch_save_aus()
    {
        // Make a global variable to hold the id's to pretend to be cmi5launch instance id.
        global $cmi5launch, $auidForTest;

        $cmi5launch = new \stdClass();
        $cmi5launch->id = 1;

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

            $this->assertIsNumeric($auId, "Expected array to have numeric values");
        }

        // Is this not savin?
     //   echo "Returned AU ids: " . var_dump($returnedAUids) . "\n";

        $auidForTest = $returnedAUids;
    } 
    // Test retrieving an AU from the DB with a correct value.
    public function testcmi5launch_retrieve_aus_from_db()
    {
        // ok, what if we saved ere the retrieved
       // Access the global array of ids from above test
        global $auidForTest;

       // global $auidForTest;
        $helper = new au_helpers();

        // Save new aus to db to pull. 
        //Lets create 4 aus statement
        for ($i = 0; $i < 3; $i++) {
            $testAus[$i][] = $this->mockStatement2;
        }

        //So now with this fake 'statement', lets ensure it pulls the correct value which is "correct Retrieval"
        $returnedAUids = $helper->cmi5launch_save_aus($helper->cmi5launch_create_aus($testAus));

        //and what is here?
      //  echo"au id for test: " . var_dump($auidForTest) . "\n";
        // It takes singular ids, so we will iterate through them
        foreach ($returnedAUids as $auId) {
            
            $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($auId);
            
            // And the return should be an au object
            $this->assertInstanceOf(au::class, $returnedAu, "Expected retrieved object to be an au object");
        }
    }   


    // Test retrieving an AU from the DB with a null value and thrown exception.
    public function testcmi5launch_retrieve_aus_from_db_null_exception()
    {
        $helper = new au_helpers();

        // And if it fails it should fail gracefully, throwin the correct exception.
        $badid = 0;

        // Catch the exception.
        $this->expectException(nullException::class);
        $this->expectExceptionMessage("Error attempting to get AU data from DB. Check AU id. AU id is: " . $badid ."</p>" . null);
    
        $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($badid);

    }   
}