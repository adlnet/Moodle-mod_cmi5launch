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
 * Class to handle Assignable Units.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */
namespace mod_cmi5launch\local;
defined('MOODLE_INTERNAL') || die();

// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

/**
 * Class au
 *
 * This class is used to create an AU object from a statement.
 * It is used to handle the data for an AU in the cmi5launch module.
 *
 * @package mod_cmi5launch\local
 */
class au {

    /**
     * @var int|null AU ID from the Moodle database.
     */
    public $id;

    /**
     * @var int|null Attempt number.
     */
    public $attempt;

    /**
     * @var string|null URL for the AU.
     */
    public $url;

    /**
     * @var string|null Type of AU.
     */
    public $type;

    /**
     * @var string|null LMS ID of the AU from cmi5 player.
     */
    public $lmsid;

    /**
     * @var float|null Grade achieved.
     */
    public $grade;

    /**
     * @var mixed|null Score data.
     */
    public $scores;

    /**
     * @var string|null Title of the AU.
     */
    public $title;

    /**
     * @var string|null MoveOn criteria.
     */
    public $moveon;

    /**
     * @var int|null Index of the AU within the cmi5 object.
     */
    public $auindex;

    /**
     * @var mixed|null Parent AU relationships.
     */
    public $parents;

    /**
     * @var mixed|null Objectives associated with the AU.
     */
    public $objectives;

    /**
     * @var string|null Description of the AU.
     */
    public $description;

    /**
     * @var string|null Type of activity.
     */
    public $activitytype;

    /**
     * @var string|null Launch method for the AU.
     */
    public $launchmethod;

    /**
     * @var float|null Mastery score threshold.
     */
    public $masteryscore;

    /**
     * @var bool|null Whether the AU was satisfied.
     */
    public $satisfied;

    /**
     * @var string|null URL used to launch the AU (from the cmi5 player).
     */
    public $launchurl;

    /**
     * @var array|null Array to hold linked session ids..
     */
    public $sessions;

    /**
     * @var mixed|null Progress data for the AU.
     */
    public $progress;

    /**
     * @var bool|null Indicates if there is no attempt.
     */
    public $noattempt;

    /**
     * @var bool|null Whether the AU was completed.
     */
    public $completed;

    /**
     * @var bool|null Whether the AU was passed.
     */
    public $passed;

    /**
     * @var bool|null Whether the AU is in progress.
     */
    public $inprogress;

    /**
     * @var int|null ID of the user.
     */
    public $userid;

    /**
     * @var int|null Moodle course ID (matches the id of cmi5 object/course this AU is part of).
     */
    public $moodlecourseid;

    // The following HAVE to be PascalCase as they are for compatibility with the cmi5 player statements.
    // Even though this is against Moodle coding standards.

    /**
     * @var string|null Launch method from player input (PascalCase).
     */
    public $launchMethod;

    /**
     * @var string|null LMS ID from player input (PascalCase).
     */
    public $lmsId;

    /**
     * @var string|null MoveOn from player input (PascalCase).
     */
    public $moveOn;

    /**
     * @var int|null AU index from player input (PascalCase).
     */
    public $auIndex;

    /**
     * @var string|null Activity type from player input (PascalCase).
     */
    public $activityType;

    /**
     * @var float|null Mastery score from player input (PascalCase).
     */
    public $masteryScore;

    
    /**
     * Constructs AUs. Is fed array and where array key matches property, sets the property.
     * @param mixed $statement - Data to make AU.
     * @throws \mod_cmi5launch\local\nullException
     */
    public function __construct($statement) {

        // Or that the statement is not an array or not an object.
        if (is_null($statement) || (!is_array($statement) && !is_object($statement) )) {

            throw new nullException(get_string('cmi5launchaubuilderror', 'cmi5launch'), 0);
        }
        // If it is an array, create the object.
        foreach ($statement as $key => $value) {

            $this->$key = ($value);
        }
    }
}
