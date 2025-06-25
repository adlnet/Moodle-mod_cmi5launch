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
 * Checks the completion state of cmi5launch modules.
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\task;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');
require_once($CFG->dirroot.'/lib/completionlib.php');

/**
 * Scheduled task to check the completion state of cmi5launch modules.
 *
 * @package mod_cmi5launch
 * */
class check_completion extends \core\task\scheduled_task {

    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('checkcompletion', 'mod_cmi5launch');
    }

    /**
     * Execute the task.
     *
     * This function checks the completion state of all cmi5launch modules
     * and updates the completion state for each user enrolled in the course.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $module = $DB->get_record('modules', ['name' => 'cmi5launch'], '*', MUST_EXIST);
        $modules = $DB->get_records('cmi5launch');
        $courses = []; // Cache course data incase the multiple modules exist in a course.

        foreach ($modules as $cmi5launch) {
            echo ('Checking module id '.$cmi5launch->id.'. '.PHP_EOL);
            $cm = $DB->get_record(
                'course_modules',
                ['module' => $module->id,
                    'instance' => $cmi5launch->id],
                '*',
                MUST_EXIST
            );
            if (!isset($courses[$cm->course])) {
                $courses[$cm->course] = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
                $courses[$cm->course]->enrolments = $DB->get_records('user_enrolments', ['status' => 0]);
            }
            $course = $courses[$cm->course];
            $completion = new \completion_info($course);

            $possibleresult = COMPLETION_COMPLETE;

            if ($cmi5launch->cmi5expiry > 0) {
                $possibleresult = COMPLETION_UNKNOWN;
            }

            if ($completion->is_enabled($cm) && $cmi5launch->cmi5verbid) {
                foreach ($course->enrolments as $enrolment) {
                    echo ('Checking user id '.$enrolment->userid.'. ');
                    $oldstate = $completion->get_data($cm, false, $enrolment->userid)->completionstate;
                    echo ('Old completion state was '.$oldstate.'. ');
                    $completion->update_state($cm, $possibleresult, $enrolment->userid);
                    $newstate = $completion->get_data($cm, false, $enrolment->userid)->completionstate;
                    echo ('New completion state is '.$newstate.'. '.PHP_EOL);
                    if ($oldstate !== $newstate) {
                        // Trigger Activity completed event.
                        $event = \mod_cmi5launch\event\activity_completed::create([
                            'objectid' => $cmi5launch->id,
                            'context' => \context_module::instance($cm->id),
                            'userid' => $enrolment->userid,
                        ]);
                        $event->add_record_snapshot('course_modules', $cm);
                        $event->add_record_snapshot('cmi5launch', $cmi5launch);
                        $event->trigger();
                    }
                }
            }
        }
    }
}
