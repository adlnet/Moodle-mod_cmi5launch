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
 * Page to create cmi5 connection, and tenant and token.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */


// Needed for moodle page.
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
// Include our class file.
require_once($CFG->dirroot.'/mod/cmi5launch/classes/local/setup_form.php');
// Login check.
require_login();

// Tell moodle about our page, tell it what the url is.
$PAGE->set_url('/mod/cmi5launch/setupform.php');
// Tell moodle the context, in this case the site context (it's system wide not a course or course page).
$PAGE->set_context(\context_system::instance());
// Title tells what is on tab.
$PAGE->set_title(title: get_string('cmi5launchsettingtitle', 'cmi5launch'));

// We want to initialze form in PHP before you echo to page. Wou dont want to render while doing calcs.

// We want to display a form.
$mform = new setup_cmi5();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // If cancel was pressed, then redirect back to the settings page.
    redirect(url: $CFG->wwwroot . '/admin/settings.php'. '?section=modsettingcmi5launch', message: "Cancelled");

} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.

    // Retrieve data from form.
    $cmi5url = $fromform->cmi5url;
    $cmi5name = $fromform->cmi5name;
    $cmi5password = $fromform->cmi5password;

    // Save data to the database, and configure the settings.
    $resulturl = set_config('cmi5launchplayerurl', $cmi5url, $plugin = 'cmi5launch');
    $resultname = set_config('cmi5launchbasicname', $cmi5name, $plugin = 'cmi5launch');
    $resultpass = set_config('cmi5launchbasepass', $cmi5password, $plugin = 'cmi5launch');


    // As long as they are not null/false we can move on to the next form.
    if ($resulturl && $resultname && $resultpass) {
        // Move to next form.
        redirect(url: $CFG->wwwroot . '/mod/cmi5launch/tenantsetup.php',
            message: get_string('cmi5launchsettingsaved', 'cmi5launch'));
    } else {
        // If for some reason they are null or false, then we will redirect back to the form.
        redirect(url: $CFG->wwwroot . '/mod/cmi5launch/setupform.php',
            message: get_string('cmi5launchsettingsavedfail', 'cmi5launch'));

    }

};

// Display page.
echo $OUTPUT->header();
// Display the form.
$mform->display();

echo $OUTPUT->footer();


