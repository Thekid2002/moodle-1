<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_livequiz\query;


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
     * Executes the query and returns the first result.
     * @return abstract_db_model the first result of the query
     */
    public function first(): abstract_db_model
    {
        return $this->repository->select($this);
    }

    /**
     * Executes the query and returns all results.
     * @return array the results of the query
     */
    public function all(): array
    {
        return $this->repository->select_all($this);
    }


    public function to_sql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->repository->tablename}";
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        if ($this->limit > 0) {
            $sql .= " LIMIT $this->limit";
        }
        return $sql;
    }
}