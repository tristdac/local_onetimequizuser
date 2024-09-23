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
global $DB;

header('Content-Type: application/json');

$pcSessionId = required_param('pcid', PARAM_ALPHANUMEXT); // PC/session identifier

$record = $DB->get_record('local_onetimequizuser_qr_scans', ['pcid' => $pcSessionId, 'scanned' => 1]);
if ($record) {
    $quizRecord = $DB->get_record('local_onetimequizuser_tokens', ['userid' => $record->invigilatorid], 'quizid');
    $coursemodule = get_coursemodule_from_instance('quiz', $quizRecord->quizid, 0, false, MUST_EXIST);

    if ($coursemodule) {
        $quizUrl = new moodle_url('/mod/quiz/view.php', array('id' => $coursemodule->id));
        echo json_encode(['success' => true, 'redirectUrl' => $quizUrl->out(false)]);
        
        // If successful, redirect to signup.php with redirectUrl as a parameter
        // window.location.href = '/signup.php?destination=' + encodeURIComponent(data.redirectUrl);
    } else {
        echo json_encode(['success' => false, 'message' => 'Quiz ID not found for this session.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'QR code has not been scanned yet.']);
}