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
 *Class to handle invidual courses
 *experimental 
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class course {
  // Properties
  public $id, $url, $type, $lmsid, $grade, $scores, $title, $moveon, $auindex, $parents, $objectives, $description = [], $activitytype, $launchmethod, $masteryscore, $satisfied ;
public $courseid; //The id assigned by cmi5
public $userid; //The user id who is taking the course
  public $returnurl; //the users returnurl? needed?
  public $registrationid; //the registration id assigned by the CMI5 player
  public $aus = array(); //array of AUs in the course


  //Do we needs 'sessions? todo


  //Holds launch url, this may be the way to have separate sessions.
  public $launchurl, $sessionid;

  //Maybe an array will hold info better
  //I'm thinkig sessionId->launchurl (for that session)
  public $sessions = array(); //Sessions still needed?
  

  //These may be needed later if AU's become part of a block to keep track of 
  //being finished.
  public $progress;
  
  public  $noattempt = true|false;

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

//Constructs courses. Is fed array and where array key matches property, sets the property.  
function __construct($statement){

  foreach($statement as $key => $value){
    
    //If the key exists as a property, set it.
    if(property_exists($this, $key) ){
      
      $this->$key = ($value);
    }
	}	

}

}
?>