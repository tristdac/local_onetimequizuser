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

ob_start(); // Start output buffering
// Check if the cookies exist before attempting to delete them
if (isset($_COOKIE['pcSessionId'])) {
    echo 'is set 1';
    // Delete pcSessionId cookie by setting its expiry to a time in the past
    setcookie('pcSessionId', '', time() - 3600, '/local/onetimequizuser', '', isset($_SERVER['HTTPS']), true);
}

if (isset($_COOKIE['pcSessionExpiry'])) {
    echo 'is set 2';
    // Delete pcSessionExpiry cookie by setting its expiry to a time in the past
    setcookie('pcSessionExpiry', '', time() - 3600, '/local/onetimequizuser', '', isset($_SERVER['HTTPS']), true);
}


//  define('NO_MOODLE_COOKIES', true); // This page does not require a Moodle session
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/local/onetimequizuser/classes/guest_form.php');
require_once($CFG->dirroot.'/local/onetimequizuser/locallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

global $PAGE, $CFG, $OUTPUT, $SESSION;

$PAGE->set_url(new moodle_url('/local/onetimequizuser/signup.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('createaccount', 'local_onetimequizuser'));

$destination = optional_param('destination', '', PARAM_URL);
$decodedDestination = $CFG->wwwroot . '/' . ltrim(urldecode($destination), '/');

$cmid = null;
$roleid = 5; // Assuming the role ID for 'student' is 5

// Validate the decoded URL
if (!empty($decodedDestination) && filter_var($decodedDestination, FILTER_VALIDATE_URL) === FALSE) {
    // If the decoded URL is invalid, redirect to the site home or handle the error
    $defaultUrl = new moodle_url('/'); // Define a default URL to use if the destination is invalid
    $destination = $defaultUrl->out(false); // Update $destination to be the default URL

} else {
    // If the decoded URL is valid, you may proceed with additional logic
    // For example, extracting the 'id' parameter from the query string of the valid destination URL
    $urlComponents = parse_url($decodedDestination);

    if (isset($urlComponents['query'])) {
        // Parse the query string for parameters
        parse_str($urlComponents['query'], $queryParams);
        if (isset($queryParams['id'])) {
            $cmid = (int) $queryParams['id']; // Ensure cmid is an integer
        }
    }
}

if (!$cm = get_coursemodule_from_id('quiz', $cmid)) {
    $SESSION->redirect_message = 'Invalid Course Module ID provided.';
    redirect(new moodle_url('/local/onetimequizuser/ondemand.php'));
}

if (!$quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    $SESSION->redirect_message = 'Invalid Quiz ID provided.';
    redirect(new moodle_url('/local/onetimequizuser/ondemand.php'));
}


// Fetch the course module object for 'quiz' with the given $cmid.
if (!$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST)) {
    print_error('invalidcoursemodule');
}

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

// Since we don't have a specific user, and you want to check for potential 'student' access,
// you might consider a hypothetical scenario or default role access. For a specific user check:
// $isAccessible = $info->is_user_visible($USER->id); // For a real user

// However, without a specific user, you cannot directly use is_user_visible().
// You might need to assess general conditions or defaults for 'student' access at this point.

// Example check for module visibility (without user-specific checks)
if (!$cm->visible) {
    $SESSION->redirect_message = 'This quiz is currently not available.';
    redirect(new moodle_url('/local/onetimequizuser/ondemand.php'));
}

if (!$info->is_available_for_all()) {
    // Note: This is a general check and might not cover all user-specific restrictions
    $SESSION->redirect_message = 'This quiz has restrictions that may prevent access.';
    redirect(new moodle_url('/local/onetimequizuser/ondemand.php'));
}

// Retrieve the policy URL and link name from plugin settings
$policyUrlSetting = get_config('local_yourpluginname', 'policyurl');
$policyLinkNameSetting = get_config('local_yourpluginname', 'policylinkname');

// If not yet configured, use default/fallback values
$policyUrl = !empty($policyUrlSetting) ? $policyUrlSetting : new moodle_url('/default/policy/url');
$policyLinkName = !empty($policyLinkNameSetting) ? $policyLinkNameSetting : 'Policy';

// Generate the HTML link
$policyLink = html_writer::link($policyUrl, $policyLinkName, ['target' => '_blank']);

// Get the disclaimer text from language string and replace the placeholder with the actual link
$disclaimerText = get_string('quiztimingdisclaimer_help', 'local_onetimequizuser');
$disclaimerTextWithLink = str_replace('[[policy_link]]', $policyLink, $disclaimerText);

// Determine the time limit text.
$timelimitText = $quiz->timelimit ? format_time($quiz->timelimit) : get_string('notimelimit', 'local_onetimequizuser');

$quizInfo = ''; // Placeholder for quiz information HTML

// Adding quiz information to the $quizInfo variable
$quizInfo .= html_writer::tag('h4', 'Assessment Information');
$quizInfo .= html_writer::tag('p', 'This PC is configured for the assessment below. When instructed to do so by your invigilator, please enter your full name and submit.');
$quizInfo .= html_writer::tag('p', '<strong>Assessment Name:</strong> ' . format_string($quiz->name), ['class' => 'quiz-info-title']);
$quizInfo .= html_writer::tag('p', '<strong>Time Limit:</strong> ' . format_string($timelimitText), ['class' => 'quiz-info-time']);

// Display Pass Grade if Set
$gradeItem = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'courseid' => $quiz->course));
if ($gradeItem) {
    $passGrade = $gradeItem->gradepass;
    if ($passGrade > 0) {
        $quizInfo .= html_writer::tag('p', 'Pass grade: '.$passGrade, ['class' => 'quiz-pass-grade']);
    }
}

