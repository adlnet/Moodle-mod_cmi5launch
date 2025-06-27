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
 * The restore steps for cmi5 launch activity.
 *
 * @package    mod_cmi5launch
 * @copyright  2025 Megan Bohland
 * @copyright  Based on work by 2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/cmi5launch/backup/moodle2/restore_cmi5launch_stepslib.php'); // Because it exists (must).

/**
 * cmi5launch restore activity task that provides the structure for the restore.
 *
 * @package    mod_cmi5launch
 */
class restore_cmi5launch_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have.
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     *
     * @return void
     */
    protected function define_my_steps() {
        // Choice only has one structure step.
        $this->add_step(new restore_cmi5launch_activity_structure_step('cmi5launch_structure', 'cmi5launch.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('cmi5launch', ['intro'], 'cmi5launch');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_rules() {
        $rules = [];

        // List of cmi5launchs in course.
        $rules[] = new restore_decode_rule('CMI5LAUNCHINDEX', '/mod/cmi5launch/index.php?id=$1', 'course');

        // Cmi5launch viewed by cmid (common link form).
        $rules[] = new restore_decode_rule('CMI5LAUNCHVIEWBYID', '/mod/cmi5launch/view.php?id=$1', 'course_module');

        // Cmi5launch viewed by instance id.
        $rules[] = new restore_decode_rule('CMI5LAUNCHVIEWBYB', '/mod/cmi5launch/view.php?b=$1', 'cmi5launch');

        // Convert old cmi5launch links MDL-33362 & MDL-35007.
        $rules[] = new restore_decode_rule('CMI5LAUNCHSTART', '/mod/cmi5launch/view.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the restore_logs_processor when restoring cmi5launch logs.
     * It must return one array of objects.
     * @return array
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('cmi5launch', 'add', 'view.php?id={course_module}', '{cmi5launch}');
        $rules[] = new restore_log_rule('cmi5launch', 'update', 'view.php?id={course_module}', '{cmi5launch}');
        $rules[] = new restore_log_rule('cmi5launch', 'view', 'view.php?id={course_module}', '{cmi5launch}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the restore_logs_processor when restoring course logs.
     * It must return one array of objects.
     *
     * Note these rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All are rules not linked to any module instance (cmid = 0).
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('cmi5launch', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
