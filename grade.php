
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
 *
 * @package   mod_cmi5
 * @category  grade
 * @copyright 2023 M.Bohland
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

//MB TODO
//Maybe return later? Framework for Gradebook in Moodle integration 


// Course module ID
$id = required_param('id', PARAM_INT);
// Item number, may be != 0 for activities that allow more than one grade per user
$itemnumber = optional_param('itemnumber', 0, PARAM_INT); 
 // Graded user ID (optional)
$userid = optional_param('userid', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('cmi5', $id)) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (! $scorm = $DB->get_record('cmi5launch', array('id' => $cm->instance))) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (! $course = $DB->get_record('cmi5launch', array('id' => $cm->id))) {
    throw new \moodle_exception('coursemisconf');
}

require_login($course, false, $cm);

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
	//This is teacher/manger/non editing teacher;

}else{
    //This is student or other non-teacher role
}   
