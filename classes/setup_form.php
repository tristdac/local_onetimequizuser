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
require_once($CFG->dirroot.'/local/onetimequizuser/locallib.php');

class setup_form extends moodleform {
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        // Adding a dropdown to select a quiz
        // Assuming you have a function get_quiz_options() that returns an associative array of quizid => quizname
        $mform->addElement('select', 'quizid', get_string('selectquiz', 'local_onetimequizuser'), get_quiz_options());
        $mform->addRule('quizid', null, 'required', null, 'client');

        // Adding a text input for naming the group/cohort
        $mform->addElement('checkbox', 'isinternal', get_string('isinternaldesc', 'local_onetimequizuser'));
        $mform->setType('isinternal', PARAM_BOOL); // Set type of element


        // Adding a text input for naming the group/cohort
        $mform->addElement('text', 'groupname', get_string('groupname', 'local_onetimequizuser'));
        $mform->setType('groupname', PARAM_NOTAGS); // Set type of element
        $mform->addRule('groupname', null, 'required', null, 'client');

        // Add a submit button
        $this->add_action_buttons(true, get_string('submit', 'local_onetimequizuser'));
    }
}
