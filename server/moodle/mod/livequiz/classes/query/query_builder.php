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

use core_reportbuilder\external\conditions\delete;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\repositories\abstract_crud_repository;

/**
 * Class query_builder
 *
 * This class is used to build SQL queries for the repositories.
 *
 * @package mod_livequiz
 */
class query_builder implements query_builder_interface {
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
     * @return select_query_builder the select query builder
     */
    public function select(array | string $columns = '*'): select_query_builder {
        return new select_query_builder($this->repository, $columns);
    }

    public function update(abstract_db_model $entity): void {
        $this->repository->update($entity);
    }

    public function delete(): delete_query_builder {
        return new delete_query_builder($this->repository);
    }

    public function insert(abstract_db_model $entitiy): int {
        return $this->repository->insert($entitiy);
    }
}
