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
 * Page to create tenant behind the scenes.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cmi5launch\local\cmi5_connectors;

?>

<script>

function goback(){
   
    // Retrieve the form and submit it.
    let input = document.getElementById('gobackform');
        input.submit();
    }
</script>

<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();

global $cmi5launch, $CFG;

// External classes and functions.
$cmi5helper = new cmi5_connectors;
$gettoken = $cmi5helper->cmi5launch_get_retrieve_token();


// Return link/button to settings page.
 $link = "</br>
 <p id=name >
     <div class='input-group rounded'>
       <button class='btn btn-secondary' name='tokenbutton' onclick='goback()'>
         <span class='button-label'>OK</span>
         </button>
     </div>
 </p>";


 // Before a token can be made, there must be a tenant name and id, so verify these exist, if not throw error.

 // Retrieves the string if there or false if not.
 $tenantname = get_config('cmi5launch', 'cmi5launchtenantname');
 $tenantid = get_config('cmi5launch', 'cmi5launchtenantid');

// If niether are false.
if ($tenantname != null && $tenantid != null) {

    // Make the new token and grab results.
    $token = $gettoken($tenantname, $tenantid);

// If the token is not false it should be what we need
    if ($token != false) {
    
        //Save it to the settings. 
        $tokenresult = set_config('cmi5launchtenanttoken', $token, $plugin = 'cmi5launch');

        if ($tokenresult == false) {
            echo "Failed to save token to settings. Check connection with DB and try again.";
            echo "<br>";
            echo "Save failed. With result " . $tokenresult . "<br>";
            
            // If fail we freeze and alert user with a window with error message.
            echo $link;
        }else {
        // Assumin the tokenresult is not false, it was saved correctly and we cango back to setting pae.
        // If result is true then redirect back to settings page.
        $settingurl = new moodle_url($CFG->wwwroot . '/' . 'admin/settings.php', array('section' => 'modsettingcmi5launch'));
                
        redirect($settingurl, 'Successfully retrieved and saved new bearer token', 10);
        }
    }
    else {

        echo "Failed to retrieve token from cmi5 player. Check connection with player, ensure tenant name and ID exist, and try again.";
        echo "<br>";
        echo "Token retrieval failed. With result " . $tokenresult . "<br>";
        
            // If fail we freeze and alert user with a window with error message.
            echo $link;
    }

} else {
    
    // If there is no tenant name then alert user, when they click to clear take them back to settings page.
    echo "Tenant name and/or ID not retrieved or blank. Please create a tenant before trying again.";
 
    echo $link;

}
?>


    <form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>

