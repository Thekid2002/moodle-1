<?php

namespace mod_livequiz\repositories;

use core\exception\coding_exception;
use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\livequiz_lecturer_relation;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;

class livequiz_lecturer_repository extends abstract_crud_repository
{
    /**
     * Select a quiz lecturer relation
     * @param delimit_query_builder|select_query_builder $query_builder
     * @return livequiz_lecturer_relation
     * @throws dml_exception
     */
    public function select(delimit_query_builder|select_query_builder $query_builder): livequiz_lecturer_relation
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No quiz_lecturer_relation could be found');
        }
        return new livequiz_lecturer_relation($result->id, $result->quiz_id, $result->lecturer_id);
    }

    /**
     * Select all quiz lecturer relations
     * @param delimit_query_builder|select_query_builder $query_builder
     * @return array
     * @throws dml_exception
     */
    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        $quiz_lecturers = [];
        foreach ($results as $result) {
            $quiz_lecturer = new livequiz_lecturer_relation($result->id, $result->quiz_id, $result->lecturer_id);
            $quiz_lecturers[] = $quiz_lecturer;
        }
        return $quiz_lecturers;
    }

    /**
     * Insert a quiz lecturer relation into the database
     * @param livequiz_lecturer_relation $entity the quiz lecturer relation to insert
     * @return int the id of the inserted quiz lecturer relation
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record($this->tablename, $entity->get_data());
    }

    /**
     * Insert an array of entities into the database
     * @param array $entities An array of entities to insert into the database
     * @return void Insert an array of entities into the database
     * @throws dml_exception
     * @throws coding_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        for ($i = 0; $i < count($entities); $i++) {
            $entities[$i] = $entities[$i]->get_data();
        }
        $DB->insert_records($this->tablename, $entities);
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