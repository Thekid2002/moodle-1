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

use dml_exception;
use dml_transaction_exception;

use mod_livequiz\models\answer;
use mod_livequiz\models\livequiz;
use mod_livequiz\models\question;
use mod_livequiz\models\questions_answers_relation;
use mod_livequiz\models\quiz_questions_relation;
use PhpXmlRpc\Exception;
use function PHPUnit\Framework\throwException;

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
    public function submit_quiz(livequiz $livequiz): livequiz {
        $questions = $livequiz->get_questions();

        if (!count($questions)) {
            throw new Exception("A Livequiz Must have atleast 1 Question");
        }

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

            $this->submit_questions($livequiz);

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
     */
    private function submit_questions(livequiz $livequiz): void {
        $existingquestions = $this->get_questions_with_answers($livequiz->get_id());
        $newquestions = $livequiz->get_questions();

        $quizid = $livequiz->get_id();

        $updatedquestionids = [];

        // existing = [4,5,6]
        // new = [16]

        foreach ($newquestions as $newquestion){
            foreach ($existingquestions as $existingquestion) {
                if($newquestion->get_id() == null) {
                    $answers = $newquestion->get_answers();
                    $questionid = question::insert_question($newquestion);

                    quiz_questions_relation::insert_quiz_question_relation($questionid, $quizid);
                    $this::submit_answers($questionid, $answers);
                    $updatedquestionids[] = $questionid;

                } elseif($newquestion->get_id() == $existingquestion->get_id()) {
                    $newquestion->update_question();
                    $updatedquestionids[] = $newquestion->id;
                    //updatedquestionids = []
                }
            }
        }

        $deletedquestions = array_diff($existingquestions, $updatedquestionids);
        //deletedquestions= [4,5,6]
        if(count($deletedquestions) > 0){
            foreach ($deletedquestions as $deletedquestion) {
                //TODO delete
//                question::delete_question($deletedquestion);
            }
        }
    }

    /**
     * Submits answers to the database.
     *
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function submit_answers(int $questionid, array $answers): void {
        $newanswers = $answers;
        $existinganswers = questions_answers_relation::get_answers_from_question($questionid);

        $updatedanswerids = [];

        foreach ($newanswers as $newanswer) {
            foreach ($existinganswers as $existinganswer) {
                if($newanswer->get_id() == $existinganswer->get_id()) {
                    $newanswer->update_answer();
                    $updatedanswerids[] = $newanswer->get_id();
                }
            }

            if(!in_array($newanswer->get_id(), $updatedanswerids)) {
                $answerid = answer::insert_answer($newanswer);
                questions_answers_relation::insert_question_answer_relation($questionid, $answerid);
                $updatedanswerids[] = $answerid;
            }
        }

        $deletedanswers = array_diff($existinganswers, $updatedanswerids);

        if(count($deletedanswers) > 0){
            foreach ($deletedanswers as $deletedanswer) {
                //TODO delete
//                answer::delete_answer($deletedanswer);
            }
        }
    }

    /**
     * Gets questions with answers from the database.
     *
     * @throws dml_exception
     */
    private function get_questions_with_answers(int $quizid): array {
        $questions = quiz_questions_relation::get_questions_from_quiz_id($quizid);

        foreach ($questions as $question) {
            $answers = questions_answers_relation::get_answers_from_question($question->get_id());
            $question->add_answers($answers);
        }
        return $questions;
    }
}
