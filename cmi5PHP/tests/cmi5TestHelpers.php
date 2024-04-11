<?php
namespace cmi5Test;

use mod_cmi5launch\local\course;
use PHPUnit\Framework\TestCase;


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
   * Create a fake course for testing.
   * @param mixed $createdid - id that was created for testing by maketestcmi5launch.
   * @return void
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
