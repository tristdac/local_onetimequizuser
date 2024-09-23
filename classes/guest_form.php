<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_onetimequizuser
 * @copyright   2024 Tristan daCosta <tristan.dacosta@edinburghcollege.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class guest_form extends moodleform {
    // Add elements to form as before

    public function definition() {
        $mform = $this->_form;

        // Assuming $this->_customdata['destination'] is available
        $destination = isset($this->_customdata['destination']) ? $this->_customdata['destination'] : '';

        $mform->addElement('text', 'guestname', get_string('guestname', 'local_onetimequizuser')); // Add elements to your form
        $mform->setType('guestname', PARAM_NOTAGS); // Set type of element
        $mform->addRule('guestname', null, 'required', null, 'client');

        // Add a hidden element for destination
        $mform->addElement('hidden', 'destination', $destination);
        $mform->setType('destination', PARAM_URL); // Set to PARAM_URL for proper validation


        $this->add_action_buttons(false, get_string('submit'));
    }

    // Add validation for the guest name
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        $guestname = $data['guestname'];
        // Remove spaces and non-alphabetic characters, then check length
        $processedName = preg_replace('/[^a-zA-Z]/', '', $guestname);
        if (strlen($processedName) > 85) { // Adjusted for timestamp and processing
            $errors['guestname'] = get_string('guestnametoolong', 'local_onetimequizuser'); // You'll need to add this string to your lang file
        }

        return $errors;
    }
}
