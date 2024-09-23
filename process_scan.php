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

require_once('../../config.php');
require_login();

global $DB, $USER;

$rawData = file_get_contents('php://input');
$data = json_decode($rawData);

if (isset($data->pcid) && isset($data->quizid)) {
    $pcid = $data->pcid;
    $quizid = $data->quizid;

    // Retrieve the existing record for the PC/session
    $record = $DB->get_record('local_onetimequizuser_qr_scans', ['pcid' => $pcid]);

    if ($record) {
        // Update the record with the invigilator ID and selected quiz ID
        $record->scanned = 1;
        $record->invigilatorid = $USER->id; // Logged-in user's ID as the invigilator
        // $record->redirect_url = new moodle_url('/mod/quiz/view.php', ['id' => $quizid])->out(false);
        try {
            $redirectUrl = new moodle_url('/mod/quiz/view.php', ['id' => $quizid]);
            $record->redirect_url = $redirectUrl->out(false);
        } catch (Exception $e) {
            error_log('Error generating redirect URL: ' . $e->getMessage());
            // Handle error accordingly
        }

        // Update the record in the database
        $DB->update_record('local_onetimequizuser_qr_scans', $record);

        // delete_qr_code($pcid);

        echo json_encode(['success' => true, 'message' => 'Scan processed and session updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session record not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Insufficient data provided.']);
}
