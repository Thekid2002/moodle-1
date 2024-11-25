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
 * Class representing the relationship between students and answers.
 * @package   mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_livequiz\models;

use dml_exception;
use Exception;

/**
 * Class student_answers_relation
 * @package mod_livequiz\student_answers_relation
 */
class students_answers_relation extends abstract_db_model {
    /**
     * @var int|null $id
     */
    private int | null $id;

    /**
     * @var int $studentid
     */
    private int $studentid;

    /**
     * @var int $answerid
     */
    private int $answerid;

    /**
     * @var int $participationid
     */
    private int $participationid;

    /**
     * @param int|null $id The id of the relation
     * @param int $studentid The id of the student
     * @param int $answerid The id of the answer
     * @param int $participationid The id of the participation
     */
    public function __construct(int | null $id, int $studentid, int $answerid, int $participationid) {
        $this->id = $id;
        $this->studentid = $studentid;
        $this->answerid = $answerid;
        $this->participationid = $participationid;
    }

    /**
     * Get all answers for a student in a given participation
     *
     * @param int $studentid
     * @param int $participationid
     * @return array An array of answer id's
     * @throws dml_exception
     */
    public static function get_answersids_from_student_in_participation(int $studentid, int $participationid): array {
        global $DB;

        $answerrecords = $DB->get_records(
            'livequiz_students_answers',
            ['student_id' => $studentid, 'participation_id' => $participationid],
            '',
            'answer_id'
        );
        $answerids = array_column($answerrecords, 'answer_id');
        return $answerids;
    }

    /**
     * Check if an answer has any participations.
     * Returns amount of participations.
     *
     * @param int $answerid
     * @return int
     * @throws dml_exception
     */
    public static function get_answer_participation_count(int $answerid): int {
        global $DB;

        return $DB->count_records(
            'livequiz_students_answers',
            ['answer_id' => $answerid]
        );
    }

    /**
     * Clone the object
     * @return students_answers_relation
     */
    public function clone(): abstract_db_model
    {
        return new students_answers_relation($this->id, $this->studentid, $this->answerid, $this->participationid);
    }

    public function get_studentid()
    {
        return $this->studentid;
    }

    public function get_answerid()
    {
        return $this->answerid;
    }

    public function get_participationid()
    {
        return $this->participationid;
    }

    /**
     * Get the data of the object
     * @return array
     */
    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentid,
            'answer_id' => $this->answerid,
            'participation_id' => $this->participationid
        ];
    }
}
