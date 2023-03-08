<?php
class Au {
  // Properties
  public $id, $url, $type, $lmsId = [], $title, $moveOn, $auIndex, $parents, $objectives, $description = [], $activityType, $launchMethod, $masteryScore ;

  // Methods
  function set_name($name) {
    $this->name = $name;
  }
  function get_name() {
    return $this->name;
  }

//feed the construct an array of statement(s)
//WAIT, if this is called individually, then feed it ONE group of statement
function __construct($statement){

	foreach($statement as $key => $value){
		$this->$key = ($value);
	}	

}

}
?>