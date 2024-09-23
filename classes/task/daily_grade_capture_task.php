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

namespace local_onetimequizuser\task;

class daily_grade_capture_task extends \core\task\scheduled_task {
    
    public function get_name() {
        // Task name shown in admin screens.
        return get_string('dailygradecapture', 'local_onetimequizuser');
    }
    
    public function execute() {
        global $DB;

        // Logic to retrieve grades for temporary users.
        // You might use Moodle's Gradebook API to fetch grades for users who completed quizzes on the previous day.
        
        // Logic to store these grades in the local_onetimequizuser_grades table.
        // Use $DB->insert_record() to insert each grade record.
        
        // Optional: Format and send grade data via email.
    }                                                                                                                             
}
