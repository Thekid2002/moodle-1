<?php

namespace mod_livequiz\repositories;

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
        $question = new question($result->id, $result->question, $result->question_type, $result->livequiz_id);
        if(!$question) {
            throw new dml_exception('No question found');
        }
        $questionclone = $question->clone();
        $this->unit_of_work->data_clones[] = $questionclone;
        $this->unit_of_work->data[] = $question;
        return $question;
    }

    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        $questions = [];
        foreach ($results as $result) {
            $question = new question($result->id, $result->question, $result->question_type, $result->livequiz_id);
            $question_clone = $question->clone();
            $this->unit_of_work->data_clones[] = $question_clone;
            $this->unit_of_work->data[] = $question;
            $questions[] = $question;
        }
        return $questions;
    }

    /**
     * Insert a question into the database
     * @param question $entity the question to insert
     * @return void
     */
    public function insert(abstract_db_model $entity): void
    {
        $query = "INSERT INTO {$this->tablename} (question, question_type, livequiz_id) VALUES ("
            . $entity->question . ", " . $entity->question_type . ", " . $entity->livequiz_id . ")";
        $this->unit_of_work->db_pool->add_query($query);
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