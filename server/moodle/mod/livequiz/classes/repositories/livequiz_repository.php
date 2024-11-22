<?php

namespace mod_livequiz\repositories;

use dml_exception;
use mod_livequiz\classes\querybuilder\delimit_query_builder;
use mod_livequiz\classes\querybuilder\query_builder;
use mod_livequiz\classes\querybuilder\select_query_builder;
use mod_livequiz\models\livequiz;
use mod_livequiz\unitofwork\unit_of_work;
use stdClass;

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

    function select_all(query_builder $query_builder): array
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

    public function insert(stdClass $data): void
    {
        $this->unit_of_work->queries[] = 'INSERT INTO {livequiz} (name, description, startdate, enddate, duration, lecturerid) 
                VALUES (' . $data['name'] . ', ' . $data['description'] . ', ' . $data['startdate'] . ', '
            . $data['enddate'] . ', ' . $data['duration'] . ', ' . $data['lecturerid'] . ')';
    }

    public function update($data): void
    {
        $this->unit_of_work->queries[] = 'UPDATE {livequiz} SET name = ' . $data['name'] . ', description = ' . $data['description'] . ', startdate = '
            . $data['startdate'] . ', enddate = ' . $data['enddate'] . ', duration = ' . $data['duration'] . ', lecturerid = '
            . $data['lecturerid'] . ' WHERE id = ' . $data['id'];
    }

    public function delete($data): void
    {
        $this->unit_of_work->queries[] = 'DELETE FROM {livequiz} WHERE id = ' . $data['id'];
    }
}

