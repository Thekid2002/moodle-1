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

class select_query_builder implements query_builder_interface
{
    /**
     * @var array $select the columns to select in the query
     */
    protected array $select = [];

    /**
     * @var string | null the alias for the table
     */
    protected string | null $as;

    /**
     * @var array $joins the joins to add to the query
     */
    protected array $joins = [];

    /**
     * @var array $from the from clauses to add to the query
     */
    protected array $from = [];

    /**
     * @var abstract_crud_repository $repository the repository to query
     */
    protected abstract_crud_repository $repository;

    /**
     * @param abstract_crud_repository $repository the repository to query
     * @param array | string $select the columns to select
     */
    public function __construct(abstract_crud_repository $repository, array | string $select, string | null $as = null) {
        $this->repository = $repository;
        $this->as = $as;

        if (is_string($select)) {
            $this->select = [$select];
        } else {
            $this->select = $select;
        }
    }

    /**
     * Adds a left join to the query.
     * @param string $table the table to join
     * @param string $first the first column to join on
     * @param string $operator the operator to use in the join
     * @param string $second the second column to join on
     * @return $this the query builder
     */
    public function left_join(string $table, string $first, string $operator, string $second, string $as): select_query_builder {
        $this->joins[] = "LEFT JOIN {{$table}} AS {$as}\n ON $first $operator $second\n";
        $this->select[] = "{$as}.*";
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
    public function join(string $table, string $first, string $operator, string $second): select_query_builder {
        $this->joins[] = "JOIN {" . $table . "} ON $first $operator $second";
        $this->from[] = "{" .$table . "} AS {$table}";
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): delimit_query_builder {
        $delimit_query_builder = new delimit_query_builder($this->repository, $this->joins, $this->select, $this->from, $this->as);
        return $delimit_query_builder->where($column, $operator, $value);
    }

    /**
     * Completes the query and returns the first result.
     * @return abstract_db_model the first result of the query
     */
    public function first(): abstract_db_model
    {
        return $this->repository->select($this);
    }

    /**
     * Completes the query and returns all results.
     * @return array the result of the query
     */
    public function all(): array
    {
        return $this->repository->select_all($this);
    }

    public function to_sql(): string
    {
        $delimitquerybuilder = new delimit_query_builder($this->repository, $this->joins, $this->select, $this->from, $this->as);
        return $delimitquerybuilder->to_sql();
    }
}