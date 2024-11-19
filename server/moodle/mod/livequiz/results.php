<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This displays when viewing results from attempting a quiz.
 * @package   mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/accesslib.php');
require_once('readdemodata.php');

use mod_livequiz\services\livequiz_services;

global $PAGE, $OUTPUT, $DB;
// Get submitted parameters.
$cmid = required_param('id', PARAM_INT); // Course module id.
$quizid = required_param('livequizid', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'livequiz');
$participationnumber = optional_param('participationnumber', 0, PARAM_INT);
$index = $participationnumber - 1; // Index is 0-based.

$instance = $DB->get_record('livequiz', ['id' => $cm->instance], '*', MUST_EXIST);

// Read demo data - REMOVE WHEN PUSHING TO STAGING.
$livequizservice = livequiz_services::get_singleton_service_instance();
$currentquiz = $livequizservice->get_livequiz_instance($instance->id);
if (empty($currentquiz->get_questions())) {
    $demodatareader = new \mod_livequiz\readdemodata();
    $demoquiz = $demodatareader->insertdemodata($currentquiz);
} else {
    $demoquiz = $currentquiz;
}

require_login($course, false, $cm);

$_SESSION['completed'] = true;

$context = context_module::instance($cm->id); // Set the context for the course module.

$PAGE->set_context($context); // Make sure to set the page context.

if ($index == -1) {
    $participation = $livequizservice->get_newest_participation_for_quiz($quizid, $USER->id);
}
else {
    if (isset($_SESSION['participations'][$index])) {
        // The following is the actual participation object, that we can use to display the results.
        $participation = $_SESSION['participations'][$index];
        error_log('Participation: ' . print_r($participation, true));
        error_log('Participation number: ' . $participationnumber);
        error_log('DB participation id: ' . $participation->get_id());
        
    }
    else {
        error_log("No participation was found in session");
    }
}

// Set the page URL, without exposing the participationid.
$PAGE->set_url(new moodle_url('/mod/livequiz/results.php', [
    'id'                    => $cmid,
    'quizid'                => $quizid,
    'participationnumber'   => $participationnumber]
));

$PAGE->set_title(get_string('modulename', 'mod_livequiz'));
$PAGE->set_heading(get_string('modulename', 'mod_livequiz'));

// Rendering.
$output = $PAGE->get_renderer('mod_livequiz');
$results = new \mod_livequiz\output\results_page($cmid, $demoquiz, $participation);

echo $OUTPUT->header();
echo $output->render($results);
echo $OUTPUT->footer();
