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
 * launches the experience with the requested registration
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('cmi5launch', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $cmi5launch  = $DB->get_record('cmi5launch', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $cmi5launch  = $DB->get_record('cmi5launch', ['id' => $n], '*', MUST_EXIST);
    $course     = $DB->get_record('course', ['id' => $cmi5launch->course], '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('cmi5launch', $cmi5launch->id, $course->id, false, MUST_EXIST);
} else {
    error(get_string('idmissing', 'report_cmi5'));
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
