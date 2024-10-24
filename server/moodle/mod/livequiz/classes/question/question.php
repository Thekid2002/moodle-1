<?php

namespace mod_livequiz\question;

use dml_exception;
use dml_transaction_exception;
use mod_livequiz\answers\answers;
use mod_livequiz\questions_answers_relation\questions_answers_relation;
use mod_livequiz\quiz_questions_relation\quiz_questions_relation;
use stdClass;

class question
{
    private $id;
    private $correct;
    private $description;
    private $explanation;

    /**
     * Constructor for the question class. Inserts a new question into the database.
     * Appends the question to a quiz, given the quiz id.
     *
     * @param $correct
     * @param $description
     * @param $explanation
     * @param $answers
     * @param $quizid
     * @throws dml_transaction_exception
     */
    public function __construct($title, $description, $timelimit, $explanation, $answers, $quizid)
    {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();

            $questiondata = [
                'title' => $title,
                'description' => $description,
                'timelimit' => $timelimit,
                'explanation' => $explanation,
            ];

            $question_id = $DB->insert_record('livequiz_questions', $questiondata);

            if (isset($answers)) {
                $this->$answers = $answers;
                foreach ($answers as $answer) {
                    new answers($answer->correct, $answer->description, $answer->explanation, $question_id);
                }
            }

            quiz_questions_relation::append_question_to_quiz($this, $quizid);

            $transaction->allow_commit();
        } catch (dml_exception $e) {
            $transaction->rollback($e);
        }

        return $question_id;
    }

    /**
     * Gets a question instance, with all relevant attributes
     *
     * @param $id
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_question_from_id($id) {
        global $DB;
        $question = $DB->get_record('livequiz_questions', ['id'=>$id]);
        $answers = questions_answers_relation::get_answers_from_question($question->id);
        $question->answers = $answers;
        return $question;
    }

    /**
     * Update a question
     *
     * @param $questiondata
     * @return bool
     * @throws dml_exception
     */
    public static function update_question($questiondata) {
        global $DB;
        return $DB->update_record('livequiz_questions', $questiondata);
    }

    /**
     * TODO: Implement this method
     * Delete a question
     *
     * @param $questiondata
     * @throws dml_exception
     */
    public static function delete_question($questiondata) {
        global $DB;
    }
}