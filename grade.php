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
 * Redirect the user based on their capabilities to reporting page.
 * @package   mod_cmi5launch
 * @category  grade
 * @copyright 2023 Megan Bohland
 * @copyright Based on work by 2010 onwards Dan Marsden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
require_login($course, true, $cm);



// This page is the go-between from moodle grader (index.php) to our report.php.
// It's never visited itself, but is almost like an invisible page.
// So the params it holds or can retrieve, such as gradeid, userid, etc., can all be retrieved here and passed to report.php.

// Course module ID
// This is what's needed if we only want the full course view, it is always included.
$id = required_param('id', PARAM_INT);

// The following are optional parameters, they are what is needed if we want to zoom in on only ONE user's grades.

// Item number, may be != 0 for activities that allow more than one grade per user.
// Itemnumber is from the moodle grade_items table, which holds info on the grade item itself such as course, mod type, etc.
$itemnumber = optional_param('itemnumber', 0, PARAM_INT);

// Graded user ID (optional) (not the currently logged in user).
$userid = optional_param('userid', 0, PARAM_INT);

// The itemid is from the Moodle grade_grades table, it corresponds to a grade column.
$itemid = optional_param('itemid', 0, PARAM_INT);

// This gradeid, which is also from the grade_grades table. It corresponds to a row entry, ie. a particular users info.
$gradeid = optional_param('gradeid', 0, PARAM_INT);



$PAGE->set_url('/mod/cmi5launch/grade.php', [
    'id' => $id,
    'itemid' => $itemid,
    'itemnumber' => $itemnumber,
    'gradeid' => $gradeid,
    'userid' => $userid,
]);

global $cmi5launch, $USER;

// Get the course module.
if (! $cm = get_coursemodule_from_id('cmi5launch', $cm->id)) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (! $course = $DB->get_record('cmi5launch', ['course' => $cm->course, 'name' => $cm->name])) {

    $returned = $DB->get_record('cmi5launch', ['course' => $cm->course, 'name' => $cm->name]);

    throw new \moodle_exception('coursemisconf');
}
$contextmodule = context_module::instance($cm->id);



// Check the user has the capability to view grades.
if (has_capability('mod/cmi5launch:viewgrades', $context)) {

    // This is a teacher/manager/etc, they can see all grades, so we need to update all grades before they view.
    // Get all enrolled users.
    $users = get_enrolled_users($contextmodule);

    foreach ($users as $user) {

        // Call updategrades to ensure all grades are up to date before view.
        cmi5launch_update_grades($cm, $user->id);
    }

    // If the logged in user has an id pass that along, as they may have grades to view as well.
    if ($userid != 0 || $userid != null) {

        redirect(new moodle_url('/mod/cmi5launch/report.php', [
            'id' => $cm->id,
            'userid' => $userid,
            'itemnumber' => $itemnumber,
            'itemid' => $itemid,
            'gradeid' => $gradeid,
        ]));


    } else {
        redirect(new moodle_url('/mod/cmi5launch/report.php', [
            'id' => $cm->id,
            'itemid' => $itemid]));
    }

} else {
    // This is student or other non-teacher role.
    // If this is just the student we only need to worry about updating their grades, because thats all they'll see.
    // Retrieve/update the users grades for this course.
    cmi5launch_update_grades($cmi5launch, $USER->id);

    redirect(new moodle_url('/mod/cmi5launch/report.php', [
        'id' => $cm->id,
        'userid' => $userid]));
}
