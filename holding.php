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

global $PAGE, $CFG, $OUTPUT, $DB;

$quizid = required_param('quizid', PARAM_INT);
$destination = required_param('destination', PARAM_URL); // URL to the quiz

$PAGE->set_url(new moodle_url('/local/onetimequizuser/holding.php', ['quizid' => $quizid]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('holdingpage', 'local_onetimequizuser'));
$PAGE->set_heading(get_string('holdingpageheading', 'local_onetimequizuser'));

// Output the page
echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('examinstructions', 'local_onetimequizuser'));
echo html_writer::tag('p', get_string('pleasewait', 'local_onetimequizuser'));

// Countdown display for when the exam starts
echo html_writer::tag('p', get_string('examstartcountdown', 'local_onetimequizuser'), ['id' => 'countdown', 'style' => 'display:none;']);

?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Poll the server every 5 seconds to check if the exam has started
        setInterval(function() {
            fetch('/local/onetimequizuser/check_exam_start.php?quizid=<?php echo $quizid; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.exam_started) {
                        startCountdown(10); // Start a 10-second countdown
                    }
                })
                .catch(error => console.error('Error checking exam status:', error));
        }, 5000); // Poll every 5 seconds

        function startCountdown(seconds) {
            const countdownElement = document.getElementById('countdown');
            countdownElement.style.display = 'block';
            const interval = setInterval(function() {
                countdownElement.textContent = 'Your exam will start in ' + seconds + ' seconds.';
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = '<?php echo $destination; ?>'; // Redirect to the quiz
                }
                seconds--;
            }, 1000);
        }
    });
</script>

<?php
echo $OUTPUT->footer();
