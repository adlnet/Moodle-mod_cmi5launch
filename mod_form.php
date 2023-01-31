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
 * The main cmi5launch configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_cmi5launch_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        global $CFG;
        $cfgcmi5launch = get_config('cmi5launch');

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('cmi5launchname', 'cmi5launch'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'cmi5launchname', 'cmi5launch');
        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        $mform->addElement('header', 'packageheading', get_string('cmi5packagetitle', 'cmi5launch'));
        $mform->addElement(
            'static',
            'packagesettingsdescription',
            get_string('cmi5packagetitle', 'cmi5launch'),
            get_string('cmi5packagetext', 'cmi5launch')
        );

        // Start required Fields for Activity.
        $mform->addElement('text', 'cmi5launchurl', get_string('cmi5launchurl', 'cmi5launch'), array('size' => '64'));
        $mform->setType('cmi5launchurl', PARAM_TEXT);
        $mform->addRule('cmi5launchurl', null, 'required', null, 'client');
        $mform->addRule('cmi5launchurl', get_string('maximumchars', '', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('cmi5launchurl', 'cmi5launchurl', 'cmi5launch');
        $mform->setDefault('cmi5launchurl', 'https://example.com/example-activity/index.html');
        
        
        $mform->addElement('text', 'cmi5activityid', get_string('cmi5activityid', 'cmi5launch'), array('size' => '64'));
        $mform->setType('cmi5activityid', PARAM_TEXT);
        $mform->addRule('cmi5activityid', null, 'required', null, 'client');
        $mform->addRule('cmi5activityid', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('cmi5activityid', 'cmi5activityid', 'cmi5launch');
        $mform->setDefault('cmi5activityid', 'https://example.com/example-activity');
        // End required Fields for Activity.

        // New local package upload.

        //Ok, this is making an array of filemanager
        $filemanageroptions = array();
        $filemanageroptions['accepted_types'] = array('.zip');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;
        $mform->addElement(
            'filemanager',
            'packagefile',
            get_string('cmi5package',
            'cmi5launch'),
            null,
            $filemanageroptions
        );
        $mform->addHelpButton('packagefile', 'cmi5package', 'cmi5launch');

        // Start advanced settings.
        $mform->addElement('header', 'lrsheading', get_string('lrsheading', 'cmi5launch'));

        $mform->addElement(
            'static',
            'description',
            get_string('lrsdefaults', 'cmi5launch'),
            get_string('lrssettingdescription', 'cmi5launch')
        );

        // Override default LRS settings.
        $mform->addElement('advcheckbox', 'overridedefaults', get_string('overridedefaults', 'cmi5launch'));
        $mform->addHelpButton('overridedefaults', 'overridedefaults', 'cmi5launch');

        // Add LRS endpoint.
        $mform->addElement(
            'text',
            'cmi5launchlrsendpoint',
            get_string('cmi5launchlrsendpoint', 'cmi5launch'),
            array('size' => '64')
        );
        $mform->setType('cmi5launchlrsendpoint', PARAM_TEXT);
        $mform->addRule('cmi5launchlrsendpoint', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('cmi5launchlrsendpoint', 'cmi5launchlrsendpoint', 'cmi5launch');
        $mform->setDefault('cmi5launchlrsendpoint', $cfgcmi5launch->cmi5launchlrsendpoint);
        $mform->disabledIf('cmi5launchlrsendpoint', 'overridedefaults');

        // Add LRS Authentication.
        $authoptions = array(
            1 => get_string('cmi5launchlrsauthentication_option_0', 'cmi5launch'),
            2 => get_string('cmi5launchlrsauthentication_option_1', 'cmi5launch'),
            0 => get_string('cmi5launchlrsauthentication_option_2', 'cmi5launch')
        );
        $mform->addElement(
            'select',
            'cmi5launchlrsauthentication',
            get_string('cmi5launchlrsauthentication', 'cmi5launch'),
            $authoptions
        );
        $mform->disabledIf('cmi5launchlrsauthentication', 'overridedefaults');
        $mform->addHelpButton('cmi5launchlrsauthentication', 'cmi5launchlrsauthentication', 'cmi5launch');
        $mform->getElement('cmi5launchlrsauthentication')->setSelected($cfgcmi5launch->cmi5launchlrsauthentication);

        $mform->addElement(
            'static',
            'description',
            get_string('cmi5launchlrsauthentication_watershedhelp_label', 'cmi5launch'),
            get_string('cmi5launchlrsauthentication_watershedhelp', 'cmi5launch')
        );

        // Add basic authorisation login.
        $mform->addElement(
            'text',
            'cmi5launchlrslogin',
            get_string('cmi5launchlrslogin', 'cmi5launch'),
            array('size' => '64')
        );
        $mform->setType('cmi5launchlrslogin', PARAM_TEXT);
        $mform->addRule('cmi5launchlrslogin', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('cmi5launchlrslogin', 'cmi5launchlrslogin', 'cmi5launch');
        $mform->setDefault('cmi5launchlrslogin', $cfgcmi5launch->cmi5launchlrslogin);
        $mform->disabledIf('cmi5launchlrslogin', 'overridedefaults');

        // Add basic authorisation pass.
        $mform->addElement(
            'password',
            'cmi5launchlrspass',
            get_string('cmi5launchlrspass', 'cmi5launch'),
            array('size' => '64')
        );
        $mform->setType('cmi5launchlrspass', PARAM_TEXT);
        $mform->addRule('cmi5launchlrspass', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('cmi5launchlrspass', 'cmi5launchlrspass', 'cmi5launch');
        $mform->setDefault('cmi5launchlrspass', $cfgcmi5launch->cmi5launchlrspass);
        $mform->disabledIf('cmi5launchlrspass', 'overridedefaults');

        // Duration.
        $mform->addElement(
            'text',
            'cmi5launchlrsduration',
            get_string('cmi5launchlrsduration', 'cmi5launch'),
            array('size' => '64')
        );
        $mform->setType('cmi5launchlrsduration', PARAM_TEXT);
        $mform->addRule('cmi5launchlrsduration', get_string('maximumchars', '', 5), 'maxlength', 5, 'client');
        $mform->addHelpButton('cmi5launchlrsduration', 'cmi5launchlrsduration', 'cmi5launch');
        $mform->setDefault('cmi5launchlrsduration', $cfgcmi5launch->cmi5launchlrsduration);
        $mform->disabledIf('cmi5launchlrsduration', 'overridedefaults');

        // Actor account homePage.
        $mform->addElement(
            'text',
            'cmi5launchcustomacchp',
            get_string('cmi5launchcustomacchp', 'cmi5launch'),
            array('size' => '64')
        );
        $mform->setType('cmi5launchcustomacchp', PARAM_TEXT);
        $mform->addRule('cmi5launchcustomacchp', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('cmi5launchcustomacchp', 'cmi5launchcustomacchp', 'cmi5launch');
        $mform->setDefault('cmi5launchcustomacchp', $cfgcmi5launch->cmi5launchcustomacchp);
        $mform->disabledIf('cmi5launchcustomacchp', 'overridedefaults');

        // Don't use email.
        $mform->addElement(
            'advcheckbox',
            'cmi5launchuseactoremail',
            get_string('cmi5launchuseactoremail', 'cmi5launch')
        );
        $mform->addHelpButton('cmi5launchuseactoremail', 'cmi5launchuseactoremail', 'cmi5launch');
        $mform->setDefault('cmi5launchuseactoremail', $cfgcmi5launch->cmi5launchuseactoremail);
        $mform->disabledIf('cmi5launchuseactoremail', 'overridedefaults');
        // End advanced settings.

        // Behavior settings.
        $mform->addElement('header', 'behaviorheading', get_string('behaviorheading', 'cmi5launch'));

        // Allow multiple ongoing registrations.
        $mform->addElement('advcheckbox', 'cmi5multipleregs', get_string('cmi5multipleregs', 'cmi5launch'));
        $mform->addHelpButton('cmi5multipleregs', 'cmi5multipleregs', 'cmi5launch');
        $mform->setDefault('cmi5multipleregs', 1);

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        // Add Verb Id setting.
        $verbgroup = array();
        $verbgroup[] =& $mform->createElement(
            'checkbox',
            'completionverbenabled',
            ' ',
            get_string('completionverb', 'cmi5launch')
        );
        $verbgroup[] =& $mform->createElement('text', 'cmi5verbid', ' ', array('size' => '64'));
        $mform->setType('cmi5verbid', PARAM_TEXT);

        $mform->addGroup($verbgroup, 'completionverbgroup', get_string('completionverbgroup', 'cmi5launch'), array(' '), false);
        $mform->addGroupRule(
            'completionverbgroup', array(
                'cmi5verbid' => array(
                    array(get_string('maximumchars', '', 255), 'maxlength', 255, 'client')
                )
            )
        );

        $mform->addHelpButton('completionverbgroup', 'completionverbgroup', 'cmi5launch');
        $mform->disabledIf('cmi5verbid', 'completionverbenabled', 'notchecked');
        $mform->setDefault('cmi5verbid', 'http://adlnet.gov/expapi/verbs/completed');

        // Add Completion Expiry Date setting.
        $completiongroup = array();
        $completiongroup[] =& $mform->createElement(
            'checkbox',
            'completionexpiryenabled',
            ' ',
            get_string('completionexpiry', 'cmi5launch')
        );
        $completiongroup[] =& $mform->createElement('text', 'cmi5expiry', ' ', array('size' => '64'));
        $mform->setType('cmi5expiry', PARAM_TEXT);

        $mform->addGroup(
            $completiongroup,
            'completionexpirygroup',
            get_string('completionexpirygroup', 'cmi5launch'),
            array(' '),
            false
        );
        $mform->addGroupRule(
            'completionexpirygroup', array(
                'cmi5expiry' => array(
                    array(get_string('maximumchars', '', 10), 'maxlength', 10, 'client')
                )
            )
        );

        $mform->addHelpButton('completionexpirygroup', 'completionexpirygroup', 'cmi5launch');
        $mform->disabledIf('cmi5expiry', 'completionexpiryenabled', 'notchecked');
        $mform->setDefault('cmi5expiry', '365');

        return array('completionverbgroup', 'completionexpirygroup');
    }

    public function completion_rule_enabled($data) {
        if (!empty($data['completionverbenabled']) && !empty($data['cmi5verbid'])) {
            return true;
        }
        if (!empty($data['completionexpiryenabled']) && !empty($data['cmi5expiry'])) {
            return true;
        }
        return false;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked.
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionverbenabled) || !$autocompletion) {
                $data->cmi5verbid = '';
            }
            if (empty($data->completionexpiryenabled) || !$autocompletion) {
                $data->cmi5expiry = '';
            }
        }
        return $data;
    }

    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        global $DB;

        // Determine if default lrs settings were overriden.
        if (!empty($defaultvalues['overridedefaults'])) {
            if ($defaultvalues['overridedefaults'] == '1') {
                // Retrieve activity lrs settings from DB.
                $cmi5launchlrs = $DB->get_record(
                    'cmi5launch_lrs',
                    array('cmi5launchid' => $defaultvalues['instance']),
                    $fields = '*',
                    IGNORE_MISSING
                );
                $defaultvalues['cmi5launchlrsendpoint'] = $cmi5launchlrs->lrsendpoint;
                $defaultvalues['cmi5launchlrsauthentication'] = $cmi5launchlrs->lrsauthentication;
                $defaultvalues['cmi5launchcustomacchp'] = $cmi5launchlrs->customacchp;
                $defaultvalues['cmi5launchuseactoremail'] = $cmi5launchlrs->useactoremail;
                $defaultvalues['cmi5launchlrsduration'] = $cmi5launchlrs->lrsduration;
                $defaultvalues['cmi5launchlrslogin'] = $cmi5launchlrs->lrslogin;
                $defaultvalues['cmi5launchlrspass'] = $cmi5launchlrs->lrspass;

            }
        }

        $draftitemid = file_get_submitted_draft_itemid('packagefile');
        file_prepare_draft_area(
            $draftitemid,
            $this->context->id,
            'mod_cmi5launch',
            'package',
            0,
            array('subdirs' => 0, 'maxfiles' => 1)
        );
        $defaultvalues['packagefile'] = $draftitemid;

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        if (!empty($defaultvalues['cmi5verbid'])) {
            $defaultvalues['completionverbenabled'] = 1;
        } else {
            $defaultvalues['cmi5verbid'] = 'http://adlnet.gov/expapi/verbs/completed';
        }
        if (!empty($defaultvalues['cmi5expiry'])) {
            $defaultvalues['completionexpiryenabled'] = 1;
        } else {
            $defaultvalues['cmi5expiry'] = 365;
        }

    }
    // Validate the form elements after submitting (server-side).
    public function validation($data, $files) {
        global $CFG, $USER;
        $errors = parent::validation($data, $files);
        if (!empty($data['packagefile'])) {
            $draftitemid = file_get_submitted_draft_itemid('packagefile');

            file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_cmi5launch',
                'packagefilecheck',
                null,
                array('subdirs' => 0, 'maxfiles' => 1)
            );

            // Get file from users draft area.
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

            if (count($files) < 1) {
                return $errors;
            }
            $file = reset($files);
            // Validate this cmi5 package.
            $errors = array_merge($errors, cmi5launch_validate_package($file));
        }
        return $errors;
    }
}
