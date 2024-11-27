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

namespace mod_livequiz\repositories;

use coding_exception;
use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\students_answers_relation;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;
use mod_livequiz\unitofwork\unit_of_work;

class student_answer_repository extends abstract_crud_repository {

    /**
     * @var string $tablename The name of the table in the database.
     */
    public static string $tablename = 'livequiz_students_answers';

    /**
     * Select a student answer
     * @throws \dml_exception
     */
    public function select(delimit_query_builder|select_query_builder $query_builder): abstract_db_model
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No student answer found');
        }
        return new students_answers_relation($result->id, $result->student_id, $result->question_id,
            $result->answer_id);
    }

    /**
     * Select all student answers
     * @param delimit_query_builder|select_query_builder $query_builder the query builder to use
     * @return array an array of student answers
     * @throws dml_exception if no student answers are found
     */
    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        $students_answers = [];
        foreach ($results as $result) {
            $student_answer = new students_answers_relation($result->id, $result->student_id, $result->question_id, $result->answer_id);
            $students_answers[] = $student_answer;
        }
        return $students_answers;
    }

    /**
     * Insert a student answer into the database
     * @param students_answers_relation $entity the student answer to insert
     * @return int the id of the inserted student answer
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record(self::$tablename, $entity->get_data());
    }

    public function update(abstract_db_model $data): void
    {
        // TODO: Implement update() method.
    }

    public function delete(delete_query_builder $delete_query_builder): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * Insert an array of student answers into the database
     * @param array<students_answers_relation> $entities the list of student answers to insert
     * @throws coding_exception
     * @throws dml_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        for ($i = 0; $i < count($entities); $i++) {
            $entities[$i] = $entities[$i]->get_data();
        }
        $DB->insert_records(self::$tablename, $entities);
    }

    /**
     * Insert an array of student answers into the database and return the ids of the inserted entities
     * @param array<students_answers_relation> $entities An array to insert into the database
     * @return array<int> An array of the ids of the inserted entities
     * @throws dml_exception
     */
    public function insert_array_get_ids(array $entities): array
    {
        global $DB;
        $ids = [];
        for ($i = 0; $i < count($entities); $i++) {
            $ids[] = $DB->insert_record(self::$tablename, $entities[$i]->get_data());
        }
        return $ids;
    }
}