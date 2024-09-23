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

$token = required_param('token', PARAM_ALPHANUM);

// Page setup
$PAGE->set_url(new moodle_url('/local/onetimequizuser/scan.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('scantitle', 'local_onetimequizuser'));
$PAGE->set_heading(get_string('scanheading', 'local_onetimequizuser'));

// Retrieve the quizid associated with this user
$record = $DB->get_record('local_onetimequizuser_tokens', array('userid' => $USER->id, 'token' => $token));

if ($record && !empty($record->quizid)) {
    $quizid = $record->quizid;
} else {
    redirect(new moodle_url('/local/onetimequizuser/setup.php'));
}

// Output the page header
echo $OUTPUT->header();
echo get_string('scaninstructions', 'local_onetimequizuser');
?>

<div id="scanSuccessIcon" style="display: none; color: green;">
    <i class="fa fa-check-circle"></i> Scan was successful!
</div>
<div id="qr-reader" style="width:100%;"></div>
<div id="qr-reader-results"></div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<audio id="success" src="/local/onetimequizuser/assets/success.mp3" type="audio/mp3"></audio>
<script type="text/javascript">
    function onScanSuccess(decodedText, decodedResult) {
        // Play feedback sound
        document.getElementById('success').play();
        console.log('Code scanned...');

        const url = new URL(decodedText);
        const queryParams = new URLSearchParams(url.search);
        const pcid = queryParams.get('pcid'); // Extract pcid from the URL
        const quizid = <?php echo json_encode($quizid); ?>;

        // Send the decodedText to the server
        fetch('/local/onetimequizuser/process_scan.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                    pcid: pcid, // Assuming you have this value from elsewhere
                    quizid: quizid, // The quizid retrieved in scan.php and made available to your JavaScript
                }),
            credentials: 'same-origin' // Ensures cookies for session authentication are included
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(data => {
            // Handle server response here
            if(data.success) {
                // Update UI or provide further feedback as needed
                console.log("Scan processed successfully");
                // Show the visual feedback element
                var successMessage = document.getElementById('scanSuccessIcon');
                successMessage.style.display = 'block';

                // Hide the message again after 5 seconds (5000 milliseconds)
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000);
            } else {
                // Handle errors or invalid scans
                console.error("Scan processing failed:", data.message ? data.message : "No additional error info.");
            }
        })
        .catch(error => console.error('Error sending scan data:', error));
    }
    
    var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 }, false);
    html5QrcodeScanner.render(onScanSuccess);
</script>

<?php
// Output the page footer
echo $OUTPUT->footer();
