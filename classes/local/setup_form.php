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
 * Form for cmi5 connection, namely basic info - player url, user name and password.
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */

 defined('MOODLE_INTERNAL') || die();

// Moodleform is defined in formslib.php.
require_once("$CFG->libdir/formslib.php");
/**
 * A form for the purpose of settin up connection to the cmi5 player.
 */
class setup_cmi5 extends moodleform {
    // Add elements to form.
    /**
     * Form definition.
     *
     * This function is called when the form is created.
     * It defines the elements of the form.
     */
    public function definition() {
        $message = get_string('cmi5launchsetupformplayer', 'cmi5launch');
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Add elements to your form. Second arg is the name of element
        // Player url.
        $mform->addElement('text', 'cmi5url', get_string('cmi5launchplayerurl', 'cmi5launch'));
        // Set type of element.
        $mform->setType('cmi5url', PARAM_NOTAGS);
        // Default value, the second arg here is the default value and appears in the text box.
        $mform->setDefault('cmi5url', get_string('cmi5launchplayerurl_default', 'cmi5launch'));
        // Add a rule to make this field required.
        $mform->addRule('cmi5url', $message, 'required');
        // Add a help button with a help message.
        $mform->addHelpButton('cmi5url', 'cmi5launchplayerurl', 'cmi5launch');

        // User name.
        $mform->addElement('text', 'cmi5name', get_string('cmi5launchbasicname', 'cmi5launch'));
        // Set type of element.
        $mform->setType('cmi5name', PARAM_NOTAGS);
        // Default value. The second arg here is the default value and appears in the text box.
        $mform->setDefault('cmi5name', get_string('cmi5launchbasicname_default', 'cmi5launch'));
        // Add a rule to make this field required.
        $mform->addRule('cmi5name',  $message, 'required');
        // Add a help button with a help message.
        $mform->addHelpButton('cmi5name', 'cmi5launchbasicname', 'cmi5launch');

        // Password.
        // Add elements to your form. Second arg is the name of element.
        $mform->addElement('text', 'cmi5password', get_string('cmi5launchbasepass', 'cmi5launch'));
        // Set type of element.
        $mform->setType('cmi5password', PARAM_NOTAGS);
        // Default value. The second arg here is the default value and appears in the text box.
        $mform->setDefault('cmi5password', get_string('cmi5launchbasepass_default', 'cmi5launch'));
        // Add a rule to make this field required.
        $mform->addRule('cmi5password',  $message, 'required');
        // Below is the help button.
        $mform->addHelpButton('cmi5password', 'cmi5launchbasepass', 'cmi5launch');

         $this->add_action_buttons();
    }

    /**
     * A function for validatin the form, currently unused.
     * @param mixed $data - The data submitted by the form.
     * @param mixed $files -  The files submitted by the form, if any.
     * @return array - An array of errors, if any.
     */
    public function validation($data, $files) {
        return [];
    }
}
