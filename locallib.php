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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/user/lib.php');

function create_temp_user($username, $guestname) {
    global $DB, $CFG;

    $user = new stdClass();
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->username = $username;
    // $user->password = hash('sha256', random_bytes(32)); // Secure random password
    $user->password = generate_dynamic_password();
    $user->firstname = $guestname; // Adjust according to your form's data
    $user->lastname = "(OnDemand)";
    $user->email = generate_unique_email($username); // Implement this based on your requirements
    $user->id = user_create_user($user);

    return $user->id;
}

function generate_username($guestname) {
    // Sanitize the guest name to remove spaces and non-alphanumeric characters
    $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $guestname);
    // Shorten the name to a manageable length if necessary
    $shortName = substr($cleanName, 0, 8);
    // Append a timestamp to ensure uniqueness
    $timestamp = time();
    
    $username = strtolower($shortName . $timestamp);
    
    return $username;
}

function generate_unique_email($username) {
    $email = $username.'@guest.nomail';

    return $email;
}

function generate_dynamic_password() {
    global $CFG;

    // Retrieve Moodle's password policy settings
    $minLength = !empty($CFG->minpasswordlength) ? $CFG->minpasswordlength : 8; // Default to 8 if not set
    $minDigits = !empty($CFG->minpassworddigits) ? $CFG->minpassworddigits : 1; // Default to 1 if not set
    $minLower = !empty($CFG->minpasswordlower) ? $CFG->minpasswordlower : 1; // Default to 1 if not set
    $minUpper = !empty($CFG->minpasswordupper) ? $CFG->minpasswordupper : 1; // Default to 1 if not set
    $minNonAlphaNum = !empty($CFG->minpasswordnonalphanum) ? $CFG->minpasswordnonalphanum : 1; // Default to 1 if not set

    // Character pools
    $digits = '0123456789';
    $lowerLetters = 'abcdefghijklmnopqrstuvwxyz';
    $upperLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $specialChars = '!@#$%^&*()-_=+[]{};:,.<>?';

    // Initial password parts based on requirements
    $password = [
        'digits' => substr(str_shuffle($digits), 0, $minDigits),
        'lower' => substr(str_shuffle($lowerLetters), 0, $minLower),
        'upper' => substr(str_shuffle($upperLetters), 0, $minUpper),
        'special' => substr(str_shuffle($specialChars), 0, $minNonAlphaNum),
    ];

    // Calculate remaining length to fill
    $currentLength = array_sum(array_map('strlen', $password));
    $remainingLength = max($minLength - $currentLength, 0);

    // Fill the remaining length with a mix of all characters to meet the total length requirement
    $allChars = $digits . $lowerLetters . $upperLetters . $specialChars;
    $password['fill'] = substr(str_shuffle($allChars), 0, $remainingLength);

    // Shuffle and concatenate all parts to form the final password
    $finalPassword = str_shuffle(implode('', $password));

    return $finalPassword;
}

function get_quiz_options() {
    global $DB;
    
    $selectedCategory = get_config('local_onetimequizuser', 'quizcategory');
    $options = array();
    
    if (!empty($selectedCategory)) {
        // Find all courses within the selected category (including subcategories)
        $categoryContext = CONTEXT_COURSECAT::instance($selectedCategory);
        $subcategories = $categoryContext->get_child_contexts();
        $categoryIds = array($selectedCategory); // Include the selected category itself
        
        foreach ($subcategories as $context) {
            if ($context->contextlevel == CONTEXT_COURSECAT) {
                $categoryIds[] = $context->instanceid;
            }
        }
        
        // Fetch courses in these categories
        list($catSql, $catParams) = $DB->get_in_or_equal($categoryIds);
        $courses = $DB->get_records_select('course', "category $catSql", $catParams, '', 'id');
        $courseIds = array_keys($courses);

        if (!empty($courseIds)) {
            // Find quizzes in the selected courses
            list($inSql, $params) = $DB->get_in_or_equal($courseIds);
            $quizzes = $DB->get_records_sql("SELECT * FROM {quiz} WHERE course $inSql ORDER BY name ASC", $params);
            
            foreach ($quizzes as $quiz) {
                $options[$quiz->id] = format_string($quiz->name);
            }
        }
    }

    return $options;
}



function generate_secure_token($quizid, $userid) {
    global $DB;
    
    // First, delete any existing token records for this user
    $DB->delete_records('local_onetimequizuser_tokens', array('userid' => $userid));
    
    // Generate a secure random token
    $token = bin2hex(random_bytes(32)); 
    // Token expires in 1 hour
    $expires = time() + 3600; 
    
    // Prepare the new token record
    $record = new stdClass();
    $record->token = $token;
    $record->quizid = $quizid;
    $record->userid = $userid;
    $record->expires = $expires;
    
    // Insert the new token record into the database
    $DB->insert_record('local_onetimequizuser_tokens', $record);
    
    // Return the new token
    return $token;
}

function generate_pc_session_identifier() {
    // Generate a unique identifier for this session/PC.
    // This example simply uses a high-entropy random string, which should suffice for uniqueness.
    $identifier = strtotime("now") . bin2hex(random_bytes(16)); // 32 characters
    
    // Optionally, you could append or prepend other identifying information,
    // like a timestamp, if you want to include more context in the identifier.
    // However, for most cases, a strong random string alone should be adequate.

    return $identifier;
}

// This might be part of the process handling the invigilator's selection
function assign_redirect_url_to_session($pcid, $quizUrl) {
    global $DB;
    
    if ($record = $DB->get_record('local_onetimequizuser_qr_scans', ['pcid' => $pcid])) {
        $record->redirect_url = $quizUrl;
        $DB->update_record('local_onetimequizuser_qr_scans', $record);
    }
}

function delete_q($pcSessionId) {
    // Assuming $fileinfo is defined as in your example, including $pcSessionId
    $fileinfo = array(
        'contextid' => context_system::instance()->id,
        'component' => 'local_onetimequizuser',
        'filearea' => 'pc_qr_codes',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => 'pc_qr_' . $pcSessionId . '.png'
    );

    // Get an instance of the file storage
    $fs = get_file_storage();

    // Use the get_file method to retrieve the file, if it exists
    $file = $fs->get_file(
        $fileinfo['contextid'], 
        $fileinfo['component'], 
        $fileinfo['filearea'], 
        $fileinfo['itemid'], 
        $fileinfo['filepath'], 
        $fileinfo['filename']
    );

    // If the file exists, delete it
    if ($file) {
        $file->delete();
    }
}

