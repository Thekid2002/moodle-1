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

namespace mod_livequiz\unitofwork;

use mod_livequiz\models\abstract_db_model;
use mod_livequiz\repositories\abstract_crud_repository;

/**
 * Class query_builder
 *
 * This class is used to build SQL queries for the repositories.
 *
 * @package mod_livequiz
 */
class query_builder {
    /**
     * @var array $select the columns to select in the query
     */
    protected array $select = [];

    /**
     * @var array $joins the joins to add to the query
     */
    protected array $joins = [];

    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];

    /**
     * @var array $bindings the bindings for the query
     */
    public array $bindings = [];

    /**
     * @var abstract_crud_repository the repository to build the query for
     */
    protected abstract_crud_repository $repository;

    /**
     * query_builder constructor.
     * @param abstract_crud_repository $repository the repository to build the query for
     */
    public function __construct(abstract_crud_repository $repository) {
        $this->repository = $repository;
    }

    /**
     * Sets the columns to select in the query.
     * @param array | string $columns the columns to select
     * @return $this the query builder
     */
    public function select(array | string $columns = '*'): query_builder {
        if (is_array($columns)) {
            $this->select = $columns;
        } else {
            $this->select = [$columns];
        }
        return $this;
    }

    /**
     * Adds a left join to the query.
     * @param string $table the table to join
     * @param string $first the first column to join on
     * @param string $operator the operator to use in the join
     * @param string $second the second column to join on
     * @return $this the query builder
     */
    public function left_join(string $table, string $first, string $operator, string $second): query_builder {
        $this->joins[] = "LEFT JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Adds an inner join to the query.
     * @param string $table the table to join
     * @param string $first the first column to join on
     * @param string $operator the operator to use in the join
     * @param string $second the second column to join on
     * @return $this the query builder
     */
    public function join(string $table, string $first, string $operator, string $second): query_builder {
        $this->joins[] = "JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Adds a where clause to the query.
     * @param string $column the column to filter on
     * @param string $operator the operator to use in the filter
     * @param mixed $value the value to filter on
     * @return $this the query builder
     */
    public function where(string $column, string $operator, mixed $value): query_builder {
        $this->where[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Builds the SQL query from the query builder.
     * @return string the SQL query
     */
    public function to_sql(): string {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->repository->tablename . ' ' . implode(' ', $this->joins);
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        return $sql;
    }

    /**
     * Executes the query and returns the first result or throws an exception if no results are found.
     * @throws \dml_exception if no results are found
     */
    public function complete(): abstract_db_model {
        return $this->repository->select($this);
    }

    /**
     * Executes the query and returns all results.
     */
    public function all(): array {
        return $this->repository->select_all($this);
    }
}
