<?php
namespace cmi5Test;

use mod_cmi5launch\local\course;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session;
use mod_cmi5launch\local\session_helpers;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../classes/local/au_helpers.php');
require_once(__DIR__ . '/../../classes/local/session_helpers.php');


/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */

 // maybe put fake 'records here and the other one can call it? easier than and neater looking then just ploppin it down

/**
 * Make a fake record for testing.
 * @return mixed
 */
function maketestcmi5launch()
{
  global $DB, $cmi5launch;
  //Ok for starters, what is cmilaunch

  // Make a cmi5launch record
  $cmi5launch = new \stdClass();
  $cmi5launch->id = 1;
  $cmi5launch->course = 1;
  $cmi5launch->name = 'Test cmi5launch';
  $cmi5launch->intro = 'Test cmi5launch intro';
  $cmi5launch->introformat = 1;
  $cmi5launch->cmi5activityid = 'testcmi5activityid';
  $cmi5launch->registrationid = 'testregistrationid';
  $cmi5launch->returnurl = 'testreturnurl';
  $cmi5launch->courseid = 1;
  $cmi5launch->cmi5verbid = 'testcmi5verbid';
  $cmi5launch->cmi5expiry = 1;
  $cmi5launch->overridedefaults = 1;
  $cmi5launch->cmi5multipleregs = 1;
  $cmi5launch->timecreated = 00;
  $cmi5launch->timemodified = 00;
  $cmi5launch->courseinfo = 'testcourseinfo';
  $cmi5launch->aus = '{testaus:1, testaus:2}';

  $newid = $DB->insert_record('cmi5launch', $cmi5launch);

  return $newid;

}
/**
 * Delete a fake record created for testing.
 * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
 * @return void
 */
function deletetestcmi5launch($createdid)
{
  global $DB, $cmi5launch;
  // Delete the cmi5launch record
  $DB->delete_records('cmi5launch', array('id' => $createdid));

}

/**
 * Delete a fake record(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestcmi5launch.
 * @return void
 */
function deletetestcmi5launch_usercourse($ids)
{
	global $DB, $cmi5launch;

	foreach ($ids as $id) {
		// Delete the cmi5launch record
		$DB->delete_records('cmi5launch_usercourse', array('id' => $id));
	}
}

/**
 * Delete a fake au(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestas.
 * @return void
 */
function deletetestcmi5launch_aus($ids)
{
  global $DB, $cmi5launch;

  foreach ($ids as $id) {
	// Delete the cmi5launch record
	$DB->delete_records('cmi5launch_aus', array('id' => $id));
  }

}

/**
 * Delete a fake session(s) created for testing.
 * @param mixed $ids - id(s) that was created for testing by maketestas.
 * @return void
 */
