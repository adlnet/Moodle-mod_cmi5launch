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
 *Class to handle Assignable Units 
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class Au {
  // Properties
  public $id, $url, $type, $lmsId = [], $title, $moveOn, $auIndex, $parents, $objectives, $description = [], $activityType, $launchMethod, $masteryScore;

  //Holds launch url, this may be the way to have separate sessions.
  public $launchUrl, $sessionId;

  //Maybe an array will hold info better
  //I'm thinkig sessionId->launchurl (for that session)
  public $sessions = array();
  

  //These may be needed later if AU's become part of a block to keep track of 
  //being finished.
  public $progress;
  
  public  $noAttempt = true|false;
  public $completed = array('finished'=> true|false, 'info' =>"");
  public $passed = array('finished'=> true|false, 'info' =>"");
  public $inProgress = array('finished'=> true|false, 'info' =>"");

  // Methods
  function set_name($name) {
    $this->name = $name;
  }
  function get_name() {
    return $this->name;
  }

//Constructs AUs. Is fed array and where array key matches property, sets the property.  
function __construct($statement){

  foreach($statement as $key => $value){
    
    $this->$key = ($value);
	}	

}

}
?>