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
 * @package mod_cmi5launch
 */


namespace mod_cmi5launch\local;

defined(constant_name: 'MOODLE_INTERNAL') || die();

// Include the errorover (error override) funcs.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/errorover.php');

/**
 * A class to hold session properties and methods.
 *
 * @package mod_cmi5launch\local
 */
class session {

    // Even though  it is not Moodle standard, some properties are pascal-case.
    // This is because this is as they are in cmi5 player, and needs to match for communication.

    /**
     * @var string|null Session ID in Moodle DB.
     */
    public $id;

    /**
     * @var string|null Name of the tenant.
     */
    public $tenantname;

    /**
     * @var string|null ID of the tenant.
     */
    public $tenantId;

    /**
     * @var string|null The IDs of the AUs in this course (assigned by player and unique).
     */
    public $registrationsCoursesAusId;

    /**
     * @var string|null LMS-specific ID (Unique and assigned by player).
     */
    public $lmsid;

    /**
     * @var array Progress information (e.g., scores, completion status).
     */
    public $progress = [];

    /**
     * @var string|null Launch URL.
     */
    public $launchurl;

    /**
     * @var float|null Grade assigned in this session.
     */
    public $grade;

    /**
     * @var string|null Timestamp when the session was created.
     */
    public $createdAt;

    /**
     * @var string|null Timestamp when the session was last updated.
     */
    public $updatedAt;

    /**
     * @var string|null Registration code or token (Unique value assigned by player).
     */
    public $code;

    /**
     * @var string|null Timestamp of the last activity/request.
     */
    public $lastRequestTime;

    /**
     * @var string|null Token ID used to launch this session.
     */
    public $launchTokenId;

    /**
     * @var string|null Launch mode used (e.g., OwnWindow, AnyWindow).
     */
    public $launchMode;

    /**
     * @var float|null Mastery score if AU is completed.
     */
    public $masteryScore;

    /**
     * @var bool|null Whether the session has been launched.
     */
    public $isLaunched;

    /**
     * @var bool|null Whether the session has been initialized.
     */
    public $isInitialized;

    /**
     * @var string|null Timestamp of initialization.
     */
    public $initializedAt;

    /**
     * @var bool|null Whether the session was completed.
     */
    public $isCompleted;

    /**
     * @var bool|null Whether the session was passed.
     */
    public $isPassed;

    /**
     * @var bool|null Whether the session was failed.
     */
    public $isFailed;

    /**
     * @var bool|null Whether the session was terminated.
     */
    public $isTerminated;

    /**
     * @var bool|null Whether the session was abandoned.
     */
    public $isAbandoned;

    /**
     * @var string|null Moodle ID of the course associated with the session.
     */
    public $courseid;

    /**
     * @var bool|null Whether the session ended with a completed status.
     */
    public $completed;

    /**
     * @var bool|null Whether the session ended with a passed status.
     */
    public $passed;

    /**
     * @var bool|null Whether the session is in progress.
     */
    public $inprogress;

    // Properties for saving to the Moodle DB (lowercase keys).

    /**
     * @var string|null Session ID (Moodle DB field).
     */
    public $sessionid;

    /**
     * @var int|null User ID (Moodle).
     */
    public $userid;

    /**
     * @var string|null Registration-course-AU ID (unique value from cmi5 player).
     */
    public $registrationscoursesausid;

    /**
     * @var string|null Session creation time.
     */
    public $createdat;

    /**
     * @var string|null Last update time.
     */
    public $updatedat;

    /**
     * @var string|null Launch token used.
     */
    public $launchtokenid;

    /**
     * @var string|null Last time the user interacted with the AU.
     */
    public $lastrequesttime;

    /**
     * @var string|null Mode in which the session was launched.
     */
    public $launchmode;

    /**
     * @var float|null Mastery score threshold.
     */
    public $masteryscore;

    /**
     * @var string|null Tenant ID from the player.
     */
    public $tenantid;

    /**
     * @var float|null Final score of the session.
     */
    public $score;

    /**
     * @var mixed|null Response data or status.
     */
    public $response;

    /**
     * @var bool|null If the session was launched.
     */
    public $islaunched;

    /**
     * @var bool|null If the session was initialized.
     */
    public $isinitialized;

    /**
     * @var string|null Initialization timestamp.
     */
    public $initializedat;

    /**
     * @var string|null Duration of the session.
     */
    public $duration;

    /**
     * @var bool|null Completion status.
     */
    public $iscompleted;

    /**
     * @var bool|null Pass status.
     */
    public $ispassed;

    /**
     * @var bool|null Fail status.
     */
    public $isfailed;

    /**
     * @var bool|null Termination status.
     */
    public $isterminated;

    /**
     * @var bool|null Abandonment status.
     */
    public $isabandoned;

    /**
     * @var string|null Launch method (e.g., 'AnyWindow', 'NewWindow').
     */
    public $launchmethod;

    /**
     * @var int|null Moodle course ID linked to the session.
     */
    public $moodlecourseid;

    /**
     * Constructs sessions. Is fed array and where array key matches property, sets the property.
     *
     * @param mixed $statement - Data to make session.
     * @throws \mod_cmi5launch\local\nullException
     */
    public function __construct($statement) {

        if (is_null($statement) || (!is_array($statement) && !is_object($statement))) {
            throw new nullException(get_string('cmi5launchsessionbuilderror', 'cmi5launch'), 0);
        }

        foreach ($statement as $key => $value) {
            $this->$key = $value;
        }
    }
}

