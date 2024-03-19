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

        if (empty($contextlist)) {
            echo"empty contexts";
            return;
        }

        // Get the user.
        $user = $contextlist->get_user();
        $userid = $user->id;

        // Get the list of contexts that contain user information for the specified user.
        foreach ($contextlist as $context) {
           
          //  $context = \context::instance_by_id($context->id);
            echo "<br>";
            echo "context is replaced? ??";
            var_dump($context);
            echo "<br>";
            $data = helper::get_context_data($context, $user);
            echo "<br>";
            echo "And the data we   are exporting is";
            var_dump($data);
            echo "<br>";
            // Below is where the  name is coming from
            writer::with_context($context)->export_data(['Course info'], $data);
        
            // LEt's iterate through tables and export them 
            $tables = array('cmi5launch_usercourse', 'cmi5launch_sessions', 'cmi5launch_aus');
            //    helper::export_context_files($context, $user);
// For tomorrow it's saying cm is null
            $cm = get_coursemodule_from_id('cmi5launch', $context->instanceid);
            //what is courese modukle
            echo "<br>";
            echo "And the data we  CMCMCMCMCM are exporting is";
            var_dump($cm);
            echo "<br>";
            
            $mid =  $cm->instance;
            $params = array ('userid' => $userid,   'moodlecourseid'=> $mid);
                // the context->instanceid = cm ->id and the cm.instance equals moodlecourseid

            $sql = "SELECT *
            FROM {cmi5launch_usercourse} c5l
            WHERE c5l.userid = :userid 
            AND c5l.moodlecourseid = :moodlecourseid";
            $stateset = $DB->get_recordset('cmi5launch_usercourse', $params);

             // userdata
             $coursedata = [];
            // now foreach it incase there are multiple
            foreach ($stateset as $state) {

               //Ok what is state
                echo "STate in STATE loop where I am tryin to get user to read info ";
                echo " stata is ";
                var_dump($state);
                echo "< br>";
                // Make user friendlynames
                $userfriendly = array( 'ID of instance' => $state->id,
                    'User ID' => $state->userid,
                    'Course ID' => $state->courseid, 
                    'Moodle Course ID' => $state->moodlecourseid, 
                    'Registration ID' => $state->registrationid, 
                    'CMI5 Activity ID' => $state->cmi5activityid,
                    'Return URL' => $state->returnurl,
                    'AUs of instance' => $state->aus,
                    'Grades of AUs' => $state->ausgrades, 
                    'Overall grade of instance' => $state->grade);

                // Then add as ONE item in array, that way if there is more thannn one it unpacks nicely
                $coursedata = ['userinfo' => $userfriendly];
              // what is course data is it in 0
                echo " &&&&&&&&&&&&&&&&&&&&course data is ";
                var_dump($coursedata);
                echo "< br>";
            }
            $contextdata = (object)array_merge((array)$data, $coursedata);
                // If the activity has xAPI state data by the user, include it in the export.
                writer::with_context($context)->export_data(
                    ['Course info pertaining to user'],
                    (object) $contextdata
                );
//////////////////////////////////////////
                    /////////////////////////////NOW AUS
                    $stateset = $DB->get_recordset('cmi5launch_aus', $params);

                    // userdata
                    $ausdata = [];
                // now foreach it incase there are multiple
                foreach ($stateset as $state) {
     
               // Make user friendlynames
               $userfriendly = array( 'ID of AU instance' => $state->id,
                   'User ID' => $state->userid,
                   'The attempt number of the AU' => $state->attempt,
                     'LMS ID of the AU' => $state->lmsid,
                     'Moodle Course ID' => $state->moodlecourseid,
                     'URL of the AU' => $state->url,
                     'The type of the AU' => $state->type,
                        'The title of the AU' => $state->title,
                        'The description of the AU' => $state->description,
                        'The objectives of the AU' => $state->objectives,
                        'The move on value of the AU' => $state->moveon,
                        'The AU index of the AU' => $state->auindex,
                        'The cmi5 activity type of the AU' => $state->activitytype,
                        'The amount it goes to mastery score' => $state->masteryscore,
                        'Whether it has been completed' => $state->completed,
                        'Whether it has been passed' => $state->passed,
                        'Whether it is in progress' => $state->inprogress,
                        'Whether it has been satisfied' => $state->satisfied,
                        'Whether it has not been attempted' => $state->noattempt,
                        'The individual sessions of the AU' => $state->sessions,
                        'The scores of the AU, as array' => $state->scores,
                        'The overall grade of the AU' => $state->grade,
                   );
            
            
               // Then add as ONE item in array, that way if there is more thannn one it unpacks nicely
               $ausdata = ['Session info' => $userfriendly];
             // what is course data is it in 0
               echo " &&&&&&&&&&&&&&&&&&&&course data is ";
               var_dump($ausdata);
               echo "< br>";
            }
            
            $contextdata = (object)array_merge((array)$data, $ausdata);
               // If the activity has xAPI state data by the user, include it in the export.
               writer::with_context($context)->export_data(
                   ['AU info pertaining to user'],
                   (object) $contextdata
               );
            //////////////////////////////////////
            ///////////////////////////////////////////
            /////////////////////////////NOW SESSIONS
            $stateset = $DB->get_recordset('cmi5launch_sessions', $params);

        // userdata
        $sessiondata = [];
    // now foreach it incase there are multiple
    foreach ($stateset as $state) {

  //Ok what is state
   echo "STate in STATE loop where I am tryin to get user to read info ";
   echo " stata is ";
   var_dump($state);
   echo "< br>";
   // Make user friendlynames
   $userfriendly = array( 'ID of session instance' => $state->id,
        'Session ID' => $state->sessionid,
       'User ID' => $state->userid,
       'Moodle Course ID' => $state->moodlecourseid, 
       'Registration Courses AUs ID' => $state->registrationscoursesausid, 
       'Time a session was started' => $state->creeatedat,
         'Time a session was updated' => $state->updatedat,
         'Code' => $state->code,
            'Last request time' => $state->lastrequesttime,
            'Amount it goes to MAstery Score' => $state->masteryscore,
            'The score of the session' => $state->score,
            'If it was launched' => $state->islaunched,
            'If it was initialized' => $state->isinitialized,
            'Time it was initialized' => $state->initializedat,
            'Duration of session' => $state->duration,
            'If it was completed' => $state->iscompleted,
            'If it was passed' => $state->ispassed,
            'If it was failed' => $state->isfailed,
            'If it was terminated' => $state->isterminated,
            'If it was abandoned' => $state->isabandoned,
            'Progress of session in statements from LRS' => $state->progress,
            'Launch URL' => $state->launchurl);


   // Then add as ONE item in array, that way if there is more thannn one it unpacks nicely
   $sessiondata = ['Session info' => $userfriendly];
 // what is course data is it in 0
   echo " &&&&&&&&&&&&&&&&&&&&course data is ";
   var_dump($sessiondata);
   echo "< br>";
}

$contextdata = (object)array_merge((array)$data, $sessiondata);
   // If the activity has xAPI state data by the user, include it in the export.
   writer::with_context($context)->export_data(
       ['Session info pertaining to user'],
       (object) $contextdata
   );
//////////////////////////////////////
///////////////////////////////////////////
            // Now lets try to do specific queries to make it legible to user

            foreach ($tables as $table) {
                // There could be more than one so lets make this an array like the attempt array in tutorial
                //$state = $DB->get_record($table, $params);
                $stateset = $DB->get_recordset($table, $params);
                foreach ($stateset as $state) {

                    if ($state) {

                        echo "STate in STATE loop ";
                        echo " stata is ";
                        //var_dump(json_decode(json_encode($state), true));
                        var_dump(array($state));

                        echo "< br>";
                       // $state 

                        $contextdata = (object) array_merge((array($state)),(array) $data);

                        echo "PLEASE WORK";
                        echo " contextdata is ";
                        var_dump($contextdata);
                        echo "< br>";

                        writer::with_context($context)->export_data([$table], (object) $contextdata);
                    }
                }
                // the context->instanceid = cm ->id and the cm.instance equals moodlecourseid
                // Get user's xAPI state data for the particular context.
                $state = $DB->get_record('cmi5launch_usercourse', $params);

                echo "PLEASE WORK";
                echo " state is ";
                var_dump($state);
                echo "< br>";
/*
                if ($state) {
                    // If the activity has xAPI state data by the user, include it in the export.
                    writer::with_context($context)->export_data(
                        ['privacy:cmi5launch', 'core_cmi5', 'I BEt this makes a third'],
                        (object) $state
                    );
                }*/
            }
        }


        /*
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT c5l.userid, c5l.moodlecourseid, c5l.registrationid, c5l.ausgrades, c5l.grade
        FROM {cmi5launch_usercourse} c5l
        JOIN {course_modules} cm
        ON cm.id = c5l.moodlecourseid
        JOIN {context} ctx
        ON ctx.instanceid = cm.id
        AND ctx.contextlevel = :modlevel
        WHERE ctx.id {$contextsql}";
        // To hold records
        $records = [];
        $params = ['modname' => 'cmi5launch', 'userid' => $user->id] + $contextparams;
     
       */
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
    
        $DB->delete_records_select('cmi5lauch_usercourse', $sql, $params);
        $DB->delete_records_select('cmi5lauch_sessions', $sql, $params);
        $DB->delete_records_select('cmi5lauch_aus', $sql, $params);
    }

   
    }
