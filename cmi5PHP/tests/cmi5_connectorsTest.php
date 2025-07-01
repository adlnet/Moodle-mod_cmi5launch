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

use mod_cmi5launch\local\cmi5launch_helpers;
use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\test\cmi5TestHelpers;
use mod_cmi5launch\local\playerException;

require_once( "cmi5TestHelpers.php");
require_once($CFG->dirroot . '/mod/cmi5launch/lib.php');

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
 *  * @package mod_cmi5launch
 * @package mod_cmi5launch
 */
class cmi5_connectorsTest extends TestCase {

    // Use setupbefore and after class sparingly. In this case, we don't want to use it to connect tests, but rather to
    // 'prep' the test db with values the tests can run against.
    public static function setUpBeforeClass(): void {
        global $DB, $cmi5launch, $cmi5launchid;

        // Make a fake cmi5 launch record.
        $cmi5launchid = maketestcmi5launch();

    }

    public static function tearDownAfterClass(): void {
        global $DB, $cmi5launch,  $cmi5launchid;

        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Restore overridden global variable.
        unset($USER);
        unset($cmi5launchsettings);
        unset($cmi5launch);
        unset($cmi5launchid);
        unset($testcourseid);
        unset($testcourseausids);
        unset($testcoursesessionids);
        
        // Delete the test record.
        // deletetestcmi5launch($cmi5launchid);

    }

    protected function setUp(): void {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = ["cmi5launchtenanttoken" => "Testtoken", "cmi5launchplayerurl" => "http://test/launch.php", "cmi5launchcustomacchp" => "http://testhomepage.com",
            "cmi5launchbasicname" => 'testname', "cmi5launchbasepass" => "testpassword"];

        // Override global variable and function so that it returns test data.
        $USER = new \stdClass();
        $USER->username = "testname";
        $USER->id = 10;

        $testcourseid = maketestcourse($cmi5launchid);
    }

    protected function tearDown(): void {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;
        // Restore overridden global variable.
        unset($USER);
        unset($cmi5launchsettings);

        deletetestcmi5launch_usercourse($testcourseid);
    }


