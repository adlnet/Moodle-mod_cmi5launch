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
 * Class to implement Moodle's privacy APIs.
 *
 * @copyright  2024 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

 namespace mod_cmi5launch\privacy;

 use core_privacy\local\metadata\collection;
 use core_privacy\local\request\approved_contextlist;
 use core_privacy\local\request\approved_userlist;
 use core_privacy\local\request\contextlist;
 use core_privacy\local\request\helper;
 use core_privacy\local\request\userlist;
 use core_privacy\local\request\writer;

/**
 * Class to implement Privacy APIs for the cmi5launch module.
 */
class provider implements
        
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {


    /**
     * Retreives relevant userdata.
     * @param \core_privacy\local\metadata\collection $collection - The cmi5 module collection.
     * @return collection - The data collection for the cmi5launch module.
     */
    public static function get_metadata(collection $collection): collection {

        // Database tables.
        $collection->add_database_table(
            'cmi5launch_usercourse',
            [
                'id' => 'privacy:metadata:cmi5launch_usercourse:id',
                'userid' => 'privacy:metadata:cmi5launch_usercourse:userid',
                'registrationid' => 'privacy:metadata:cmi5launch_usercourse:registrationid',
                'ausgrades' => 'privacy:metadata:cmi5launch_usercourse:ausgrades',
                'grade' => 'privacy:metadata:cmi5launch_usercourse:grade',
            ],

            'privacy:metadata:cmi5launch_usercourse',
        );
        $collection->add_database_table(
            'cmi5launch_sessions',
            [
                'id' => 'privacy:metadata:cmi5launch_sessions:id',
                'sessionid' => 'privacy:metadata:cmi5launch_sessions:sessionid',
                'userid' => 'privacy:metadata:cmi5launch_sessions:userid',
                'registrationscoursesausid' => 'privacy:metadata:cmi5launch_sessions:registrationscoursesausid',
                'createdat' => 'privacy:metadata:cmi5launch_sessions:createdat',
                'updatedat' => 'privacy:metadata:cmi5launch_sessions:updatedat',
                'code' => 'privacy:metadata:cmi5launch_sessions:code',
                'launchtokenid' => 'privacy:metadata:cmi5launch_sessions:launchtokenid',
                'lastrequesttime' => 'privacy:metadata:cmi5launch_sessions:lastrequesttime',
                'score' => 'privacy:metadata:cmi5launch_sessions:score',
                'islaunched' => 'privacy:metadata:cmi5launch_sessions:islaunched',
                'isinitialized' => 'privacy:metadata:cmi5launch_sessions:isinitialized',
                'initializedat' => 'privacy:metadata:cmi5launch_sessions:initializedat',
                'duration' => 'privacy:metadata:cmi5launch_sessions:duration',
                'iscompleted' => 'privacy:metadata:cmi5launch_sessions:iscompleted',
                'ispassed' => 'privacy:metadata:cmi5launch_sessions:ispassed',
                'isfailed' => 'privacy:metadata:cmi5launch_sessions:isfailed',
                'isterminated' => 'privacy:metadata:cmi5launch_sessions:isterminated',
                'isabandoned' => 'privacy:metadata:cmi5launch_sessions:isabandoned',
                'progress' => 'privacy:metadata:cmi5launch_sessions:progress',
                'launchurl' => 'privacy:metadata:cmi5launch_sessions:launchurl',

            ],

            'privacy:metadata:cmi5launch_sessions',
        );

        $collection->add_database_table(
            'cmi5launch_aus',
            [
                'id' => 'privacy:metadata:cmi5launch_aus:id',
                'userid' => 'privacy:metadata:cmi5launch_aus:userid',
                'attempt' => 'privacy:metadata:cmi5launch_aus:attempt',
                'lmsid' => 'privacy:metadata:cmi5launch_aus:lmsid',
                'completed' => 'privacy:metadata:cmi5launch_aus:completed',
                'passed' => 'privacy:metadata:cmi5launch_aus:passed',
                'inprogress' => 'privacy:metadata:cmi5launch_aus:inprogress',
                'noattempt' => 'privacy:metadata:cmi5launch_aus:noattempt',
                'satisfied' => 'privacy:metadata:cmi5launch_aus:satisfied',
                'sessions' => 'privacy:metadata:cmi5launch_aus:sessions',
                'scores' => 'privacy:metadata:cmi5launch_aus:scores',
                'grade' => 'privacy:metadata:cmi5launch_aus:grade',
            ],

            'privacy:metadata:cmi5launch_aus',
        );

        // External systems.
        $collection->add_external_location_link('lrs', [
            'registrationid' => 'privacy:metadata:lrs:registrationid',
            'createdat' => 'privacy:metadata:lrs:createdat',
        ], 'privacy:metadata:lrs');

        $collection->add_external_location_link('cmi5_player', [
            'registrationid' => 'privacy:metadata:cmi5_player:registrationid',
            'actor' => 'privacy:metadata:cmi5_player:actor',
            'courseid' => 'privacy:metadata:cmi5_player:courseid',
            'returnurl' => 'privacy:metadata:cmi5_player:returnurl',
            'sessionid' => 'privacy:metadata:cmi5_player:sessionid',
        ], 'privacy:metadata:cmi5_player');

        return $collection;
    }


     /**
      * Export all user data for the specified user, in the specified contexts.
      *
      * @param approved_contextlist $contextlist The approved contexts to export information for.
      */
    public static function export_user_data(approved_contextlist $contextlist) {

        global $DB;

        if (empty($contextlist)) {
            echo"empty contexts";
            return;
        }

        // Get the user.
        $user = $contextlist->get_user();
        $userid = $user->id;

        // Get the list of contexts that contain user information for the specified user.
        // (The context->instanceid = cm->id and the cm.instance equals moodlecourseid).
        foreach ($contextlist as $context) {

            $data = helper::get_context_data($context, $user);

            // Retrieve the coursemodule.
            $cm = get_coursemodule_from_id('cmi5launch', $context->instanceid);

            // The course modules instance correlates to the moodle course id in our tables.
            // Combined with the user id, we can get the specific records we need.
            $mid = $cm->instance;
            $params = ['userid' => $userid,   'moodlecourseid' => $mid];

            // Start getting data on usercourse table.
            $recordset = $DB->get_recordset('cmi5launch_usercourse', $params);

            // To hold the course data.
            $coursedata = [];

            // Cycle through recordset in case there are multiple.
            foreach ($recordset as $record) {

                // Make user friendly names for data.
                $userfriendly = [ 'ID of instance' => $record->id,
                    'User ID' => $record->userid,
                    'Course ID' => $record->courseid,
                    'Moodle Course ID' => $record->moodlecourseid,
                    'Registration ID' => $record->registrationid,
                    'Return URL' => $record->returnurl,
                    'AUs of instance' => $record->aus,
                    'Grades of AUs' => $record->ausgrades,
                    'Overall grade of instance' => $record->grade];

                // Then add as ONE item in array, that way if there is more than one it unpacks nicely.
                $coursedata = ['User information' => $userfriendly];
            }

            // Combine the course data with the usercourse data.
            $contextdata = (object)array_merge((array)$data, $coursedata);

            // Write data out.
            writer::with_context($context)->export_data(
                    ['Course info pertaining to user'],
                    (object) $contextdata
            );

            // Now get the AU data.
            $recordset = $DB->get_recordset('cmi5launch_aus', $params);

            // To hold the AU data.
            $ausdata = [];

            // Cycle through recordset in case there are multiple.
            foreach ($recordset as $record) {

                // Make user friendly names for data display.
                $userfriendly = [ 'ID of AU instance' => $record->id,
                    'User ID' => $record->userid,
                    'The attempt number of the AU' => $record->attempt,
                    'LMS ID of the AU' => $record->lmsid,
                    'Moodle Course ID' => $record->moodlecourseid,
                    'URL of the AU' => $record->url,
                    'The type of the AU' => $record->type,
                    'The title of the AU' => $record->title,
                    'The description of the AU' => $record->description,
                    'The objectives of the AU' => $record->objectives,
                    'The move on value of the AU' => $record->moveon,
                    'The AU index of the AU' => $record->auindex,
                    'The cmi5 activity type of the AU' => $record->activitytype,
                    'The amount it goes to mastery score' => $record->masteryscore,
                    'Whether it has been completed' => $record->completed,
                    'Whether it has been passed' => $record->passed,
                    'Whether it is in progress' => $record->inprogress,
                    'Whether it has been satisfied' => $record->satisfied,
                    'Whether it has not been attempted' => $record->noattempt,
                    'This AUs individual session\'s IDs' => $record->sessions,
                    'The scores of the AU, as array' => $record->scores,
                    'The overall grade of the AU' => $record->grade,
                ];

                // Then add as ONE item in array, that way if there is more than one it unpacks nicely.
                $ausdata = ['AU info' => $userfriendly];
            }

            // Combine the course data with the au data.
            $contextdata = (object)array_merge((array)$data, $ausdata);

            // Write data out.
            writer::with_context($context)->export_data(
                ['AU info pertaining to user'],
                (object) $contextdata
            );

            // Now get the sessions data.
            $recordset = $DB->get_recordset('cmi5launch_sessions', $params);

            // To hold session information.
            $sessiondata = [];

            // Cycle through recordset in case there are multiple.
            foreach ($recordset as $record) {

                // Make user friendly names for display.
                $userfriendly = [ 'ID of session instance' => $record->id,
                'Session ID' => $record->sessionid,
                'User ID' => $record->userid,
                'Moodle Course ID' => $record->moodlecourseid,
                'Registration Courses AUs ID' => $record->registrationscoursesausid,
                'Time a session was started' => $record->creeatedat,
                'Time a session was updated' => $record->updatedat,
                'Code' => $record->code,
                'Launch token ID' => $record->launchtokenid,
                'Last request time' => $record->lastrequesttime,
                'Amount it goes to Mastery Score' => $record->masteryscore,
                'The score of the session' => $record->score,
                'If it was launched' => $record->islaunched,
                'If it was initialized' => $record->isinitialized,
                'Time it was initialized' => $record->initializedat,
                'Duration of session' => $record->duration,
                'If it was completed' => $record->iscompleted,
                'If it was passed' => $record->ispassed,
                'If it was failed' => $record->isfailed,
                'If it was terminated' => $record->isterminated,
                'If it was abandoned' => $record->isabandoned,
                'Progress of session in recordments from LRS' => ("<pre>" . implode("\n ",
                    json_decode($record->progress) ) . "</pre>"),
                'Launch URL' => $record->launchurl];

                // Then add as ONE item in array, that way if there is more than one it unpacks nicely.
                $sessiondata = ['Session info' => $userfriendly];
            }

            // Combine the course data with the session data.
            $contextdata = (object)array_merge((array)$data, $sessiondata);

            // Write data out.
            writer::with_context($context)->export_data(
            ['Session info pertaining to user'],
            (object) $contextdata
            );

        }

    }


    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        global $DB;

        $sql = "SELECT ctx.id
        FROM {context} ctx
        JOIN {course_modules} cm
        ON cm.id = ctx.instanceid
        AND ctx.contextlevel = :context
        JOIN {modules} m
        ON m.id = cm.module
        AND m.name = 'cmi5launch'
        JOIN {%s} c5l
        ON c5l.moodlecourseid = cm.instance
        WHERE c5l.userid = :userid";

        $params = ['context' => CONTEXT_MODULE, 'userid' => $userid];

        $contextlist = new contextlist();
        $contextlist->add_from_sql(sprintf($sql, 'cmi5launch_usercourse'), $params);
        $contextlist->add_from_sql(sprintf($sql, 'cmi5launch_sessions'), $params);
        $contextlist->add_from_sql(sprintf($sql, 'cmi5launch_aus'), $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $sql = "SELECT c5l.userid
            FROM {%s} c5l
            JOIN {modules} m
            ON m.name = 'cmi5launch'
            JOIN {course_modules} cm
            ON cm.module = m.id
            JOIN {context} ctx
            ON ctx.instanceid = cm.id
            AND ctx.contextlevel = :modlevel
            WHERE ctx.id = :contextid";

        $params = ['modlevel' => CONTEXT_MODULE, 'contextid' => $context->id];

        $userlist->add_from_sql('userid', sprintf($sql, 'cmi5launch_usercourse'), $params);
        $userlist->add_from_sql('userid', sprintf($sql, 'cmi5launch_sessions'), $params);
        $userlist->add_from_sql('userid', sprintf($sql, 'cmi5launch_aus'), $params);

    }

    /**
     * Delete all user data which matches the specified context.
     *
     * @param context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {

        global $DB;

        // This should not happen, but just in case.
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('cmi5launch', $context->instanceid);
        if (!$cm) {
            return;
        }

        // This table needs a diferent key, but to be deleted still.
        $DB->delete_records('cmi5launch', ['id' => $cm->instance]);

        // Tables to delete from with same key.
        $tables = ['cmi5launch_usercourse', 'cmi5launch_sessions', 'cmi5launch_aus'];

        foreach ($tables as $table) {

            $DB->delete_records($table, ['moodlecourseid' => $cm->instance]);

        }
    }


    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {

            // Retrieve the instance id from the context.
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

            // Tables to delete from with same key if context matches.
            $tables = ['cmi5launch_usercourse', 'cmi5launch_sessions', 'cmi5launch_aus'];

            foreach ($tables as $table) {

                $sql = ["moodlecourseid" => $instanceid, "userid" => $userid];

                $deleted = $DB->delete_records($table, $sql);

            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {

        global $DB;

        $context = $userlist->get_context();

        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['moodlecourseid' => $cm->instance], $userinparams);
        $sql = "moodlecourseid = :moodlecourseid AND userid {$userinsql}";

        $DB->delete_records_select('cmi5launch_usercourse', $sql, $params);
        $DB->delete_records_select('cmi5launch_sessions', $sql, $params);
        $DB->delete_records_select('cmi5launch_aus', $sql, $params);
    }


}
