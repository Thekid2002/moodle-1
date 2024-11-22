<?php

namespace mod_livequiz\repositories;

use mod_livequiz\models\livequiz;
use mod_livequiz\unitofwork\query_builder;
use mod_livequiz\unitofwork\unit_of_work;

require_once('../../config.php');

class livequiz_repository extends abstract_crud_repository {
    private unit_of_work $unit_of_work;

    function __construct(unit_of_work $unit_of_work)
    {
        $this->unit_of_work = $unit_of_work;
        $this->tablename = 'livequiz';
    }

    function select(): query_builder
    {
        return new query_builder($this);
    }

    function select_all(): array
    {
        global $DB;
        $quizes = $DB->get_records_sql('SELECT * FROM {livequiz}');
        foreach ($quizes as $quiz) {
            $this->unit_of_work->data_clones[] = clone $quiz;
        }
        return $quizes;
    }

    public function insert($data): string
    {
        return 'INSERT INTO {livequiz} (name, description, startdate, enddate, duration, lecturerid) 
                VALUES (' . $data['name'] . ', ' . $data['description'] . ', ' . $data['startdate'] . ', '
            . $data['enddate'] . ', ' . $data['duration'] . ', ' . $data['lecturerid'] . ')';
    }

    public function update($data): string
    {
        return 'UPDATE {livequiz} SET name = ' . $data['name'] . ', description = ' . $data['description'] . ', startdate = '
            . $data['startdate'] . ', enddate = ' . $data['enddate'] . ', duration = ' . $data['duration'] . ', lecturerid = '
            . $data['lecturerid'] . ' WHERE id = ' . $data['id'];
    }

    public function delete($predicate): string
    {
        return 'DELETE FROM {livequiz} WHERE ' . $predicate;
    }
}