    /**
     * Test of the cmi5launch_create_course method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_create_course_pass() {

        // Function that will be called in function under test.
        $testfunction = 'cmi5launch_stream_and_send';
        // To determine the headers.
        $id = 0;
        $tenanttoken = "testtoken";

        // If we make filename an object with it's own get_content method, we can stub it out.
        $filename = new class {
            public function get_content() {
                return "testfilecontents";
            }
        };

        // Player will return a string.
        $result = json_encode(["statusCode" => "200",
            "message" => "testmessage"]
        );

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post' ])
            ->getMock();

        // We will have the mock return a basic string, as it's not under test
        // the string just needs to be returned as is. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, 'testfilecontents', 'http://test/launch.php/api/v1/course', 'zip', 'testtoken')
            ->willReturn($result);

        // Call the method under test.
        $returnedresult = $csc->cmi5launch_create_course($id, $tenanttoken, $filename);

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
    public function testcmi5launch_create_course_fail_with_message() {

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

         // Arguments to be passed to the method under test.
        $id = 0;
        $tenanttoken = "testtoken";
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // If we make filename an object with it's own get_content method, we can stub it out.
        $filename = new class {
            public function get_content() {
                return "testfilecontents";
            }
        };
        $test = false;
             // Expected exceptions
             $exceptionmessage = "Player communication error. Something went wrong creating the course. CMI5 Player returned 404 error. With message 'testmessage'.";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // We will have the mock return a fake message as if the player had a problem with request.
        // This should enable us to test the method under failing conditions. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, 'testfilecontents', 'http://test/launch.php/api/v1/course', 'zip', 'testtoken')
            ->willReturn($errormessage);

            // Wait, i bet this is being thrown in the cmi5 connectors error message func and so we need to catch
            // the correct output not an exception
            $this->expectExceptionMessage($exceptionmessage);
            $this->expectException(playerException::class);

            // Call the method under test.
        $returnedresult = $csc->cmi5launch_create_course($id, $tenanttoken, $filename);

    }

    /**
     * Test of the cmi5launch_create_course method with a failed response from the player.
     * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
     * @return void
     */
    public function testcmi5launch_create_course_fail_with_exception() {

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

         // Arguments to be passed to the method under test.
        $id = 0;
        $tenanttoken = "testtoken";
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // If we make filename an object with it's own get_content method, we can stub it out.
        $filename = new class {
            public function get_content() {
                return "testfilecontents";
            }
        };
        $test = false;
             // Expected exceptions
             $exceptionmessage = "Player communication error. Something went wrong creating the course.";

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // We will have the mock return a fake message as if the player had a problem with request.
        // This should enable us to test the method under failing conditions. We do expect create_course to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, 'testfilecontents', 'http://test/launch.php/api/v1/course', 'zip', 'testtoken')
            ->willReturn($errormessage);

            // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, 'creating the course.') // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out>
        // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn(false);

            // Wait, i bet this is being thrown in the cmi5 connectors error message func and so we need to catch
            // the correct output not an exception
            $this->expectExceptionMessage($exceptionmessage);
            $this->expectException(playerException::class);

            // Call the method under test.
        $returnedresult = $csc->cmi5launch_create_course($id, $tenanttoken, $filename);

    }
    /**
     * Test of the cmi5launch_create_tenant method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_create_tenant_pass() {

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Arguments to be passed to the method under test.
        $newtenantname = "newtenantname";
        $data = ['code' => 'newtenantname'];
        // Encode data as it will be encoded when sent to player
        $data = json_encode($data);

        // Message for stubbed method to return.
        $returnvalue = json_encode(["statusCode" => "200",  "code" => "newtenantname", "id" => "9" ] );

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new tenant.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // We will have the mock return a basic string, as it's not under test.
        // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, $data, 'http://test/launch.php/api/v1/tenant', 'json', 'testname', 'testpassword') // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out>
            // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn($returnvalue);

        // Call the method under test.
        $result = $csc->cmi5launch_create_tenant( $newtenantname);

        // And the return should be a string (the original method returns what the player sends back json-decoded or FALSE)
        $this->assertIsString($result);
        $this->assertEquals( $returnvalue, $result);

    }


    /**
     * Test of the cmi5launch_create_tenant method with a failed response from the player. Should trigger an exception.
     * @return void
     */
    public function testcmi5launch_create_tenant_fail() {

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Arguments to be passed to the method under test.

        $newtenantname = "newtenantname";
        $data = ['code' => 'newtenantname'];
        // Encode data as it will be encoded when sent to player
        $data = json_encode($data);

        // Message for stubbed method to return.
        $errormessage = ["statusCode" => "400",  "message" => "website not found", "id" => "9" ];

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new tenant.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // We will have the mock return a basic string, as it's not under test.
        // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, $data, 'http://test/launch.php/api/v1/tenant', 'json', 'testname', 'testpassword') // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out>
            // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn($errormessage);

            // Expected exceptions
             $exceptionmessage = "Player communication error. Something went wrong creating the tenant CMI5 Player returned 400 error. With message 'website not found'.";

            // the correct output not an exception
            $this->expectExceptionMessage($exceptionmessage);
            $this->expectException(playerException::class);

        // Call the method under test.
        $result = $csc->cmi5launch_create_tenant( $newtenantname);

    }


    /**
     * Test of the cmi5launch_create_tenant method with a failed response from the player. Should trigger an exception.
     * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
     * @return void
     */
    public function testcmi5launch_create_tenant_fail_2() {

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Arguments to be passed to the method under test.

        $newtenantname = "newtenantname";
        $data = ['code' => 'newtenantname'];
        // Encode data as it will be encoded when sent to player
        $data = json_encode($data);

        // Message for stubbed method to return.
        $errormessage = ["statusCode" => "400",  "message" => "website not found", "id" => "9" ];

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other methods.
        // Create a mock of the send_request class as we don't actually want
        // to create a new tenant.
        $csc = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // We will have the mock return a basic string, as it's not under test.
        // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, $data, 'http://test/launch.php/api/v1/tenant', 'json', 'testname', 'testpassword') // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out>
            // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn($errormessage);
            // We will have the mock return a basic string, as it's not under test.
        // The string just needs to be returned as is. We do expect create_tenant to only call this once.
        $csc->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, "creating the tenant") // for tomorrow, is thi failing because with only evaluates strings? Like do we need to string the array out>
        // IRL it returns something that needs to be json decoded, so lets pass somethin that is encoded>
            ->willReturn(false);

