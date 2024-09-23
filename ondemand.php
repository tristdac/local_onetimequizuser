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

// define('NO_MOODLE_COOKIES', true); // This page does not require a Moodle session
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/local/onetimequizuser/locallib.php');
require_once($CFG->dirroot.'/local/onetimequizuser/lib/php-qrcode-master/lib/full/qrlib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');
require_once($CFG->dirroot . '/lib/filelib.php');

global $SESSION;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/onetimequizuser/ondemand.php');
$PAGE->set_title('OnDemand Access');
$PAGE->set_heading('OnDemand Access');

if (isset($_COOKIE['pcSessionId'])) {
    $pcSessionId = $_COOKIE['pcSessionId'];
} else {
    $pcSessionId = generate_pc_session_identifier(); // Generate a unique identifier for the session
    
    // Set a cookie that expires in 1 hour
    $cookieName = 'pcSessionId';
    $expiryTime = time() + 3600; // 1 hour from now
    setcookie($cookieName, $pcSessionId, $expiryTime);

    // Optionally, set another cookie to explicitly track the expiry time, useful for the JavaScript countdown
    $expiryCookieName = 'pcSessionExpiry';
    setcookie($expiryCookieName, $expiryTime, $expiryTime);
}

echo $OUTPUT->header();

$url = new moodle_url('/local/onetimequizuser/pc_authenticate.php', array('pcid' => $pcSessionId));

// Generate the QR code and save it to a temporary file
$tempFilePath = $CFG->tempdir . '/pc_qr_code_' . uniqid() . '.png';
QRcode::png($url->out(false), $tempFilePath, 'L', 4, 2);

// Read the image file into a string
$qrImageContent = file_get_contents($tempFilePath);

// Delete the temporary file
unlink($tempFilePath);

$fs = get_file_storage();

// Prepare the file record object
$fileinfo = array(
    'contextid' => context_system::instance()->id,
    'component' => 'local_onetimequizuser',
    'filearea' => 'qr_codes',
    'itemid' => 0,
    'filepath' => '/',
    'filename' => 'pc_qr_' . $pcSessionId . '.png'
);

