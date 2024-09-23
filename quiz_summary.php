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
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
global $PAGE, $OUTPUT, $USER, $SESSION;

// Check that the user is logged in and has the invigilator capability
require_login();
$context = context_system::instance();
require_capability('local/onetimequizuser:managequizzes', $context);

// Set up the page
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/onetimequizuser/quiz_summary.php'));
$PAGE->set_title(get_string('quizsummary', 'local_onetimequizuser'));
$PAGE->set_heading(get_string('quizsummary', 'local_onetimequizuser'));

// Example of retrieving quiz details and QR code file path
// These would have been set during the quiz configuration process
$quizid = optional_param('quizid', 0, PARAM_INT); // Example of getting quiz ID passed as a parameter

// Load quiz information from the database
if ($quizid) {
    $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);
    $quizName = format_string($quiz->name);
} else {
    $quizName = get_string('unknownquiz', 'local_onetimequizuser');
}

// Get the course module from the instance
if ($cm = get_coursemodule_from_instance('quiz', $quizid)) {
    $cmid = $cm->id;
    // Now you have the cmid and can use it as needed
} else {
    // Handle the case where no corresponding course module is found
    print_error('nocoursemodule', 'local_onetimequizuser');
}

// Assuming system context, adjust as needed
$contextid = context_system::instance()->id;
$component = 'local_onetimequizuser';
$filearea = 'qr_codes';
$itemid = 0; // Assuming itemid is not used and set to 0
$filepath = '/';
$filename = 'qr_' . $USER->id . '.png'; // The QR code filename pattern you're using

// Construct the URL to the stored QR code file
$qrUrl = moodle_url::make_pluginfile_url(
    $contextid,
    $component,
    $filearea,
    $itemid,
    $filepath,
    $filename
);

// Use get_fast_modinfo() to obtain course_modinfo instance
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$modinfo = get_fast_modinfo($course);

// Use $modinfo to get the cm_info object
$cm_info = $modinfo->get_cm($cmid);

if (!$cm_info) {
    print_error('invalidcoursemodule');
}

// Now $cm_info is guaranteed to be of type cm_info, which can be used to create an info_module instance
$info = new \core_availability\info_module($cm_info);

// Example check for module visibility (without user-specific checks)
if (!$cm->visible) {
    $SESSION->redirect_message = 'This quiz is currently not available.';
    redirect(new moodle_url('/local/onetimequizuser/setup.php'));
}

if (!$info->is_available_for_all()) {
    // Note: This is a general check and might not cover all user-specific restrictions
    $SESSION->redirect_message = 'This quiz has restrictions that may prevent access.';
    redirect(new moodle_url('/local/onetimequizuser/setup.php'));
}

// Determine the time limit text.
$timelimitText = $quiz->timelimit ? format_time($quiz->timelimit) : get_string('notimelimit', 'quiz');

$quizInfo = ''; // Placeholder for quiz information HTML

// Adding quiz information to the $quizInfo variable
$quizInfo .= html_writer::tag('h4', 'Assessment Information');
$quizInfo .= html_writer::tag('p', 'Scanned PCs will be configured using the following:');
$quizInfo .= html_writer::tag('p', '<strong>Assessment Name:</strong> ' . format_string($quiz->name), ['class' => 'quiz-info-title']);
$quizInfo .= html_writer::tag('p', '<strong>Group Name:</strong> Example Group <i>(External candidates)</i>');
$quizInfo .= html_writer::tag('p', '<strong>Time Limit:</strong> ' . format_string($timelimitText), ['class' => 'quiz-info-time']);

// Display Pass Grade if Set
$gradeItem = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'courseid' => $quiz->course));
if ($gradeItem) {
    $passGrade = $gradeItem->gradepass;
    if ($passGrade > 0) {
        $quizInfo .= html_writer::tag('p', '<strong>Pass grade:</strong> '.$passGrade, ['class' => 'quiz-pass-grade']);
    }
}

echo $OUTPUT->header();

// echo html_writer::start_div('container');
echo html_writer::start_div('row');
echo html_writer::start_div('quizinfo col-md-4');

// echo $OUTPUT->heading(get_string('quizsummary', 'local_onetimequizuser'));

// Display quiz information and instructions
// echo html_writer::tag('p', get_string('selectedquiz', 'local_onetimequizuser', $quizName));
// echo html_writer::tag('p', get_string('moduleid', 'local_onetimequizuser', $cmid));
// Quiz info container
echo html_writer::div($quizInfo, 'quiz-info');

echo html_writer::start_div('qr-container');

// Output the QR code image
echo html_writer::empty_tag('img', ['src' => $qrUrl->out(false), 'alt' => 'QR Code', 'id' => 'qrCode']);
// echo html_writer::span('', 'countdown', ['id' => 'countdownTimer']);
// echo html_writer::tag('button', 'Show QR Code', ['id' => 'toggleBlur', 'class' => 'btn btn-primary', 'type' => 'button']);

echo html_writer::end_div();

echo html_writer::end_div();

echo html_writer::start_div('invigilator-instructions col-md-8');

