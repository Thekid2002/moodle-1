<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_livequiz\repositories;

use mod_livequiz\classes\querybuilder\delimit_query_builder;
use mod_livequiz\classes\querybuilder\query_builder;
use mod_livequiz\classes\querybuilder\select_query_builder;
use mod_livequiz\models\abstract_db_model;
use stdClass;

/**
 * Class abstract_crud_repository
 */
abstract class abstract_crud_repository {
    /**
     * @var string $tablename The name of the table in the database.
     */
    public string $tablename;
    public abstract function select(select_query_builder | delimit_query_builder $query_builder): abstract_db_model;
    public abstract function select_all(query_builder $query_builder): array;
    public abstract function insert(stdClass $data): void;
    public abstract function update($data): void;
    public abstract function delete($predicate): void;
}