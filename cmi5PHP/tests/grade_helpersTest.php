<?php

namespace cmi5Test;

use Exception;
use mod_cmi5launch\local\grade_helpers;
use mod_cmi5launch\local\nullException;
use mod_cmi5launch\local\fieldException;
use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;


require_once( "cmi5TestHelpers.php");

/**
 * Tests for grade_helpers class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *  */
class grade_helpersTest extends TestCase
{

    
    // Use setupbefore and after class sparingly. In this case, we don't want to use it to connect tests, but rather to
    // 'prep' the test db with values the tests can run against. 
    public static function setUpBeforeClass(): void
    {
        
    }

    public static function tearDownAfterClass(): void
    {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Delete the test record.
      //  deletetestcmi5launch($cmi5launchid);

        // Delete the test course.
       // deletetestcmi5launch_usercourse($cmi5launchid);

        // Delete the test AUs.
     //   deletetestcmi5launch_aus($testcourseausids);

        // Delete the test sessions.
      //  deletetestcmi5launch_sessions($testcoursesessionids);

      
        
        // Restore overridden global variable.
        unset($GLOBALS['USER']);
      //  unset($GLOBALS['DB']);
        unset($GLOBALS['cmi5launchsettings']);
        unset($GLOBALS['cmi5launch']);
        unset($GLOBALS['cmi5launchid']);
        unset($GLOBALS['testcourseid']);
        unset($GLOBALS['testcourseausids']);
        unset($GLOBALS['testcoursesessionids']);
        
    }

    protected function setUp(): void
    {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Make a fake cmi5 launch record.
        $cmi5launchid = maketestcmi5launch();

        $cmi5launchsettings = array("cmi5launchtenanttoken" => "Testtoken", "cmi5launchplayerurl" => "http://test/launch.php", "cmi5launchcustomacchp" => "http://testhomepage.com");

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

     //what are the testcourseauids here>
       
        // Assign the AUs to the course.
        assign_aus_to_courses($testcourseid, $testcourseausids);
         }

    protected function tearDown(): void
    {
        
    }


