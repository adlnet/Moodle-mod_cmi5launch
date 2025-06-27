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
 * @package mod_cmi5launch
 */

use mod_cmi5launch\local\cmi5_connectors;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/reportbuilder/classes/local/report/column.php');
require_once($CFG->dirroot . '/mod/cmi5launch/lib.php');


// Include our class file.
require_once($CFG->dirroot . '/mod/cmi5launch/classes/local/tenant_form.php');
// Tell moodle about our page, tell it what the url is.
$PAGE->set_url('/mod/cmi5launch/tenantsetup.php');
// Tell moodle the context, in this case the site context (it's system wide not a course or course page).
$PAGE->set_context(\context_system::instance());
// Title tells what is on tab.
$PAGE->set_title(title: get_string('cmi5launchtenanttitle', 'cmi5launch'));


global $cmi5launch, $CFG, $cm;

require_login();
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
        $tenant = $createtenant($cmi5tenant);

        // The return response should be json and have 'id' and 'code'. But it is a string and we need to convert it to an array.
        $response = json_decode($tenant, true);

        // Save the code as the tenant name and ID as ID.
        $name = $response['code'];
        $id = $response['id'];

        // Check we have a tenant and is, and save them to db for later retrieval (particularly id).
        if ($name != null && $id != null) {


            $result = set_config('cmi5launchtenantname', $name, $plugin = 'cmi5launch');

            $idresult = set_config('cmi5launchtenantid', $id, $plugin = 'cmi5launch');

            if ($idresult && $result) {

                // If result is true then redirect back to settings page.
                redirect(url: $CFG->wwwroot . '/mod/cmi5launch/tokensetup.php',
                    message: get_string('cmi5launchtenantmadesuccess', 'cmi5launch'));

            } else {
                echo get_string('cmi5launchtenantfailsave', 'cmi5launch');
                echo "<br>";
                echo get_string('cmi5launchtenantfailsavemessage', 'cmi5launch') . $result;
                echo "<br>";

                echo $link;
            }
        } else {

            echo get_string('cmi5launchtenantfailplayersavemessage', 'cmi5launch');

            echo $link;
        }
    } else {

        // If there is no tenant name then alert user, when they click to clear take them back to settings page.
        echo get_string('cmi5launchtenantnamefail', 'cmi5launch');

        echo $link;
    }
};
echo $OUTPUT->header();

// Display the form.
$mform->display();
echo $OUTPUT->footer();
?>


<form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>
