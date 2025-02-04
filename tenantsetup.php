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



require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/cmi5launch/locallib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');
// Include our class file
require_once($CFG->dirroot.'/mod/cmi5launch/classes/local/tenant_form.php');
// Tell moodle about our page, tell it what the url is.\\
$PAGE->set_url('/mod/cmi5launch/tenantsetup.php');
// Tell moodle the context, in this case the site context (it's system wide not a course or course page.)
$PAGE->set_context(\context_system::instance());
// Title tells what is on tab
$PAGE->set_title(title: 'Creating a tenant');

$PAGE->requires->jquery();

global $cmi5launch, $CFG;

// External classes and functions.
$cmi5helper = new cmi5_connectors;
$createtenant = $cmi5helper->cmi5launch_get_create_tenant();

// Instantiate form.
$mform = new setup_tenant();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {

    // If they cancel, redirect back to the setup page.
    redirect(url: $CFG->wwwroot . '/mod/cmi5launch/setupform.php', message: 'Cancelled');

} else if ($fromform = $mform->get_data()) {

    // Retrieve username.
    $cmi5tenant = $fromform->cmi5tenant;

    if ($cmi5tenant != null) {

        // Make the new tenant and grab results.
        // here is an aarea that could fail. Should we try catch or is that covered in the creat tenant call?
        //TODO
        $tenant = $createtenant($cmi5tenant);

        echo" Tenant: ";
        echo "<br>";
        var_dump($tenant);
        // The return response should be json and have 'id' and 'code' 
        $response = $tenant;

        // Save the code as the tenant name and ID as ID.
        $name = $response['code'];
        $id = $response['id'];

        echo "Tenant code: " . $name . "<br>";
        echo "Tenant ID: " . $id . "<br>";

        // check we have a tenant and is, and save them to db for later retrieval (particularly id)
        if ($name != null && $id != null) {


            $result = set_config('cmi5launchtenantname', $name, $plugin = 'cmi5launch');

            $idresult = set_config('cmi5launchtenantid', $id, $plugin = 'cmi5launch');

            if ($idresult && $result) {

                // If result is true then redirect back to settings page.
                // except now we dont want to redirect to  settings! We want to go to 
                // The TOKEN setup form
                // Wait, maybe it should do this automatically? Like they don't need to enter it sine we are making this make it for them, and we don't need them to
                // press a button on a new form JUST to make a token. Lets do it behind the scenes and they can retrieve it if they want through an 
                //echo or settings page? 
//                $settingurl = new moodle_url($CFG->wwwroot . '/' . 'admin/settings.php', array('section' => 'modsettingcmi5launch'));
                redirect(url: $CFG->wwwroot . '/mod/cmi5launch/tokensetup.php', message: 'Tenant made and saved successfully');

               // redirect($settingurl, 'Successfully made and saved new tenant', 10);

            } else {
                echo "Failed to save tenant to DB.";
                echo "<br>";
                echo "Tenant name: " . $name . " failed to save as setting. With result " . $result . "<br>";
                //if fail shoudl we freeze and alert user with a window towith error message

                echo $link;
            }
        } else {

            echo "Failed to make tenant. Check connection to player and tenant name (cannot reuse old tenant names).";

            echo $link;
        }
    } else {

        // If there is no tenant name then alert user, when they click to clear take them back to settings page.
        echo "Tenant name not retrieved or blank. Please try again.";

        echo $link;

    }

}
;
echo $OUTPUT->header();

// Display the form.
$mform->display();
echo $OUTPUT->footer();
?>


    <form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>