echo html_writer::tag('p', get_string('invigilatorinstructions', 'local_onetimequizuser'));

echo html_writer::end_div();

echo html_writer::tag('button', get_string('startexam', 'local_onetimequizuser'), ['id' => 'startExamBtn', 'class' => 'btn btn-primary']);

echo html_writer::end_div();
// echo html_writer::end_div();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial setup
    let pcCount = 0;

    // Simulate step 1 completion
    setTimeout(function() {
        completeStep(1); // Simulate that "Configure Quiz" is done
    }, 1000); // Simulate step completion after 1 second

    // Simulate step 2 completion (QR code scanned by invigilator)
    setTimeout(function() {
        completeStep(2); // Simulate that the invigilator scanned the QR code
    }, 2000); // Simulate after 2 seconds

    // Function to mark a step as completed and show the green checkmark
    function completeStep(stepNumber) {
        var step = document.getElementById('step' + stepNumber);
        var tick = document.getElementById('tick' + stepNumber);

        if (step && tick) {
            // Display the green tick
            tick.style.display = 'inline';
            step.classList.add('completed-step');
        }
    }

    // Poll the server every 5 seconds to check how many PCs are ready
    setInterval(() => {
        fetch('/local/onetimequizuser/check_pc_ready.php')
            .then(response => response.json())
            .then(data => {
                if (data.pcReadyCount) {
                    pcCount = data.pcReadyCount; // Update the count based on the server response
                    document.getElementById('pcCount').textContent = pcCount; // Update the UI
                }
            })
            .catch(error => {
                console.error('Error fetching PC ready count:', error);
            });
    }, 5000); // Poll every 5 seconds
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial setup
    let pcCount = 0;

    // Simulate step 1 completion
    setTimeout(function() {
        completeStep(1); // Simulate that "Configure Quiz" is done
    }, 1000); // Simulate step completion after 1 second

    // Simulate step 2 completion (QR code scanned by invigilator)
    setTimeout(function() {
        completeStep(2); // Simulate that the invigilator scanned the QR code
    }, 2000); // Simulate after 2 seconds

    // Function to mark a step as completed and show the green checkmark
    function completeStep(stepNumber) {
        var step = document.getElementById('step' + stepNumber);
        var tick = document.getElementById('tick' + stepNumber);

        if (step && tick) {
            // Display the green tick
            tick.style.display = 'inline';
            step.classList.add('completed-step');
        }
    }

    // Poll the server every 5 seconds to check how many PCs are ready
    setInterval(() => {
        fetch('/local/onetimequizuser/check_pc_ready.php')
            .then(response => response.json())
            .then(data => {
                if (data.pcReadyCount) {
                    pcCount = data.pcReadyCount; // Update the count based on the server response
                    document.getElementById('pcCount').textContent = pcCount; // Update the UI
                }
            })
            .catch(error => {
                console.error('Error fetching PC ready count:', error);
            });
    }, 5000); // Poll every 5 seconds
});
</script>

<script>
    document.getElementById('startExamBtn').addEventListener('click', function() {
        fetch('/local/onetimequizuser/start_exam.php?quizid=<?php echo $quizid; ?>', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exam started successfully!');
            } else {
                alert('Error starting the exam.');
            }
        })
        .catch(error => console.error('Error starting exam:', error));
    });
</script>

<!-- <script>
function toggleBlurAndButtonVisibility() {
    const qrCode = document.getElementById('qrCode');
    const toggleButton = document.getElementById('toggleBlur');
    // Toggle blur
    qrCode.classList.toggle('blur');

    // If the QR code is blurred, show the button, else hide it
    if (qrCode.classList.contains('blur')) {
        toggleButton.style.display = 'block'; // Show button
    } else {
        toggleButton.style.display = 'none'; // Hide button
        startCountdown(); // Start or restart the countdown when QR is visible
    }
}

let countdownTime = 15; // 15 seconds until obfuscation
let countdownInterval;

function startCountdown() {
    const countdownTimerElement = document.getElementById('countdownTimer');
    countdownTime = 15; // Reset countdown time to 15 seconds
    countdownTimerElement.textContent = `QR code will obfuscate in ${countdownTime} seconds`;

    // Clear any existing interval to avoid duplicates
    clearInterval(countdownInterval);

    countdownInterval = setInterval(() => {
        countdownTime--;
        countdownTimerElement.textContent = `QR code is available for next ${countdownTime} seconds`;

        if (countdownTime <= 0) {
            clearInterval(countdownInterval); // Stop the countdown
            toggleBlurAndButtonVisibility(); // Apply the blur and show the button
            countdownTimerElement.textContent = ''; // Optionally clear the countdown message
        }
    }, 1000);
}

// Initial start of the countdown
startCountdown();

document.getElementById('toggleBlur').addEventListener('click', function() {
    // Toggle blur and button visibility
    toggleBlurAndButtonVisibility();
});

// Initially hide the button until the QR code is first obfuscated
document.getElementById('toggleBlur').style.display = 'none';
</script> -->

<?php

echo $OUTPUT->footer();
