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

use mod_cmi5launch\local\nullException;
use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\session;

/**
 * Class sessionTest.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \au
 * @package mod_cmi5launch
 */
class sessionTest extends TestCase {

    private $sessionproperties, $emptystatement, $mockstatementvalues;

    protected function setUp(): void {
        // All the properties in an AU object.
        $this->sessionproperties = [
            'id',
            'tenantname',
            'tenantId',
            'registrationsCoursesAusId',
            'lmsid',
            'progress',
            'launchurl',
            'grade',
            'createdAt',
            'updatedAt',
            'code',
            'lastRequestTime',
            'launchTokenId',
            'launchMode',
            'masteryScore',
            'isLaunched',
            'isInitialized',
            'initializedAt',
            'isCompleted',
            'isPassed',
            'isFailed',
            'isTerminated',
            'isAbandoned',
            'courseid',
            'completed',
            'passed',
            'inprogress',
            'sessionid',
            'userid',
            'registrationscoursesausid',
            'createdat',
            'updatedat',
            'launchtokenid',
            'lastrequesttime',
            'launchmode',
            'masteryscore',
            'tenantid',
            'score',
            'response',
            'islaunched',
            'isinitialized',
            'initializedat',
            'duration',
            'iscompleted',
            'ispassed',
            'isfailed',
            'isterminated',
            'isabandoned',
            'launchmethod',
            'moodlecourseid',
        ];

        $this->emptystatement = [];

        // A good test would be to test the constructor with a statement that has all the properties set.
        $this->mockstatementvalues = [
            'id' => 'id',
            'tenantname' => 'tenantname',
            'tenantId' => 'tenantId',
            'registrationsCoursesAusId' => 'registrationsCoursesAusId',
            'lmsid' => 'lmsid',
            'progress' => 'progress',
            'launchurl' => 'launchurl',
            'grade' => 'grade',
            'createdAt' => 'createdAt',
            'updatedAt' => 'updatedAt',
            'code' => 'code',
            'lastRequestTime' => 'lastRequestTime',
            'launchTokenId' => 'launchTokenId',
            'launchMode' => 'launchMode',
            'masteryScore' => 'masteryScore',
            'isLaunched' => 'isLaunched',
            'isInitialized' => 'isInitialized',
            'initializedAt' => 'initializedAt',
            'isCompleted' => 'isCompleted',
            'isPassed' => 'isPassed',
            'isFailed' => 'isFailed',
            'isTerminated' => 'isTerminated',
            'isAbandoned' => 'isAbandoned',
            'courseid' => 'courseid',
            'completed' => 'completed',
            'passed' => 'passed',
            'inprogress' => 'inprogress',
            'sessionid' => 'sessionid',
            'userid' => 'userid',
            'registrationscoursesausid' => 'registrationscoursesausid',
            'createdat' => 'createdat',
            'updatedat' => 'updatedat',
            'launchtokenid' => 'launchtokenid',
            'lastrequesttime' => 'lastrequesttime',
            'launchmode' => 'launchmode',
            'masteryscore' => 'masteryscore',
            'tenantid' => 'tenantid',
            'score' => 'score',
            'response' => 'response',
            'islaunched' => 'islaunched',
            'isinitialized' => 'isinitialized',
            'initializedat' => 'initializedat',
            'duration' => 'duration',
            'iscompleted' => 'iscompleted',
            'ispassed' => 'ispassed',
            'isfailed' => 'isfailed',
            'isterminated' => 'isterminated',
            'isabandoned' => 'isabandoned',
            'launchmethod' => 'launchmethod',
            'moodlecourseid' => 'moodlecourseid',
        ];

    }

    protected function tearDown(): void {

    }

    /**
     * Test of session constructor class
     * Should instantiate an session object with no values.
     * @return void
     */
    public function testinstantiationwithempty() {
        // Make an session object with no values.
        $obj = new session($this->emptystatement);

        // Assert its an AU object.
        $this->assertInstanceOf(session::class, $obj);

        // It is saying session is not transversable. Implementing traversable in session is breaking the code, typecast the object as array for dirty fix.
        // Make sure the session object does not have any 'extra' properties, only the amount passed in
        $expectedamount = count($this->sessionproperties);
        $sessionarray = (array) $obj;
        $this->assertCount($expectedamount, $sessionarray, "Session has $expectedamount properties");

        // Properties exists and are empty
        foreach ($sessionarray as $property => $value) {

            // If value is 'progress' it's an array not null.
            if ($property == 'progress') {

                $this->assertArrayHasKey($property, $sessionarray, "$property exists");
                $this->assertIsArray($value, "$property empty");

            }else{

                $this->assertArrayHasKey($property, $sessionarray, "$property exists");
                $this->assertNull($value, "$property empty");
            }
        }
    }

    /**
     * Test of session constructor class.
     * Should instantiate a session object with values.
     * @return void
     */
    public function testinstantiationwithvalues() {
        $obj = new session($this->mockstatementvalues);

        // Assert it's an AU object?
        $this->assertInstanceOf(session::class, $obj);

        // It is saying AU is not transversable. Implementing traversable in AU is breaking the code, typecast the object as array for dirty fix.
        // Make sure the AU object does not have any 'extra' properties, only the amount passed in
        $expectedamount = count($this->sessionproperties);
        $sessionarray = (array) $obj;
        $this->assertCount($expectedamount, $sessionarray, "Serssion has $expectedamount properties");

        // Properties exists and are correct (value should equal name of property)
        foreach ($sessionarray as $property => $value) {

            $this->assertArrayHasKey($property, $sessionarray, "$property exists");
            $this->assertEquals($property, $value, "$value does not equal $property");
        }
    }

    /**
     * Test of session constructor class exceptions. This one tests if statement is null.
     * @return void
     */
    public function testinstantiation_except_null() {
        // Null statement to send and trigger exception.
        $nullstatement = null;

        // Expected message
          // Catch the exception.
          $this->expectException(nullException::class);
          $this->expectExceptionMessage("Statement to build session is null or not an array/object." );

        $obj = new session($nullstatement);

    }

    /**
     * Test of AU constructor class exceptions. This one tests if statement passed in is not an array.
     * @return void
     */
    public function testinstantiation_except_nonarray() {
        // Null statement to send and trigger exception.
        $nullstatement = "string";

          // Catch the exception.
          $this->expectException(nullException::class);
          $this->expectExceptionMessage("Statement to build session is null or not an array/object." );

        $obj = new session($nullstatement);

    }


}