function deletetestcmi5launch_sessions($ids)
{
  global $DB, $cmi5launch;

  foreach ($ids as $id) {
	// Delete the cmi5launch record
	$DB->delete_records('cmi5launch_sessions', array('id' => $id));
  }

}

  /**
   * Create a fake course for testing.
   * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
   * @return mixed $newid
   */
  function maketestcourse ($createdid){

  global $DB, $cmi5launch, $USER;

  // cmi5 launch is coming in null, looks like we need to make a fake one of that! Wonder if theres a way to make one just, first time testing?
  // Instead of before every test, like a way to prepare the test environment?
    // Reload cmi5 course instance.
    $record = $DB->get_record('cmi5launch', array('id' => $createdid));
    
     // Make a new course record.
     $userscourse = new course($record);

    // Populate with data for testing.
    $userscourse->userid = $USER->id;
    $userscourse->returnurl = 'https://testmoodle.com';
    $userscourse->registrationid = 'testregistrationid';
    $userscourse->moodlecourseid = $createdid;


    // Save new record to DB.
    $newid = $DB->insert_record('cmi5launch_usercourse', $userscourse);

    return $newid;

  }
  
  
  /**
   * Create a fake course for testing.
   * @param mixed $createdid - id that was created for testing by maketestcourse.
   * @return array $newauids - array of AU ids
   */
  function maketestaus ($testcourseid)
  {
	global $DB, $cmi5launch, $USER;

	// Mock values to make AUs.
	/*
	$mockvalues = array(
		'id' => 'id',
		'attempt' => 'attempt',
		'url' => 'url',
		'type' => 'type',
		'lmsid' => 'lmsid',
		'grade' => 'grade',
		'scores' => 'scores',
		'title' => array(0 => array('text' => 'title')),
		'moveon' => 'moveon',
		'auindex' => 'auindex',
		'parents' => 'parents',
		'objectives' => 'objectives',
		'description' => array(0 => array('text' => 'description')),
		'activitytype' => 'activitytype',
		'launchmethod' => 'launchmethod',
		'masteryscore' => 'masteryscore',
		'satisfied' => 'satisfied',
		'launchurl' => 'launchurl',
		'sessions' => 'sessions',
		'progress' => 'progress',
		'noattempt' => 'noattempt',
		'completed' => 'completed',
		'passed' => 'passed',
		'inprogress' => 'inprogress',
		'masteryScore' => 'masteryScore', 
		'launchMethod' => 'launchMethod', 
		'lmsId' => 'lmsId', 
		'moveOn' => 'moveOn', 
		'auIndex' => 'auIndex', 
		'activityType' => 'activityType',
		'moodlecourseid' => 'moodlecourseid',
		'userid' => 'userid'
	);
*/
	// Make new AUs, lets make five.
	$aus = array();
	for ($i = 0; $i < 5; $i++) {
	
		$toaddtostring = strval($i);
	// Add i to each value so the AUs are unique.
	$mockvalues = array(
		'id' => 'id' . $i,
		'attempt' =>  $i,
		'url' => 'url' . $i,
		'type' => 'type' . $i,
		'lmsid' => 'lmsid' . $i,
		'grade' => 'grade' . $i,
		'scores' => 'scores' . $i,
		'title' => array(0 => array('text' => $i)),
		'moveon' => 'moveon' . $i,
		'auindex' =>  $i,
		'parents' => 'parents' . $i,
		'objectives' => 'objectives' . $i,
		'description' =>  array(0 => array('text' => $i)),
		'activitytype' => 'activitytype' . $i,
		'launchmethod' => 'method' . $i,
		'masteryscore' =>  $i,
		'satisfied' =>  $i,
		'launchurl' => 'launchurl' . $i,
		'sessions' => 'sessions' . $i,
		'progress' => 'progress' . $i,
		'noattempt' =>  $i,
		'completed' =>  $i,
		'passed' =>  $i,
		'inprogress' =>  $i,
		'moveOn' => 'moveOn' . $i, 
		'activityType' => 'activityType' . $i,
		'moodlecourseid' => 'moodlecourseid' . $i,
		'userid' => 'userid' . $i
	);
		$aus[] = new au($mockvalues);
	}

	// Now save the fake aus to the test database
	$auhelper = new au_helpers();
	$saveau = $auhelper->get_cmi5launch_save_aus();	

	// Ok what the hack is going on here with saveaus
	/*echo"<br>";
	echo " Well let sprint them like the save aus wopuld separate them, ";
	foreach ($aus as $auobject) {
		echo "<br>";
		echo "AU ID: " . $auobject->id;
		var_dump($auobject);
		echo"<br>";
	}
	echo"<br>";
*/
	// Save AUs to test DB and save IDs.
	$newauids = $saveau($aus);
    
	// Return array of AU ids
    return $newauids;
  
    } 

  /**
   * Create fake sessions for testing.
   * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
   * @return array $sessionid
   */
  function maketestsessions ($testcourseid)
  {
	global $DB, $cmi5launch, $USER;
/*
	// Mock values to make sessions.
	$mockvalues = array(
		'id' => 'id',
		'sessionid' => 'sessionid',
		'userid' => 'userid',
		'moodlecourseid' => 'moodlecourseid',
		'registrationscoursesausid' => 'registrationscoursesausid',
		'tenantname' => 'tenantname',
		'createdat' => 'createdat',
		'updatedat' => 'updatedat',
		'code' => 'code',
		'launchtokenid' => 'launchtokenid',
		'lastrequesttime' => 'lastrequesttime',
		'launchmode' => 'launchmode',
		'masteryscore' => 'masteryscore',
		'score' => 'score',
		'islaunched' => 'islaunched',
		'isinitialized' => 'isinitialized',
		'duration' => 'duration',
		'iscompleted' => 'iscompleted',
		'ispassed' => 'ispassed',
		'isfailed' => 'isfailed',
		'isterminated' => 'isterminated',
		'isabandoned' => 'isabandoned',
		'progress' => 'progress',
		'launchmethod' => 'launchmethod',
		'launchurl' => 'launchurl',
	);
*/
	// Make new sessions, lets make five.
	$sessions = array();
	$sessionid = array();

	for ($i = 0; $i < 5; $i++) {

		$sessionid[] = $i;
		//$toaddtostring = strval($i);
	// Add i to each value so the AUs are unique.
	// Mock values to make sessions.
	$mockvalues = array(
		'id' => $i,
		'sessionid' =>  'sessionid' . $i,
		'userid' => 'userid' . $i,
		'moodlecourseid' => 'moodlecourseid' . $i,
		'registrationscoursesausid' => 'registrationscoursesausid' . $i,
		'tenantname' => 'tenantname' . $i,
		'createdat' => 'createdat' . $i,
		'updatedat' => 'updatedat' . $i,
		'code' => 'code' . $i,
		'launchtokenid' => 'launchtokenid' . $i,
		'lastrequesttime' => 'lastrequesttime' . $i,
		'launchmode' => 'launchmode' . $i,
		'masteryscore' => 'masteryscore' . $i,
		'score' => 'score' . $i,
		'islaunched' => 'islaunched' . $i,
		'isinitialized' => 'isinitialized' . $i,
		'duration' => 'duration' . $i,
		'iscompleted' => 'iscompleted' . $i,
		'ispassed' => 'ispassed' . $i,
		'isfailed' => 'isfailed' . $i,
		'isterminated' => 'isterminated' . $i,
		'isabandoned' => 'isabandoned' . $i,
		'progress' => 'progress' . $i,
		'launchmethod' => 'launchmethod' . $i,
		'launchurl' => 'launchurl' . $i,
	);
		$sessions[] = new session($mockvalues);
	}
	// Pass in the au index to retrieve a launchurl and session id.
//$urldecoded = $cmi5launchretrieveurl($cmi5launch->id, $auindex);

// Retrieve and store session id in the aus table.
//$sessionid = intval($urldecoded['id']);


// Retrieve the launch url.
$launchurl = 'testurl';
// And launch method.
$launchmethod = 'testmethod';


// Now save the fake sessions to the test database
$sessionhelper = new session_helpers();
$createsession = $sessionhelper->cmi5launch_get_create_session();	

	// For each session id in the list, create a session.
	foreach ($sessionid as $id) {
		$createsession($id, $launchurl, $launchmethod);
	}
	// Save AUs to test DB and save IDs.
	//$newauids = $createsession($aus);
    
	// Return array of session ids
    return $sessionid;
  
    } 

	/**
	 * Heeelper func that assigns the sessions made to the aus for testing purposes. 
	 * @param mixed $auids
	 * @param mixed $sessionids
	 * @return void
	 */
    function assign_sessions_to_aus($auids, $sessionids){

        // HElper function to assign sessions to aus
        $au_helpers = new au_helpers();
		$retrieve_aus = $au_helpers->get_cmi5launch_retrieve_aus_from_db();
		$save_aus = $au_helpers->get_cmi5launch_save_aus();

		//Array to holdd newly created AUs
		$newaus = array();
		// First populate the aus with the sessions
        foreach ($auids as $auid ){
            // Assiging the sessions to the aus
			$au = $retrieve_aus($auid);

			// Now the AU will have properties and we want to assign the sessionid array to the 'sessions'  property
			$au->sessions = $sessionids;

			$newaus[] = $au;
        }

		// now save the new aus back to db
		$save_aus($newaus);
    }

		/**
	 * Heeelper func that assigns the aus made to the course(s) for testing purposes. 
	 * @param mixed $auids
	 * @param mixed $sessionids
	 * @return void
	 */
    function assign_aus_to_courses($courseids, $auids){

		global $DB;
        // HElper function to assign sessions to aus
		// Mooonday- there are no course helpers so just call the course, assign the auids
		// and then save ther course with DB calls

		// Retreive the courses
		// Imust have copied below from the func above, it doesn't seem relevant
        $au_helpers = new au_helpers();
		$retrieve_aus = $au_helpers->get_cmi5launch_retrieve_aus_from_db();
		$save_aus = $au_helpers->get_cmi5launch_save_aus();

		//Array to holdd newly created AUs
		$newaus = array();
		// First populate the aus with the sessions
        foreach ($courseids as $courseid ){
            
			// Get the course
			$record = $DB->get_record('cmi5launch_usercourse', array('id' => $courseid));

			// Assigning the sessions to the aus
			$record->aus == $auids;
			
			// Save the course back to the db.
			$DB->update_record('cmi5launch_usercourse', $record);
        }

    }


/*
  function get_file_get_contents() {
    return ['file_get_contents'];
}
*/
 global $file_get_contents;
  // Ok lets make a file_et_contents for this namespace, we know it should return a json string?
  // And what should it receive? just the reular arguments?
  /**
   * A local file_get_contents to overide the PHP function for testing.
   * As we do not want to actually get the file, we just want to test the function calling that function.
   * 
   */
  function file_get_contents($url, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
  {
    // Do we wamt to test whats passed in as well? pr ois that not necessary?
    // MAybe we can return the args as json encoded string?
    return json_encode(func_get_args());
  }


?>
