<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\cmi5_connectors;
use mod_cmi5launch\local\course;

require_once( "cmi5TestHelpers.php");

/**
 * Tests for cmi5 course class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *  */
class courseTest extends TestCase
{
    private $courseproperties, $emptystatement, $coursepropertiesvalues;

    protected function setUp(): void
    {
        // All the properties in an AU object.
        $this->courseproperties = array(
            'id',
            'url',
            'ausgrades',
            'type',
            'lmsid',
            'grade',
            'scores',
            'title',
            'moveon',
            'auindex',
            'parents',
            'objectives',
            'launchurl',
            'sessions',
            'sessionid',
            'returnurl',
            'description',
            'activitytype',
            'launchmethod',
            'masteryscore',
            'progress',
            'noattempt',
            'completed',
            'passed',
            'inprogress',
            'satisfied',
            'moodlecourseid',
            'courseid',
            'userid',
            'registrationid',
            'aus',
        );

        $this->emptystatement = array();

        // Perhaps a good test would be to test the constructor with a statement that has all the properties set.
        $this->coursepropertiesvalues = array(
            'id' => 'id',
            'url' => 'url',
            'ausgrades' => 'ausgrades',
            'type' => 'type',
            'lmsid' => 'lmsid',
            'grade' => 'grade',
            'scores' => 'scores',
            'title' => 'title',
            'moveon' => 'moveon',
            'auindex' => 'auindex',
            'parents' => 'parents',
            'objectives' => 'objectives',
            'launchurl' => 'launchurl',
            'sessions' => 'sessions',
            'sessionid' => 'sessionid', 
            'returnurl' => 'returnurl',
            'description' => 'description',
            'activitytype' => 'activitytype',
            'launchmethod' => 'launchmethod',
            'masteryscore' => 'masteryscore',
            'progress' => 'progress',
            'noattempt' => 'noattempt',
            'completed' => 'completed',
            'passed' => 'passed',
            'inprogress' => 'inprogress',
            'satisfied' => 'satisfied',
            'moodlecourseid' => 'moodlecourseid',
            'courseid' => 'courseid',
            'userid' => 'userid',
            'registrationid' => 'registrationid',
            'aus' => 'aus'
        );
    }

    protected function tearDown(): void
    {

    }

    /**
     * Test instantiation of course object with empty statement.
     * 
     * @return void
     */
    public function testInstantiationWithEmpty()
    {
        // Call course constructor.
        $obj = new course($this->emptystatement);

        // Ensure it is a course object.
        $this->assertInstanceOf(course::class, $obj);

        //Make sure the course object does not have any 'extra' properties, only the amount passed in
        $expectedAmount = count($this->courseproperties);
        
        // Typecasting the object as an array to allow traversing.
        $coursearray = (array) $obj;
        $this->assertCount($expectedAmount, $coursearray, "AU has $expectedAmount properties");

        // Verify properties exist and are empty or null.
        foreach ($coursearray as $property => $value) {

            $this->assertArrayHasKey($property, $coursearray, "$property exists");

            $this->assertThat(
                $value,
                $this->logicalOr(
                    $this->isNull($value, "$property is null"),
                    $this->isEmpty($value, "$property is empty")
                ), 
               );
            }

    }

    /**
     * Test instantiation of course object with values.
     * 
     * @return void
     */
    public function testInstantiationWithValues()
    {
        // Call course constructor.
        $obj = new course($this->coursepropertiesvalues);

        // Verify it is a course object.
        $this->assertInstanceOf(course::class, $obj);
        
        // Make sure the course object does not have any 'extra' properties, only the amount passed in.
        $expectedamount = count($this->courseproperties);
        
        // Typecasting the object as an array to allow traversing.
        $coursearray = (array) $obj;    
        $this->assertCount($expectedamount, $coursearray, "AU has $expectedamount properties");

        // Properties exists and are correct. Value should equal name of property.
        // Except the id, that should be null ad the constructor does that purposefully to allow it to be set later.
        foreach ($coursearray as $property => $value) {

            $this->assertArrayHasKey($property, $coursearray, "$property exists");
            
            if ($property == 'id') {

                $this->assertNull($value, "$property is null");
            }
            else {
                $this->assertEquals($property, $value, "$value does not equal $property");
            }
            
        }
    }


}
    