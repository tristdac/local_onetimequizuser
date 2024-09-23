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

require_once('../../config.php');
require_login();

global $DB;

$quizid = required_param('quizid', PARAM_INT);

// Mark the exam as started in the database
$record = new stdClass();
$record->quizid = $quizid;
$record->exam_started = 1;
$record->timestarted = time();

// Update or insert record for exam start
if ($DB->record_exists('local_onetimequizuser_exams', ['quizid' => $quizid])) {
    $DB->update_record('local_onetimequizuser_exams', $record);
} else {
    $DB->insert_record('local_onetimequizuser_exams', $record);
}

echo json_encode(['success' => true]);