            // Expected exceptions
             $exceptionmessage = "Player communication error. Something went wrong creating the tenant.";

            // the correct output not an exception
            $this->expectExceptionMessage($exceptionmessage);
            $this->expectException(playerException::class);

        // Call the method under test.
        $result = $csc->cmi5launch_create_tenant( $newtenantname);

    }
    /**
     * * Test the retrieve_registration method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_get_pass() {
        // Arguments to be passed to the method under test.
        $id = 0;
        $registration = "testregistration";

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration/testregistration";

        // The player returns a string.
        $returnvalue = json_encode([
            "actor" => "testtenantname",
            "aus" => [
                "testau1",
                "testau2",
            ],
            "code" => "testregistrationcode",
        ]);

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, "Testtoken", $urltosend)
            ->willReturn($returnvalue);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_get($registration, $id);

        // And the return should be a string.
        $this->assertIsString($result);
        $this->assertEquals($returnvalue, $result);
    }


    /**
     * * Test the retrieve_registration method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_get_fail() {
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The expected error message to be output.
        // $expectedstring= "<br>Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The arguments to be passed to the method under test.
        $id = 0;
        $registration = "testregistration";

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration/testregistration";

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get'])
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, "Testtoken", $urltosend)
            ->willReturn($errormessage);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'.";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_get($registration, $id);

    }

    /**
     * * Test the retrieve_registration method with a failed response from the player.
     * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_get_fail_2() {
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The expected error message to be output.
        // $expectedstring= "<br>Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The arguments to be passed to the method under test.
        $id = 0;
        $registration = "testregistration";

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration/testregistration";

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get', 'cmi5launch_connectors_error_message'])
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, "Testtoken", $urltosend)
            ->willReturn($errormessage);

        $mockedclass->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, "retrieving the registration.")
            ->willReturn(false);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the registration information.";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_get($registration, $id);

    }

    /**
     * * Test the retrieve_registration_with_post method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_post_pass() {
        // The arguments to be passed to the method under test.
        $id = 0;
        $courseid = 1;
        $filetype = "json";

        // The data to be passed to the mocked method.
        $data = [
            "courseId" => $courseid,
            "actor" => [
                'objectType' => 'Agent',
                "account" => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration";

        // The player returns a string.
        $returnvalue = json_encode([
            "actor" => "testtenantname",
            "aus" => [
                "testau1",
                "testau2",
            ],
            "code" => "testregistrationcode",
        ]);

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($returnvalue);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_post($courseid, $id);

        // And the return should be a string.
        $this->assertIsString($result);
        $this->assertEquals($result, "testregistrationcode");
    }

    /**
     * * Test the retrieve_registration_with_post method with a failed response from the player. Should throw exception.
     * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_post_fail_2() {
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];
        // The arguments to be passed to the method under test.
        $id = 0;
        $courseid = 1;
        $filetype = "json";

        // The data to be passed to the mocked method.
        $data = [
            "courseId" => $courseid,
            "actor" => [
                'objectType' => 'Agent',
                "account" => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration";

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        $mockedclass->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, "retrieving the registration.")
            ->willReturn(false);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the registration information.";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_post($courseid, $id);

    }
    /**
     * * Test the retrieve_registration_with_post method with a failed response from the player. Should throw exception.
     * @return void
     */
    public function testcmi5launch_retrieve_registration_with_post_fail() {
        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];
        // The arguments to be passed to the method under test.
        $id = 0;
        $courseid = 1;
        $filetype = "json";

        // The data to be passed to the mocked method.
        $data = [
            "courseId" => $courseid,
            "actor" => [
                'objectType' => 'Agent',
                "account" => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = "http://test/launch.php/api/v1/registration";

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the registration. CMI5 Player returned 404 error. With message 'testmessage'";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_registration_with_post($courseid, $id);

    }

    /**
     * * Test the retrieve_token method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_token_pass() {
        global $CFG, $cmi5launchid;

        $settings = cmi5launch_settings($cmi5launchid);
        // Arguments to be passed to the method under test.
        // $url = "http://test/launch.php";
        // $username = "testname";
        $filetype = "json";
        // $password = "testpassword";
        $audience = "testaudience";
        $tenantid = 0;
        $username = $settings['cmi5launchbasicname'];
        $url = $settings['cmi5launchplayerurl'] . "/api/v1/auth";
        $password = $settings['cmi5launchbasepass'];
        // The data to be passed to the mocked method.
        $data = [
            "tenantId" => $tenantid,
            "audience" => $audience,
        ];

        // The player returns a json string.
        $returnvalue = "testtoken";

        $playervalue = ["statusCode" => "200",  "token" => "testtoken", "message" => "testmessage" ];

         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $url, $filetype, $username, $password)
            ->willReturn(json_encode($playervalue));

            // Call the method under test.
            $result = $mockedclass->cmi5launch_retrieve_token($audience, $tenantid);

            // And the return should be a string (the original method returns what the player sends back or FALSE.
            $this->assertIsString($result);
            $this->assertEquals($result, $returnvalue);
    }

    /**
     * * Test the retrieve_token method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_token_fail() {
        global $CFG, $cmi5launchid;
        $settings = cmi5launch_settings($cmi5launchid);

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // Arguments to be passed to the method under test.
        $url = $settings['cmi5launchplayerurl'] . '/api/v1/auth';

        $audience = "testaudience";
        $tenantid = 0;

        // $actor = $USER->username;
        $username = $settings['cmi5launchbasicname'];
        $password = $settings['cmi5launchbasepass'];

        // The data to be passed to the mocked method.
        $filetype = "json";
        $data = [
            "tenantId" => $tenantid,
            "audience" => $audience,
        ];
         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $url, $filetype, $username, $password)
            ->willReturn($errormessage);

             // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the tenant token. CMI5 Player returned 404 error. With message 'testmessage'";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_token($audience, $tenantid);

    }

            /**
             * * Test the retrieve_token method with a failed response from the player.
             * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
             * @return void
             */
    public function testcmi5launch_retrieve_token_fail_2() {
        global $CFG, $cmi5launchid;
        $settings = cmi5launch_settings($cmi5launchid);

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // Arguments to be passed to the method under test.
        $url = $settings['cmi5launchplayerurl'] . '/api/v1/auth';

        $audience = "testaudience";
        $tenantid = 0;

        // $actor = $USER->username;
        $username = $settings['cmi5launchbasicname'];
        $password = $settings['cmi5launchbasepass'];

        // The data to be passed to the mocked method.
        $filetype = "json";
        $data = [
            "tenantId" => $tenantid,
            "audience" => $audience,
        ];
         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors' )
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $url, $filetype, $username, $password)
            ->willReturn($errormessage);

            // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, 'retrieving the tenant token.')
            ->willReturn(false);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the tenant token.";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_token($audience, $tenantid);

    }

    /**
     * * Test the retrieve_url (launchurl) method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_url_pass() {
        global $cmi5launchid;

        // Arguments to be passed to the method under test.
        $id = $cmi5launchid;
        $auindex = 1;
        $filetype = "json";
        $returnurl = "https://testmoodle.com";
        $registrationid = "testregistrationid";

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/course/" . "1"  ."/launch-url/" . $auindex;

        // The player returns a string.
        $returnvalue = json_encode([
            "id" => 21,
            "launchMethod" => "AnyWindow",
            "url" => "http://testlaunchurl",
        ]);
         // Function that will be called in function under test.
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
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
    public function testcmi5launch_retrieve_url_fail() {
        global $cmi5launchid;

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The expected error message to be output.
        $expectedstring = "<br>Something went wrong retrieving launch url. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The id to be passed to method under test.
        $id = $cmi5launchid;
        $auindex = 1;
        $filetype = "json";

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];

        $returnurl = "https://testmoodle.com";
        $registrationid = "testregistrationid";

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/course/" . "1"  ."/launch-url/" . $auindex;

        // Function that will be called in function under test.
        $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the launch url from player. CMI5 Player returned 404 error. With message 'testmessage'";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_url($id, $auindex);

    }

        /**
         * * Test the retrieve_url (launchurl) method with a failed response from the player.
         * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
         * @return void
         */
    public function testcmi5launch_retrieve_url_fail_2() {
        global $cmi5launchid;

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The id to be passed to method under test.
        $id = $cmi5launchid;
        $auindex = 1;
        $filetype = "json";

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];

        $returnurl = "https://testmoodle.com";
        $registrationid = "testregistrationid";

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => $returnurl,
            'reg' => $registrationid,
        ];

        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/course/" . "1"  ."/launch-url/" . $auindex;

        // Function that will be called in function under test.
        $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the other.
        // Create a mock of the send_request class as we don't actually want
        // to create a new course in the player.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_post', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_post')
            ->with($testfunction, json_encode($data), $urltosend, $filetype, "Testtoken")
            ->willReturn($errormessage);

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_connectors_error_message')
            ->with($errormessage, 'retrieving the launch url from player.')
            ->willReturn(false);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the launch url from player.";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_url($id, $auindex);

    }

    /**
     * Test the send_request_to_cmi5_player_post method with one arg.
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_one_arg() {
        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass';
        // Which returns the 'options' parameter passed to it.
        // The player returns a string under normal circumstances.
        $returnvalue = json_encode([
            "statusCode" => 200,
            "Response" => "Successful Post",
        ]);

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        ];

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";

        // The arguments to pass in, in this case one, a pretend token.
        $token = "testtoken";

             // Expected exceptions
             $exceptionmessage = "Player communication error. Error communicating with player, sending a POST request.";

             // Expected exceptions and messages
          // $this->expectExceptionMessage($exceptionmessage);
         // $this->expectException(playerException::class);

        // Class for function under test.
        $helper = new cmi5_connectors;
        // Call the method under test.
        $test = $helper->cmi5launch_send_request_to_cmi5_player_post($testfunction, $data, $url, $filetype, $token);

        // If the right message is displayed the try/catch worked.
        $this->assertEquals($returnvalue, $test, "The return value should be the same as the return value from the mocked method.");

    }

        /**
         * Test the send_request_to_cmi5_player_post method with one arg.
         * Test the thrown exception.
         * @return void
         */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_one_arg_fail() {
        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_excep';
        // Which returns the 'options' parameter passed to it.

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        ];

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";

        // The arguments to pass in, in this case one, a pretend token.
        $token = "testtoken";

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong communicating with player, sending or crafting a POST request: ";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Class for function under test.
        $helper = new cmi5_connectors;
        // Call the method under test.
        // Note: by not sending an actual function, this will cause an exception and allow testing of try/catch and error override.
        $test = $helper->cmi5launch_send_request_to_cmi5_player_post('testfunction', $data, $url, $filetype, $token);
    }


    /**
     * * Test the send_request_to_cmi5_player_post method with two args.
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_post_with_two_args() {

        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass';

        // The player returns a string under normal circumstances.
        $returnvalue = json_encode([
            "statusCode" => 200,
            "Response" => "Successful Post",
        ]);

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        ];

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";
        $contenttype = "application/json\r\n";

        // The arguments to pass in, in this case one, a pretend username and password.
        $username = "testname";
        $password = "testpassword";

        $helper = new cmi5_connectors;
        $test = $helper->cmi5launch_send_request_to_cmi5_player_post($testfunction, $data, $url, $filetype, $username, $password);

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
    public function testcmi5launch_send_request_to_cmi5_player_post_with_two_args_fail() {

        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass';

        // The player returns a string under normal circumstances.
        $returnvalue = json_encode([
            "statusCode" => 200,
            "Response" => "Successful Post",
        ]);

        // The data to be passed to the mocked method.
        $data = [
            'actor' => [
                'objectType' => 'Agent',
                'account' => [
                    "homePage" => "http://testhomepage.com",
                    "name" => "testname",
                ],
            ],
            'returnUrl' => 'returnurl',
            'reg' => 'registrationid',
        ];

        // Fake arguments to pass in.
        $filetype = "json";
        $url = "http://test/url.com";
        $contenttype = "application/json\r\n";

        // The arguments to pass in, in this case one, a pretend username and password.
        $username = "testname";
        $password = "testpassword";

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong communicating with player, sending or crafting a POST request: ";

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Class for function under test.
        $helper = new cmi5_connectors;
        $test = $helper->cmi5launch_send_request_to_cmi5_player_post('testfunction', $data, $url, $filetype, $username, $password);

    }


    /**
     * * Test the send_request_to_cmi5_player_get
     * This is what is used to retrieve info like tenant info.
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_with_get_pass() {

        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_pass';

        // The player returns a string under normal circumstances.
        $returnvalue = json_encode([
            "statusCode" => 200,
            "Response" => "Successful Post",
            ]);

        // The arguments to pass in.
        $token = "testtoken";
        $url = "http://test/url.com";

        // Get class and the function under test.
        $helper = new cmi5_connectors;
        $test = $helper->cmi5launch_send_request_to_cmi5_player_get($testfunction, $token, $url);

        // And the return should be an array.
        $this->assertIsString($test);
        // And it should be the same as the return value.
        $this->assertEquals($test, $returnvalue, true);

    }

    /**
     * * Test the send_request_to_cmi5_player_get
     * This is what is used to retrieve info like tenant info.
     * This is meant to fail, we want it to act as if the player is unreachable
     * @return void
     */
    public function testcmi5launch_send_request_to_cmi5_player_with_get_fail() {
           // We send the TEST function to the function under test now!
           $testfunction = 'cmi5Test\cmi5launch_test_stream_and_send_excep';

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The arguments to pass in, in this case one, a pretend token.
        $token = "testtoken";
        $url = "http://test/url.com";

        // Options that should be built in the method, and we need to make sure it goes
        // to the right branch by matching it.
        $options = [
            'http' => [
                'method'  => 'GET',
                'ignore_errors' => true,
                'header' => ["Authorization: Bearer ". $token,
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"],
            ],
        ];
          // Expected exceptions
              $exceptionmessage = "Player communication error. Something went wrong communicating with player, sending or crafting a GET request: ";

            // Expected exceptions and messages
            $this->expectExceptionMessage($exceptionmessage);
            $this->expectException(playerException::class);

                    // This is the SUT?
        $helper = new cmi5_connectors;
        $get = $helper->cmi5launch_get_send_request_to_cmi5_player_get();

        // $test = $mockedclass->cmi5launch_send_request_to_cmi5_player_get($token, $url);
        $test = $get($testfunction, $token, $url);

    }



    /**
     * * Test the retrieve_session method with a successful response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_session_info_from_player_pass() {
        global $cmi5launchid;

        // The id to be passed to method under test.
        $id = $cmi5launchid;

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];
        $token = $settings['cmi5launchtenanttoken'];
        $sessionid = "testsessionid";

        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/session/" . $sessionid;

        // The player returns a string, but the mocked method returns an array.
        $returnvalue = json_encode([
            "statusCode" => 200,
            "launchMethod" => "AnyWindow",
            "url" => "http://testlaunchurl",
        ]);

         // We send the TEST function to the function under test now!
         $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, $token, $urltosend)
            ->willReturn(($returnvalue));

        // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_session_info_from_player($sessionid, $id);

        // And the return should be an array (the original method returns what the player sends back json-decoded or FALSE)
        $this->assertIsString($result);
        $this->assertEquals($result, ($returnvalue));
    }

    /**
     * * Test the retrieve_session (launchurl) method with a failed response from the player.
     * @return void
     */
    public function testcmi5launch_retrieve_session_info_from_player_fail() {
        global $cmi5launchid;

        // Error message for stubbed method to return.
        $errormessage = json_encode(
            ["statusCode" => "404",
            "error" => "Not Found",
            "message" => "testmessage" ]);

        // The id to be passed to method under test.
        $id = $cmi5launchid;

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];
        $sessionid = "testsessionid";
        $token = $settings['cmi5launchtenanttoken'];
        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/session/" . $sessionid;

        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, $token, $urltosend)
            ->willReturn($errormessage);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the session information. CMI5 Player returned 404 error. With message 'testmessage'.";

          // Expected exceptions and messages
          $this->expectExceptionMessage($exceptionmessage);
          $this->expectException(playerException::class);

              // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_session_info_from_player($sessionid, $id);

    }

        /**
         * * Test the retrieve_session (launchurl) method with a failed response from the player.
         * This one tests if resulttest is false. This path shouldnt be able to be reached but is here to test the failsafe.
         * @return void
         */
    public function testcmi5launch_retrieve_session_info_from_player_fail_excep() {
        global $cmi5launchid;

        // Error message for stubbed method to return.
        $errormessage = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The expected error message to be output.
        $expectedstring = "<br>Something went wrong retrieving session information. CMI5 Player returned 404 error. With message 'testmessage'.<br>";

        // The id to be passed to method under test.
        $id = $cmi5launchid;

        // Retrieve settings like the method under test will.
        $settings = cmi5launch_settings($id);
        $playerurl = $settings['cmi5launchplayerurl'];
        $sessionid = "testsessionid";
        $token = $settings['cmi5launchtenanttoken'];
        // This is the url the stubbed method shopuld receive.
        $urltosend = $playerurl . "/api/v1/session/" . $sessionid;

        // We send the TEST function to the function under test now!
        $testfunction = 'cmi5launch_stream_and_send';

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\cmi5_connectors')
            ->onlyMethods(['cmi5launch_send_request_to_cmi5_player_get', 'cmi5launch_connectors_error_message'])
            ->getMock();

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_send_request_to_cmi5_player_get')
            ->with($testfunction, $token, $urltosend)
            ->willReturn(($errormessage));

            // Mock returns json encoded data, as it would be from the player.
            $mockedclass->expects($this->once())
                ->method('cmi5launch_connectors_error_message')
                ->with($errormessage, 'retrieving the session information.')
                ->willReturn(false);

        // Expected exceptions
        $exceptionmessage = "Player communication error. Something went wrong retrieving the session information.";

          // Expected exceptions and messages
          $this->expectExceptionMessage($exceptionmessage);
          $this->expectException(playerException::class);

              // Call the method under test.
        $result = $mockedclass->cmi5launch_retrieve_session_info_from_player($sessionid, $id);

    }

    /**
     * * Test the cmi5launch_connectors method first if branch, which is when the player is not communicating.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_one() {
        // The function takes a response mmessage and a string of 'type' which is what
        // function failed, to fill in error message.
        // In the case where the player is not on, it would simply be 'false'.
        $response = false;
        $type = "retreiving 'item'";

        $helper = new cmi5_connectors;
        $error = $helper->cmi5launch_get_connectors_error_message();

        // The expected error message to be output.
        $exceptionmessage = "Something went wrong " . $type . " CMI5 Player is not communicating. Is it running?";

          // Expected exceptions and messages
          $this->expectExceptionMessage($exceptionmessage);
          $this->expectException(playerException::class);

        // Call the method under test.
        $result = $error($response, $type);

    }

    /**
     * * Test the cmi5launch_connectors method second if branch, which is when the player returns an error.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_two() {
        // The function takes a response message and a string of 'type' which is what failed to be fetched.
        $type = "retreiving 'item'";

        // Error message for stubbed method to return.
        $response = ["statusCode" => "404",  "error" => "Not Found", "message" => "testmessage" ];

        // The expected error message to be output.
        $exceptionmessage = "Something went wrong " . $type . " CMI5 Player returned "  . $response['statusCode'] . " error. With message '" . $response['message'] . "'.";

        $helper = new cmi5_connectors;
        $error = $helper->cmi5launch_get_connectors_error_message();

        // Expected exceptions and messages
        $this->expectExceptionMessage($exceptionmessage);
        $this->expectException(playerException::class);

        // Call the method under test.
        $result = $error($response, $type);

    }

    /**
     * * Test the cmi5launch_connectors method last if branch, which is when the player returns a 200
     * message with requested info. No errors.
     * @return void
     */
    public function testcmi5launch_connectors_error_message_path_three() {
        // The function takes a response message and a string of 'type' which is what failed to be fetched.
        $type = "retreiving 'item'";

        // The player returns a string under normal circumstances.
        $response = json_encode([
        "statusCode" => 200,
        "Response" => "Successful Post",
         ]);

         $helper = new cmi5_connectors;
         $error = $helper->cmi5launch_get_connectors_error_message();

        // Call the method under test.
        $result = $error($response, $type);

         // Result should be debug echo string and false
         $this->assertTrue($result, "Expected retrieved object to be true");
    }


}

