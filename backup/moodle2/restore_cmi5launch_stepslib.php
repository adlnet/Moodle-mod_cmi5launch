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


/**
 * Structure step to restore one cmi5launch activity
 */
class restore_cmi5launch_activity_structure_step extends restore_activity_structure_step {


    /**
     * Store original session IDs to remap after execution.
     * @var array
     */
    private $arrayofoldsessionids = [];
    /**
     * Store original AU IDs to remap after execution.
     * @var array
     */
    private $arrayofoldauids = [];
    /**
     * Store new session IDs to remap after execution
     * @var array
     */
    private $arrayofnewsessionids = [];
    /**
     * Store new AU IDs to remap after execution.
     * @var array
     */
    private $arrayofnewauids = []; // Store new AU IDs to remap after execution.

    /**
     * Define order and structure of the restore process.
     */
    protected function define_structure() {
        $paths = [];

        // Core activity must come first.
        $paths[] = new restore_path_element('cmi5launch', '/activity/cmi5launch');

        // Sessions MUST be restored before AUs, since AUs rely on session ID mappings.
        $paths[] = new restore_path_element('session', '/activity/cmi5launch/session_records/session');
        $paths[] = new restore_path_element('au', '/activity/cmi5launch/aus_records/au');

        // Then usercourses (which rely on AUS remapping).
        $paths[] = new restore_path_element('usercourse', '/activity/cmi5launch/usercourses/usercourse');

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process cmi5launch tag information
     * @param array $data The restore data.
     */
    protected function process_cmi5launch($data) {
        global $DB;

        $data = (object) $data;
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

    // The tables must be restored in the following order as session ids are mapped and stored in AUS, and AUs to usercourses.

    /**
     * Remap amd restore session records.
     * @param mixed $data - The data to restore.
     * @return void
     */
    protected function process_session($data) {
        global $DB;
        debugging("Restoring session data", DEBUG_DEVELOPER);
        $data = (object) $data;
        $oldid = $data->id;

        // Remap user and course foreign keys.
        if (!empty($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        // Remap course foreign key.
        if (!empty($data->moodlecourseid)) {
            $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);
        }

        // Add current id to oldsession id array for remapping later.
        $this->arrayofoldsessionids[] = $oldid;
        debugging("Processing session with old ID {$oldid}", DEBUG_DEVELOPER);

        $newitemid = $DB->insert_record('cmi5launch_sessions', $data);
        debugging("Inserted session with new ID {$newitemid}", DEBUG_DEVELOPER);

        // Add new id to newsessionid array for remapping later.
        $this->arrayofnewsessionids[] = $newitemid;
        debugging("New session ID {$newitemid} added to array for remapping later", DEBUG_DEVELOPER);

        // Save mapping so we can remap session IDs in aus.scores and aus.sessions.
        $this->set_mapping('cmi5launch_sessions', $oldid, $newitemid);
        debugging("Restored session: old ID {$oldid} is now mapped to new DB ID {$newitemid}", DEBUG_DEVELOPER);

    }

    /**
     * Process and restore AU records.
     * This function handles the restoration of AU records, remapping user and course IDs.
     * @param mixed $data - The data to restore.
     * @return void
     */
    protected function process_au($data) {
        global $DB;
        debugging("Restoring AU data", DEBUG_DEVELOPER);
        $data = (object) $data;
        $oldid = $data->id;

        // Remap foreign keys.
        if (!empty($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);

        // Add oldid to oldauid array for remapping later.
        $this->arrayofoldauids[] = $oldid;
        debugging("Processing AU with old ID {$oldid}", DEBUG_DEVELOPER);

        // Check for existing in case of duplicates.
        $existing = $DB->get_record('cmi5launch_aus', [
            'userid' => $data->userid,
            'moodlecourseid' => $data->moodlecourseid,
            'lmsid' => $data->lmsid,
        ]);

        if ($existing) {
            // Check if restored data is more complete.
            $restorehasmoredata = (
                !empty($data->sessions) ||
                !empty($data->scores) ||
                isset($data->grade)
            );
            if ($restorehasmoredata) {
                $data->id = $existing->id;
                $DB->update_record('cmi5launch_aus', $data);
                debugging("Updated existing AU with better data for userid={$data->userid}", DEBUG_DEVELOPER);

                $newitemid = $existing->id;
                // Add new id to newauid array for remapping later.
                $this->arrayofnewauids[] = $newitemid;
                debugging("New AU ID {$newitemid} added to array for remapping later", DEBUG_DEVELOPER);

            } else {
                debugging("Skipped duplicate AU record for userid={$data->userid}, kept existing", DEBUG_DEVELOPER);

                $newitemid = $DB->insert_record('cmi5launch_aus', $data);
                // Add new id to newauid array for remapping later.
                $this->arrayofnewauids[] = $newitemid;
                debugging("New AU ID {$newitemid} added to array for remapping later", DEBUG_DEVELOPER);

            }
        } else {
            debugging("Final cleaned sessions for AU {$oldid}: " . $data->sessions, DEBUG_DEVELOPER);
            // Insert AU.
            $newitemid = $DB->insert_record('cmi5launch_aus', $data);
            // Add new id to newauid array for remapping later.
            $this->arrayofnewauids[] = $newitemid;
            debugging("New AU ID {$newitemid} added to array for remapping later", DEBUG_DEVELOPER);

        }

        // Store mapping for use in usercourse 'aus' JSON.
        $this->set_mapping('cmi5launch_aus', $oldid, $newitemid);// Save remap info.

        $this->au_dbid_map[$oldid] = $newitemid;
    }

    /**
     * Process and restore usercourse records.
     * This function handles the restoration of usercourse records, remapping user and course IDs.
     * @param mixed $data The data to restore.
     * @return void
     */
    protected function process_usercourse($data) {
        global $DB;

        debugging("Restoring usercourse" . PHP_EOL, DEBUG_DEVELOPER);

        $data = (object) $data;
        $oldid = $data->id;

        // Fix foreign keys.
        $data->moodlecourseid = $this->get_mappingid('cmi5launch', $data->moodlecourseid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Set courseid from current course.
        $data->courseid = $this->get_courseid();

        // Preserve original AU IDs for later remapping in after_execute.
        if (!empty($data->aus)) {
            $this->usercourse_aus_originals[$oldid] = json_decode($data->aus);
        }

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
            $restorehasmoredata = (
                !empty($data->aus) ||
                !empty($data->ausgrades) ||
                isset($data->grade)
            );

            if ($restorehasmoredata) {
                $data->id = $existing->id;
                $DB->update_record('cmi5launch_usercourse', $data);

                debugging("Updated usercourse with better data for userid={$data->userid}", DEBUG_DEVELOPER);

                $newitemid = $existing->id;
            } else {

                debugging("Skipped duplicate usercourse for userid={$data->userid}, kept existing", DEBUG_DEVELOPER);
                $newitemid = $existing->id;
            }
        } else {
            $newitemid = $DB->insert_record('cmi5launch_usercourse', $data);
        }

        // Save the mapping.
        $this->set_mapping('cmi5launch_usercourse', $oldid, $newitemid);
    }


    /**
     * After execute function to remap session IDs in AUs and update them.
     * This function is called after the restore process is complete.
     * It remaps session IDs in AUs and updates the AU records accordingly.
     */
    protected function after_execute() {
        global $DB;

        // Add cmi5launch related files.
        $this->add_related_files('mod_cmi5launch', 'intro', null);

        // Remap session IDs in AUs now that all mappings exist.
        $aus = $DB->get_records('cmi5launch_aus');

        // For each new AU ID, get record, remap sessions and resave.
        foreach ($this->arrayofnewauids as $auid) {

            $au = $DB->get_record('cmi5launch_aus', ['id' => $auid]);

            // Decode existing sessions.
            $oldsessions = json_decode($au->sessions, true);

            // New session ids.
            $newsessionids = [];

            foreach ($oldsessions as $oldsessionid) {
                // Remap each session ID.
                $newsessionid = $this->get_mappingid('cmi5launch_sessions', $oldsessionid);
                if ($newsessionid !== false) {
                    $newsessionids[] = $newsessionid; // Store new session ID.
                    debugging("Saved new session ID {$newsessionid} for AU {$auid}", DEBUG_DEVELOPER);
                } else {
                    debugging("Failed to remap session ID {$oldsessionid} for AU {$auid}", DEBUG_DEVELOPER);
                }
            }
                // Once done with all session IDs for this AU, update the AU record.
                $au->sessions = json_encode($newsessionids);
                $DB->update_record('cmi5launch_aus', $au);
                debugging("Updated AU {$auid} with new session IDs: " . implode(', ', $newsessionids), DEBUG_DEVELOPER);
        }

    }

}
