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
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace mod_cmi5launch\privacy;

 use core_privacy\local\metadata\collection;
 use core_privacy\local\request\approved_contextlist;
 use core_privacy\local\request\approved_userlist;
 use core_privacy\local\request\contextlist;
 use core_privacy\local\request\helper;
 use core_privacy\local\request\transform;
 use core_privacy\local\request\userlist;
 use core_privacy\local\request\writer;
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider
        {

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


        //from scorm example:

            /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Remove contexts different from COURSE_MODULE.
        $contexts = array_reduce($contextlist->get_contexts(), function($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->id;
            }
            return $carry;
        }, []);

        if (empty($contexts)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;
        // Get SCORM data.
        foreach ($contexts as $contextid) {
            $context = \context::instance_by_id($contextid);
            $data = helper::get_context_data($context, $user);
            writer::with_context($context)->export_data([], $data);
            helper::export_context_files($context, $user);
        }

        // Get scoes_track data.
        list($insql, $inparams) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED);
        $sql = "SELECT v.id,
                       a.attempt,
                       e.element,
                       v.value,
                       v.timemodified,
                       ctx.id as contextid
                  FROM {scorm_attempt} a
                  JOIN {scorm_scoes_value} v ON a.id = v.attemptid
                  JOIN {scorm_element} e on e.id = v.elementid
                  JOIN {course_modules} cm
                    ON cm.instance = a.scormid
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id $insql
                   AND a.userid = :userid ";
        $params = array_merge($inparams, ['userid' => $userid]);

        $alldata = [];
        $scoestracks = $DB->get_recordset_sql($sql, $params);
        foreach ($scoestracks as $track) {
            $alldata[$track->contextid][$track->attempt][] = (object)[
                    'element' => $track->element,
                    'value' => $track->value,
                    'timemodified' => transform::datetime($track->timemodified),
                ];
        }
        $scoestracks->close();

        // The scoes_track data is organised in: {Course name}/{SCORM activity name}/{My attempts}/{Attempt X}/data.json
        // where X is the attempt number.
        array_walk($alldata, function($attemptsdata, $contextid) {
            $context = \context::instance_by_id($contextid);
            array_walk($attemptsdata, function($data, $attempt) use ($context) {
                $subcontext = [
                    get_string('myattempts', 'scorm'),
                    get_string('attempt', 'scorm'). " $attempt"
                ];
                writer::with_context($context)->export_data(
                    $subcontext,
                    (object)['scoestrack' => $data]
                );
            });
        });

        // Get aicc_session data.
        $sql = "SELECT ss.id,
                       ss.scormmode,
                       ss.scormstatus,
                       ss.attempt,
                       ss.lessonstatus,
                       ss.sessiontime,
                       ss.timecreated,
                       ss.timemodified,
                       ctx.id as contextid
                  FROM {scorm_aicc_session} ss
                  JOIN {course_modules} cm
                    ON cm.instance = ss.scormid
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id $insql
                   AND ss.userid = :userid";
        $params = array_merge($inparams, ['userid' => $userid]);

        $alldata = [];
        $aiccsessions = $DB->get_recordset_sql($sql, $params);
        foreach ($aiccsessions as $aiccsession) {
            $alldata[$aiccsession->contextid][] = (object)[
                    'scormmode' => $aiccsession->scormmode,
                    'scormstatus' => $aiccsession->scormstatus,
                    'lessonstatus' => $aiccsession->lessonstatus,
                    'attempt' => $aiccsession->attempt,
                    'sessiontime' => $aiccsession->sessiontime,
                    'timecreated' => transform::datetime($aiccsession->timecreated),
                    'timemodified' => transform::datetime($aiccsession->timemodified),
                ];
        }
        $aiccsessions->close();

        // The aicc_session data is organised in: {Course name}/{SCORM activity name}/{My AICC sessions}/data.json
        // In this case, the attempt hasn't been included in the json file because it can be null.
        array_walk($alldata, function($data, $contextid) {
            $context = \context::instance_by_id($contextid);
            $subcontext = [
                get_string('myaiccsessions', 'scorm')
            ];
            writer::with_context($context)->export_data(
                $subcontext,
                (object)['sessions' => $data]
            );
        });
    }


            /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {


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
    
        // This table needs a diferent key, but to be deleted still
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
            echo"empty";
            return;
        }
        $userid = $contextlist->get_user()->id;
      
        
        foreach ($contextlist->get_contexts() as $context) {

            //Retrieve the instance id from the context.
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

            // Tables to delete from with same key if context matches.
            $tables = ['cmi5launch_usercourse', 'cmi5launch_sessions', 'cmi5launch_aus'];

            foreach ($tables as $table) {

                $sql = array("moodlecourseid" => $instanceid, "userid" => $userid);
                
                $params = array('moodlecourseid' => $instanceid, 'userid' => $userid);

                $deleted = $DB->delete_records($table, $sql);

        }
    }


    }
/*
    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {

        echo " FINE ARE WE CALLIN IT THEN?";
        global $DB;

        $context = $userlist->get_context();

        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        /*
        $usercourse = $DB->get_record('cmi5lauch_usercourse', ['moodlecourseid' => $cm->instance]);
        $sessions = $DB->get_record('cmi5lauch_sessions', ['moodlecourseid' => $cm->instance]);
        $aus = $DB->get_record('cmi5lauch_aus', ['moodlecourseid' => $cm->instance]);
    */


        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['moodlecourseid' => $cm->instance], $userinparams);
        $sql = "moodlecourseid = :moodlecourseid AND userid {$userinsql}";
    
        $DB->delete_records_select('cmi5lauch_usercourse', $sql, $params);
        $DB->delete_records_select('cmi5lauch_sessions', $sql, $params);
        $DB->delete_records_select('cmi5lauch_aus', $sql, $params);
    }

   
    }
