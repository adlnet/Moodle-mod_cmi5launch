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
 * Define all the restore steps that will be used by the restore_cmi5launch_activity_task
 *
 * @package    mod_cmi5launch
 * @copyright  2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one cmi5launch activity
 */
class restore_tincan_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('cmi5launch', '/activity/cmi5launch');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process cmi5launch tag information
     * @param array $data information
     */
    protected function process_cmi5launch($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('cmi5launch', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        global $DB;

        // Add cmi5launch related files.
        $this->add_related_files('mod_cmi5launch', 'intro', null);
    }
}
