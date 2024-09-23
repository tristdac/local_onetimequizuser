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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/onetimequizuser/classes/setup_form.php');
require_once($CFG->dirroot.'/local/onetimequizuser/locallib.php');
require_once($CFG->dirroot.'/local/onetimequizuser/lib/php-qrcode-master/lib/full/qrlib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');
require_once($CFG->dirroot . '/lib/filelib.php');
global $USER, $SESSION;
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/onetimequizuser/setup.php'));
$PAGE->set_title(get_string('setupquiz', 'local_onetimequizuser'));
$PAGE->set_heading(get_string('setupquiz', 'local_onetimequizuser'));

$form = new setup_form();

if ($form->is_cancelled()) {
    // Form was cancelled, redirecting to Moodle or another page
    redirect(new moodle_url('/?redirect=0'));
} else if ($form->is_submitted() && $data = $form->get_data()) {

    $token = generate_secure_token($data->quizid, $USER->id);
    $url = new moodle_url('/local/onetimequizuser/authenticate.php', array('token' => $token));

    $tempFilePath = $CFG->tempdir . '/qr_code_' . uniqid() . '.png';

    // Generate the QR code and save it to the temporary file
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
        'filename' => 'qr_' . $USER->id . '.png'
    );

    // Delete any existing file for this session to avoid duplicates
    if ($oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {
        $oldfile->delete();
    }

    // Create the file from the string
    $qrFile = $fs->create_file_from_string($fileinfo, $qrImageContent);

    // Redirect to the quiz summary page
    $params = array('quizid' => $data->quizid, 'groupname' => $data->groupname); // Assuming $data->quizid exists
    redirect(new moodle_url('/local/onetimequizuser/quiz_summary.php', $params));

} else {
    // Form hasn't been submitted yet or there was an error, display it
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}