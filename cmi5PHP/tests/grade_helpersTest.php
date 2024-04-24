<?php

namespace cmi5Test;

use mod_cmi5launch\local\grade_helpers;
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
        $testcoursesessionids = maketestsessions($testcourseid);

        // Assign the sessions to AUs.
        assign_sessions_to_aus($testcourseausids, $testcoursesessionids);

        // Assign the AUs to the course.
        assign_aus_to_courses($testcourseid, $testcourseausids);
    }

    public static function tearDownAfterClass(): void
    {
        global $DB, $cmi5launch, $cmi5launchid, $USER, $testcourseid, $testcourseausids, $testcoursesessionids, $cmi5launchsettings;

        // Delete the test record.
        deletetestcmi5launch($cmi5launchid);

        // Delete the test course.
        deletetestcmi5launch_usercourse($cmi5launchid);

        // Delete the test AUs.
        deletetestcmi5launch_aus($testcourseausids);

        // Delete the test sessions.
        deletetestcmi5launch_sessions($testcoursesessionids);

        
        // Restore overridden global variable.
        unset($GLOBALS['USER']);
        unset($GLOBALS['DB']);
        unset($GLOBALS['cmi5launchsettings']);
        unset($GLOBALS['cmi5launch']);
        unset($GLOBALS['cmi5launchid']);
        unset($GLOBALS['testcourseid']);
        unset($GLOBALS['testcourseausids']);
        unset($GLOBALS['testcoursesessionids']);
        
    }

    protected function setUp(): void
    {
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

        $cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

        // So the systems under test does a lot, may need refactoring
        // But we will need the testcourseid and userid to get the"usercourse"
         // Ah the user course has aus in it and the function grabs them, then for each au grabs their session
         // huhthese should be refactored into smaller functions.   
        // well then again it already is a lot of calls so maybe its fine
        // I dunno, my brain says its frisday

        // so lets start by pretending to call finction under test 
        // I have to start somewhere

        // Bring in functions and classes.
        $gradehelper = new grade_helpers;

        // Functions from other classes.
        $updatesession = $gradehelper->get_cmi5launch_check_user_grades_for_updates();

        $result = $updatesession($USER);

        // Ok once called the function checks that the user course with
        // cmi5launch->courseid and user->id exists
        // If it doesn't returns false (this will be a separate test)
        // if it does, continue...
        // continuing on we retrieve it
        //  if usercourse is null (as oppossed to doesn't exit???) then they have net participaated in course yet and it return overall grade
        // Except I found an error BECAUSE overall grade doesn't exist yet!
        // So either it never fins null or and I need to nix that path,
        // OR im lucky and need to declare overall elsewher
        // wait, either way yhtis is lucky, how has this never thrown error? A usercourse cant be null right? Then it would have just been false.
        // If yep, I think this whole branch is phony baloeny, not the return part, that is for EVERYONE, but the null part, there is no null

        // Ok, moving on the usercoure->aus are decoded, so we will want to make our test usercourse have aus

        // B
    }

}
    

?>
