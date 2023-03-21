<?php
class Au {
  // Properties
  public $id, $url, $type, $lmsId = [], $title, $moveOn, $auIndex, $parents, $objectives, $description = [], $activityType, $launchMethod, $masteryScore;

  public $progress;
  
  public $completed2, $passed2, $inProgress2, $noAttempt = true|false;

  //So here I will check for the first two verbs and update them if found
  //I will dump all info in info
  //If they are not found I will dump into the third
  //If none found it will just be no attempt
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

//Should a function to evluate progress be here? Or just to change the 
//true/false properties?


//feed the construct an array of statement(s)
//WAIT, if this is called individually, then feed it ONE group of statement
function __construct($statement){

  //why didn't i make comments?!?! UGH
  //I THINK this is taking an array and assigning the arrays values to it's keys
  //I assume these are saving if the keys mactch the AU properties
  foreach($statement as $key => $value){
		$this->$key = ($value);
	}	

}

}
?>