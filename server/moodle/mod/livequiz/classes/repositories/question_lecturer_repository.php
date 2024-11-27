<?php

namespace mod_livequiz\classes\repositories;

use coding_exception;
use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\question;
use mod_livequiz\models\questions_lecturer_relation;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;
use mod_livequiz\repositories\abstract_crud_repository;

class question_lecturer_repository extends abstract_crud_repository
{
    /**
     * @var string $tablename The name of the table in the database.
     */
    public static string $tablename = 'livequiz_questions_lecturer';

    /**
     * Select a question lecturer relation
     * @param delimit_query_builder|select_query_builder $query_builder
     * @return questions_lecturer_relation
     * @throws dml_exception
     */
    public function select(delimit_query_builder|select_query_builder $query_builder): questions_lecturer_relation
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No question_lecturer_relation could be found');
        }
        return new questions_lecturer_relation($result->id, $result->question_id, $result->lecturer_id);

    }

    /**
     * Select all question lecturer relations
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
            $question = new questions_lecturer_relation($result->id, $result->question_id, $result->lecturer_id);
            $questions[] = $question;
        }
        return $questions;
    }

    /**
     * Insert a question lecturer relation into the database
     * @param questions_lecturer_relation $entity the question lecturer relation to insert
     * @return int the id of the inserted question lecturer relation
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record(self::$tablename, $entity->get_data());
    }

    /**
     * Insert an array of entities into the database
     * @param array<question> $entities
     * @return array<int>
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

    /**
     * Update a question lecturer relation in the database
     * @param questions_lecturer_relation $entity the question lecturer relation to update
     * @throws dml_exception
     */
    public function update(abstract_db_model $entity): void
    {
        global $DB;
        $DB->update_record(self::$tablename, $entity->get_data());
    }

    /**
     * Delete a question lecturer relation from the database
     * @param delete_query_builder $delete_query_builder
     * @return void
     */
    public function delete(delete_query_builder $delete_query_builder): void
    {
        // TODO: Implement delete() method.
    }
}