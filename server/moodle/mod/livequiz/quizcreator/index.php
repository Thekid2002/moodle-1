<?php
require_once('../../../config.php');
require_once('../form/createquizform.php');
require_once('../hub/NavBar.php');
require_login();

$PAGE->set_url(new moodle_url('/mod/livequiz/quizcreator'));
$PAGE->requires->css(new moodle_url('/mod/livequiz/quizcreator/CreatorStyles.css'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Make a quiz");
$PAGE->set_heading("Create a quiz");

echo $OUTPUT->header();

if (class_exists('createNavbar')) {
    $Navbar = new createNavbar(); // Create an instance of the Navbar class
    $Navbar->display($activeTab); // Call the display method with the active tab
} else {
    // Handle the error if the class does not exist
    echo "Navbar class does not exist.";
}

// Opret formular
$mform = new createquizform();

// Vis formularen
$mform->display();

echo '<div class="quiz_modal_buttons">';

echo '<div class="image_upload_container">';
echo '<div id="imagePreviewContainer"><img id="imagePreview" src="#" alt="Image Preview" /></div>';
echo '<label for="imageUpload" class="custom-file-upload">Add Image</label>';
echo '<input type="file" id="imageUpload" name="quizImage" accept="image/png" />';
echo '</div>';

// Save button (unchanged)
echo '<button id="saveQuiz" class="save_button">Save</button>';

// Cancel button with new class 'cancel_button'
echo '<button id="cancelQuiz" class="cancel_button">Cancel</button>';

echo '</div>';

$PAGE->requires->js(new moodle_url('../amd/src/quizcreator.js'));
echo $OUTPUT->footer();