// Delete any existing file for this session to avoid duplicates
if ($oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {
    $oldfile->delete();
}

// Create the QR code file in Moodle's file system
$qrFile = $fs->create_file_from_string($fileinfo, $qrImageContent);

// Construct the URL to the stored QR code file
$qrUrl = moodle_url::make_pluginfile_url(
    $fileinfo['contextid'], 
    $fileinfo['component'], 
    $fileinfo['filearea'], 
    $fileinfo['itemid'], 
    $fileinfo['filepath'], 
    $fileinfo['filename']
);

// Prepare and insert a new record for tracking the QR code scan status
$record = new stdClass();
$record->pcid = $pcSessionId; // Unique session identifier
$record->scanned = 0; // Not yet scanned
$record->invigilatorid = null; // No invigilator yet
$record->redirect_url = null; // No redirect URL yet

// Insert the record into the database if it doesn't already exist
if (!$DB->record_exists('local_onetimequizuser_qr_scans', ['pcid' => $pcSessionId])) {
    $DB->insert_record('local_onetimequizuser_qr_scans', $record);
}

if (!empty($SESSION->redirect_message)) {
    // Use Moodle's built-in notification function to display the message
    echo $OUTPUT->notification($SESSION->redirect_message, \core\output\notification::NOTIFY_ERROR);

    // Clear the message to prevent it from displaying on subsequent page loads
    unset($SESSION->redirect_message);
}

?>


<div class="row">
    <div class="text-container col-md-8">
        <p>Your assessment is nearly ready. If you see this page, it means your quiz session isn't set up just yet. But don't worry, getting started is easy:</p>
        <ol>
            <li><strong>Ask Your Invigilator:</strong> If you're in a supervised environment, please raise your hand to get the attention of your invigilator. They'll either scan the QR code displayed on this screen or enter the quiz ID manually to direct you to the right quiz.</li>
            <li><strong>Enter Quiz ID:</strong> If you've been provided with a Quiz ID directly, you can manually enter it below to begin your assessment.</li>
        </ol>
        <p>We're here to ensure a smooth and straightforward experience as you prepare to take your quiz. If you encounter any issues or have questions, your invigilator is here to assist. Let's get started!</p>

        <!-- Trigger/Open The Modal -->
        <a href="#" id="manualEntryTrigger">Manual Quiz ID Entry</a>
        <br>
        <!-- The Modal -->
        <div id="manualEntryModal" class="modal">

            <!-- Modal content -->
            <div class="modal-content">
                <span class="close">&times;</span>
                <form id="quizIdForm" action="/local/onetimequizuser/signup.php" method="get">
                    <input type="text" id="quizid" name="quizid" placeholder="Enter Quiz ID">
                    <input type="submit" id="submitBtn" value="Submit">
                </form>
            </div>


        </div>
    </div>
    <div class="qr-code-container col-md-4">
        <p>Invigilator only</p>
        <div class="qr-container">
            <?php 
            echo html_writer::empty_tag('img', ['src' => $qrUrl->out(false), 'alt' => 'QR Code']);
            ?>
        </div>
        <div id="sessionTimer">Loading session time...</div>
    </div>
</div>

<script>
    function getCookie(name) {
        let cookieArray = document.cookie.split(';');
        for(let i = 0; i < cookieArray.length; i++) {
            let cookiePair = cookieArray[i].split('=');
            if(name == cookiePair[0].trim()) {
                return decodeURIComponent(cookiePair[1]);
            }
        }
        return null;
    }

    let pcSessionId = getCookie('pcSessionId');

    setInterval(() => {
        if (pcSessionId) {
            // console.log(pcSessionId);
            fetch('/local/onetimequizuser/check_scan.php?pcid=' + encodeURIComponent(pcSessionId))
            .then(response => response.json())
            .then(data => {

                if(data.redirectUrl) {
                    window.location.href = '/local/onetimequizuser/signup.php?destination=' + encodeURIComponent(data.redirectUrl);
                }
                
            })
            .catch(error => {
                // Handle any errors
                console.error('Error:', error);
            });
        } else {
            console.error('PC Session ID cookie not found.');
        }
    }, 2000); // Check every 5 seconds
</script>

<script>
    // Get the modal
    var modal = document.getElementById("manualEntryModal");

    // Get the button that opens the modal
    var btn = document.getElementById("manualEntryTrigger");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // Get the input element where the quiz ID should be entered
    var quizIdInput = document.getElementById('quizid');

    // Get the form element
    var form = document.getElementById('quizIdForm');

    // When the user clicks the button, open the modal 
    btn.onclick = function() {
        modal.style.display = "block";
        quizIdInput.focus(); // Set focus to the input box immediately after showing the modal
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Handle the form submission
    form.onsubmit = function(event) {
        // Prevent the form from submitting the default way if necessary
        event.preventDefault();
        
        var quizId = quizIdInput.value;
        if (quizId) {
            // Construct the destination URL
            var destinationUrl = encodeURIComponent('/mod/quiz/view.php?id=' + quizId);
            
            // Redirect to the signup page with the destination as a parameter
            window.location.href = '/local/onetimequizuser/signup.php?destination=' + destinationUrl;
        } else {
            alert("Please enter a Quiz ID.");
        }
    };
</script>

<script>
function startSessionCountdown(duration, display) {
    var timer = duration, minutes, seconds;
    var countdown = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = 'New code in: ' + minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(countdown); // Stop the countdown
            window.location.reload(); // Reload the page
        }
    }, 1000);
}

window.onload = function () {
    var expiryTime = readCookie('pcSessionExpiry'); // Read the expiry time from the cookie
    if (expiryTime) {
        var currentTime = Math.floor(Date.now() / 1000); // Get the current time in seconds
        var duration = expiryTime - currentTime; // Calculate remaining duration in seconds
        if (duration > 0) {
            var display = document.getElementById('sessionTimer'); // Element to display the countdown
            startSessionCountdown(duration, display);
        } else {
            // If the duration is already expired or negative, reload immediately
            window.location.reload();
        }
    }
};

// Function to read a cookie's value
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
</script>


