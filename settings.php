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
 * Cmi5 settings. Ability to update and change
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package mod_cmi5launch
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes as well as some code from the scorm module (Source code was uncredited).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined(constant_name: 'MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/cmi5launch/constants.php');


// Class for connecting to CMI5 player grades.
use mod_cmi5launch\local\grade_helpers;

// Bring in grade helpers.
$gradehelpers = new grade_helpers;

$getgradearray = $gradehelpers->cmi5launch_get_grade_method_array();
$cmi5launchgetattemptsarray = $gradehelpers->cmi5launch_fetch_attempts_array();
$cmi5launchgetgradetypearray = $gradehelpers->cmi5launch_fetch_what_grade_array();

if ($ADMIN->fulltree) {
    $PAGE->requires->js_call_amd('mod_cmi5launch/settings', 'init');

    require_once($CFG->dirroot . '/mod/cmi5launch/settingslib.php');

    // From scorm grading stuff.
    $yesno = [
        0 => get_string('no'),
        1 => get_string('yes'),
    ];

    // Default display settings.
    $settings->add(new admin_setting_heading(
        'cmi5launch/cmi5launchlrsfieldset',
        get_string('cmi5launchlrsfieldset', 'cmi5launch'),
        get_string('cmi5launchlrsfieldset_help', 'cmi5launch')
    ));

    // LRS settings.
    $settings->add(new admin_setting_heading('cmi5launch/cmi5lrssettings', get_string('cmi5lrssettingsheader', 'cmi5launch'), ''));

    $settings->add(new admin_setting_configtext_mod_cmi5launch(
        'cmi5launch/cmi5launchlrsendpoint',
        get_string('cmi5launchlrsendpoint', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_help', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_default', 'cmi5launch'),
        PARAM_URL
    ));

    $options = [
        1 => get_string('cmi5launchlrsauthentication_option_0', 'cmi5launch'),
        2 => get_string('cmi5launchlrsauthentication_option_1', 'cmi5launch'),
        0 => get_string('cmi5launchlrsauthentication_option_2', 'cmi5launch'),
    ];
    // Note the numbers above are deliberately mis-ordered for reasons of backwards compatibility with older settings.

    $setting = new admin_setting_configselect(
        'cmi5launch/cmi5launchlrsauthentication',
        get_string('cmi5launchlrsauthentication', 'cmi5launch'),
        get_string('cmi5launchlrsauthentication_help', 'cmi5launch') . '<br/>'
            . get_string('cmi5launchlrsauthentication_watershedhelp', 'cmi5launch'),
        1,
        $options
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'cmi5launch/cmi5launchlrslogin',
        get_string('cmi5launchlrslogin', 'cmi5launch'),
        get_string('cmi5launchlrslogin_help', 'cmi5launch'),
        get_string('cmi5launchlrslogin_default', 'cmi5launch')
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'cmi5launch/cmi5launchlrspass',
        get_string('cmi5launchlrspass', 'cmi5launch'),
        get_string('cmi5launchlrspass_help', 'cmi5launch'),
        get_string('cmi5launchlrspass_default', 'cmi5launch')
    );
    $settings->add($setting);

    $settings->add(new admin_setting_configtext(
        'cmi5launch/cmi5launchlrsduration',
        get_string('cmi5launchlrsduration', 'cmi5launch'),
        get_string('cmi5launchlrsduration_help', 'cmi5launch'),
        get_string('cmi5launchlrsduration_default', 'cmi5launch')
    ));

    $settings->add(new admin_setting_configtext(
        'cmi5launch/cmi5launchcustomacchp',
        get_string('cmi5launchcustomacchp', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_help', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_default', 'cmi5launch')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'cmi5launch/cmi5launchuseactoremail',
        get_string('cmi5launchuseactoremail', 'cmi5launch'),
        get_string('cmi5launchuseactoremail_help', 'cmi5launch'),
        1
    ));

    // The first time user logs in there will be a button for setup.
    $showbutton = false;
    // If tenantname or id is false, this is a first time setup we'll have the new button.
    $tenantid = get_config('cmi5launch', 'cmi5launchtenantid');
    $tenantname = get_config('cmi5launch', 'cmi5launchtenantname');

    if ($tenantid == null || $tenantid == false) {
        $showbutton = true;
    }

    $settings->add(new admin_setting_heading('cmi5launch/cmi5launchsettings',
        get_string('cmi5launchsettingsheader', 'cmi5launch'), ''));

    if ($showbutton) {

        // Show only a button, otherwise regular information showing.
        // This is the first time setup.
        // Use this for the button instead of config text.
        // Setup form link/button.
        $setupurl = new moodle_url('/mod/cmi5launch/setupform.php');
        $setupbutton = html_writer::link(
            $setupurl,
            get_string('cmi5launchsetupbuttontitle', 'cmi5launch'),
            ['class' => 'btn btn-secondary']
        );
        $settings->add(new admin_setting_description(
            'cmi5launch/setupform',
            get_string('cmi5launchsetupbutton', 'cmi5launch'),
            $setupbutton
        ));

    } else {

        $settings->add(
            new admin_setting_configtext_mod_cmi5launch(
                'cmi5launch/cmi5launchplayerurl',
                get_string('cmi5launchplayerurl', 'cmi5launch'),
                get_string('cmi5launchplayerurl_help', 'cmi5launch'),
                get_string('cmi5launchplayerurl_default', 'cmi5launch'),
                PARAM_URL
            )
        );

        $setting = new admin_setting_configtext(
            'cmi5launch/cmi5launchbasicname',
            get_string('cmi5launchbasicname', 'cmi5launch'),
            get_string('cmi5launchbasicname_help', 'cmi5launch'),
            get_string('cmi5launchbasicname_default', 'cmi5launch')
        );
        $settings->add($setting);

        $setting = new admin_setting_configtext(
            'cmi5launch/cmi5launchbasepass',
            get_string('cmi5launchbasepass', 'cmi5launch'),
            get_string('cmi5launchbasepass_help', 'cmi5launch'),
            get_string('cmi5launchbasepass_default', 'cmi5launch')
        );
        $settings->add($setting);

        // Display tenant info.
        $tenantnamestring = get_string('cmi5launchtenantnameis', 'cmi5launch') . $tenantname;
        $tenantidstring = get_string('cmi5launchtenantidis', 'cmi5launch') . $tenantid;
        $tenantwarning = get_string('cmi5launchtenant_warning', 'cmi5launch');
        $todisplay = $tenantnamestring . $tenantidstring . $tenantwarning;
        $setting = new admin_setting_description('cmi5launchtenantmessage', "cmi5launch tenant name and id:", $todisplay);
        $settings->add($setting);

        // Token information.
        $setting = new admin_setting_configtext(
            'cmi5launch/cmi5launchtenanttoken',
            get_string('cmi5launchtenanttoken', 'cmi5launch'),
            get_string('cmi5launchtenanttoken_help', 'cmi5launch') ,

            get_string('cmi5launchtenanttoken_default', 'cmi5launch')
        );

        $settings->add($setting);

        // Token setup form link/button.
        $tokenurl = new moodle_url('/mod/cmi5launch/tokensetup.php');
        $tokenbutton = html_writer::link(
            $tokenurl,
            get_string('cmi5launchtokensetupbutton', 'cmi5launch'),
            ['class' => 'btn btn-secondary']
        );
        $settings->add(new admin_setting_description(
            'cmi5launch/tokensetup',
            get_string('cmi5launchtokensetupheading', 'cmi5launch'),
            $tokenbutton
        ));


    }

    // Default grade settings.
    $settings->add(new admin_setting_heading('cmi5launch/gradesettings', get_string('defaultgradesettings', 'cmi5launch'), ''));
    $settings->add(new admin_setting_configselect(
        'cmi5launch/grademethod',
        get_string('grademethod', 'cmi5launch'),
        get_string('grademethoddesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_GRADE_HIGHEST,
        $getgradearray()
    ));

    for ($i = 0; $i <= 100; $i++) {
        $grades[$i] = "$i";
    }

    $settings->add(new admin_setting_configselect(
        'cmi5launch/maxgrade',
        get_string('maximumgrade'),
        get_string('maximumgradedesc', 'cmi5launch'),
        100,
        $grades
    ));

    $settings->add(new admin_setting_heading('cmi5launch/othersettings', get_string('defaultothersettings', 'cmi5launch'), ''));

    // Default attempts settings.
    $settings->add(
        new admin_setting_configselect(
            'cmi5launch/maxattempt',
            get_string('maximumattempts', 'cmi5launch'),
            '',
            '0',
            $cmi5launchgetattemptsarray()
        ),
        get_string('whatmaxdesc', 'cmi5launch'),
    );

    $settings->add(new admin_setting_configselect(
        'cmi5launch/whatgrade',
        get_string('whatgrade', 'cmi5launch'),
        get_string('whatgradedesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_HIGHEST_ATTEMPT,
        $cmi5launchgetgradetypearray()
    ));

    // Not sure if we want to implement mastery override at this time -MB.
    /*
    $settings->add(new admin_setting_configselect('cmi5launch/masteryoverride',
    get_string('masteryoverride', 'cmi5launch'), get_string('masteryoverridedesc', 'cmi5launch'), 1, $yesno));
    */

    $settings->add(new admin_setting_configselect(
        'cmi5launch/MOD_CMI5LAUNCH_LAST_ATTEMPTlock',
        get_string('mod_cmi5launch_last_attempt_lock', 'cmi5launch'),
        get_string('mod_cmi5launch_last_attempt_lockdesc', 'cmi5launch'),
        0,
        $yesno
    ));
}


