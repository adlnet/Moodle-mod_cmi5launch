<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;

require_once( "cmi5TestHelpers.php");

/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \cmi5_connectors
 * @covers \cmi5_connectors::cmi5launch_create_course
 * @covers \cmi5_connectors::cmi5launch_create_tenant
 * @covers \cmi5_connectors::cmi5launch_retrieve_registration_with_get
 * @covers \cmi5_connectors::cmi5launch_retrieve_registration_with_post
 * @covers \cmi5_connectors::cmi5launch_retrieve_token
 * @covers \cmi5_connectors::cmi5launch_retrieve_url
 * @covers \cmi5_connectors::cmi5launch_send_request_to_cmi5_player_get
 * @covers \cmi5_connectors::cmi5launch_send_request_to_cmi5_player_post
 * @covers \cmi5_connectors::cmi5launch_retrieve_session_info_from_player
 * @covers \cmi5_connectors::cmi5launch_connectors_error_message
 *  */
class cmi5_connectorsTest extends TestCase
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
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $cmi5launchsettings;

          $cmi5launchsettings = array("cmi5launchtenanttoken" => "Testtoken", "cmi5launchplayerurl" => "http://test/launch.php", "cmi5launchcustomacchp" => "http://testhomepage.com");


        // Override global variable and function so that it returns test data.
        $USER = new \stdClass();
        $USER->username = "testname";
        $USER->id = 10;


        $testcourseid = maketestcourse($cmi5launchid);
    }

    protected function tearDown(): void
    {
        // Restore overridden global variable.
        unset($GLOBALS['USER']);
        unset($GLOBALS['cmi5launchsettings']);
    }


    /**
     * Test of the cmi5launch_create_course method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_create_course_pass()
    {
        // To determine the headers.
        $id = 0;
        $tenanttoken = "testtoken";
        
        //If we make filename an object with it's own get_content method, we can stub it out.
        $filename = new class { 
            public function get_content() {
                return "testfilecontents";
            }
        };

        // Player will return a string.
        $result = json_encode(array("statusCode" => "200",
            "message" => "testmessage")
        );
        
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post' ))
            ->getMock();

        // We will have the mock return a basic string, as it's not under test
        // the string just needs to be returned as is. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with('testfilecontents', 'http://test/launch.php/api/v1/course','zip', 'testtoken')
            ->willReturn($result);

        // Call the method under test. 
        $returnedresult =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);

        // This should be the same, since the 'error messages' function in cmi5 connector will test the 
        // 'result' and if it has 200, which we know it does, return it as is. 
         $this->assertSame($returnedresult, $result);
        // And the return should be a string (the original method returns what the player sends back).
        $this->assertIsString($returnedresult);
    }

    /**
     * Test of the cmi5launch_create_course method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_create_course_fail_with_message()
    {

        // Message we expect to be output.
        $expectedstring= "<br>Something went wrong creating the course. CMI5 Player returned 404 error. With message 'testmessage'.<br>";
        // Arguments to be passed to the method under test.
        $id = 0;
        $tenanttoken = "testtoken";
        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );
        
        //If we make filename an object with it's own get_content method, we can stub it out.
        $filename = new class { 
            public function get_content() {
                return "testfilecontents";
            }
        };

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        // We will have the mock return a fake message as if the player had a problem with request.
        // This should enable us to test the method under failing conditions. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with('testfilecontents', 'http://test/launch.php/api/v1/course', 'zip', 'testtoken')
            ->willReturn($errormessage);

        // Call the method under test. 
        $returnedresult =$csc->cmi5launch_create_course($id, $tenanttoken, $filename);

        // Result should be debug echo string and false.
        $this->assertNotTrue($returnedresult, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($expectedstring);
    }


    /**
     * Test of the cmi5launch_create_tenant method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_create_tenant_pass()
    {
        // Arguments to be passed to the method under test.
        $urltosend = "playerwebaddress";
        $username = "testname";
        $password = "testpassword";
        $newtenantname = "testtenantname";
        $data = array ('code' => 'testtenantname');
        // Encode data as it will be encoded when sent to player
        $data = json_encode($data);

        // This is the expected return value.
        $returnvalue = array(
            "code" => "testtenantname",
            "id" => 9
        );


        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new tenant.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        // We will have the mock return a basic string, as it's not under test.
        // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($data, 'playerwebaddress','json', 'testname', 'testpassword') // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out> 
            // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn('{
                "code": "testtenantname",
                "id": 9
                }'
            );

        //Call the method under test. 
        $result =$csc->cmi5launch_create_tenant($urltosend, $username, $password, $newtenantname);

        // And the return should be a string (the original method returns what the player sends back json-decoded or FALSE)
        $this->assertIsArray($result);
        $this->assertEquals( $returnvalue, $result);
    }

    /**
     * Test of the cmi5launch_create_tenant method with a error response from the player.
     * @return void
     */
    public function testcmi5launch_create_tenant_fail()
    {
        // Arguments to be passed to the method under test.
        $urltosend = "playerwebaddress";
        $username = "testname";
        $password = "testpassword";
        $newtenantname = "testtenantname";
        $data = array ('code' => 'testtenantname');
        // Encode data as it will be encoded when sent to player
        $data = json_encode($data);

        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong creating the tenant. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );
            
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();


        // We will have the mock return a fake message as if the player had a problem with request.
        // This will enable us to test the method under failing conditions. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($data, 'playerwebaddress', 'json', 'testname', 'testpassword')
            ->willReturn($errormessage);

        //Call the method under test. 
        $result =$csc->cmi5launch_create_tenant($urltosend, $username, $password, $newtenantname);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        // And it should output this error message
        $this->expectOutputString($expectedstring);
    }

    /**
     * * Test the retrieve_registration method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_get_pass()
    {
        // Arguments to be passed to the method under test.
        $id = 0;
        $registration = "testregistration";

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration/testregistration";

        // The player returns a string.
        $returnvalue = json_encode(array(
            "actor" => "testtenantname",
            "aus" => array(
                "testau1",
                "testau2"
            ),
            "code" => "testregistrationcode",
        ));

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_get'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with("Testtoken", $urltosend)
            ->willReturn($returnvalue);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_registration_with_get($registration, $id);

        // And the return should be a string.
        $this->assertIsString($result);
        $this->assertEquals($returnvalue, $result);
        }


    /**
     * * Test the retrieve_registration method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_get_fail()
    {
        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );
        
        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The arguments to be passed to the method under test. 
        $id = 0;
        $registration = "testregistration";

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration/testregistration";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_get'))
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with("Testtoken", $urltosend)
            ->willReturn($errormessage);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_registration_with_get($registration, $id);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($expectedstring);
        }

    /**
     * * Test the retrieve_registration_with_post method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_post_pass()
    {
        // The arguments to be passed to the method under test. 
        $id = 0;
        $courseid = 1;
        $filetype = "json";

        // The data to be passed to the mocked method.
        $data = array(
            "courseId" => $courseid, 
            "actor" => array(
                "account" =>  array (
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname"
                ),
            ),
        );

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration";

        // The player returns a string.
        $returnvalue = json_encode(array(
            "actor" => "testtenantname",
            "aus" => array(
                "testau1",
                "testau2"
            ),
            "code" => "testregistrationcode",
        ));

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($returnvalue);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_registration_with_post($courseid, $id);

        // And the return should be a string.
        $this->assertIsString($result);
        $this->assertEquals($result, "testregistrationcode");
    }

    /**
     * * Test the retrieve_registration_with_post method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_post_fail()
    {
        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The arguments to be passed to the method under test. 
        $id = 0;
        $courseid = 1;
        $filetype = "json";

        // The data to be passed to the mocked method.
        $data = array(
            "courseId" => $courseid, 
            "actor" => array(
                "account" =>  array (
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname"
                ),
            ),
        );

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_registration_with_post($courseid, $id);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($expectedstring);
    }


    /**
     * * Test the retrieve_token method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_token_pass()
    {
        // Arguments to be passed to the method under test.
        $url = "http://test/launch.php";
        $username = "testname";
        $filetype = "json";
        $password = "testpassword";
        $audience = "testaudience";
        $tenantid = 0;

        // The data to be passed to the mocked method.
        $data = array(
            "tenantId" => $tenantid,
            "audience" => $audience,
        );

        // The player returns a json string.
        $returnvalue = '{
            "token": "testtoken"
            }';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $url, $filetype, $username, $password)
            ->willReturn($returnvalue);

            //Call the method under test. 
            $result = $mockedclass->cmi5launch_retrieve_token($url, $username, $password, $audience, $tenantid);

            // And the return should be a string (the original method returns what the player sends back or FALSE.
            $this->assertIsString($result);
            $this->assertEquals($result, $returnvalue);
        }

    /**
     * * Test the retrieve_token method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_token_fail()
    {
        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong retrieving the token. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // Arguments to be passed to the method under test.
        $url = "http://test/launch.php";
        $username = "testname";
        $password = "testpassword";
        $audience = "testaudience";
        $tenantid = 0;

        // The data to be passed to the mocked method.
        $filetype = "json";
        $data = array(
            "tenantId" => $tenantid,
            "audience" => $audience,
        );


        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $url, $filetype, $username, $password)
            ->willReturn($errormessage);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_token($url, $username, $password, $audience, $tenantid);

        // Result should be debug echo string and false.
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        // And it should output this error message.
        $this->expectOutputString($expectedstring);
        }

    /**
     * * Test the retrieve_url (launchurl) method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_url_pass()
    {
        global $cmi5launchid;
        
        // Arguments to be passed to the method under test. 
        $id = $cmi5launchid;
        $auindex = 1;
        $filetype = "json";
        $returnurl = "https://testmoodle.com";
        $registrationid = "testregistrationid";

        //Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];

        // The data to be passed to the mocked method.
        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ),
            ),
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        );

        // This is the url the stubbed method shopuld receive.
        $urltosend =  $playerurl . "/api/v1/course/" . "1"  ."/launch-url/" . $auindex;

        // The player returns a string.
        $returnvalue = json_encode(array(
            "id" => 21,
            "launchMethod" => "AnyWindow",
            "url" => "http://testlaunchurl"
        ));

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn(($returnvalue));

        // Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_url($id, $auindex);

        // And the return should be an array because the method returns it json_decoded.
        $this->assertIsArray($result);
        $this->assertEquals($result, json_decode($returnvalue, true));
        }

    /**
     * * Test the retrieve_url (launchurl) method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_url_fail()
    {
        global $cmi5launchid;
        
        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong retrieving launch url. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The id to be passed to method under test. 
        $id = $cmi5launchid;
        $auindex = 1;
        $filetype = "json";
        
        //Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];

        $returnurl = "https://testmoodle.com";
        $registrationid = "testregistrationid";

        // The data to be passed to the mocked method.
        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ),
            ),
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        );

        // This is the url the stubbed method shopuld receive.
        $urltosend =  $playerurl . "/api/v1/course/" . "1"  ."/launch-url/" . $auindex;

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_post'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with(json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_url($id, $auindex);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($expectedstring);
    }

    /**
     * Test the send_request_to_cmi5_player_post method with one arg. 
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_one_arg()
    {
        // The player returns a string under normal circumstances.
        $returnvalue = json_encode(array(
            "statusCode" => 200,
            "Response" => "Successful Post",
        ));
    
        // The data to be passed to the mocked method.
        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ),
            ),
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        );

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";
        $contenttype = "application/json\r\n";

        //The arguments to pass in, in this case one, a pretend token.
        $token = "testtoken";

        // Options that should be built in the method, and we need to make sure it goes
        // to the right branch by matching it.
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'ignore_errors' => true,
                'header' => array("Authorization: Bearer ". $token,
                    "Content-Type: " .$contenttype .
                    "Accept: " . $contenttype),
                'content' => ($data),
            ),
        );
            
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_stream_and_send'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_stream_and_send')
            ->with($url, $options)
            ->willReturn($returnvalue) ;

        // Call the method under test.    
        $test = $mockedclass->cmi5launch_send_request_to_cmi5_player_post($data, $url, $filetype, $token);

        // And the return should be a string.
        $this->assertIsString($test);
        // And it should be the same as the return value.
        $this->assertEquals($test, $returnvalue);
    }

    /**
     * * Test the send_request_to_cmi5_player_post method with two args. 
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_two_args()
    {
        // The player returns a string under normal circumstances.
        $returnvalue = json_encode(array(
            "statusCode" => 200,
            "Response" => "Successful Post",
        ));
    
        // The data to be passed to the mocked method.
        $data = array(
            'actor' => array(
                'account' => array(
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ),
            ),
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        );

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";
        $contenttype = "application/json\r\n";

        //The arguments to pass in, in this case one, a pretend username and password.
        $username = "testname";
        $password = "testpassword";

        // Options that should be built in the method, and we need to make sure it goes
        // to the right branch by matching it.
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => array('Authorization: Basic '. base64_encode("$username:$password"),
                    "Content-Type: " .$contenttype .
                    "Accept: " . $contenttype),
                'content' => ($data),
            ),
        );
            
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_stream_and_send'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_stream_and_send')
            ->with($url, $options)
            ->willReturn($returnvalue) ;

        // Call the method under test.
        $test = $mockedclass->cmi5launch_send_request_to_cmi5_player_post($data, $url, $filetype, $username, $password);

        // And the return should be a string.
        $this->assertIsString($test);
        // And it should be the same as the return value.
        $this->assertEquals($test, $returnvalue);
    }

    /**
     * * Test the send_request_to_cmi5_player_get
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_get_pass()
    {

        // The player returns a string under normal circumstances.
        $returnvalue = json_encode(array(
            "statusCode" => 200,
            "Response" => "Successful Post",
            ));

        //The arguments to pass in.
        $token = "testtoken";
        $url = "http://test/url.com";

        // Options that should be built in the method, we build here to pass to mock.
        $options = array (
            'http' => array (
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => array ("Authorization: Bearer ". $token,
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"),
            ),
        );
            
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_stream_and_send'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_stream_and_send')
            ->with($url, $options)
            ->willReturn($returnvalue) ;

        // Call the method under test.
        $test = $mockedclass->cmi5launch_send_request_to_cmi5_player_get($token, $url);

        // And the return should be an array.
        $this->assertIsArray($test);
        // And it should be the same as the return value.
        $this->assertEquals($test, json_decode($returnvalue, true) );

    }

    /**
     * * Test the send_request_to_cmi5_player_get
     * This is what is used to retrieve info like tenant info.
     * This is meant to fail, we want it to act as if the player is unreachable
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_get_fail()
    {

        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        //The arguments to pass in, in this case one, a pretend token.
        $token = "testtoken";
        $url = "http://test/url.com";

        // Options that should be built in the method, and we need to make sure it goes
        // to the right branch by matching it.
        $options = array (
            'http' => array (
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => array ("Authorization: Bearer ". $token,
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"),
            ),
        );

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_stream_and_send'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_stream_and_send')
            ->with($url, $options)
            ->willReturn(json_encode($errormessage) ) ;

        $test = $mockedclass->cmi5launch_send_request_to_cmi5_player_get($token, $url);

        // And the return should be an array since the method under test returns player message decoded.
        $this->assertIsArray($test);
        // And it should be the same as the return value.
        $this->assertEquals($test, $errormessage);
    }

    /**
     * * Test the retrieve_session method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_session_info_from_player_pass()
    {
        global $cmi5launchid;

        // The id to be passed to method under test. 
        $id = $cmi5launchid;

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];
        $token = $settings['cmi5launchtenanttoken'];
        $sessionid = "testsessionid";

        // This is the url the stubbed method shopuld receive.
        $urltosend =  $playerurl . "/api/v1/session/" . $sessionid;

        // The player returns a string, but the mocked method returns an array.
        $returnvalue = array(
            "statusCode" => 200,
            "launchMethod" => "AnyWindow",
            "url" => "http://testlaunchurl"
        );

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_get'))
            ->getMock();

        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($token, $urltosend)
            ->willReturn(json_encode($returnvalue));


        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_session_info_from_player($sessionid, $id);

        // And the return should be an array (the original method returns what the player sends back json-decoded or FALSE)
        $this->assertIsString($result);
        $this->assertEquals($result, json_encode($returnvalue));
    }

    /**
     * * Test the retrieve_session (launchurl) method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_session_info_from_player_fail()
    {
        global $cmi5launchid;

        // Error message for stubbed method to return.
        $errormessage = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        // The expected error message to be output.
        $expectedstring= "<br>Something went wrong retrieving session info. CMI5 Player returned 404 error. With message 'testmessage'.<br>";
        
        // The id to be passed to method under test. 
        $id = $cmi5launchid;

        //Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];
        $sessionid = "testsessionid";
        $token = $settings['cmi5launchtenanttoken'];
        // This is the url the stubbed method shopuld receive.
        $urltosend =  $playerurl . "/api/v1/session/" . $sessionid;

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(array('cmi5launch_send_request_to_cmi5_player_get'))
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($token, $urltosend)
            ->willReturn(json_encode($errormessage));

        //Call the method under test. 
        $result = $mockedclass->cmi5launch_retrieve_session_info_from_player($sessionid, $id);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($expectedstring);
    }

    /**
     * * Test the cmi5launch_connectors method first if branch, which is when the player is not communicating.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_one()
    {
        // The function takes a response mmessage and a string of 'type' which is what 
        // function failed, to fill in error message.
        // In the case where the player is not on, it would simply be 'false'.
        $response = false;
        $type = "retreiving 'item'";

        // The expected error message to be output.
        $errormessage ="<br>Something went wrong " . $type . ". CMI5 Player is not communicating. Is it running?<br>";

        // Call the method under test.
        $result = cmi5_connectors::cmi5launch_connectors_error_message($response, $type);

        // Result should be debug echo string and false
        $this->assertNotTrue($result, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString($errormessage);
    }

    /**
     * * Test the cmi5launch_connectors method second if branch, which is when the player returns an error.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_two()
    {
        // The function takes a response message and a string of 'type' which is what failed to be fetched.
        $type = "retreiving 'item'";

        // Error message for stubbed method to return.
        $response = array("statusCode" => "404",  "error" => "Not Found","message" => "testmessage" );

        // The expected error message to be output.
        $expectedstring ="<br>Something went wrong " . $type . ". CMI5 Player returned "  . $response['statusCode'] . " error. With message '" . $response['message'] . "'.<br>";

        // Call the method under test.
        $result = cmi5_connectors::cmi5launch_connectors_error_message($response, $type);
    
         // Result should be debug echo string and false
         $this->assertNotTrue($result, "Expected retrieved object to be false");
         //And it should output this error message
         $this->expectOutputString($expectedstring);    
    }

    /**
     * * Test the cmi5launch_connectors method last if branch, which is when the player returns a 200
     * message with requested info. No errors.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_three()
    {
        // The function takes a response message and a string of 'type' which is what failed to be fetched.
        $type = "retreiving 'item'";

        // The player returns a string under normal circumstances.
        $response = json_encode(array(
        "statusCode" => 200,
        "Response" => "Successful Post",
         ));

        // Call the method under test.
        $result = cmi5_connectors::cmi5launch_connectors_error_message($response, $type);
    
         // Result should be debug echo string and false
         $this->assertTrue($result, "Expected retrieved object to be true");
    }

}
    