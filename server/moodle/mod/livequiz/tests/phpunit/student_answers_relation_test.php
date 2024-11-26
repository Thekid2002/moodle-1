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
    protected function create_test_data(): students_answers_relation {
        return new students_answers_relation(null, 1, 1, 1);
    }

    /**
     * Create answer test data.
     * @return answer[]
     */
    protected function create_answer_data(): array {
        $unitofwork = new unit_of_work();
        $answers = [];
        for ($i = 0; $i < 10; $i++) {
            $answer = new answer(null, 1, 'Answer Option' . $i, 'Answer Explenation' . $i);
            $answerid = $unitofwork->answers->insert($answer);
            $answers[] = $unitofwork->answers->select()
                ->where('id', '=', $answerid)
                ->first();
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
        $id = $unitofwork->student_answer_relations->insert($actual);

        /**
         * @var students_answers_relation $inserted
         */
        $inserted = $unitofwork->student_answer_relations
            ->select()
            ->where('id', '=', $id)
            ->first();

        $this->assertEquals($inserted->get_studentid(), $actual->get_studentid(), 'Student id does not match');
        $this->assertEquals($inserted->get_answerid(), $actual->get_answerid(), 'Answer id does not match');
        $this->assertEquals($inserted->get_participationid(), $actual->get_participationid(), 'Participation id does not match');
        $this->assertIsNumeric($actual);
        $this->assertGreaterThan(0, $actual);
    }
    /**
     * Test of get_answersids_from_student_in_participation
     * @covers \mod_livequiz\models\student_livequiz_relation::get_answersids_from_student_in_participation
     * @return void
     */
    public function test_get_answersids_from_student_in_participation(): void {
        $unitofwork = new unit_of_work();
        $studentanswerdata = $this->create_test_data();
        $answerdata = $this->create_answer_data();

        // Simulates multiple answers to a participation from a student.
        for ($i = 0; $i < 10; $i++) {
            $studentanswerrelation = new students_answers_relation(
                null,
                $studentanswerdata['studentid'],
                $answerdata[$i]->get_id(),
                $studentanswerdata['participationid']
            );
            $unitofwork->student_answer_relations->insert($studentanswerrelation);
        }

        // Get all answerids for a student in a participation

        $studentanswerdata = $unitofwork->student_answer_relations->select()
            ->where('student_id', '=', $studentanswerdata['studentid'])
            ->where('participation_id', '=', $studentanswerdata['participationid'])
            ->all();

        $answerids = $unitofwork->student_answer_relations->select("answer_id")
            ->where('student_id', '=', $studentanswerdata['studentid'])
            ->where('participation_id', '=', $studentanswerdata['participationid'])
            ->all();

        // For each answer ensure we are fetching the same id's we inserted.
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($answerids[$i], $answerdata[$i]->get_id());
        }
    }
}
