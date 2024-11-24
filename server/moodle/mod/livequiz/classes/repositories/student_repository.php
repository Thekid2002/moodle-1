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

namespace mod_livequiz\repositories;

use dml_exception;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;

class student_repository extends abstract_crud_repository
{
    /**
     * @var string $table the table to query
     */
    protected string $table = 'livequiz_students';

    /**
     * @var string $model the model to use for the repository
     */
    protected string $model = 'mod_livequiz\models\student';

    /**
     * @var string $id_column the primary key column for the table
     */
    protected string $id_column = 'id';

    /**
     * @var array $columns the columns to select in the query
     */
    protected array $columns = [
        'id',
        'livequiz_id',
        'user_id',
        'timecreated',
        'timemodified',
    ];

    /**
     * @var array $joins the joins to add to the query
     */
    protected array $joins = [];

    /**
     * @var array $select the columns to select in the query
     */
    protected array $select = [];

    public function select(delimit_query_builder|select_query_builder $query_builder): abstract_db_model
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if (!$result) {
            throw new dml_exception('No student found');
        }
        $student = new ($result->id, $result->livequiz_id, $result->user_id, $result->timecreated, $result->timemodified);
        if (!$student) {
            throw new dml_exception('No student found');
        }
    }

    public function select_all(delimit_query_builder|select_query_builder $query_builder): array
    {
        // TODO: Implement select_all() method.
    }

    public function insert(abstract_db_model $data): void
    {
        // TODO: Implement insert() method.
    }

    public function update(abstract_db_model $data): void
    {
        // TODO: Implement update() method.
    }

    public function delete(delete_query_builder $delete_query_builder): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param int $livequiz_id the livequiz id to get students for
     * @return array the