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

// Ensure this script is called via AJAX
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_login();

// Query the database to count how many PCs are ready (scanned)
$readyPCs = $DB->count_records('local_onetimequizuser_qr_scans', array('scanned' => 1));

// Return the count in JSON format
echo json_encode(array('pcReadyCount' => $readyPCs));
