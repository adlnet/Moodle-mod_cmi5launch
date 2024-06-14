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

/* For global cmi5 settings  */

/**
 * Defines the version of cmi5launch
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package mod_cmi5launch
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes as well as some code from the scorm module (Source code was uncredited).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */




defined('MOODLE_INTERNAL') || die;

use mod_cmi5launch\local\cmi5_connectors;
?>

<script>
// Function for popup window.
function openprompt(){

    var site = prompt("Please enter the tenant name:", "The cmi5 tenant name. Please enter a name you would like to use");
    if (site != null) {
      // Set the form paramters.
      $('#variableName').val(site);

        // Post it.
        $('#settingform').submit();
    }

}
function totokenpage(){

        // Post it.
        $('#settingformtoken').submit();
    }

</script>
<?php


// maybe add if ($hassiteconfig?) Can regulare users access this? TODO -MB
if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/cmi5launch/locallib.php');
    require_once($CFG->dirroot . '/mod/cmi5launch/settingslib.php');

    // MB
    // From scorm grading stuff.
    $yesno = array(0 => get_string('no'),
                   1 => get_string('yes'));

    // Default display settings.
    $settings->add(new admin_setting_heading('cmi5launch/cmi5launchlrsfieldset',
        get_string('cmi5launchlrsfieldset', 'cmi5launch'),
        get_string('cmi5launchlrsfieldset_help', 'cmi5launch')));

    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchlrsendpoint',
        get_string('cmi5launchlrsendpoint', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_help', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_default', 'cmi5launch'), PARAM_URL));

    $options = array(
        1 => get_string('cmi5launchlrsauthentication_option_0', 'cmi5launch'),
        2 => get_string('cmi5launchlrsauthentication_option_1', 'cmi5launch'),
        0 => get_string('cmi5launchlrsauthentication_option_2', 'cmi5launch'),
    );
    // Note the numbers above are deliberately mis-ordered for reasons of backwards compatibility with older settings.

    $setting = new admin_setting_configselect('cmi5launch/cmi5launchlrsauthentication',
        get_string('cmi5launchlrsauthentication', 'cmi5launch'),
        get_string('cmi5launchlrsauthentication_help', 'cmi5launch').'<br/>'
        .get_string('cmi5launchlrsauthentication_watershedhelp', 'cmi5launch')
        , 1, $options);
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchlrslogin',
        get_string('cmi5launchlrslogin', 'cmi5launch'),
        get_string('cmi5launchlrslogin_help', 'cmi5launch'),
        get_string('cmi5launchlrslogin_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchlrspass',
        get_string('cmi5launchlrspass', 'cmi5launch'),
        get_string('cmi5launchlrspass_help', 'cmi5launch'),
        get_string('cmi5launchlrspass_default', 'cmi5launch'));
    $settings->add($setting);

    $settings->add(new admin_setting_configtext('cmi5launch/cmi5launchlrsduration',
        get_string('cmi5launchlrsduration', 'cmi5launch'),
        get_string('cmi5launchlrsduration_help', 'cmi5launch'),
        get_string('cmi5launchlrsduration_default', 'cmi5launch')));

    $settings->add(new admin_setting_configtext('cmi5launch/cmi5launchcustomacchp',
        get_string('cmi5launchcustomacchp', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_help', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_default', 'cmi5launch')));

    $settings->add(new admin_setting_configcheckbox('cmi5launch/cmi5launchuseactoremail',
        get_string('cmi5launchuseactoremail', 'cmi5launch'),
        get_string('cmi5launchuseactoremail_help', 'cmi5launch'),
        1));

    $settings->add(new admin_setting_heading('cmi5launch/cmi5launchsettings', get_string('cmi5launchsettingsheader', 'cmi5launch'), ''));


    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchplayerurl',
        get_string('cmi5launchplayerurl', 'cmi5launch'),
        get_string('cmi5launchplayerurl_help', 'cmi5launch'),
        get_string('cmi5launchplayerurl_default', 'cmi5launch'), PARAM_URL));

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchbasicname',
        get_string('cmi5launchbasicname', 'cmi5launch'),
        get_string('cmi5launchbasicname_help', 'cmi5launch'),
        get_string('cmi5launchbasicname_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchbasepass',
        get_string('cmi5launchbasepass', 'cmi5launch'),
        get_string('cmi5launchbasepass_help', 'cmi5launch'),
        get_string('cmi5launchbasepass_default', 'cmi5launch'));
    $settings->add($setting);


    // Buttons.
    $linktotenant = "</br>
    <p id=name >
        <div class='input-group rounded'>
          <button class='btn btn-secondary' type='reset' name='tenantbutton' onclick='openprompt()'>
            <span class='button-label'>Generate tenant</span>
            </button>
        </div>
    </p>
      ";
      $linktotoken = "</br>
      <p id=name >
          <div class='input-group rounded'>
            <button class='btn btn-secondary' type='reset' name='tokenbutton' onclick='totokenpage()'>
              <span class='button-label'>Generate bearer token</span>
              </button>
          </div>
      </p>
        ";
      //$link ="<a href='http://www.google.com' target='_parent'><button>Click me !</button></a>";
    
      // Verify there is a tenant name and id. If there is, display it, if not allow them to make one.
       // Retrieves the string if there or false if not.
        $tenantname = get_config('cmi5launch', 'cmi5launchtenantname');
        $tenantid = get_config('cmi5launch', 'cmi5launchtenantid');

        // If niether are false.
    if ($tenantname != null && $tenantid != null) {
       
        $todisplay  = "<b>Tenant name is: " . $tenantname . ". Tenant id is: " . $tenantid . "</b><div><br> The tenant name and ID have been set. They cannot be changed without causing problems with existing cmi5 launch link activities. To change, plugin must be uninstalled and reinstalled.</div> <div><br></div>";
        $setting = new admin_setting_description('cmi5launchtenantmessage', "cmi5launch tenant name and id:",  $todisplay);
        $settings->add($setting);
    
    } else {

        $setting = new admin_setting_configtext(
            'cmi5launch/cmi5launchtenantname',
            get_string('cmi5launchtenantname', 'cmi5launch'),
            " " . get_string('cmi5launchtenantname_help', 'cmi5launch') . $linktotenant,
            get_string('cmi5launchtenantname_default', 'cmi5launch')
        );
        $settings->add($setting);
    };

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchtenanttoken',
        get_string('cmi5launchtenanttoken', 'cmi5launch'),
        get_string('cmi5launchtenanttoken_help', 'cmi5launch') . $linktotoken,
        get_string('cmi5launchtenanttoken_default', 'cmi5launch'));
    $settings->add($setting);


    $setting=
        new admin_setting_configselect_with_lock(
            'restore/restore_replace_keep_roles_and_enrolments',
            new lang_string('setting_keep_roles_and_enrolments', 'backup'),
            new lang_string('config_keep_roles_and_enrolments', 'backup'),
            array('value' => 0, 'locked' => 0),
            array(1 => get_string('yes'), 0 => get_string('no'))
        );
    $settings->add($setting);

    // MB.
    // Grade stuff I'm bringing over.
        // Default grade settings.
    $settings->add(new admin_setting_heading('cmi5launch/gradesettings', get_string('defaultgradesettings', 'cmi5launch'), ''));
    $settings->add(new admin_setting_configselect('cmi5launch/grademethod',
        get_string('grademethod', 'cmi5launch'), get_string('grademethoddesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_GRADE_HIGHEST, cmi5launch_get_grade_method_array()));

    for ($i = 0; $i <= 100; $i++) {
        $grades[$i] = "$i";
    }

    $settings->add(new admin_setting_configselect('cmi5launch/maxgrade',
        get_string('maximumgrade'), get_string('maximumgradedesc', 'cmi5launch'), 100, $grades));

    $settings->add(new admin_setting_heading('cmi5launch/othersettings', get_string('defaultothersettings', 'cmi5launch'), ''));

    // Default attempts settings.
    $settings->add(new admin_setting_configselect('cmi5launch/maxattempt',
        get_string('maximumattempts', 'cmi5launch'), '', '0', cmi5launch_get_attempts_array()),
        get_string('whatmaxdesc', 'cmi5launch'), );

    $settings->add(new admin_setting_configselect('cmi5launch/whatgrade',
        get_string('whatgrade', 'cmi5launch'), get_string('whatgradedesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_HIGHEST_ATTEMPT, cmi5launch_get_what_grade_array()));

    // Not sure if we want to implement mastery override at this time -MB.
    /*
    $settings->add(new admin_setting_configselect('cmi5launch/masteryoverride',
    get_string('masteryoverride', 'cmi5launch'), get_string('masteryoverridedesc', 'cmi5launch'), 1, $yesno));
    */

    $settings->add(new admin_setting_configselect('cmi5launch/MOD_CMI5LAUNCH_LAST_ATTEMPTlock',
        get_string('mod_cmi5launch_last_attempt_lock', 'cmi5launch'), get_string('mod_cmi5launch_last_attempt_lockdesc', 'cmi5launch'), 0, $yesno));


    }

    ?>  
    <form id="settingformtoken" action="../mod/cmi5launch/tokensetup.php" method="get">
 
</form>


 <form id="settingform" action="../mod/cmi5launch/tenantsetup.php" method="get">
        
        <input id="variableName" name="variableName" type="hidden" value="default">

    </form>
