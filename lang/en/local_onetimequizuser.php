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
 * Plugin strings are defined here.
 *
 * @package     local_onetimequizuser
 * @category    string
 * @copyright   2024 Tristan daCosta <tristan.dacosta@edinburghcollege.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'One Time Quiz User';
$string['createaccount'] = 'Create a Temporary Account';
$string['submit'] = 'Submit';
$string['guestname'] = 'Your Name';
$string['guestnametoolong'] = 'The name is too long after processing. Please use a shorter name.';
$string['setupquiz'] = 'Setup Quiz';
$string['selectquiz'] = 'Select a Quiz';
$string['groupname'] = 'Group or Cohort Name';
$string['quizsummary'] = 'Quiz Schedule Summary';
$string['selectedquiz'] = 'Scheduled Quiz: <strong>{$a}</strong>';
$string['notimelimit'] = 'No time limit has been set for this quiz. Your invigilator will inform you of time limits prior to the examination commencing.';
// $string['invigilatorinstructions'] = 'As an invigilator, you play a crucial role in facilitating the quiz session. Please follow these steps to ensure a smooth experience for all participants:<br><br>
// <ol>
//     <li><strong>Configure the Quiz</strong>: Begin by selecting the desired quiz and configuring any necessary options on the setup page. Once configured, submit the form to generate a unique QR code for the session.</li>
//     <li><strong>Display the QR Code</strong>: A QR code will be generated and displayed on the summary page along with the quiz details. This QR code is essential for linking the student devices to the specific quiz session.</li>
//     <li><strong>Prepare Student Devices</strong>: Ensure each student\'s device is ready at the login page, which should automatically display a QR code.</li>
//     <li><strong>Scan the QR Code with Your Device</strong>: Use your device to scan the QR code displayed on your screen. This action authenticates your device and prepares it for the next step.</li>
//     <li><strong>Scan Student Device QR Codes</strong>: With your device now authenticated, proceed to scan the QR code on each student\'s device. This action will configure their device to redirect to the specific quiz once they log in or enter their details.</li>
//     <li><strong>Verify Configuration</strong>: After scanning, verify that each student\'s device is correctly configured by asking them to complete the login or identification process. Their device should automatically redirect them to the appropriate quiz.</li>
//     <li><strong>Monitor the Quiz Session</strong>: During the quiz, monitor the session closely for any issues and assist students as needed.</li>
//     <li><strong>Conclude the Session</strong>: Once the quiz is complete, ensure all devices are logged out and any session-specific configurations are cleared.</li>
// </ol>
// Should you encounter any difficulties, please do not hesitate to contact the technical support team for assistance. Thank you for ensuring the integrity and smooth operation of the quiz session.';
$string['invigilatorinstructions'] = '<h3>Invigilator Instructions: </h3><ol>
    <li id="step1">Configure Quiz <i class="fa fa-check" style="color:green; display:none;" id="tick1"></i></li>
    <li id="step2">Scan QR code on this page <i class="fa fa-check" style="color:green; display:none;" id="tick2"></i></li>
    <li id="step3">Scan QR codes on candidate PCs: <span id="pcCount">0</span> PCs ready</li>
    <li id="step4">Start Quiz <i class="fa fa-check" style="color:green; display:none;" id="tick4"></i></li>
</ol>';
$string['unknownquiz'] = 'Unknown Quiz';
$string['qralttext'] = 'QR Code for Quiz Session';
$string['errornoqr'] = 'QR Code could not be generated.';
$string['viewqr'] = 'View QR Codes';
$string['onetimequizuser:viewqr'] = 'Permission to view QR codes';
$string['qrimage'] = 'QR Code';
$string['scantitle'] = 'QR Code Scanner';
$string['scanheading'] = 'Scan QR Code';
$string['scaninstructions'] = 'Please position the QR code within the frame below to scan. Make sure the code is well-lit and fully visible.';
$string['quiztimingdisclaimer'] = 'Quiz Timing Disclaimer';
$string['quiztimingdisclaimer_help'] = 'Please note: Specific timing accommodations apply. For full details, see our [[policy_link]]';
$string['moduleid'] = 'Quiz ID: <strong>{$a}</strong><p class="muted">This is the module ID for the quiz you have selected. If you are unable to scan QR codes on assessment center PCs or would prefer to enter this manually, use this number.</p>';
$string['quizcategory'] = 'Quiz category';
$string['quizcategory_desc'] = 'Select the category to scope quiz selection.';
$string['isinternaldesc'] = 'Quiz will be taken by currently enrolled students <strong>ONLY</strong>';
$string['startexam'] = 'Start Exam';
// $string['quiztimingdisclaimer_help'] = '<h3>Important Notice Regarding Quiz Timing</h3>
// <p>At [Your Assessment Center Name], we strive to create a fair and supportive examination environment for all candidates. We understand that individuals have unique needs, and we are committed to accommodating those to the best of our abilities.</p>
// <p><strong>Please be aware of the following regarding your quiz timing:</strong></p>
// <ul>
//     <li>The standard duration allocated for this quiz is designed to accommodate the majority of participants under typical conditions.</li>
//     <li>We recognise, however, that some candidates are entitled to extra time allowances as part of their accommodation needs. If this applies to you, please rest assured that your additional time has been factored into your quiz duration.</li>
//     <li>Your specific quiz timing, including any extra time allocations, will be displayed on your quiz interface once you commence. We encourage you to manage your time effectively to complete the quiz within the allocated period.</li>
// </ul>
// <p><strong>For Candidates with Approved Extra Time:</strong></p>
// <ul>
//     <li>Your extra time has been automatically applied. There is no need for further action on your part to activate this accommodation.</li>
//     <li>If you believe there has been an oversight, or if you encounter any issues related to your time allocation, please immediately notify an invigilator or contact [Contact Information/Help Desk] for assistance.</li>
// </ul>
// <p><strong>For All Candidates:</strong></p>
// <ul>
//     <li>We advise reviewing all instructions and questions carefully before starting and planning your responses considering the time available.</li>
//     <li>Should you have any questions or require assistance during the quiz, please do not hesitate to reach out to an invigilator or use the designated help channels.</li>
// </ul>
// <p>[Your Assessment Center Name] is dedicated to ensuring an equitable assessment process for all participants. We wish you the best of luck on your quiz and commend you for your hard work and preparation.</p>';