<?php

namespace mod_livequiz\repositories;

use mod_livequiz\models\abstract_db_model;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;

class livequiz_question_repository extends abstract_crud_repository
{
    public string $tablename = 'livequiz_questions';

    public function select(delimit_query_builder|select_query_builder $query_builder): abstract_db_model
    {
        // TODO: Implement select() method.
    }

    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        // TODO: Implement select_all() method.
    }

    public function insert(abstract_db_model $entity): int
    {
        // TODO: Implement insert() method.
    }

    public function insert_array(array $entities): void
    {
        // TODO: Implement insert_array() method.
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