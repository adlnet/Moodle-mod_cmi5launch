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
 *Class to handle Sessions. 
 * TODO The question is, to we want to build this exactly like the session table in cmi5??? 
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class Session {
  // Properties
  //$id is session id
  public $id, $tenantname, $lmsid, $firstlaunch, $lastlaunch, $progress = [], $auid, $aulaunchurl, $launchurl, $completed_passed, $grade, $registrationid, $lrscode, $created_at, $updated_at, $registration_course_aus_id, $code, $last_request_time, $launch_mode, $mastery_score, $context_template, $is_launched, $is_initialized, $is_completed, $is_passed, $is_failed, $is_terminated, $is_abandoned, $courseid;


  //Why did I ake these arrays? they should just be bools
  public $completed, $passed, $inprogress = true | false;
  //public $passed = array('finished'=> true|false, 'info' =>"");
  //public $inProgress = array('finished'=> true|false, 'info' =>"");

  // Methods
  function set_name($name) {
    $this->name = $name;
  }
  function get_name() {
    return $this->name;
  }

//Constructs sessionss. Is fed array and where array key matches property, sets the property.  
function __construct($statement){

  foreach($statement as $key => $value){
    
    $this->$key = ($value);
	}	

}

}
?>