<?php

namespace mod_livequiz\repositories;

use mod_livequiz\unitofwork\unit_of_work;

require_once('../../config.php');

class livequiz_repository extends abstract_crud_repository {
    private unit_of_work $unit_of_work;

    function __construct(unit_of_work $unit_of_work)
    {
        $this->unit_of_work = $unit_of_work;
    }

    function select($predicate): string
    {
        return 'SELECT * FROM {livequiz} WHERE ' . $predicate;
    }

    function select_all(): string
    {
        return 'SELECT * FROM {livequiz}';
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

