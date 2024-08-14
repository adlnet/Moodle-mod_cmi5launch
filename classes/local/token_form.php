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
 * Form for cmi5 connection, and tenant and token. 
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");


class setup_token extends moodleform {
    // Add elements to form.
    public function definition() {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Add elements to your form. Second arg is the name of element
        $mform->addElement('text', 'cmi5token', get_string('cmi5launchtenanttoken', 'cmi5launch'));
        // Set type of element.
        $mform->setType('cmi5token', PARAM_NOTAGS);
        // Default value.
        $mform->setDefault('cmi5token', get_string('cmi5launchtenanttoken_default', 'cmi5launch')); // The second arg here is the default value and appears in the text box.
        // These three go together for making one eleme
       // $mform->addElement('button', 'generatetoken', 'Generate Token');
        // Add a rule to make this field required.
        $mform->addRule('cmi5token', 'This is needed to connect to player', 'required');

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}
