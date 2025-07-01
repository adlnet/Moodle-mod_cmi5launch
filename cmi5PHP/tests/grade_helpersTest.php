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

use mod_cmi5launch\local\grade_helpers;
use mod_cmi5launch\local\nullException;
use PHPUnit\Framework\TestCase;

require_once( "cmi5TestHelpers.php");

/**
 * Tests for grade_helpers class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *  * @package mod_cmi5launch
 * @package mod_cmi5launch
 */
class grade_helpersTest extends TestCase {



    // Use setupbefore and after class sparingly. In this case, we don't want to use it to connect tests, but rather to
    // 'prep' the test db with values the tests can run against.
    public static function setUpBeforeClass(): void {

    }

    public static function tearDownAfterClass(): void {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Restore overridden global variable.
        unset($USER);
        unset($cmi5launchsettings);
        unset($cmi5launch);
        unset($cmi5launchid);
        unset($testcourseid);
        unset($testcourseausids);
        unset($testcoursesessionids);

    }

    protected function setUp(): void {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Make a fake cmi5 launch record.
        $cmi5launchid = maketestcmi5launch();

        // Assign the new id as the cmi5launch id.
        $cmi5launch->id = $cmi5launchid;
        // Update db
        $DB->update_record('cmi5launch', $cmi5launch);
        // Gradetypes for ref
        // GRADE_AUS_CMI5 = 0.
        // GRADE_HIGHEST_CMI5 = 1.
        // GRADE_AVERAGE_CMI5 =  2.
        // GRADE_SUM_CMI5 = 3.
        $cmi5launchsettings = [
            "cmi5launchlrsendpofloat" => "Test LRS pofloat",
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

        // Make test course, AUs and sessions.
        $testcourseid = maketestcourse($cmi5launchid);
        $testcourseausids = maketestaus($testcourseid);
        $testcoursesessionids = maketestsessions();

        // Assign the sessions to AUs.
        $newaus = assign_sessions_to_aus($testcourseausids, $testcoursesessionids);

        // Assign the AUs to the course.
        assign_aus_to_courses($testcourseid, $testcourseausids);

        // Delete testcoursesessionids.
        deletetestcmi5launch_sessions($testcoursesessionids);
    }

    protected function tearDown(): void {
        global $sessionids;
        deletetestcmi5launch_sessions($sessionids);
    }


    /**
     * Test of the cmi5launch_average_grade method.
     * This takes scores and averages them.
     * // We need to test with scores being a string and array
     * @return void
     */
    public function testcmi5launch_average_grade_multiple() {

        // Scores as an array.
        $scoresarray = [1, 2, 3, 4, 5];

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        // So the average of either should be.
        $average = 3;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $averagegrade = $helpers->get_cmi5launch_average_grade();

        // Call the method under test.
        $resultstring = $averagegrade($scoresstring);
        $resultarray = $averagegrade($scoresarray);

        // Check the result for string.
        $this->assertEquals($average, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultstring);

        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultarray);

    }

    /*
    * Test of the cmi5launch_average_grade method.
    * This takes scores and averages them.
    * // We need to test with a singular float as string or array
    * @return void
    */
    public function testcmi5launch_average_grade_singular() {
        // Scores as an array.
        $scoresarray = [0 => 3];

        // Score as a plain float.
        $scorefloat = 3;

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        // So the average of either should be.
        $average = 3;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $averagegrade = $helpers->get_cmi5launch_average_grade();

        // Call the method under test.
        $resultstring = $averagegrade($scoresstring);
        $resultarray = $averagegrade($scoresarray);
        $resultfloat = $averagegrade($scorefloat);

        // Check the result for string.
        $this->assertEquals($average, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultstring);

        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultarray);

