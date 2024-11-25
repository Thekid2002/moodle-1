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
 * LiveQuiz student_answers_relation_test
 *
 * This class contains unit tests for functions in the student_answers_relation class.
 *
 * @package mod_livequiz
 * @copyright 2024 Software AAU
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_livequiz;
use advanced_testcase;
use mod_livequiz\models\students_answers_relation;
use mod_livequiz\models\answer;
use mod_livequiz\unitofwork\unit_of_work;

/**
 * student_answers_relation
 */
final class student_answers_relation_test extends advanced_testcase {
    /**
     * Create participation test data. Used in every test.
     * @return array
     */
    protected function create_test_data(): array {
        return  $studentanswertestdata = [
            'studentid' => 1,
            'participationid' => 1,
            'answerid' => 1,
        ];
    }

    /**
     * Create answer test data.
     * @return answer[]
     */
    protected function create_answer_data(): array {
        global $DB;
        $answers = [];
        for ($i = 0; $i < 10; $i++) {
            $answer = new answer(1, 'Answer Option' . $i, 'Answer Explenation' . $i);
            $answerid = answer::insert_answer($answer);
            $answers[] = answer::get_answer_from_id($answerid); // This ensures id's are set since set_id() is private.
        }
        return $answers;
    }
    /**
     * Setup before each test.
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Test of insert_student_answer_relation
     * @covers \mod_livequiz\models\students_answers_relation::insert_student_answer_relation
     * @return void
     * @throws \dml_exception
     */
    public function test_insert_student_answer_relation(): void {
        $data = $this->create_test_data();
        $unitofwork = new unit_of_work();
        $actual = new students_answers_relation(null, $data['studentid'], $data['answerid'], $data['participationid']);
        $unitofwork->student_answer_relations->insert($actual);
        $unitofwork->save_changes();

        /**
         * @var students_answers_relation $inserted
         */
        $inserted = $unitofwork->student_answer_relations->select()
            ->where('student_id', '=', $data['studentid'])
            ->where('answer_id', '=', $data['answerid'])
            ->where('participation_id', '=', $data['participationid'])
            ->complete();

        $this->assertEquals($inserted->student_id, $actual->student_id, 'Student id does not match');
        $this->assertEquals($inserted->answer_id, $actual->answer_id, 'Answer id does not match');
        $this->assertEquals($inserted->participation_id, $actual->participation_id, 'Participation id does not match');
        $this->assertIsNumeric($actual);
        $this->assertGreaterThan(0, $actual);
    }
    /**
     * Test of get_answersids_from_student_in_participation
     * @covers \mod_livequiz\models\student_quiz_relation::get_answersids_from_student_in_participation
     * @return void
     */
    public function test_get_answersids_from_student_in_participation(): void {
        $studentanswerdata = $this->create_test_data();
        $answerdata = $this->create_answer_data();

        // Simulates multiple answers to a participation from a student.
        for ($i = 0; $i < 10; $i++) {
            students_answers_relation::insert_student_answer_relation(
                $studentanswerdata['studentid'],
                $answerdata[$i]->get_id(),
                $studentanswerdata['participationid'],
            );
        }

        // Get all answerids for a student in a participation.
        $answerids = students_answers_relation::get_answersids_from_student_in_participation(
            $studentanswerdata['studentid'],
            $studentanswerdata['participationid']
        );
        // For each answer ensure we are fetching the same id's we inserted.
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($answerids[$i], $answerdata[$i]->get_id());
        }
    }
}
