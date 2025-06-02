<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class to handle Sessions.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
namespace mod_cmi5launch\local;
// Include the errorover (error override) funcs.
require_once ($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');


defined('MOODLE_INTERNAL') || die();

class session {
    // Properties, these need to be capitilized as they are because that's how they are returned in statements and need to be saved.
    // Id is session id.
    public $id, $tenantname, $tenantId, $registrationsCoursesAusId, $lmsid,
    $progress = [], $aulaunchurl, $launchurl, $grade,
    $createdAt, $updatedAt, $registrationCourseAusId,
    $code, $lastRequestTime, $launchTokenId, $launchMode, $masteryScore,
    $isLaunched, $isInitialized, $initializedAt, $isCompleted,
    $isPassed, $isFailed, $isTerminated, $isAbandoned, $courseid, $completed, $passed, $inprogress;

    // Database properties, that need to be lower case.
    public $sessionid, $userid, $registrationscoursesausid, $createdat, $updatedat, $launchtokenid, $lastrequesttime, $launchmode, $masteryscore, $tenantid,
    $score, $response, $islaunched, $isinitialized, $initializedat, $duration, $iscompleted, $ispassed, $isfailed, $isterminated, $isabandoned, $launchmethod, $moodlecourseid;  


    // Constructs sessions. Is fed array and where array key matches property, sets the property.
    public function __construct($statement) {

        // What can go wrong here? It could be that a statement is null
        // or that the statement is not an array or not an object.
        if (is_null($statement) || (!is_array($statement) && !is_object($statement) )) {
            
            throw new nullException(get_string('cmi5launchsessionbuilderror', 'cmi5launch'), 0);
        }

        foreach ($statement as $key => $value) {

            $this->$key = ($value);
        }

    }

}
