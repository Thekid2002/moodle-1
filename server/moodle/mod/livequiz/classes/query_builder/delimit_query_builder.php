<?php

namespace mod_livequiz\classes\query_builder;

use mod_livequiz\repositories\abstract_crud_repository;

class delimit_query_builder extends select_query_builder {
    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];

    /**
     * @var int $limit the limit to add to the query
     */
    protected int $limit = 0;

    protected select_query_builder $select_query_builder;

    public function __construct(abstract_crud_repository $repository, select_query_builder $select_query_builder) {
        parent::__construct($repository);
        $this->select_query_builder = $select_query_builder;
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
}