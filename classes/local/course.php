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
 * Class to handle invidual courses.
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

 namespace mod_cmi5launch\local;

/**
 * This class represents a course in the cmi5 context.
 * It contains properties that are relevant to the course and a method to construct a course object.
 *
 * @package mod_cmi5launch\local
 */
class course {

    /**
     * @var int|null Internal Moodle database ID for the course.
     */
    public $id;

    /**
     * @var string|null Launch URL for the course.
     */
    public $url;

    /**
     * @var array|null Grades associated with AUs in the course.
     */
    public $ausgrades;

    /**
     * @var string|null Type of the course.
     */
    public $type;

    /**
     * @var string|null LMS ID assigned to the course (bby the cmi5 player).
     */
    public $lmsid;

    /**
     * @var float|null Final grade for the course.
     */
    public $grade;

    /**
     * @var mixed|null Score details for the course.
     */
    public $scores;

    /**
     * @var string|null Title of the course.
     */
    public $title;

    /**
     * @var string|null MoveOn condition at the course level.
     */
    public $moveon;

    /**
     * @var int|null AU index used for tracking.
     */
    public $auindex;

    /**
     * @var mixed|null Parent AU relationships.
     */
    public $parents;

    /**
     * @var mixed|null Objectives tied to the course.
     */
    public $objectives;

    /**
     * @var string|null URL used to launch the course assigned by player.
     */
    public $launchurl;

    /**
     * @var array Session data for the course, ids to track in DB.
     */
    public $sessions = [];

    /**
     * @var string|null Unique session ID.
     */
    public $sessionid;

    /**
     * @var string|null URL to return to after course completion.
     */
    public $returnurl;

    /**
     * @var array Description(s) of the course.
     */
    public $description = [];

    /**
     * @var string|null Activity type used in the course.
     */
    public $activitytype;

    /**
     * @var string|null Launch method used.
     */
    public $launchmethod;

    /**
     * @var float|null Mastery score required for course success.
     */
    public $masteryscore;

    /**
     * @var mixed|null Progress data.
     */
    public $progress;

    /**
     * @var bool|null Whether there was no attempt made.
     */
    public $noattempt;

    /**
     * @var bool|null Whether the course was completed.
     */
    public $completed;

    /**
     * @var bool|null Whether the course was passed.
     */
    public $passed;

    /**
     * @var bool|null Whether the course is currently in progress.
     */
    public $inprogress;

    /**
     * @var bool|null Whether the course was satisfied.
     */
    public $satisfied;

    /**
     * @var int|null Moodle course ID.
     */
    public $moodlecourseid;

    /**
     * @var string|null Course ID assigned by the cmi5 player.
     */
    public $courseid;

    /**
     * @var int|null ID of the user taking the course.
     */
    public $userid;

    /**
     * @var string|null Registration ID from the cmi5 player.
     */
    public $registrationid;

    /**
     * @var array AU objects belonging to this course, IDs mapping to the AUs in DB.
     */
    public $aus = [];

    /**
     * Constructs a course object. Accepts an array of key-value pairs to populate properties.
     *
     * @param array $statement The array containing course data.
     */
    public function __construct($statement) {

        foreach ($statement as $key => $value) {

            // If the key exists as a property, set it.
            if (property_exists($this, $key)) {
                $this->$key = $value;

                // We want the ID to be null here, so we can assign it later.
                if ($key === 'id') {
                    $this->$key = null;
                }
            }
        }
    }
}
