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
 * This page is a setup page to compliment settings and enable a user to commuicate with cmi5 player for tenant issues.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use mod_cmi5launch\local\cmi5_connectors;

?>

<script>
    // Function to go back to settings page.
    function goback() {


// Function to go back to settings page.
function goback() {
    // Find the form element by its ID
    let form = document.getElementById('gobackform');
    // Submit the form
    form.submit();
}
> main
</script>

<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/mod/cmi5launch/locallib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/reportbuilder/classes/local/report/column.php');

define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);

global $cmi5launch, $CFG;

// External classes and functions.
$cmi5helper = new cmi5_connectors;
$createtenant = $cmi5helper->cmi5launch_get_create_tenant();

// Retrieve the name entered in previous pages prompt. This will be the new tenant name.
$fromsettings = required_param('variableName', PARAM_TEXT);

// Button to return to settings page.
$link = "</br>
    <p id=name >
        <div class='input-group rounded'>
          <button class='btn btn-secondary' name='tenantbutton' onclick='goback()'>
            <span class='button-label'>Ok</span>
            </button>
        </div>
    </p>";
// Ensure a name was entered.
if ($fromsettings != null) {

    // Make the new tenant and grab results.
    $tenant = $createtenant($fromsettings);

    // The return response should be an array  and have 'id' and 'code'
    $response = $tenant;

    //Do we need an if statement for response tyopo?
    $name = $response['code'];
    $id = $response['id'];


    // maybe if we make the button link thing here and then just echo it we can save on repetitive code.
    // if we have a response, we can save the tenant name to the settings
    if ($name != null && $id != null) {
        // Save the tenant name to the settings
        $result = set_config('cmi5launchtenantname', $name, $plugin = 'cmi5launch');


        if ($result) {

            echo "Successfully made and saved new tenant";
            echo "Tenant name: " . $name . "<br>";
            echo "Tenant ID: " . $id . "<br>";

            //Hopefully that worked? Now back to settings
            $settingurl = new moodle_url($CFG->wwwroot . '/' . 'admin/settings.php', array('section' => 'modsettingcmi5launch'));
        } else {
            echo "Failed to make tenant. Check connection to player and tenant name.";
            //if fail shoudl we freeze and alert user with a window towith error message

            echo $link;
        }
    } else {

        echo "Tenant name not retrieved from player. Check connection.";

        echo $link;
    }
} else {
    echo "Tenant name not retrieved or blank. Please try again.";

    echo $link;
}
?>


<form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>