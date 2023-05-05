<?php
//This iswhere  users aer sent if clickingon an activity ame in gradebook ITill redirect based on capability
// Course module ID
$id = required_param('id', PARAM_INT);
// Item number, may be != 0 for activities that allow more than one grade per user
$itemnumber = optional_param('itemnumber', 0, PARAM_INT); 
 // Graded user ID (optional)
$userid = optional_param('userid', 0, PARAM_INT);

//TODO
//We aer currently using this capability, but we should ake one for grading
if (has_capability('mod/cmi5launch:addinstance', $context)) {
	//This is teacher/manger/non editing teacher;

}else{
    //This is student or other non-teacher role
}   
   
   
   
   
   ?>