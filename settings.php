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

defined('MOODLE_INTERNAL') || die;

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

    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchplayerurl',
        get_string('cmi5launchplayerurl', 'cmi5launch'),
        get_string('cmi5launchplayerurl_help', 'cmi5launch'),
        get_string('cmi5launchplayerurl_default', 'cmi5launch'), PARAM_URL));

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchtenantname',
        get_string('cmi5launchtenantname', 'cmi5launch'),
        get_string('cmi5launchtenantname_help', 'cmi5launch'),
        get_string('cmi5launchtenantname_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchtenantpass',
        get_string('cmi5launchtenantpass', 'cmi5launch'),
        get_string('cmi5launchtenantpass_help', 'cmi5launch'),
        get_string('cmi5launchtenantpass_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchtenanttoken',
        get_string('cmi5launchtenanttoken', 'cmi5launch'),
        get_string('cmi5launchtenanttoken_help', 'cmi5launch'),
        get_string('cmi5launchtenanttoken_default', 'cmi5launch'));
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
