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

namespace mod_livequiz\models;

use dml_exception;
use dml_transaction_exception;
use stdClass;

/**
 * Class question
 *
 * This class represents a question in the LiveQuiz module.
 * It handles creation, retrieval, and updates of quiz questions and their associated answers.
 *
 * @package mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question extends abstract_db_model {
    /**
     * @var int | null $id The id of the question.
     */
    private int | null $id;

    /**
     * @var string $title The title of the question.
     */
    public string $title;

    /**
     * @var string $description The description or body of the question.
     */
    public string $description;

    /**
     * @var int $timelimit The time limit for answering the question (in seconds).
     */
    public int $timelimit;

    /**
     * @var string $explanation The explanation for the correct answer.
     */
    public string $explanation;

    /**
     * @var array $answers A list of possible answers for the question.
     */
    private array $answers = [];

    /**
     * Constructor for the question class.
     *
     * @param int|null $id
     * @param string $title
     * @param string $description
     * @param int $timelimit
     * @param string $explanation
     */
    public function __construct(int | null $id, string $title, string $description, int $timelimit, string $explanation) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->timelimit = $timelimit;
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Gets a question instance.
     *
     * @param $id
     * @return question
     * @throws dml_exception
     */
    public static function get_question_from_id($id): question {
        global $DB;
        $questioninstance = $DB->get_record('livequiz_questions', ['id' => $id]);
        $question = new question(
            null,
            $questioninstance->title,
            $questioninstance->description,
            $questioninstance->timelimit,
            $questioninstance->explanation
        );
        $question->set_id($questioninstance->id);
        return $question;
    }

    /**
     * Updates a question in the database.
     *
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @return void
     */
    public function update_question(): void {
        global $DB;
        $questiondata = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'timelimit' => $this->timelimit,
            'explanation' => $this->explanation,
        ];
        $DB->update_record('livequiz_questions', $questiondata);
    }

    /**
     * Deletes a question from the database.
     *
     * @param int $questionid
     * @return bool
     * @throws dml_exception
     */
    public static function delete_question(int $questionid): bool {
        global $DB;
        return $DB->delete_records('livequiz_questions', ['id' => $questionid]);
    }

    /**
     * Gets the ID of the question.
     *
     * @return int|0 // Returns the ID of the question, if it has one. 0 if it does not.
     */
    public function get_id(): int {
        return $this->id ?? 0;
    }

    /**
     * Gets the answers associated with the question.
     *
     * @return array The list of answers.
     */
    public function get_answers(): array {
        return $this->answers;
    }

    /**
     * Appends an answer to the question object.
     *
     * @param array $answers The title of the question.
     */
    public function add_answers(array $answers): void {
        foreach ($answers as $answer) {
            $this->add_answer($answer);
        }
    }

    /**
     * Appends an answer to the question object.
     *
     * @param answer $answer The title of the question.
     */
    public function add_answer(answer $answer): void {
        $this->answers[] = $answer;
    }

    /**
     * Getter for question hasmultiplecorrectanswers
     * @return bool
     */
    public function get_hasmultiplecorrectanswers(): bool {
        // This is a simple check to see if the question has multiple correct answers.
        $numcorrect = 0;

        foreach ($this->answers as $answer) {
            if ($answer->get_correct()) {
                $numcorrect++;
                if ($numcorrect > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Prepares the template data for mustache.
     * @param stdClass $data
     * @return stdClass
     */
    public function prepare_for_template(stdClass $data): stdClass {
        // Add to data object.
        $data->questionid = $this->id;
        $data->questiontitle = $this->title;
        $data->questiondescription = $this->description;
        $data->questiontimelimit = $this->timelimit;
        $data->questionexplanation = $this->explanation;
        $data->answers = [];
        foreach ($this->answers as $answer) {
            $data->answers[] = [
                'answerid' => $answer->get_id(),
                'answerdescription' => $answer->get_description(),
                'answerexplanation' => $answer->get_explanation(),
                'answercorrect' => $answer->get_correct(),
            ];
        }
        if ($this->get_hasmultiplecorrectanswers()) {
            $data->answertype = 'checkbox';
        } else {
            $data->answertype = 'radio';
        }
        return $data;
    }

    /**
     * Clones the question object.
     * @return question
     */
    public function clone(): question
    {
        return new question($this->id, $this->title, $this->description, $this->timelimit, $this->explanation);
    }

    /**
     * Get the data of the question object.
     * @return array
     */
    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'timelimit' => $this->timelimit,
            'explanation' => $this->explanation,
        ];
    }
}
