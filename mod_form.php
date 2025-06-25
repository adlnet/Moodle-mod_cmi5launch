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
 * @copyright  2023 Bohland
 * @copyright  Based on work by 2013 Andrew Downes
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
        $mform->addElement('text', 'name', get_string('cmi5launchname', 'cmi5launch'), ['size' => '64']);
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

        // New local package upload.

        // This is making an array of filemanager.
        $filemanageroptions = [];
        $filemanageroptions['accepted_types'] = ['.zip'];
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

        // Actor account homePage.
        $mform->addElement(
            'text',
            'cmi5launchcustomacchp',
            get_string('cmi5launchcustomacchp', 'cmi5launch'),
            ['size' => '64']
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

    /**
     * Add completion rules to the form.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        // Add Verb Id setting.
        $verbgroup = [];
        $verbgroup[] =& $mform->createElement(
            'checkbox',
            'completionverbenabled',
            ' ',
            get_string('completionverb', 'cmi5launch')
        );
        $verbgroup[] =& $mform->createElement('text', 'cmi5verbid', ' ', ['size' => '64']);
        $mform->setType('cmi5verbid', PARAM_TEXT);

        $mform->addGroup($verbgroup, 'completionverbgroup', get_string('completionverbgroup', 'cmi5launch'), [' '], false);
        $mform->addGroupRule(
            'completionverbgroup', [
                'cmi5verbid' => [
                    [get_string('maximumchars', '', 255), 'maxlength', 255, 'client'],
                ],
            ]
        );

        $mform->addHelpButton('completionverbgroup', 'completionverbgroup', 'cmi5launch');
        $mform->disabledIf('cmi5verbid', 'completionverbenabled', 'notchecked');
        $mform->setDefault('cmi5verbid', 'http://adlnet.gov/expapi/verbs/completed');

        // Add Completion Expiry Date setting.
        $completiongroup = [];
        $completiongroup[] =& $mform->createElement(
            'checkbox',
            'completionexpiryenabled',
            ' ',
            get_string('completionexpiry', 'cmi5launch')
        );
        $completiongroup[] =& $mform->createElement('text', 'cmi5expiry', ' ', ['size' => '64']);
        $mform->setType('cmi5expiry', PARAM_TEXT);

        $mform->addGroup(
            $completiongroup,
            'completionexpirygroup',
            get_string('completionexpirygroup', 'cmi5launch'),
            [' '],
            false
        );
        $mform->addGroupRule(
            'completionexpirygroup', [
                'cmi5expiry' => [
                    [get_string('maximumchars', '', 10), 'maxlength', 10, 'client'],
                ],
            ]
        );

        $mform->addHelpButton('completionexpirygroup', 'completionexpirygroup', 'cmi5launch');
        $mform->disabledIf('cmi5expiry', 'completionexpiryenabled', 'notchecked');
        $mform->setDefault('cmi5expiry', '365');

        return ['completionverbgroup', 'completionexpirygroup'];
    }

    /**
     * Check if completion rules are enabled.
     *
     * @param array $data The submitted form data.
     * @return bool True if at least one completion rule is enabled, false otherwise.
     */
    public function completion_rule_enabled($data) {
        if (!empty($data['completionverbenabled']) && !empty($data['cmi5verbid'])) {
            return true;
        }
        if (!empty($data['completionexpiryenabled']) && !empty($data['cmi5expiry'])) {
            return true;
        }
        return false;
    }

    /**
     * Get the data from the form.
     *
     * @return stdClass The data from the form.
     */
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

    /**
     * Preprocess the data before displaying the form.
     *
     * @param array $defaultvalues The default values for the form.
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        global $DB;

        // Determine if default lrs settings were overriden.
        if (!empty($defaultvalues['overridedefaults'])) {
            if ($defaultvalues['overridedefaults'] == '1') {
                // Retrieve activity lrs settings from DB.
                $cmi5launchlrs = $DB->get_record(
                    'cmi5launch_lrs',
                    ['cmi5launchid' => $defaultvalues['instance']],
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
            ['subdirs' => 0, 'maxfiles' => 1]
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
    /**
     * Validate the form elements after submitting (server-side).     *
     * @param array $data The submitted form data.
     * @param array $files The submitted files.
     * @return array An array of errors, if any.
     */
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
                ['subdirs' => 0, 'maxfiles' => 1]
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
