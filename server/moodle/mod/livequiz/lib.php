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
defined('MOODLE_INTERNAL') || die();

/**
 * @param $quizdata
 * @return bool|int
 * @throws dml_exception
 */
function livequiz_add_instance($quizdata){
    global $DB;

    $quizdata->timecreated = time();
    $quizdata->timemodified = time();

    $quizdata->id = $DB->insert_record('livequiz', $quizdata);

    return $quizdata->id;
}

/**
 * @param $quizdata
 * @return bool
 * @throws dml_exception
 */
function livequiz_update_instance($quizdata){
    global $DB;

    $quizdata->timemodified = time();
    //$quizdata->id = $quizdata->instance;

    $DB->update_record('livequiz', $quizdata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function livequiz_delete_instance($id){
    global $DB;

    $DB->delete_records('livequiz', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function questions_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('questions', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function questions_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('questions', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function questions_delete_instance($id)
{
    global $DB;

    $DB->delete_records('questions', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function answers_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('answers', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function answers_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('answers', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function answers_delete_instance($id)
{
    global $DB;

    $DB->delete_records('answers', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function questions_answers_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('questions_answers', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function questions_answers_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('questions_answers', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function questions_answers_delete_instance($id)
{
    global $DB;

    $DB->delete_records('questions_answers', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function students_answers_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('students_answers', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function students_answers_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('students_answers', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function students_answers_delete_instance($id)
{
    global $DB;

    $DB->delete_records('students_answers', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function quiz_students_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('quiz_students', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function quiz_students_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('quiz_students', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function quiz_students_delete_instance($id)
{
    global $DB;

    $DB->delete_records('quiz_students', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function quiz_questions_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('quiz_questions', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function quiz_questions_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('quiz_questions', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function quiz_questions_delete_instance($id)
{
    global $DB;

    $DB->delete_records('quiz_questions', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function questions_lecturer_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('questions_lecturer', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function questions_lecturer_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('questions_lecturer', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function questions_lecturer_delete_instance($id)
{
    global $DB;

    $DB->delete_records('questions_lecturer', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function quiz_lecturer_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('quiz_lecturer', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function quiz_lecturer_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('quiz_lecturer', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function quiz_lecturer_delete_instance($id)
{
    global $DB;

    $DB->delete_records('quiz_lecturer', ['id' => $id]);

    return true;
}

/**
 * @param $questiondata
 * @return bool|int
 * @throws dml_exception
 */
function course_quiz_create_instance($questiondata){
    global $DB;

    $questiondata->id = $DB->insert_record('course_quiz', $questiondata);

    return $questiondata->id;
}

/**
 * @param $questiondata
 * @return bool
 * @throws dml_exception
 */
function course_quiz_update_instance($questiondata)
{
    global $DB;

    $DB->update_record('course_quiz', $questiondata);

    return true;
}

/**
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function course_quiz_delete_instance($id)
{
    global $DB;

    $DB->delete_records('course_quiz', ['id' => $id]);

    return true;
}