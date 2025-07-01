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
 * @copyright 2023 Megan Bohland
 * @copyright  Based on work by 2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to backup one cmi5launch activity
 */
class backup_cmi5launch_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure of the backup.
     *
     * @return backup_nested_element The root element of the backup structure.
     */
    protected function define_structure() {

        // Define root plugin element.
        // Note: args here are  tablename, primary key(s), and then fields.
        $cmi5launch = new backup_nested_element('cmi5launch', ['id'], ['course', 'name', 'intro', 'introformat',
            'cmi5activityid', 'registrationid', 'returnurl', 'courseid', 'cmi5verbid', 'cmi5expiry', 'overridedefaults',
            'cmi5multipleregs', 'timecreated', 'timemodified', 'courseinfo', 'aus',
        ]);

        // Add children groups
        // AUs and Sessions are not linked via keys, but an array stored in a field accessed programmatically.
        // AUs are for assignable units, which are the individual activities for each usercourse.
        $austable = new backup_nested_element('aus_records');

        $au = new backup_nested_element('au', ['id'], [ 'userid', 'attempt', 'launchmethod', 'lmsid', 'moodlecourseid', 'url',
            'type', 'title', 'moveon', 'auindex', 'parents', 'objectives', 'description', 'activitytype', 'masteryscore',
            'completed', 'passed', 'inprogress', 'noattempt', 'satisfied', 'sessions', 'scores', 'grade']);
        $austable->add_child($au);

        // Sessions, which are the individual sessions for each AU.
        $sessionstable = new backup_nested_element('session_records');

        $session = new backup_nested_element('session', ['id'], ['sessionid', 'userid', 'moodlecourseid',
            'registrationscoursesausid', 'tenantname', 'createdat', 'updatedat', 'code', 'launchtokenid', 'lastrequesttime',
            'launchmode', 'masteryscore', 'score', 'islaunched', 'isinitialized', 'initializedat', 'duration', 'iscompleted',
            'ispassed', 'isfailed', 'isterminated', 'isabandoned', 'progress', 'launchmethod', 'launchurl']);
        $sessionstable->add_child($session);

        // Data sources.
        // Link tables to root.
        $cmi5launch->add_child($sessionstable);
        $cmi5launch->add_child($austable);

        // Usercourse is a users individual cmi5launch object.
        $usercourses = new backup_nested_element('usercourses');
        $cmi5launch->add_child($usercourses);

        $usercourse = new backup_nested_element('usercourse', ['id'], ['courseid', 'moodlecourseid', 'userid', 'cmi5activityid',
            'registrationid', 'returnurl', 'aus', 'ausgrades', 'grade']);
        $usercourses->add_child($usercourse);

        // Data sources.
        $cmi5launch->set_source_table('cmi5launch', ['id' => backup::VAR_ACTIVITYID]);

        // Note: Adjust filters here if you want to limit to this activity/course.
        $usercourse->set_source_table('cmi5launch_usercourse', ['moodlecourseid' => backup::VAR_ACTIVITYID]); // No parent filter.
        $au->set_source_table('cmi5launch_aus', ['moodlecourseid' => backup::VAR_ACTIVITYID]); // No parent filter.
        $session->set_source_table('cmi5launch_sessions', ['moodlecourseid' => backup::VAR_ACTIVITYID]); // No parent filter.
        // Return the root element (cmi5launch), wrapped into standard activity structure.
        return $this->prepare_activity_structure($cmi5launch);
    }
}
