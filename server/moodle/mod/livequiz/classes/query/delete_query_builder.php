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

use mod_livequiz\repositories\abstract_crud_repository;


class delete_query_builder implements query_builder_interface
{
    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];

    /**
     * @var abstract_crud_repository $repository the repository to query
     */
    protected abstract_crud_repository $repository;

    public function __construct(abstract_crud_repository $repository) {
        $this->repository = $repository;
    }

    /**
     * Turns the delete query into a string.
     * @return string the delete query as a string
     */
    public function to_sql()
    {
        $sql = 'DELETE FROM ' . $this->repository->tablename;
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        return $sql;
    }

}