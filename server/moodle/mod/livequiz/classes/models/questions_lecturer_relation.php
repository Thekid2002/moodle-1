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

/**
 * Class for relation between question and lecturer
 * @package   mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questions_lecturer_relation extends abstract_db_model {
    /**
     * @var int|null $id
     */
    private int | null $id;

    /**
     * @var int $question_id
     */
    private int $question_id;

    /**
     * @var int $lecturer_id
     */
    private int $lecturer_id;

    /**
     * livequiz_questions_lecturer_relation constructor.
     * @param int|null $id
     * @param int $question_id
     * @param int $lecturer_id
     */
    public function __construct(int | null $id, int $question_id, int $lecturer_id)
    {
        $this->id = $id;
        $this->question_id = $question_id;
        $this->lecturer_id = $lecturer_id;
    }

    /**
     *
     * Append a relation between af lecturer_id and the question_id. For easy access
     *
     * @param int $questionid
     * @param int $lecturerid
     * @return void
     * @throws dml_exception
     * @throws dml_transaction_exception
     *
     */
    public static function append_lecturer_questions_relation(int $questionid, int $lecturerid): void {
        global $DB;
        $DB->insert_record('livequiz_questions_lecturer', ['lecturer_id' => $lecturerid, 'question_id' => $questionid]);
    }

    /**
     *
     * Gets lecturer relations to questions by lecturer id. Will be used to get all the question that relates to that teacher
     *
     *
     * @param int $lecturerid
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     *
     */
    public static function get_lecturer_questions_relation_by_lecturer_id(int $lecturerid): array {
        global $DB;
        return (array) $DB->get_record('livequiz_questions_lecturer', ['lecturer_id' => $lecturerid]);
    }

    /**
     *
     * Gets lecturer relation by question id. Will be used to get all the teachers that have made that question.
     *
     *
     * @param int $questionid
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     *
     */
    public static function get_lecturer_questions_relation_by_questions_id(int $questionid): array {
        global $DB;
        return (array) $DB->get_record('livequiz_questions_lecturer', ['question_id' => $questionid]);
    }

    public function clone(): abstract_db_model
    {
        return new questions_lecturer_relation($this->id, $this->question_id, $this->lecturer_id);
    }

    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'lecturer_id' => $this->lecturer_id,
        ];
    }
}
