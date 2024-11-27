<?php

namespace mod_livequiz\repositories;

use coding_exception;
use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\answer;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;
use mod_livequiz\query\delete_query_builder;

class answer_repository extends abstract_crud_repository {

    /**
     * @var string $tablename The name of the table in the database.
     */
    public static string $tablename = 'livequiz_answers';

    /**
     * Select an answer from the database
     * @param delimit_query_builder|select_query_builder $query_builder the query to select the answer
     * @return answer the answer selected
     * @throws dml_exception
     */
    public function select(delimit_query_builder|select_query_builder $query_builder): answer
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No answer found');
        }
        return new answer($result->id, $result->correct, $result->description, $result->explanation);
    }

    /**
     * Select all answers from the database
     * @param delimit_query_builder|select_query_builder $query_builder the query to select the answers
     * @return array the answers selected
     * @throws dml_exception if no answers are found
     */
    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        $answers = [];
        foreach ($results as $result) {
            $answer = new answer($result->id, $result->correct, $result->description, $result->explanation);
            $answers[] = $answer;
        }
        return $answers;
    }

    /**
     * Insert an answer into the database
     *
     * @param answer $entity the answer to insert
     * @return int the id of the inserted answer
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record(self::$tablename, $entity->get_data());
    }

    /**
     * Insert an array of answers into the database
     * @param array<answer> $entities the list of answers to insert
     * @throws dml_exception
     * @throws coding_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        $DB->insert_records(self::$tablename, $entities);
    }

    /**
     * Insert an array of answers into the database
     * @param array<answer> $entities the list of answers to insert
     * @return array<int> the ids of the inserted answers
     * @throws dml_exception
     */
    public function insert_array_get_ids(array $entities): array
    {
        global $DB;
        $ids = [];
        foreach ($entities as $entity) {
            $ids[] = $DB->insert_record(self::$tablename, $entity->get_data());
        }
        return $ids;
    }

    public function update(abstract_db_model $entity): void
    {
        // TODO: Implement update() method.
    }

    public function delete(delete_query_builder $delete_query_builder): void
    {
        // TODO: Implement delete() method.
    }
}