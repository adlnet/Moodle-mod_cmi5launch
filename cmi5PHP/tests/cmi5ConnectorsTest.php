<?php
namespace cmi5Test;

/*
Example of how au helpers works with au in same folder

namespace mod_cmi5launch\local;

use mod_cmi5launch\local\au;
use cmi5Test\testHelpers;
*/ 
use cmi5Test\cmi5TestHelpers;
use mod_cmi5launch\local\cmi5launch_settings;
//use cmi5Test\cmi5TestHelpers;
//          use cmi5Test\testHelpers;
use mod_cmi5launch\local\au;
//use cmi5Test\cmi5TestHelpers\cmi5launch_settings;
use PHPUnit\Framework\TestCase;
//use mod_cmi5launch\local\au;
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
        global $DB, $CFG, $filename;

        $id = 0;
        $tenanttoken = "testtoken";
       
       //If we make filename an object with it's own get_content method, we can stub it out
      
  
       $filename = new class { 
  
        public function get_content() {
            return "testfilecontents";
        }
    };
 // Wait this moiht work, we can just make this object a method, and stub it out ourselves, 
 // or would i be better to try and use their mocks
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();
            
            

          //$setting =  $this->getMockBuilder(\stdclass::class)->addMethods(array('cmi5launch_settings'))->getMock();
      //  $setting = $this->getFunctionMod(_mod_cmi5launch\local__, 'cmi5launch_settings');
        //    $setting = $this->getMockBuilder(\stdclass::class)
         //   ->addMethods(array('cmi5launch_settings'))
         //   ->getMock();

           // $setting = $this->createMock(cmi5launch__settings::class)
            //->addMethods(array('cmi5launch_settings'))
            //->getMock();

