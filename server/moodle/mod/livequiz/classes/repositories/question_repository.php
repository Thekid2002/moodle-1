<?php

namespace mod_livequiz\repositories;

use coding_exception;
use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\question;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;

class question_repository extends abstract_crud_repository
{
    /**
     * @var string $tablename The name of the table in the database.
     */
    public static string $tablename = 'livequiz_questions';

    /**
     * Select a question from the database
     * @param delimit_query_builder|select_query_builder $query_builder the query to select the question
     * @return question the question selected
     * @throws dml_exception
     */
    public function select(delimit_query_builder|select_query_builder $query_builder): question
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No question found');
        }

        return new question($result->id, $result->title, $result->description, $result->timelimit,
            $result->explanation);
    }

    /**
     * Select all questions from the database
     * @param delimit_query_builder|select_query_builder $query_builder
     * @return array
     * @throws dml_exception
     */
    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        $questions = [];
        foreach ($results as $result) {
            $question = new question($result->id, $result->title, $result->description, $result->timelimit,
                $result->explanation);
            $questions[] = $question;
        }
        return $questions;
    }

    /**
     * Insert a question into the database
     * @param question $entity the question to insert
     * @return int the id of the inserted question
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record(self::$tablename, $entity->get_data());
    }

    public function update(abstract_db_model $entity): void
    {
        // TODO: Implement update() method.
    }

    public function delete(delete_query_builder $delete_query_builder): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * Insert an array of questions into the database
     * @param array<question> $entities the list of questions to insert
     * @return array<int> the ids of the inserted questions
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

    /**
     * Insert an array of entities into the database without returning the ids
     * @param array<question> $entities
     * @throws coding_exception
     * @throws dml_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        $DB->insert_records(self::$tablename, $entities);
    }
}