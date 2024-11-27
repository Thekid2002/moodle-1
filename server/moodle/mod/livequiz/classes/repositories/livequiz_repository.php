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

use coding_exception;
use dml_exception;
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\livequiz;

class livequiz_repository extends abstract_crud_repository {
    /**
     * @var string $tablename The name of the table in the database.
     */
    public static string $tablename = 'livequiz';

    /**
     * @throws \dml_exception
     */
    function select(select_query_builder | delimit_query_builder $query_builder): livequiz
    {
        global $DB;
        $sql = $query_builder->to_sql();
        echo $sql;
        echo "\n";
        echo print_object($query_builder->bindings);
        echo "\n";
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        echo print_object($result);
        if(!$result) {
            throw new dml_exception('No livequiz found');
        }
        return new livequiz($result->id, $result->name, $result->course, $result->intro,
            $result->introformat, $result->timecreated, $result->timemodified);
    }

    /**
     * @throws dml_exception
     */
    function select_all(select_query_builder | delimit_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        if(!$results) {
            throw new dml_exception('No livequizzes found');
        }
        $livequizzes = [];
        foreach($results as $result) {
            $livequiz = new livequiz($result->id, $result->name, $result->course, $result->intro,
                $result->introformat, $result->timecreated, $result->timemodified);
            $livequizzes[] = $livequiz;
        }
        return $livequizzes;
    }

    /**
     * @param livequiz $entity the entity to insert
     * @return int the id of the inserted entity
     * @throws dml_exception
     */
    public function insert(abstract_db_model $entity): int
    {
        global $DB;
        return $DB->insert_record(self::$tablename, $entity->get_data());
    }

    /**
     * @param livequiz $entity the entity to update
     * @return void
     * @throws dml_exception
     */
    public function update(abstract_db_model $entity): void
    {
        global $DB;
        $DB->update_record(self::$tablename, $entity->get_data());
    }

    /**
     * @param delete_query_builder $delete_query_builder the query builder to delete the entity
     * @return void
     */
    public function delete(delete_query_builder $delete_query_builder): void
    {
        $query = $delete_query_builder->to_sql();

    }

    /**
     * Insert an array of livequizzes into the database and return the ids
     * @param array<livequiz> $entities the list of livequizzes to insert
     * @return array<int> the ids of the inserted livequizzes
     * @throws dml_exception
     */
    public function insert_array_get_ids(array $entities): array
    {
        global $DB;
        $ids = [];
        foreach ($entities as $entity) {
            $ids[] = $DB->insert_record(self::$tablename, $entity->get_data());
        }
        return $ids;
    }

    /**
     * Insert an array of livequizzes into the database
     * @param array<livequiz> $entities the list of livequizzes to insert
     * @throws dml_exception
     * @throws coding_exception
     */
    public function insert_array(array $entities): void
    {
        global $DB;
        $DB->insert_records(self::$tablename, $entities);
    }
}

