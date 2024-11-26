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
 * This class is responsible for handling the relationship between students and quizzes.
 * @package   mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_livequiz_relation extends abstract_db_model {

    /**
     * @var int|null $id
     */
    public int | null $id;

    /**
     * @var int $livequizid
     */
    public int $livequizid;

    /**
     * @var int $studentid
     */
    public int $studentid;

    /**
     * Constructor for the student_livequiz_relation class. Returns the object.
     *
     * @param int|null $id
     * @param int $livequizid
     * @param int $studentid
     */
    public function __construct(int | null $id, int $livequizid, int $studentid)
    {
        $this->id = $id;
        $this->livequizid = $livequizid;
        $this->studentid = $studentid;
    }


    /**
     * Append a student-quiz relation given both their ids.
     *
     * @param int $quizid
     * @param int $studentid
     * @return int
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public static function insert_student_quiz_relation(int $quizid, int $studentid): int {
        global $DB;
        return $DB->insert_record('livequiz_quiz_student', ['livequiz_id' => $quizid, 'student_id' => $studentid], true);
    }

    /**
     * Get all participation the student has made for a quiz
     * @param int $quizid
     * @param int $studentid
     * @return array
     */
    public static function get_all_student_participation_for_quiz(int $quizid, int $studentid): array {
        global $DB;
        $participationrecords =
            $DB->get_records(
                'livequiz_quiz_student',
                ['livequiz_id' => $quizid, 'student_id' => $studentid],
                'id DESC',
                '*'
            );
        $participations = [];
        foreach ($participationrecords as $participation) {
            $participations[] = new participation($participation->student_id, $participation->livequiz_id);
        }
        return $participations;
    }

    public function clone(): abstract_db_model
    {
        return new student_livequiz_relation($this->id, $this->livequizid, $this->studentid);
    }

    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'livequizid' => $this->livequizid,
            'studentid' => $this->studentid
        ];
    }
}
