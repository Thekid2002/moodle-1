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
    public string $tablename = 'livequiz_questions';

    /**
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
        return $DB->insert_record($this->tablename, $entity->get_data());
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
     * @throws coding_exception
     * @throws dml_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        for ($i = 0; $i < count($entities); $i++) {
            $entities[$i] = $entities[$i]->get_data();
        }
        $DB->insert_records($this->tablename, $entities);
    }
}