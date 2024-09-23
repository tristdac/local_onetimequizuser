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

// Check if the exam has started
$exam = $DB->get_record('local_onetimequizuser_exams', ['quizid' => $quizid]);

if ($exam && $exam->exam_started) {
    echo json_encode(['exam_started' => true]);
} else {
    echo json_encode(['exam_started' => false]);
}