// Include the disclaimer in the quiz info
$quizInfo .= html_writer::tag('p', $disclaimerTextWithLink, ['class' => 'quiz-info-disclaimer']);

$customdata = array('destination' => $destination); // Assuming $destination is already defined
$form = new guest_form(null, $customdata);

if ($form->is_cancelled()) {
    // Form cancelled
} else if ($data = $form->get_data()) {
    // Process form submission
    $processedUsername = generate_username($data->guestname); // Implement generate_username based on your logic
    $userid = create_temp_user($processedUsername, $data->guestname); // Your custom function to create a user

    if ($userid) {
        // User created successfully, now fetch the complete user record
        $user = get_complete_user_data('id', $userid);

        if ($cmid) {
            $cmRecord = $DB->get_record('course_modules', ['id' => $cmid], 'course', MUST_EXIST);
            error_log(print_r($cmRecord, TRUE));
            if ($cmRecord) {
                $courseId = $cmRecord->course;
            }
        }

        // Skip enrollment if the course is the site's home course
        if ($courseId != 1) {

            $enrol = enrol_get_plugin('manual');
            if ($enrol) {
                $instances = enrol_get_instances($courseId, true);
                foreach ($instances as $instance) {
                    if ($instance->enrol === 'manual') {
                        $enrol->enrol_user($instance, $userid, $roleid);
                        break; // Stop after finding the first applicable manual enrollment instance
                    }
                }
            }
        }



        if ($user) {
            // Sign the user in
            complete_user_login($user);
            
            // Redirect to the specified location
            redirect($destination);
        } else {
            // Handle the case where user data could not be fetched
            error_log('Failed to fetch user data for userId: ' . $userid);
            // Consider adding a redirect or error message here
        }
    } else {
        // Handle user creation failure
        // Consider logging this error or displaying an error message
        error_log('User creation failed for username: ' . $processedUsername);
        // Redirect or error message
    }
} else {
    // Display the form
    echo $OUTPUT->header();

    // Container for both quiz info and form
    echo html_writer::start_div('container');

    // Row for both quiz info and form
    echo html_writer::start_div('row');

    // Quiz info container
    echo html_writer::div($quizInfo, 'quiz-info col-md-6');

    // Form container
    echo html_writer::start_div('form-container col-md-6');
    $form->display();
    echo html_writer::end_div(); // Close form-container div

    echo html_writer::end_div(); // Close form-row div

    echo html_writer::end_div(); // Close container div

    echo $OUTPUT->footer();

}
