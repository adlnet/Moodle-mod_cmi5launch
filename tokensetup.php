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

//defined('MOODLE_INTERNAL') || die();


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/reportbuilder/classes/local/report/column.php');
// Include our class file.
require_once($CFG->dirroot.'/mod/cmi5launch/classes/local/token_form.php');
// Tell moodle about our page, tell it what the url is.
$PAGE->set_url('/mod/cmi5launch/tokensetup.php');
// Tell moodle the context, in this case the site context (it's system wide not a course or course page).
$PAGE->set_context(\context_system::instance());
// Title tells what is on tab.
$PAGE->set_title(title: get_string('cmi5launchtokensetuptitle', 'cmi5launch'));
define('CMI5LAUNCH_REPORT_DEFAULT_PAGE_SIZE', 20);
define('CMI5LAUNCH_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('CMI5LAUNCH_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);
$PAGE->requires->jquery();

global $cmi5launch, $CFG;

require_login();

// External classes and functions.
$cmi5helper = new cmi5_connectors;
$gettoken = $cmi5helper->cmi5launch_get_retrieve_token();

 // Before a token can be made, there must be a tenant name and id, so verify these exist, if not throw error.

 // Retrieves the string if there or false if not.
 $tenantname = get_config('cmi5launch', 'cmi5launchtenantname');
 $tenantid = get_config('cmi5launch', 'cmi5launchtenantid');

// If neither are false.
if ($tenantname != null && $tenantid != null) {

    // Make the new token and grab results.
    $token = $gettoken($tenantname, $tenantid);

    // If the token is not false it should be what we need.
    if ($token != false) {

        // Save it to the settings.
        $tokenresult = set_config('cmi5launchtenanttoken', $token, $plugin = 'cmi5launch');

        if ($tokenresult == false) {
            echo (get_string('cmi5launchtokendbfailed', 'cmi5launch'));
            echo "<br>";
            echo (get_string('cmi5launchtokensavefailed', 'cmi5launch') . $tokenresult . "<br>");

            // If fail we freeze and alert user with a window with error message.
            echo $link;
        } else {
            // Assuming the tokenresult is not false, it was saved correctly and we cango back to setting pae.
            // If result is true then redirect back to settings page.
            $settingurl = new moodle_url($CFG->wwwroot . '/' . 'admin/settings.php', ['section' => 'modsettingcmi5launch']);

            redirect($settingurl, get_string('cmi5launchtokencreatedsuccess', 'cmi5launch'), 10);
        }
    } else {

        echo (get_string('cmi5launchtokendbretrievefailed', 'cmi5launch'));
        echo "<br>";
        echo ( get_string('cmi5launchtokenretrievefailed', 'cmi5launch') . $tokenresult . "<br>");

            // If fail we freeze and alert user with a window with error message.
            echo $link;
    }

} else {

    // If there is no tenant name then alert user, when they click to clear take them back to settings page.
    echo (get_string('cmi5launchtokennonameid', 'cmi5launch'));

    echo $link;

}


// When you want to output html use the moodle core output rendereer: often overridden in theme.
echo $OUTPUT->header();

// We want to display a form.
$mform = new setup_token();
echo $OUTPUT->footer();
?>


    <form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>

