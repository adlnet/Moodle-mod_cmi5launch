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
 */


// NEeded for moodle pae. sets up loabl
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Tell moodle about our page, tell it what the url is.\\
$PAGE->set_url('/mod/cmi5launch/setup.php');
// Tell moodle the context, in this case the site context (it's system wide not a course or course page.)
$PAGE->set_context(\context_system::instance());
// Title tells what is on tab
$PAGE->set_title(title: 'CMI5 Setup');

//whenyou want to out put html use the moodle core output rendereer: often overridden in theme
echo $OUTPUT->header();

// Easier tom ake template as objec ttypecast to array 
$templatecontext = (object) [
    'texttodisplay' => 'This is the setup page for CMI5. Here you can create a new tenant for your CMI5 player. Please enter a name for your tenant below.',
];
// now render the mustache template we made.
// takes template and template context - basically some vairables pasded into template and used to render stuff. 
echo $OUTPUT->render_from_template('mod_cmi5launch/setup', $templatecontext);

// When we first come here lets check if there are plugin settings for username, passowrd, and url, there should nt be so display the form.
// If there are, then we should display the tenant form.
// Retrieve the three settings from the database.
$playerurl = get_config('cmi5launch', 'cmi5launchplayerurl');
$playername = get_config('cmi5launch', 'cmi5launchbasicname');
$playerpass = get_config('cmi5launch', 'cmi5launchbasepass');

$playerpass = null;
echo "<br>";
echo"Settings are: ";
echo $playerurl . " ";
echo "<br>";
echo $playername;
echo "<br>";
echo $playerpass;
// If the settings are not set, then display the first form.
if(!$playerurl || !$playername || !$playerpass){

    redirect(url: $CFG->wwwroot . '/mod/cmi5launch/setupform.php', message: 'Cancelled');

}



echo $OUTPUT->footer();
?>


    <form id="gobackform" action="../../admin/settings.php" method="get">
    <input id="section" name="section" type="hidden" value="modsettingcmi5launch">

</form>

