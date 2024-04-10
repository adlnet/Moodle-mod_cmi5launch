<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\au;

/**
 * Class AuTest.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \au
 */
class auTest extends TestCase
{
    private $auProperties, $emptyStatement, $mockStatementValues;

    protected function setUp(): void
    {
        // All the properties in an AU object.
        $this->auProperties = array(
            'id',
            'attempt',
            'url',
            'type',
            'lmsid',
            'grade',
            'scores',
            'title',
            'moveon',
            'auindex',
            'parents',
            'objectives',
            'description',
            'activitytype',
            'launchmethod',
            'masteryscore',
            'satisfied',
            'launchurl',
            'sessions',
            'progress',
            'noattempt',
            'completed',
            'passed',
            'masteryScore', 
            'inprogress', 
            'launchMethod', 
            'lmsId', 
            'moveOn', 
            'auIndex', 
            'activityType',
            'moodlecourseid',
            'userid' 
        );

        $this->emptyStatement = array();

        // Perhaps a good test would be to test the constructor with a statement that has all the properties set.
        $this->mockStatementValues = array(
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
            'masteryScore' => 'masteryScore', 
            'launchMethod' => 'launchMethod', 
            'lmsId' => 'lmsId', 
            'moveOn' => 'moveOn', 
            'auIndex' => 'auIndex', 
            'activityType' => 'activityType',
            'moodlecourseid' => 'moodlecourseid',
            'userid' => 'userid'
        );
    }

    protected function tearDown(): void
    {

    }

    /**
     * Test of AU constructor class
     * Should instantiate an AU object with no values.
     * @return void
     */
    public function testInstantiationWithEmpty()
    {
        $obj = new au($this->emptyStatement);

        // Is an AU object?
        $this->assertInstanceOf(au::class, $obj);

        //It is saying AU is not transversable
        //Implementing traversable in AU is breaking the code,
        //Make sure the AU object does not have any 'extra' properties, only the amount passed in
        $expectedAmount = count($this->auProperties);
        //could typecasting the object as an array help? dirty fix
        $auArray = (array) $obj;
        $this->assertCount($expectedAmount, $auArray, "AU has $expectedAmount properties");

        //Properties exists and are empty
        foreach ($auArray as $property => $value) {

            $this->assertArrayHasKey($property, $auArray, "$property exists");
            $this->assertNull($value, "$property empty");
        }

    }

    /**
     * Test of AU constructor class
     * Should instantiate an AU object with values.
     * @return void
     */

    public function testInstantiationWithValues()
    {
        $obj = new au($this->mockStatementValues);

        // Is an AU object?
        $this->assertInstanceOf(au::class, $obj);
        
        //It is saying AU is not transversable
        //Implementing traversable in AU is breaking the code,
        //Make sure the AU object does not have any 'extra' properties, only the amount passed in
        $expectedAmount = count($this->auProperties);
        //could typecasting the object as an array help? dirty fix
        $auArray = (array) $obj;
        $this->assertCount($expectedAmount, $auArray, "AU has $expectedAmount properties");

        //Properties exists and are correct (value should equal name of property)
        foreach ($auArray as $property => $value) {

            $this->assertArrayHasKey($property, $auArray, "$property exists");
            $this->assertEquals($property, $value, "$value does not equal $property");
        }
    }
}