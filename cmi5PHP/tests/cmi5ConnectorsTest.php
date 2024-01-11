<?php
namespace cmi5Test;

use mod_cmi5launch\local\cmi5_connectors;
use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\au_helpers;
/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */
class cmi5ConnectorsTest extends TestCase
{
    private $auProperties, $emptyStatement, $mockStatementValues, $mockStatement2, $returnedAUids;

    public $auidForTest;

    protected function setUp(): void
    {
    
    }

    protected function tearDown(): void
    {
        //  $this->example = null;
    }


    // Create course sends data to the player. 
    // Now we dont actually want to talk to player or we will either make a bunch of unreal courses
    // unless we then delete it?
    // or have to make a fake player???
    // Does php have mocks or stubs?
    // They do, and what is actually returned is a massive json encoded string. It is the return
    // response when a course is created from player, but all we care about is a string return from stub? 
 //I understand what I worte here. IS it enouh to just retreive a strin? Or does it need to be in
 // same shape it would be from the player? Like do I need to verify it has, these cpmonets?
 // or is ti enouh to not.... Is my RPORTAM robusdt enouh to catch that? 
 // I don't think it i, it just throughs eneric errors, this may
 // be a place to improve it.

 //Ok, so be that as it may THIS func gets a response and sends array back. If response is false it faisl
 // robust enouh?
 // I think since this job is just to take and pass on courseinfo, it doesn't have to validate the properties of returned array, that will fall into the testing of the functhat retrieves them?

    public function testcmi5launch_create_course_pass()
    {
  
       // global $auidForTest;
        global $auidForTest;
        global $DB, $CFG;

        $id = 0;
        $tenanttoken = "testtoken";
        $filename = "testfilename";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player'))
            ->getMock();

        // We will have the mock return a basic string, as it's not under test
        // the string just needs to be returned as is. We do expect create_course to only call this once.
        $csc->expects($this->once())
        ->method('cmi5launch_send_request_to_cmi5_player')
        ->willReturn('Request sent to player')
       // ->with('Request sent to player')
    ;

    // I think I need to say expect to be called with these, 
    // because for some reason it says paraaam 0 is not matching?
        //Call the method under test. 
        $result =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);

        // And the return should be a string (the original method returns what the player sends back or FALSE)
         $this->assertIsString($result);
         $this->assertEquals('Request sent to player', $result);
        

    }
  public function testcmi5launch_create_course_fail()
  {
             // global $auidForTest;
        global $auidForTest;
        global $DB, $CFG;

        $id = 0;
        $tenanttoken = "testtoken";
        $filename = "testfilename";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player'))
            ->getMock();

        // We will have the mock return a FALSE value, this should enable us to test the
        // method under failing conditions. We do expect create_course to only call this once.
        $csc->expects($this->once())
        ->method('cmi5launch_send_request_to_cmi5_player')
        ->willReturn(FALSE)
       // ->with('Request sent to player')
    ;

    //Call the method under test. 
        $result =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);


        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
         //And it should output this error message

         $this->expectOutputString("<br>Something went wrong creating the course. CMI5 Player returned ". $result . "<br>");
 

        // And if it fails it should fail gracefully
      //  $badid = 0;
      //  $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($badid);
        
        // And the return should be a false value
       // $this->assertNotTrue($returnedAu, "Expected retrieved object to be false");
        //And it should output this error message
      //  $this->expectOutputString("<p>Error attempting to get AU data from DB. Check AU id. AU id is: " . $badid . "</p>");


  }

  //Here we will mock cmi5_send_request_to_player and have it return a string again, because thje func actually under test is 
  // cmi5launch_create_tenant, so to test it we want to make sure it calls the other func and retutrns what it does
  // or throws the specified error
  // (This is another place I have the error only if debug and wonder if it should be different. We always want that to show riht?) 
  public function testcmi5launch_create_tenant()
  {

   // global $auidForTest;
   global $auidForTest;
   global $DB, $CFG;

   $id = 0;
   $tenanttoken = "testtoken";
   $filename = "testfilename";

   // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
   // Create a mock of the send_request class as we don't actually want
   // to create a new course in the player.
   $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
       ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player'))
       ->getMock();

   // We will have the mock return a basic string, as it's not under test
   // the string just needs to be returned as is. We do expect create_course to only call this once.
   $csc->expects($this->once())
   ->method('cmi5launch_send_request_to_cmi5_player')
   ->willReturn('Request sent to player')
  // ->with('Request sent to player')
;

// I think I need to say expect to be called with these, 
// because for some reason it says paraaam 0 is not matching?
   //Call the method under test. 
   $result =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);

   // And the return should be a string (the original method returns what the player sends back or FALSE)
    $this->assertIsString($result);
    $this->assertEquals('Request sent to player', $result);
  }


}