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
 * Define all the backup steps that will be used by the backup_cmi5launch_activity_task
 *
 * @package    mod_cmi5launch
 * @copyright  2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to backup one cmi5launch activity
 */
class backup_cmi5launch_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated.
        $cmi5launch = new backup_nested_element('cmi5launch', array('id'), array(
            'name', 'intro', 'introformat', 'cmi5launchurl', 'cmi5activityid',
            'cmi5verbid', 'overridedefaults', 'cmi5multipleregs', 'timecreated',
            'timemodified', 'courseinfo', 'aus', 'grade'));

        // Define sources.
        $cmi5launch->set_source_table('cmi5launch', array('id' => backup::VAR_ACTIVITYID));

        // Return the root element (cmi5launch), wrapped into standard activity structure.
        return $this->prepare_activity_structure($cmi5launch);
    }
}
