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
        global $cmi5launch, $USER, $DB;
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
     * Test of the cmi5launch_average_grade method.
     * This takes scores and averages them.
     * // We need to test with scores being a string and array
     * @return void
     */
    public function testcmi5launch_average_grade()
    {
        // Scores as a (json_encoded) string.
        $scoresstring = json_encode("1,2,3,4,5");
        
        // Scores as an array.
        $scoresarray = [1,2,3,4,5];

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

}
    

?>
