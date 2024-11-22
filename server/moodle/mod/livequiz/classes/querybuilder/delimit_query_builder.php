<?php

namespace mod_livequiz\classes\querybuilder;

use mod_livequiz\models\abstract_db_model;
use mod_livequiz\repositories\abstract_crud_repository;

class delimit_query_builder implements query_builder_interface {
    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];

    /**
     * @var int $limit the limit to add to the query
     */
    protected int $limit = 0;

    /**
     * @var array $joins the joins to add to the query
     */
    protected array $joins = [];

    /**
     * @var array $select the columns to select in the query
     */
    protected array $select = [];

    /**
     * @var array $bindings the bindings for the query
     */
    public array $bindings = [];

    /**
     * @var abstract_crud_repository $repository the repository to query
     */
    protected abstract_crud_repository $repository;

    public function __construct(abstract_crud_repository $repository, array $joins, array $select) {
        $this->repository = $repository;
        $this->joins = $joins;
        $this->select = $select;
    }

    /**
     * Adds a where clause to the query.
     * @param string $column the column to filter on
     * @param string $operator the operator to use in the filter
     * @param mixed $value the value to filter on
     * @return $this the query builder
     */
    public function where(string $column, string $operator, mixed $value): delimit_query_builder {
        $this->where[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Adds a limit to the query.
     * @param int $limit the limit to add to the query
     * @return $this the query builder
     */
    public function count(int $limit): delimit_query_builder {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Executes the query and returns the first result or throws an exception if no results are found.
     * @throws \dml_exception if no results are found
     */
    public function complete(): abstract_db_model
    {
        return $this->repository->select($this);
    }

    public function to_sql(): string
    {
        return '';
    }
}