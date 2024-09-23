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

require_once(__DIR__ . '/../../config.php');
require_login();

$token = required_param('token', PARAM_ALPHANUM);

$record = $DB->get_record('local_onetimequizuser_tokens', ['token' => $token], '*', MUST_EXIST);

if ($record && time() < $record->expires) {
    // $user = $DB->get_record('user', ['id' => $record->userid], '*', MUST_EXIST);

    // Complete user login
    // complete_user_login($user);

    // Optionally, redirect to a specific page
    redirect(new moodle_url('/local/onetimequizuser/scan.php?token='.$token));
} else {
    // Handle invalid or expired token
    print_error('Invalid token provided');
}