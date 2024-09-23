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

function local_onetimequizuser_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'qr_codes') {
        // Assuming 'pc_qr_codes' is the file area where your QR codes are stored

        // No login required to access files in this area
        $itemid = array_shift($args); // Assuming itemid is used in your file paths, adjust as necessary
        $filename = array_pop($args); // The actual file name
        $filepath = $args ? '/' . implode('/', $args) . '/' : '/'; // Construct the file path

        // Retrieve the file from the Moodle file storage system
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'local_onetimequizuser', $filearea, $itemid, $filepath, $filename);
        if ($file) {
            // Send the file to the browser
            send_stored_file($file, 0, 0, $forcedownload, $options); // Adjust options as needed
        } else {
            send_file_not_found();
        }
    } else {
        // If the conditions are not met, don't serve the file
        send_file_not_found();
    }
}

