<?php

namespace mod_livequiz\classes\query_builder;

use mod_livequiz\models\abstract_db_model;


class delete_query_builder extends query_builder
{
    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];

    /**
     * Executes the query and returns the first result or throws an exception if no results are found.
     * @throws \dml_exception if no results are found
     */
    public function complete(): abstract_db_model
    {
        return $this->repository->delete($this);
    }

    public function to_sql(): string
    {
        $sql = 'DELETE FROM ' . $this->repository->tablename;
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        return $sql;
    }

}