// mod_cmi5launch\local\cmi5launch_settings()
// Can I mock lib? Then stuff like settings could be mocked? 

        
        // We will have the mock return a basic string, as it's not under test
        // the string just needs to be returned as is. We do expect create_course to only call this once.
        $csc->expects($this->once())
        ->method('cmi5launch_send_request_to_cmi5_player_post')
        // IT will call '/api/v1/course' nd not a whole url because that is accessed through "Settings" not reachable under test conditions, so it
        // will only use the second part of concantation
        ->with('testfilecontents', '/api/v1/course', 'testtoken')
        ->willReturn('Request sent to player');

        
    //    $setting->expects($this->any())
    //   ->method('cmi5launch_settings')
    //    ->willReturn(array('cmi5launchplayerurl' => 'http://localhost:8000/launch.php'))
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
       // $filename = array ("testfilename" => "testfilecontents");
 //If we make filename an object with it's own get_content method, we can stub it out
      
  
 $filename = new class { 
  
  public function get_content() {
      return "testfilecontents";
  }
};
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        // We will have the mock return a FALSE value, this should enable us to test the
        // method under failing conditions. We do expect create_course to only call this once.
        $csc->expects($this->once())
        ->method('cmi5launch_send_request_to_cmi5_player_post')
        ->with('testfilecontents', '/api/v1/course', 'testtoken')
        ->willReturn(FALSE)
       // ->with('Request sent to player')
    ;

    //Call the method under test. 
        $result =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);


        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
         //And it should output this error message
         $this->expectOutputString("<br>Something went wrong creating the course. CMI5 Player returned ". $result . "<br>");
 

  }

  //Here we will mock cmi5_send_request_to_player and have it return a string again, because thje func actually under test is 
  // cmi5launch_create_tenant, so to test it we want to make sure it calls the other func and retutrns what it does
  // or throws the specified error
  // (This is another place I have the error only if debug and wonder if it should be different. We always want that to show riht?) 
  public function testcmi5launch_create_tenant_pass()
  {

   // global $auidForTest;
   global $CFG;

   $urltosend = "playerwebaddress";
   $username = "testname";
   $password = "testpassword";
    $newtenantname = "testtenantname";

    $returnvalue = array(
      "code" => "testtenantname",
      "id" => 9
    );
  
   // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
   // Create a mock of the send_request class as we don't actually want
   // to create a new course in the player.
   $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
       ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
       ->getMock();

   // We will have the mock return a basic string, as it's not under test
   // the string just needs to be returned as is. We do expect create_course to only call this once.
   $csc->expects($this->once())
   ->method('cmi5launch_send_request_to_cmi5_player_post')
   ->with(array ('code' => 'testtenantname'), 'playerwebaddress', 'testname', 'testpassword')
   // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
   ->willReturn('{
    "code": "testtenantname",
    "id": 9
    }'     )
  // ->with('Request sent to player')
;

// I think I need to say expect to be called with these, 
// because for some reason it says paraaam 0 is not matching?
   //Call the method under test. 
   $result =$csc->cmi5launch_create_tenant($urltosend, $username, $password, $newtenantname);

   // And the return should be a string (the original method returns what the player sends back or FALSE)
    $this->assertIsArray($result);
    $this->assertEquals( $returnvalue, $result);
  }

  public function testcmi5launch_create_tenant_fail()
  {
    
    // global $auidForTest;
    global $CFG;

    $urltosend = "playerwebaddress";
    $username = "testname";
    $password = "testpassword";
     $newtenantname = "testtenantname";
 
     $returnvalue = array(
       "code" => "testtenantname",
       "id" => 9
     );
   
    // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
    // Create a mock of the send_request class as we don't actually want
    // to create a new course in the player.
    $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
        ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
        ->getMock();

        // This time we will have ti 'fail' so return a fail response from player
    $csc->expects($this->once())
    ->method('cmi5launch_send_request_to_cmi5_player_post')
    ->with(array ('code' => 'testtenantname'), 'playerwebaddress', 'testname', 'testpassword')
    // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
    ->willReturn(false)
   // ->with('Request sent to player')
 ;

// I think I need to say expect to be called with these, 
// because for some reason it says paraaam 0 is not matching?
   //Call the method under test. 
   $result =$csc->cmi5launch_create_tenant($urltosend, $username, $password, $newtenantname);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
         //And it should output this error message
         $this->expectOutputString("<br>Something went wrong creating the tenant. CMI5 Player returned ". $result . "<br>");
 

  }


  public function testcmi5launch_send_request_to__cmi5_player_post()
  {
    
    // global $auidForTest;
    global $CFG;

    $help = new  cmi5TestHelpers(); 
   // $testHelp = new cmi5TestHelpers();

    $databody = array ('code' => 'testtenantname'); 
    $urltosend = "playerwebaddress";
    $username = "testname";
    $password = "testpassword";
     $token = "testtoken";
 
     $returnvalue = array(
       "code" => "testtenantname",
       "id" => 9
     );
   
    // Mock a cmi5 connector object but only stub ONE method, as we want to test the other
    // Create a mock of the send_request class as we don't actually want
    // to create a new course in the player.
    $csc = $this->getMockBuilder( __NAMESPACE__ . '\cmi5TestHelpers')
        ->onlyMethods(array('file_get_contents'))
        ->getMock();

        // This time we will have ti 'fail' so return a fail response from player
    $csc->expects($this->once())
    ->method('file_get_contents')
    //->with(array ('code' => 'testtenantname'), 'playerwebaddress', 'testname', 'testpassword')
    // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
    ->willReturn("Test")
   // ->with('Request sent to player')
 ;

// I think I need to say expect to be called with these, 
// because for some reason it says paraaam 0 is not matching?
   //Call the method under test. 
   $result =$csc->cmi5launch_send_request_to_cmi5_player_post($databody, $urltosend, $username, $password);

        // Result should be debug echo string and false
      //  $this->assertNotTrue($result, "Expected retrieved object to be false");
         //And it should output this error message
         $this->expectOutputString("Test");
 

  }


}