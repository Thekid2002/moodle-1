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
use mod_livequiz\query\delete_query_builder;
use mod_livequiz\query\delimit_query_builder;
use mod_livequiz\query\select_query_builder;
use mod_livequiz\unitofwork\unit_of_work;
use mod_livequiz\models\abstract_db_model;
use mod_livequiz\models\livequiz;

class livequiz_repository extends abstract_crud_repository {
    private unit_of_work $unit_of_work;

    function __construct(unit_of_work $unit_of_work)
    {
        $this->unit_of_work = $unit_of_work;
        $this->tablename = 'livequiz';
    }

    /**
     * @throws \dml_exception
     */
    function select(select_query_builder | delimit_query_builder $query_builder): livequiz
    {
        global $DB;
        $sql = $query_builder->to_sql();
        $result = $DB->get_record_sql($sql, $query_builder->bindings);
        if(!$result) {
            throw new dml_exception('No livequiz found');
        }
        $livequiz = new livequiz($result->id, $result->name, $result->description, $result->startdate, $result->enddate, $result->duration, $result->lecturerid);
        if(!$livequiz) {
            throw new dml_exception('No livequiz found');
        }
        $livequizclone = $livequiz->clone();
        $this->unit_of_work->data_clones[] = $livequizclone;
        $this->unit_of_work->data[] = $livequiz;
        return $livequiz;
    }

    function select_all(select_query_builder | delimit_query_builder $query_builder): array
    {
        global $DB;
        $sql = $query_builder->toSql();
        $results = $DB->get_records_sql($sql, $query_builder->bindings);
        if(!$results) {
            throw new dml_exception('No livequizzes found');
        }
        $livequizzes = [];
        foreach($results as $result) {
            $livequiz = new livequiz($result->id, $result->name, $result->description, $result->startdate, $result->enddate, $result->duration, $result->lecturerid);
            if(!$livequiz) {
                throw new dml_exception('No livequiz found');
            }
            $livequizclone = $livequiz->clone();
            $this->unit_of_work->data_clones[] = $livequizclone;
            $this->unit_of_work->data[] = $livequiz;
            $livequizzes[] = $livequiz;
        }
        return $livequizzes;
    }

    /**
     * @param livequiz $entity the entity to insert
     * @return void
     */
    public function insert(abstract_db_model $entity): void
    {
        $query = "INSERT INTO {$this->tablename} (name, course, intro, introformat, timecreated, timemodified)
                    VALUES ('{$entity->name}', '{$entity->get_course()}', '{$entity->intro}', '{$entity->introformat}',
                            '{$entity->get_timecreated()}', '{$entity->get_timemodified()}')";
        $this->unit_of_work->db_pool->add_query($query);
    }

    /**
     * @param livequiz $entity the entity to update
     * @return void
     */
    public function update(abstract_db_model $entity): void
    {
        $query = "UPDATE {$this->tablename} SET name = '{$entity->name}', course = '{$entity->get_course()}',
                 intro = '{$entity->intro}', introformat = '{$entity->introformat}', 
                 timemodified = '{$entity->get_timemodified()}' WHERE id = {$entity->get_id()}";
        $this->unit_of_work->db_pool->add_query($query);
    }

    /**
     * @param delete_query_builder $delete_query_builder the query builder to delete the entity
     * @return void
     */
    public function delete(delete_query_builder $delete_query_builder): void
    {
        $query = $delete_query_builder->to_sql();
        $this->unit_of_work->db_pool->add_query($query);
    }
}

