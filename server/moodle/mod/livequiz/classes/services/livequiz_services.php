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

namespace mod_livequiz\services;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../models/livequiz.php');
require_once(__DIR__ . '/../models/question.php');
require_once(__DIR__ . '/../models/answer.php');
require_once(__DIR__ . '/../models/questions_answers_relation.php');
require_once(__DIR__ . '/../models/quiz_questions_relation.php');

require_once(__DIR__ . '/../models/livequiz_questions_lecturer_relation.php');
require_once(__DIR__ . '/../models/livequiz_quiz_lecturer_relation.php');

require_once(__DIR__ . '/../models/student_quiz_relation.php');

use dml_exception;
use dml_transaction_exception;

use mod_livequiz\models\answer;
use mod_livequiz\models\livequiz;
use mod_livequiz\models\question;
use mod_livequiz\models\questions_answers_relation;
use mod_livequiz\models\quiz_questions_relation;

use mod_livequiz\models\livequiz_quiz_lecturer_relation;
use mod_livequiz\models\livequiz_questions_lecturer_relation;

use mod_livequiz\models\participation;
use mod_livequiz\models\student_quiz_relation;

use mod_livequiz\models\student_answers_relation;
use PhpXmlRpc\Exception;

/**
 * Class livequiz_services
 *
 * This class represents the service layer for handling the models.
 *
 * @package mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class livequiz_services {
    /**
     * @var livequiz_services|null $instance
     */
    private static ?livequiz_services $instance = null;

    /**
     * livequiz_services constructor.
     */
    private function __construct() {
    }

    /**
     * Create a singleton instance of the livequiz_services class.
     *
     * @return livequiz_services
     */
    public static function get_singleton_service_instance(): livequiz_services {
        if (self::$instance == null) {
            self::$instance = new livequiz_services();
        }
        return self::$instance;
    }

    /**
     * Gets, and constructs, a livequiz instance from the database.
     *
     * @throws dml_exception
     */
    public function get_livequiz_instance(int $id): livequiz {
        $livequiz = livequiz::get_livequiz_instance($id);

        $questions = $this->get_questions_with_answers($id);

        $livequiz->add_questions($questions);

        return $livequiz;
    }

    /**
     *  This method stores quiz data in the database.
     *  Before calling this method, none of the quiz data is safe.
     *  Please make sure that the quiz object is properly populated before using.
     *  TODO:
     *  Handle lecturer id such that the intermediate table can be updated accordingly.
     *
     * @throws dml_exception|Exception
     */
    public function submit_quiz(livequiz $livequiz, int $lecturerid): livequiz {
        $questions = $livequiz->get_questions();

        foreach ($questions as $question) {
            $answers = $question->get_answers();
            if (!count($answers)) {
                throw new Exception("A Livequiz Question must have at least 1 Answer");
            }
        }

        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            $livequiz->update_quiz();

            $quizid = $livequiz->get_id();
            if (!livequiz_quiz_lecturer_relation::check_lecturer_quiz_relation_exists($quizid, $lecturerid)) {
                livequiz_quiz_lecturer_relation::append_lecturer_quiz_relation($quizid, $lecturerid);
            }
            $this->submit_questions($livequiz, $lecturerid);


            $transaction->allow_commit();
        } catch (dml_exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
        return $this->get_livequiz_instance($quizid);
    }

    /**
     * Submits questions to the database.
     *
     * @throws dml_transaction_exception
     * @throws dml_exception
     * @throws Exception
     */
    private function submit_questions(livequiz $livequiz, int $lecturerid): void {

        $existingquestions = $this->get_questions_with_answers($livequiz->get_id());
        $newquestions = $livequiz->get_questions();

        $quizid = $livequiz->get_id();
        $updatedquestionids = [];

        // Create a map of existing questions for quick lookup by ID.
        $existingquestionmap = [];
        foreach ($existingquestions as $existingquestion) {
            $existingquestionmap[$existingquestion->get_id()] = $existingquestion;
        }
        /* @var question $newquestion // Type specification for $newquestion, for PHPStorm IDE */
        foreach ($newquestions as $newquestion) {
            $questionid = $newquestion->get_id();

            if ($questionid == 0) {
                // Insert new question if ID is 0 (new question).
                $questionid = $this->insert_question_with_relations($newquestion, $lecturerid, $quizid);
                $updatedquestionids[] = $questionid;
            } else if (isset($existingquestionmap[$questionid])) {
                // Update existing question if found in the map.
                $newquestion->update_question();
                $updatedquestionids[] = $questionid;
            }

            $answers = $newquestion->get_answers();
            $this->submit_answers($questionid, $answers);
        }

        // Find deleted questions by comparing existing question IDs with updated ones.
        $existingquestionids = array_keys($existingquestionmap);
        $deletedquestionids = array_diff($existingquestionids, $updatedquestionids);

        /* @var question $question // Type specification for $question, for PHPStorm IDE */
        foreach ($deletedquestionids as $questionid) {
            self::delete_question($questionid);
        }
    }

    /**
     * inserts a question into the database
     *
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function insert_question_with_relations(question $question, int $lecturerid, int $quizid): int {
        $questionid = question::insert_question($question);
        quiz_questions_relation::insert_quiz_question_relation($questionid, $quizid);
        livequiz_questions_lecturer_relation::append_lecturer_questions_relation($questionid, $lecturerid);
        return $questionid;
    }

    /**
     * Submits answers to the database.
     *
     * @throws dml_transaction_exception
     * @throws dml_exception
     * @throws Exception
     */
    private function submit_answers(int $questionid, array $answers): void {
        $existinganswers = questions_answers_relation::get_answers_from_question($questionid);
        $newanswers = $answers;

        $updatedanswerids = [];

        $existinganswersmap = [];
        foreach ($existinganswers as $existinganswer) {
            $existinganswersmap[$existinganswer->get_id()] = $existinganswer;
        }

        /* @var answer $newanswer // Type specification for $newanswer, for PHPStorm IDE */
        foreach ($newanswers as $newanswer) {
            $answerid = $newanswer->get_id();
            if ($answerid == 0) {
                $answerid = answer::insert_answer($newanswer);
                questions_answers_relation::insert_question_answer_relation($questionid, $answerid);
                $updatedanswerids[] = $answerid;
            } else if (isset($existinganswersmap[$answerid])) {
                $newanswer->update_answer();
                $updatedanswerids[] = $answerid;
            }
        }

        $existinganswerids = array_keys($existinganswersmap);
        $deletedanswersids = array_diff($existinganswerids, $updatedanswerids);

        foreach ($deletedanswersids as $deletedanswerid) {
            self::delete_answer($deletedanswerid);
        }
    }

    /**
     * Gets questions with answers from the database.
     *
     * @throws dml_exception
     */
    public function get_questions_with_answers(int $quizid): array {
        $questions = quiz_questions_relation::get_questions_from_quiz_id($quizid);

        foreach ($questions as $question) {
            $answers = questions_answers_relation::get_answers_from_question($question->get_id());
            $question->add_answers($answers);
        }
        return $questions;
    }

    /**
     * Creates a new participation record (quiz-student record) in the database.
     * @param int $studentid
     * @param int $quizid
     * @return participation
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function insert_participation(int $studentid, int $quizid): participation {
        // Add participation using the model.
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $participation = new participation($studentid, $quizid);
        try {
            $participation->set_id(student_quiz_relation::insert_student_quiz_relation($quizid, $studentid));
            $transaction->allow_commit();
        } catch (dml_exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
        return $participation;
    }

    /**
     * Insert students_answers record in the database.
     * @param int $studentid
     * @param int $answerid
     * @param int $participationid
     * @return void
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function insert_answer_choice(int $studentid, int $answerid, int $participationid): void {
        // Add participation using the model.
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            student_answers_relation::insert_student_answer_relation($studentid, $answerid, $participationid);
            $transaction->allow_commit();
        } catch (dml_exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }


    /**
     * Gets answers from a student in a specific participation.
     *
     * @param int $studentid The ID of the student.
     * @param int $participationid The ID of the participation.
     * @return answer[] The list of answers.
     * @throws dml_exception
     */
    public function get_answers_from_student_in_participation(int $studentid, int $participationid): array {
        $answers = [];
        $answerids = student_answers_relation::get_answersids_from_student_in_participation($studentid, $participationid);
        foreach ($answerids as $answerid) {
            $answers[] = answer::get_answer_from_id($answerid);
        }
        return $answers;
    }

    /**
     * Get all answerids based on a participation id and student id
     * @param int $studentid
     * @param int $participationid
     * @return array
     * @throws dml_exception
     */
    public function get_answersids_from_student_in_participation(int $studentid, int $participationid): array {
        return student_answers_relation::get_answersids_from_student_in_participation($studentid, $participationid);
    }

    /**
     * Get the newest participation in table. The participation with highest id.
     * @param int $quizid
     * @param int $studentid
     * @return participation
     * @throws dml_exception
     */
    public function get_newest_participation_for_quiz(int $quizid, int $studentid): participation {
        $participations = student_quiz_relation::get_all_student_participation_for_quiz($quizid, $studentid);
        return $participations[0];
    }

    /**
     * gets lecturer from quiz
     * @param int $quizid
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function get_livequiz_quiz_lecturer(int $quizid): array {
        $lecturer = livequiz_quiz_lecturer_relation::get_lecturer_quiz_relation_by_quiz_id($quizid);
        return $lecturer;
    }

    /**
     * gets lecturer from question
     * @param int $questionid
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function get_livequiz_question_lecturer(int $questionid): array {
        $lecturer = livequiz_questions_lecturer_relation::get_lecturer_questions_relation_by_questions_id($questionid);
        return $lecturer;
    }

    /**
     * Deletes an answer from the database.
     *
     * @param int $answerid
     * @throws dml_exception
     */
    private static function delete_answer(int $answerid): void {
        $participationcount = student_answers_relation::get_answer_participation_count($answerid);
        if ($participationcount > 0) {
            throw new dml_exception("Cannot delete answer with participations");
        }
        questions_answers_relation::delete_relations_by_answerid($answerid);
        answer::delete_answer($answerid);
    }

    /** Deletes a question, it's answers and any relations to other entities.
     *
     * @throws dml_exception
     * @throws Exception
     */
    private static function delete_question(int $questionid): void {
        $answers = questions_answers_relation::get_answers_from_question($questionid);
        foreach ($answers as $answer) {
            $currentanswerid = $answer->get_id();
            self::delete_answer($currentanswerid);
        }
        quiz_questions_relation::delete_question_quiz_relation($questionid);
        livequiz_questions_lecturer_relation::delete_lecturer_questions_relation_by_question_id($questionid);
        question::delete_question($questionid);
    }

    /**
     * A method for fetching the relevant data for the quiz initiation.
     *
     * @param int $quizid
     * @return array
     * @throws dml_exception
     */
    public static function initiate_livequiz(int $quizid): array {
        $livequiz = livequiz::get_livequiz_instance($quizid);

        return [$livequiz->get_name(), $livequiz->get_intro()];
    }

    /**
     * Gets a question by index. Returns the question and the index.
     *
     * @param int $quizid
     * @param int $index
     * @return array
     * @throws dml_exception
     */
    public function get_question_by_index(int $quizid, int $index): array {
        $livequiz = $this->get_livequiz_instance($quizid);
        $question = $livequiz->get_question_by_index($index);

        return [$question, $index];
    }

    /**
     * Gets answers for a question, without correct status.
     *
     * @param int $questionid
     * @return array
     * @throws dml_exception
     */
    public static function get_sanitized_answers(int $questionid): array {
        $answers = questions_answers_relation::get_answers_from_question($questionid);
        $sanitizedanswers = [];
        foreach ($answers as $answer) {
            $sanitizedanswers[] = $answer->get_sanitized_answer();
        }
        return $sanitizedanswers;
    }


    /**
     * Gets the correct answer for a question.
     *
     * @param int $studentid
     * @param int $answerid
     * @param int $participationid
     * @return void
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public static function student_answer_submission(int $studentid, int $answerid, int $participationid): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            student_answers_relation::insert_student_answer_relation($studentid, $answerid, $participationid);
            $transaction->allow_commit();
        } catch (dml_exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Gets the answers a student has selected for a specific question.
     *
     * Be aware: If the student has multiple participations in the same quiz,
     * with answers to the specific question, this will all be added to the same array.
     *
     * @throws dml_exception
     */
    public function get_student_answers_for_question(int $questionid, int $quizid): array {
        $participations = student_quiz_relation::get_participations_from_quizid($quizid);
        $livequiz = $this->get_livequiz_instance($quizid);
        $livequizquestions = $livequiz->get_questions();

        /* @var question $question Type specification for the PHPstorm IDE */
        $question = null;

        foreach ($livequizquestions as $livequizquestion) {
            if ($livequizquestion->get_id() == $questionid) {
                $question = $livequizquestion;
            }
        }

        if ($question == null) {
            throw new dml_exception("Question not found in quiz");
        }

        $submissions = [];

        foreach ($participations as $participation) {
            $participationid = $participation->get_id();
            $allanswers = student_answers_relation::get_answers_from_participation($participationid);

            $correct = 0;
            $incorrect = 0;

            foreach ($allanswers as $answer) {
                if ($question->is_answer_in_question($answer->get_id())) {
                    if ($answer->get_correct()) {
                        $correct++;
                    } else {
                        $incorrect++;
                    }

                    $submissions[] = [
                        'studentid' => $participation->get_studentid(),
                        'correct' => $correct,
                        'incorrect' => $incorrect,
                    ];
                }
            }
        }

        return $submissions;
    }
}
