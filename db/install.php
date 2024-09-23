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
 * Post-installation procedure for local_onetimequizuser plugin.
 *
 * @package     local_onetimequizuser
 * @copyright   2024 Tristan daCosta <tristan.dacosta@edinburghcollege.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_onetimequizuser_install() {
    // Delay the execution of the role assignment until after the plugin install is complete.
    register_shutdown_function('local_onetimequizuser_install_roles');
}

function local_onetimequizuser_install_roles() {
    global $CFG;
    require_once($CFG->libdir . '/accesslib.php');

    // Define the new role
    $role = new stdClass();
    $role->name = 'Invigilator';
    $role->shortname = 'invigilator';
    $role->description = 'Can manage and view quizzes';
    $role->sortorder = 0;
    $role->archetype = 'manager'; // No archetype defined because it's a custom role

    // Create the role
    if (!$roleid = create_role($role->name, $role->shortname, $role->description, $role->archetype)) {
        throw new moodle_exception('Could not create the new role "Invigilator"');
    }

    // Assign capabilities to the role after they exist in the system
    $systemcontext = context_system::instance();
    assign_capability('local/onetimequizuser:createquiz', CAP_ALLOW, $roleid, $systemcontext->id, true);
    assign_capability('local/onetimequizuser:viewquiz', CAP_ALLOW, $roleid, $systemcontext->id, true);
}
