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
 * Form for cmi5 connection, to enter tenant name.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class setup_tenant extends moodleform {

    // Add elements to form.
    public function definition() {
        $messageplayer = get_string('cmi5launchsetupformplayer', 'cmi5launch');
        $messagetenant = get_string('cmi5launchtenantformplayer', 'cmi5launch');
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Add elements to your form. Second arg is the name of element
        $mform->addElement('text', 'cmi5tenant', get_string('cmi5launchtenantnamesetup', 'cmi5launch'));
        // Add a help button with a help message.
        $mform->addHelpButton('cmi5tenant', 'cmi5launchtenantnamesetup', 'cmi5launch');

        // Set type of element.
        $mform->setType('cmi5tenant', PARAM_NOTAGS);
        // Default value.
        $mform->setDefault('cmi5tenant', get_string('cmi5launchtenantname_default', 'cmi5launch')); // The second arg here is the default value and appears in the text box.
        // Add a rule to make this field required.
        $mform->addRule('cmi5tenant', $messageplayer, 'required');

        // $mform->addElement('header', 'cmi5instructions', 'Please enter a tenant name. When submitted it will create a tenant in the cmi5 player and automatically retrieve and save a bearer token for it as well.');
        $mform->addElement('html', $messagetenant);

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}
