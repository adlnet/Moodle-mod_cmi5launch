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
 * Redirect the user based on their capabilities to either a CMI5 activity or to CMI5 reports
 * @package   mod_cmi5
 * @category  grade
 * @copyright 2023 M.Bohland
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require('header.php');
// MB TODO.

// THIS page is the gobetween from moodle grader (index.php) to our report.php
// It's never visited itself, but it almost is like an invisible page. So the param it holds or can retrieve
// such as the userid (not of current user, but userid of who was specifically clicked in grader), or gradeid from that page,
// can all be retrieves here and passed to report.php
// Hypothetically we could pass to another page for studetns but dont think that's necessary.

// Course module ID
// This is what's needed if we only want the full course view, it is always included
$id = required_param('id', PARAM_INT);

// The following are optional parameters, they are what is needed if we want to zoom in on only ONE user's grades

// Item number, may be != 0 for activities that allow more than one grade per user.
// itemnumber is from the moodle grade_items table, which holds info on the grade item itself such as course, mod type, activity title, etc
$itemnumber = optional_param('itemnumber', 0, PARAM_INT); 
// Graded user ID (optional) (not currenlty loged in user).
$userid = optional_param('userid', 0, PARAM_INT);
// The itemid is from the moooodle grade_grades table I believe, appears to correspond to a grade column (for like
// one cmi5launch or other activity part of a course)
$itemid = optional_param('itemid', 0, PARAM_INT);
// This is the gradeid, which is the id, in the same grade_grades table. So like a row entry, a particular users info
$gradeid = optional_param('gradeid', 0, PARAM_INT);

global $cmi5launch, $USER, $mod;


// Get the course module.
if (! $cm = get_coursemodule_from_id('cmi5launch', $cm->id)) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (! $scorm = $DB->get_record('cmi5launch', array('id' => $cm->instance))) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (! $course = $DB->get_record('cmi5launch', array('course' => $cm->course, 'name' => $cm->name))) {

    $returned = $DB->get_record('cmi5launch', array('course' => $cm->course, 'name' => $cm->name));
   
    throw new \moodle_exception('coursemisconf');
}

///THIS is where it redircts if first hit, maybe here I can redirect via student or tea ch and give them the stuff!!!

//require_login($course, false, $cm);

//How scorm did it
/*
if (has_capability('mod/scorm:viewreport', context_module::instance($cm->id))) {
    redirect('report.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}
*/
//TODO
//We are currently using this capability, but we should make one for grading
if (has_capability('mod/cmi5launch:addinstance', $context)) {
	// This is teacher/manger/non editing teacher.
   
   if($userid != 0 || null){
    
    redirect('report.php?id=' . $cm->id . '&userid=' . $userid . '&itemnumber=' . $itemnumber . '&itemid=' . $itemid . '&gradeid=' . $gradeid);

    } else {
        redirect('report.php?id=' . $cm->id . '&itemid=' . $itemid);
    }


} else {
    // This is student or other non-teacher role.
    redirect('report.php?id='.$cm->id .'&userid=' . $userid );
}   
