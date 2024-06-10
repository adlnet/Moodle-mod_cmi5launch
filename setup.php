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
 * Class to report on sessions grades.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cmi5launch\local\au_helpers;
use mod_cmi5launch\local\session_helpers;
use mod_cmi5launch\local\cmi5_connectors;

?>

<script>

function goback(){
   
    console.log("Going back to settings");
    // Post it.
    // Set the form paramters.
// Set the form paramters.
let input = document.getElementById('gobackform');
        input.submit();
    }
</script>

<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
//require('header.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');





//require_login($course, false, $cm);

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();

global $cmi5launch, $CFG;

//$cmi5launchsettings = cmi5launch_settings($cmi5launch->id);

// External classes and functions.
$cmi5helper = new cmi5_connectors;
//$aushelpers = new au_helpers;

//$updatesession = $sessionhelper->cmi5launch_get_update_session();
//$getaus = $aushelpers->get_cmi5launch_retrieve_aus_from_db();
$createtenant = $cmi5helper->cmi5launch_get_create_tenant();
// Activity Module ID.
//$id = required_param('id', PARAM_INT);

    //Now, retrieve the damned value sent here, 
// Retrieve the registration and AU ID from view.php.
    $fromsettings = required_param('variableName', PARAM_TEXT);
 //are we getting anything ?
 echo "<br>";
 echo "From settings: " . $fromsettings . "<br>";
 echo "<br>";
if ($fromsettings != null) {

    //are we getting anything ?
    echo "<br>";
    echo "From settings: " . $fromsettings . "<br>";
    echo "<br>";
    // Make the new tenant and grab results.
    $tenant = $createtenant($fromsettings);

    // The return response should be json and have 'id' and 'code' 
    $response = $tenant;

    $code = $response['code'];
    $id = $response['id'];

    echo "Tenant code: " . $code . "<br>";
    echo "Tenant ID: " . $id . "<br>";
    // The code is actually the name assign it to settings
// The question is, can it simply be assigned or does the whole table need updatin?
//$settings['cmi5launchtenantname'] = $code;
/*
@param string $name the key to set
 * @param string $value the value to set (without magic quotes)
 * @param string $plugin (optional) the plugin scope, default null
 * @return bool true or exception
 */


    $result = set_config('cmi5launchtenantname', $code, $plugin = 'cmi5launch');
    if ($result) {
        echo "Successfully made and saved new tenant";
        echo "Tenant name: " . $code . "<br>";
        echo "Tenant ID: " . $id . "<br>";
           //if fail shoudl we freeze and alert user with a window towith error message
           $link = "</br>
           <p id=name >
               <div class='input-group rounded'>
                 <button class='btn btn-secondary' name='tenantbutton' onclick='goback()'>
                   <span class='button-label'>Ok</span>
                   </button>
               </div>
           </p>";
           echo $link;
        //Hopefully that worked? Now back to settings
      //  redirect('/admin/settings.php');
      $settingurl = new moodle_url($CFG->wwwroot . '/'.'admin/settings.php', array('section' => 'modsettingcmi5launch'));
      // redirect($CFG->wwwroot . '/' . $CFG->admin . '/settings.php'. '?section=modsettingcmi5launch');
        redirect($settingurl, 'Successfully made and saved new tenant', 10);
    
    } else {
        echo "Failed to make tenant. Check connection to player and tenant name.";
        //if fail shoudl we freeze and alert user with a window towith error message
        $link = "</br>
        <p id=name >
            <div class='input-group rounded'>
              <button class='btn btn-secondary' name='tenantbutton' onclick='goback()'>
                <span class='button-label'>Ok</span>
                </button>
            </div>
        </p>";
        echo $link;
    }
   
    // End of file
}
else {
    // Yeah a button acknowleding each instance would be good
    // and it can just call a different func? Or the same really, heading back to settings@
    // If there i no tenant name then alert user, when they lick to clear take them back to settings page
    echo "Tenant name not retrieved or blank. Please try again.";
    $link = "</br>
    <p id=name >
        <div class='input-group rounded'>
          <button class='btn btn-secondary' name='tenantbutton' onclick='goback()'>
            <span class='button-label'>OK</span>
            </button>
        </div>
    </p>";
    echo $link;
   // $n = "trial";

}
?>


    <form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>

