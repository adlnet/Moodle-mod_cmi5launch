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
 * @copyright 2023 Megan Bohland
 * @copyright  Based on work by 2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one cmi5launch activity
 */
class restore_cmi5launch_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('cmi5launch', '/activity/cmi5launch');
        $paths[] = new restore_path_element('usercourse', '/activity/cmi5launch/usercourses/usercourse');
        $paths[] = new restore_path_element('au', '/activity/cmi5launch/aus_records/au');
        $paths[] = new restore_path_element('session', '/activity/cmi5launch/session_records/session');

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
    
        // Set timestamps if not already defined.
        if (!isset($data->timecreated)) {
            $data->timecreated = time();
        }
        if (!isset($data->timemodified)) {
            $data->timemodified = $data->timecreated;
        }
    
        $newitemid = $DB->insert_record('cmi5launch', $data);
    
        // Link this instance to the course module.
        $this->apply_activity_instance($newitemid);
    
        // Save mapping for other tables.
        $this->set_mapping('cmi5launch', $oldid, $newitemid);
    }

    // The tables must be restored in this order as session ids are mapped and stored in AUS, and AUs to usercourses.
    protected function process_session($data) {
        global $DB;
    
        $data = (object)$data;
        $oldid = $data->id;
    
        // Remap user and course foreign keys.
        if (!empty($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }
        
        // Remap course foreign key.
        if (!empty($data->moodlecourseid)) {
            $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);
        }

    
        //Check for existing records to prevent duplicates. 
        $existing = $DB->get_record('cmi5launch_sessions', [
            'userid' => $data->userid,
            'sessionid' => $data->sessionid,
        ]);

        if ($existing) {
            // Check if restored data is more complete.
            $restore_has_more_data = (
                isset($data->score) ||
                isset($data->iscompleted) ||
                isset($data->ispassed) ||
                isset($data->isffailed) ||
                isset($data->isterminated) ||
                isset($data->isabandoned)
            );
            if ($restore_has_more_data) {
                $data->id = $existing->id;
                $DB->update_record('cmi5launch_sessions', $data);
                debugging("Updated session with better data for userid={$data->userid}");
                $newitemid = $existing->id;
            } else {
                debugging("Skipped duplicate session for userid={$data->userid}, kept existing");
                $newitemid = $existing->id;
            }
        } else {
            $newitemid = $DB->insert_record('cmi5launch_sessions', $data);
        }
        // Save mapping so we can remap session IDs in aus.scores and aus.sessions
        $this->set_mapping('cmi5launch_sessions', $oldid, $newitemid);
    }
    
    protected function process_au($data) {
        global $DB;
    
        $data = (object)$data;
        $oldid = $data->id;
    
        // Remap foreign keys.
        if (!empty($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }
    
        $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);
    
        // Remap session IDs in array field.
        if (!empty($data->sessions)) {
            $sessionids = json_decode($data->sessions, true);
            $newids = [];
    
            foreach ($sessionids as $oldsessionid) {
                $newid = $this->get_mappingid('cmi5launch_sessions', $oldsessionid);
                if ($newid !== false) {
                    $newids[] = $newid;
                }
            }
    
            $data->sessions = json_encode($newids);
        }
        
        //Check for existing in case of duplicates.
        $existing = $DB->get_record('cmi5launch_aus', [
            'userid' => $data->userid,
            'moodlecourseid' => $data->moodlecourseid,
        ]);

        if ($existing) {
            // Check if restored data is more complete.
            $restore_has_more_data = (
                !empty($data->sessions) ||
                !empty($data->scores) ||
                isset($data->grade)
            );
            if ($restore_has_more_data) {
                $data->id = $existing->id;
                $DB->update_record('cmi5launch_aus', $data);
                debugging("Updated aus tables with better data for userid={$data->userid}");
                $newitemid = $existing->id;
            } else {
                debugging("Skipped duplicate aus record for userid={$data->userid}, kept existing");
                $newitemid = $existing->id;
            }
        } else {
            // Insert AU.
            $newitemid = $DB->insert_record('cmi5launch_aus', $data);
        }

    
        // Store mapping for use in usercourse 'aus' JSON.
        $this->set_mapping('cmi5launch_aus', $oldid, $newitemid);
    }
    
    protected function process_usercourse($data) {
        global $DB;
    
        $data = (object)$data;
        $oldid = $data->id;
    
        // Fix foreign keys.
        $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);
        $data->userid = $this->get_mappingid('user', $data->userid);
    
        // Set courseid from current course.
        $data->courseid = $this->get_courseid();

        // Decode and remap AU IDs.
        if (!empty($data->aus)) {
            $oldaus = json_decode($data->aus);
            $newaus = [];
    
            foreach ($oldaus as $oldauid) {
                $newauid = $this->get_mappingid('cmi5launch_aus', $oldauid);
                if ($newauid !== false) {
                    $newaus[] = $newauid;
                }
            }
    
            $data->aus = json_encode($newaus);
        }
    
        $existing = $DB->get_record('cmi5launch_usercourse', [
            'userid' => $data->userid,
            'courseid' => $data->courseid,
            'registrationid' => $data->registrationid,
        ]);

        if ($existing) {
            // Check if restored data is more complete.
            $restore_has_more_data = (
                !empty($data->aus) ||
                !empty($data->ausgrades) ||
                isset($data->grade)
            );
            
            if ($restore_has_more_data) {
                $data->id = $existing->id;
                $DB->update_record('cmi5launch_usercourse', $data);
                debugging("Updated usercourse with better data for userid={$data->userid}");
                $newitemid = $existing->id;
            } else {
                debugging("Skipped duplicate usercourse for userid={$data->userid}, kept existing");
                $newitemid = $existing->id;
            }
        } else {
            $newitemid = $DB->insert_record('cmi5launch_usercourse', $data);
        }
    
        // Save the mapping.
        $this->set_mapping('cmi5launch_usercourse', $oldid, $newitemid);
    }
    

    

    protected function after_execute() {
        global $DB;

        // Add cmi5launch related files.
        $this->add_related_files('mod_cmi5launch', 'intro', null);
    }
}
