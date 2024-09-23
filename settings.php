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
 * Plugin administration pages are defined here.
 *
 * @package     local_onetimequizuser
 * @category    admin
 * @copyright   2024 Tristan daCosta <tristan.dacosta@edinburghcollege.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_onetimequizuser_settings', new lang_string('pluginname', 'local_onetimequizuser'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {

        require_once($CFG->dirroot . "/course/lib.php");
    
        // Fetch the list of course categories
        $displaylist = core_course_category::make_categories_list();
        
        // Add the setting to select a quiz category
        $settings->add(new admin_setting_configselect(
            'local_onetimequizuser/quizcategory', // Name of the setting
            get_string('quizcategory', 'local_onetimequizuser'), // Title shown in admin settings
            get_string('quizcategory_desc', 'local_onetimequizuser'), // Description
            '', // Default value
            $displaylist // The array of options for the dropdown
        ));

    }
}