        // Check the result for array.
        $this->assertEquals($average, $resultfloat);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultfloat);

    }

    /*
    * Test of the cmi5launch_average_grade method.
    * This takes scores and averages them.
    * // We need to test with a 0 as string or array
    * @return void
    */
    public function testcmi5launch_average_grade_zero() {

        // Scores as an array.
        $scoresarray = [0 => 0];

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        // Score as a plain float.
        $scorefloat = 0;

        // So the average of either should be.
        $average = 0;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $averagegrade = $helpers->get_cmi5launch_average_grade();

        // Call the method under test.
        $resultstring = $averagegrade($scoresstring);
        $resultarray = $averagegrade($scoresarray);
        $resultfloat = $averagegrade($scorefloat);

        // Check the result for string.
        $this->assertEquals($average, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultstring);

        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultarray);

        // Check the result for array.
        $this->assertEquals($average, $resultfloat);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultfloat);

    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a string and array
    * @return void
    */
    public function testcmi5launch_highest_grade_multiple() {
        // Scores as an array.
        $scoresarray = [1, 2, 3, 4, 5];

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        // So the highest of either should be.
        $highest = 5;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $highestgrade = $helpers->get_cmi5launch_highest_grade();

        // Call the method under test.
        $resultstring = $highestgrade($scoresstring);
        $resultarray = $highestgrade($scoresarray);

        // Check the result for string.
        $this->assertEquals($highest, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultarray);

    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a string and float.
    * @return void
    */
    public function testcmi5launch_highest_grade_single() {
        // Scores as an array.
        $scoresarray = [5];

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        $scorefloat = 5;

        // So the highest of either should be.
        $highest = 5;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $highestgrade = $helpers->get_cmi5launch_highest_grade();

        // Call the method under test.
        $resultstring = $highestgrade($scoresstring);
        $resultarray = $highestgrade($scoresarray);
        $resultfloat = $highestgrade($scorefloat);

        // Check the result for string.
        $this->assertEquals($highest, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultarray);

        // Check the result for float.
        $this->assertEquals($highest, $resultfloat);
        // Now the return should be an float as it is converted in function.
        $this->assertIsfloat($resultfloat);

    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a 0 and string and float.
    * @return void
    */
    public function testcmi5launch_highest_grade_zero() {
        // Scores as an array.
        $scoresarray = [0];

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);

        $scorefloat = 0;

        // So the highest of either should be.
        $highest = 0;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $highestgrade = $helpers->get_cmi5launch_highest_grade();

        // Call the method under test.
        $resultstring = $highestgrade($scoresstring);
        $resultarray = $highestgrade($scoresarray);
        $resultfloat = $highestgrade($scorefloat);

        // Check the result for string.
        $this->assertEquals($highest, $resultstring);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultarray);

        // Check the result for Float.
        $this->assertEquals($highest, $resultfloat);
        // Now the return should be an float as it is converted in function.
        $this->assertIsFloat($resultfloat);

    }
     /*
    * Test of the cmi5launch_check_user_grades_for_updates method.
    * Parses and retrieves AUs and their sessions from the returned info from CMI5 player and LRS and updates them.
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        // what is cmi5launch here in test?

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Retrieve the record.
         $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

         $auids = json_decode($userscourse->aus);

        // Array to return.
        $returnvalue = [0 => [
            "lmsid" => [
                "Title of AU" => 80,
                "Title of AU2" => 100],
            ],
            1 => [
                "overallgrade" => [
                    "0" => 80,
                    "1" => 100],
                ],
        ];

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\grade_helpers')
            ->onlyMethods(['cmi5launch_update_au_for_user_grades'])
            ->getMock();

            $sessionhelper = new \mod_cmi5launch\local\session_helpers;;

        // Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_update_au_for_user_grades')
            ->with($sessionhelper, $auids, $USER)
            ->willReturn($returnvalue);

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $checkusergrades = $mockedclass->get_cmi5launch_check_user_grades_for_updates();

        $result = $checkusergrades($USER);

        // It should return only the overall grade, the other grades being for updating the records in DB.
        $this->assertEquals($returnvalue[1], $result);
        // It should be an array.
        $this->assertIsArray($result);

    }

    /*
    * Test of the cmi5launch_check_user_grades_for_updates method.
    * This one tests if their are no grades for updates.
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates_no_grade() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        // If we pass it the wrong user id then it cant find the usercourse and we can test that path.
        $USER->id = 100;
        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $checkusergrades = $gradehelper->get_cmi5launch_check_user_grades_for_updates();

        $returnvalue = "No grades to update. No record for user found in this course.";

        $result = $checkusergrades($USER);

        // It should return only the overall grade, the other grades being for updating the records in DB.
        $this->assertEquals($returnvalue, $result[0]);
        // It should be an array.
        $this->assertIsArray($result);

    }
      /*
    * Test of the cmi5launch_check_user_grades_for_update.
    * This one tests if something goes wrong, and throws an exception
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates_excep() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Retrieve the record.
        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

        $auids = json_decode($userscourse->aus);

        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\grade_helpers')
            ->onlyMethods(['cmi5launch_update_au_for_user_grades'])
            ->getMock();
            $sessionhelper = new \mod_cmi5launch\local\session_helpers;
        $mockedclass->expects($this->once())
            ->method('cmi5launch_update_au_for_user_grades')
            ->with($sessionhelper, $auids, $USER)
            ->willReturn(null);

        // Functions from other classes.
        $checkusergrades = $mockedclass->get_cmi5launch_check_user_grades_for_updates();

        // Expected exceptions.
        $expected = " Error in updating or checking user grades. Report this error to system administrator: Error in checking user grades: Trying to access array offset on null";

        $result = $checkusergrades($USER);

        // Because this exception is thrown by the error handler, not the SUT, test the output to ensure right exception was thrown.
        $this->expectOutputString($expected);

    }

    /*
    * Test of the cmi5launch_update_au_for_user_grades method.
    * This one tests if the grades are returned highest.
    * @return void
    */
    public function testcmi5launch_update_au_for_user_grades_highest() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // GRADE_AUS_CMI5 = 0.
        // GRADE_HIGHEST_CMI5 = 1.
        // GRADE_AVERAGE_CMI5 =  2.
        // GRADE_SUM_CMI5 = 3.
        $cmi5launchsettings["grademethod"] = 1;

        // Retrieve the record.
        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

         // Call the TEST version of session helpers to pass.
         $sessionhelper = new \cmi5Test\session_helpers;

         $auids = json_decode($userscourse->aus);

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updateau = $gradehelper->get_cmi5launch_update_au_for_user_grades();

        $result = $updateau($sessionhelper, $auids, $USER);
        // Result should be an array.
        $this->assertIsArray($result);

        // And it is an array of two parts, one for the lmsid, and within it, the title and score of that title.
        // And the second, the overall grade of ALL the aus (lmsids).
        $this->assertArrayHasKey(0, $result);
        // The lmsid and title will have different number endings but should all contain the same base string,
        $lmsid = $result[0];
        $this->assertIsArray($lmsid);

        $this->assertStringStartsWith('lmsid', array_key_first($lmsid));

        // Now finagle to get the title array
        $title = $result[0][array_key_first($lmsid)];

        // Shouild be an array where the title is key and the value is score
        $this->assertIsArray($title);
        $this->assertStringStartsWith('The title text', array_key_first($title));
        // And finally the value of the 'title' is a json string equalling all the session scores, in this case we
        // made five and each was 80.
        $scoresshouldbe = "[80,80,80,80,80]";
        $this->assertEquals($scoresshouldbe, $title[array_key_first($title)]);

        // And finally the overall grade which in this instance is the highest of all the scores, or just 80.
        $this->assertArrayHasKey(1, $result);
        $this->assertEquals(80, $result[1]);
    }


          /*
    * Test of the cmi5launch_update_au_for_user_grades method.
    * This one tests if the grades are returned averaged.
    * @return void
    */
    public function testcmi5launch_update_au_for_user_grades_average() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Gradetype for reference.
        // GRADE_AUS_CMI5 = 0.
        // GRADE_HIGHEST_CMI5 = 1.
        // GRADE_AVERAGE_CMI5 =  2.
        // GRADE_SUM_CMI5 = 3.
        $cmi5launchsettings["grademethod"] = 2;

        // Retrieve the record.
        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

        // Implement the duplicate of session helper to pass through.
        $sessionhelper = new \cmi5Test\session_helpers;

        $auids = json_decode($userscourse->aus);

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updateau = $gradehelper->get_cmi5launch_update_au_for_user_grades();

        $result = $updateau($sessionhelper, $auids, $USER);

        $this->assertIsArray($result);

        // And it is an array of two parts, one for the lmsid, and within it, the title and score of that title
        // and the second, the overall grade of ALL the aus (lmsids).
        $this->assertArrayHasKey(0, $result);
        // The lmsid and title will have different number endings but should all contain the same base string,
        $lmsid = $result[0];
        $this->assertIsArray($lmsid);

        $this->assertStringStartsWith('lmsid', array_key_first($lmsid));

        // Now finagle to get the title array.
        $title = $result[0][array_key_first($lmsid)];

        // Should be an array where the title is key and the value is score.
        $this->assertIsArray($title);
        $this->assertStringStartsWith('The title text', array_key_first($title));

        // And finally the value of the 'title' is a json string equalling all the session scores, in this case we
        // made five and each was 80.
        $scoresshouldbe = "[80,80,80,80,80]";
        $this->assertEquals($scoresshouldbe, $title[array_key_first($title)]);

        // And finally the overall grade which in this instance is the average to all the scores, or just 80.
        $this->assertArrayHasKey(1, $result);

        $this->assertEquals(80, $result[1]);
    }


    /*
    * Test of the cmi5launch_update_au_for_user_grades method.
    * Tests the function if there is a bad gradetype selected (this shouldn't ever happen).
    * @return void
    */
    public function testcmi5launch_update_au_for_user_grades_bad_gradetype() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Gradetype for ref, passing in invalid value.
        // GRADE_AUS_CMI5 = 0.
        // GRADE_HIGHEST_CMI5 = 1.
        // GRADE_AVERAGE_CMI5 =  2.
        // GRADE_SUM_CMI5 = 3.
        $cmi5launchsettings["grademethod"] = 6;

        // Retrieve the record.
        $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

        // Implement the duplicate of session helper to pass through function.
        $sessionhelper = new \cmi5Test\session_helpers;

        $auids = json_decode($userscourse->aus);

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updateau = $gradehelper->get_cmi5launch_update_au_for_user_grades();

        $result = $updateau($sessionhelper, $auids, $USER);

        // There will be five of these, one for each au.
        $this->expectOutputString("Grade type not found.Grade type not found.Grade type not found.Grade type not found.Grade type not found.");

        $this->assertIsArray($result);

        // And it is an array of two parts, one for the lmsid, and within it, the title and score of that title
        // and the second, the overall grade of ALL the aus (lmsids).
        $this->assertArrayHasKey(0, $result);
        // The lmsid and title will have different number endings but should all contain the same base string,
        $lmsid = $result[0];
        $this->assertIsArray($lmsid);

        $this->assertStringStartsWith('lmsid', array_key_first($lmsid));

        // Now finagle to get the title array
        $title = $result[0][array_key_first($lmsid)];

        // Shouild be an array where the title is key and the value is score.
        $this->assertIsArray($title);
        $this->assertStringStartsWith('The title text', array_key_first($title));

        // And the value of the 'title' is a json string equalling all the session scores, in this case we
        // made five and each was 80.
        $scoresshouldbe = "[80,80,80,80,80]";
        $this->assertEquals($scoresshouldbe, $title[array_key_first($title)]);

        // And finally the overall grade in this instance will still be 80, because that is the saved value and hasnt been changed.
        $this->assertArrayHasKey(1, $result);

        $this->assertEquals(80, $result[1]);
    }


    /*
    * Test of the cmi5launch_update_au_for_user_grades method.
    // Testing when exception is thrown
    * @return void
    */
    public function testcmi5launch_update_au_for_user_grades_excep() {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // Gradetype for reference.
        // GRADE_AUS_CMI5 = 0.
        // GRADE_HIGHEST_CMI5 = 1.
        // GRADE_AVERAGE_CMI5 =  2.
        // GRADE_SUM_CMI5 = 3.
        $cmi5launchsettings["grademethod"] = 1;

        // Implement the duplicate of session helper to pass through function.
        $sessionhelper = new \cmi5Test\session_helpers;

        // By passing the auid as a string the function should throw an exception.
        $auids = "Throw an error";

            // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updateau = $gradehelper->get_cmi5launch_update_au_for_user_grades();

        // The expected is built by the two messages knowing 'title' is an empty array.
        $expected = "Error in updating or checking user grades. Report this error to system administrator: Error in checking user grades:";

        // Catch the exception.
        $this->expectException(nullException::class);
        $this->expectExceptionMessage($expected);

        $result = $updateau($sessionhelper, $auids, $USER);

    }


}