    /**
     * Test of the cmi5launch_average_grade method.
     * This takes scores and averages them.
     * // We need to test with scores being a string and array
     * @return void
     */
    public function testcmi5launch_average_grade_multiple()
    {
       
        // Scores as an array.
        $scoresarray = array (1,2,3,4,5);

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
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);

        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);
        
    }
    /*
    * Test of the cmi5launch_average_grade method.
    * This takes scores and averages them.
    * // We need to test with a singular int as string or array
    * @return void
    */
   public function testcmi5launch_average_grade_singular()
   {
      
        // Scores as an array.
        $scoresarray = array (0 => 3);

        // Score as a plain int.
        $scoreint = 3;
        
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
        $resultint = $averagegrade($scoreint);

        // Check the result for string.
        $this->assertEquals($average, $resultstring);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);

        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);

        // Check the result for array.
        $this->assertEquals($average, $resultint);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultint);
       
   }

    /*
    * Test of the cmi5launch_average_grade method.
    * This takes scores and averages them.
    * // We need to test with a 0 as string or array
    * @return void
    */
    public function testcmi5launch_average_grade_zero()
    {
       
        // Scores as an array.
        $scoresarray = array (0 => 0);
 
        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);
        
        // Score as a plain int.
        $scoreint = 0;
        
        // So the average of either should be.
        $average = 0;
 
        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $averagegrade = $helpers->get_cmi5launch_average_grade();
 
        // Call the method under test.
        $resultstring = $averagegrade($scoresstring);
        $resultarray = $averagegrade($scoresarray);
        $resultint = $averagegrade($scoreint);
 
        // Check the result for string.
        $this->assertEquals($average, $resultstring);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);
 
        // Check the result for array.
        $this->assertEquals($average, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);

        // Check the result for array.
        $this->assertEquals($average, $resultint);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultint);
               
    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a string and array
    * @return void
    */
    public function testcmi5launch_highest_grade_multiple()
    {  
        // Scores as an array.
        $scoresarray = array (1,2,3,4,5);

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
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);

    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a string and int.
    * @return void
    */
    public function testcmi5launch_highest_grade_single()
    {  
        // Scores as an array.
        $scoresarray = array (5);

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);
  
        $scoreint = 5;

        // So the highest of either should be.
        $highest = 5;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $highestgrade = $helpers->get_cmi5launch_highest_grade();

        // Call the method under test.
        $resultstring = $highestgrade($scoresstring);
        $resultarray = $highestgrade($scoresarray);
        $resultint = $highestgrade($scoreint);

        // Check the result for string.
        $this->assertEquals($highest, $resultstring);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);

        // Check the result for Int.
        $this->assertEquals($highest, $resultint);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultint);
    
    }

    /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a 0 and string and int.
    * @return void
    */
    public function testcmi5launch_highest_grade_zero()
    {  
        // Scores as an array.
        $scoresarray = array (0);

        // Scores as a (json_encoded) string.
        $scoresstring = json_encode($scoresarray);
  
        $scoreint = 0;

        // So the highest of either should be.
        $highest = 0;

        // It can't be called statically because it is not 'static' in declaration
        // make new instance of grade_helpers.
        $helpers = new grade_helpers();
        $highestgrade = $helpers->get_cmi5launch_highest_grade();

        // Call the method under test.
        $resultstring = $highestgrade($scoresstring);
        $resultarray = $highestgrade($scoresarray);
        $resultint = $highestgrade($scoreint);

        // Check the result for string.
        $this->assertEquals($highest, $resultstring);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultstring);

        // Check the result for array.
        $this->assertEquals($highest, $resultarray);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultarray);

        // Check the result for Int.
        $this->assertEquals($highest, $resultint);
        // Now the return should be an int as it is converted in function.
        $this->assertIsInt($resultint);
    
    }
     /*
    * Test of the cmi5launch_highest_grade method.
    * This takes scores and returns the highest one of them.
    * // We need to test with scores being a 0 and string and int.
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates()
    {  
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        // what is cmi5launch here in test?
        
        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
         
        // Retrieve the record.
         $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

         $auids = json_decode($userscourse->aus);

        // So the systems under test does a lot, may need refactoring
        // But we will need the testcourseid and userid to get the"usercourse"
         // Ah the user course has aus in it and the function grabs them, then for each au grabs their session
         // huhthese should be refactored into smaller functions.   
        // well then again it already is a lot of calls so maybe its fine
        // I dunno, my brain says its frisday

        // Array to return
        $returnvalue = array(0 => array(
            "lmsid" => array(
                "Title of AU" => 80, 
                "Title of AU2" => 100),
            ),
            1 => array(
                "overallgrade" => array(
                    "0" => 80, 
                    "1" => 100),
                ),
    );

        // so lets start by pretending to call finction under test 
        // I have to start somewhere
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\grade_helpers')
            ->onlyMethods(array('cmi5launch_update_au_for_user_grades'))
            ->getMock();

     
        // Ok, the arrgs ar the same, the name is the same. What is YES! its not calling the mocked class!!!!!
        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_update_au_for_user_grades')
            ->with($auids, $USER)
            ->willReturn($returnvalue);
        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $checkusergrades = $mockedclass->get_cmi5launch_check_user_grades_for_updates();

        $result = $checkusergrades($USER);

        // it should return only the overall grade, the other grades being for updating the records in DB.
        $this->assertEquals($returnvalue[1], $result);
        // IT should be an array
        $this->assertIsArray($result);


    }

      /*
    * Test of the cmi5launch_highest_grade method.
    * This one tests if their are no rades for updates
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates_no_grade()
    {  
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        // If we pass it the wrong user id then it cant find the usercourse and we can test that path.
        $USER->id = 100;
        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
         // Retrieve the record.
     //    $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

        // $auids = json_decode($userscourse->aus);

        // So the systems under test does a lot, may need refactoring
        // But we will need the testcourseid and userid to get the"usercourse"
         // Ah the user course has aus in it and the function grabs them, then for each au grabs their session
         // huhthese should be refactored into smaller functions.   
        // well then again it already is a lot of calls so maybe its fine
        // I dunno, my brain says its frisday

        // so lets start by pretending to call finction under test 
        // I have to start somewhere
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
   
        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $checkusergrades = $gradehelper->get_cmi5launch_check_user_grades_for_updates();

        $returnvalue = "No grades to update. No record for user found in this course.";
        
        $result = $checkusergrades($USER);

        // it should return only the overall grade, the other grades being for updating the records in DB.
        $this->assertEquals($returnvalue, $result[0]);
        // IT should be an array
        $this->assertIsArray($result);

    }
      /*
    * Test of the cmi5launch_highest_grade method.
    * This one tests if something goes wrong, and throws an exception
    * @return void
    */
    public function testcmi5launch_check_user_grades_for_updates_excep()
    {  
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
         // Retrieve the record.
         $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

  $auids = json_decode($userscourse->aus);

        // $auids = json_decode($userscourse->aus);
     // so lets start by pretending to call finction under test 
        // I have to start somewhere
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\grade_helpers')
            ->onlyMethods(array('cmi5launch_update_au_for_user_grades'))
            ->getMock();

            // If it returns null this should throw a null error exception. 
        $mockedclass->expects($this->once())
            ->method('cmi5launch_update_au_for_user_grades')
            ->with($auids, $USER)
            ->willReturn(null);
     
        // Functions from other classes.
        $checkusergrades = $mockedclass->get_cmi5launch_check_user_grades_for_updates();

     // Expected exceptions
        $expected = " Error in updating or checking user grades. Report this error to system administrator: Error in checking user grades: Trying to access array offset on null";

  
     // Expected exceptions and messages
 
        $result = $checkusergrades($USER);

         // Because this exception is thrown by the error handler, not the SUT, test the output to ensure right exception was thrown.
     $this->expectOutputString($expected);

    }

          /*
    * Test of the cmi5launch_update_au_for_user_grades method.
 
    * @return void
    */
    public function testcmi5launch_update_au_for_user_grades()
    {
        global $cmi5launch, $USER, $DB, $testcourseid, $cmi5launchsettings;
       // global $session_helper;
        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);
         // Retrieve the record.
         $userscourse = $DB->get_record('cmi5launch_usercourse', ['courseid' => $cmi5launch->courseid, 'userid' => $USER->id]);

         
            // The problem is that AUS needs to be an array of numbers and ita
            // an actual au 
         $auids = json_decode($userscourse->aus);

        // So the systems under test does a lot, may need refactoring
        // But we will need the testcourseid and userid to get the"usercourse"
         // Ah the user course has aus in it and the function grabs them, then for each au grabs their session
         // huhthese should be refactored into smaller functions.   
        // well then again it already is a lot of calls so maybe its fine
        // I dunno, my brain says its frisday

        // Session to return to return
        $returnvalue = new \stdClass();
            $returnvalue->iscompleted = 1;
            $returnvalue->ispassed = 1;
            $returnvalue->isterminated = 1;
            $returnvalue->score = 80;

        // so lets start by pretending to call finction under test 
        // I have to start somewhere
        // Mock a cmi5 connector object but only stub ONE method, as we want to test the others.
   
        $mockedclass = $this->getMockBuilder('mod_cmi5launch\local\grade_helpers')
            ->addMethods(array('cmi5launch_get_update_session'))
            ->getMock();

        //  Mock returns json encoded data, as it would be from the player.
        $mockedclass->expects($this->once())
            ->method('cmi5launch_get_update_session');
            //->with($auids, $USER)
           // ->willReturn($returnvalue);
     
     
            // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updateau = $mockedclass->get_cmi5launch_update_au_for_user_grades();

        $result = $updateau($auids, $USER);
                 // Because this exception is thrown by the error handler, not the SUT, test the output to ensure right exception was thrown.
     $this->expectOutputString("test");
    }

}
    

?>
