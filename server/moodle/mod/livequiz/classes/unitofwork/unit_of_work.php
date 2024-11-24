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

namespace mod_livequiz\unitofwork;

use mod_livequiz\dbpool\db_pool;
use mod_livequiz\query\query_builder;
use mod_livequiz\repositories\livequiz_repository;

class unit_of_work {
    /**
     * @var query_builder
     */
    public query_builder $livequizzes;

    /**
     * @var query_builder
     */
    public query_builder $students;

    /**
     * @var array $data the data to update
     */
    public array $data = [];

    /**
     * @var array $data_clones the cloned data
     */
    public array $data_clones = [];

    /**
     * @var db_pool $db_pool the database pool
     */
    public db_pool $db_pool;

    /**
     * unit_of_work constructor.
     */
    public function __construct()
    {
        $this->db_pool = new db_pool();
        $this->livequizzes = new query_builder(new livequiz_repository($this));
        $this->students = new query_builder(new student_repository($this));
    }

    /**
     * Commits the changes to the database.
     * @throws \dml_exception
     */
    public function save_changes(): void {
        $this->db_pool->commit();
    }

    /**
     * Begins a transaction.
     */
    public function begin_transaction(): void {
        $this->transaction_started = true;
    }

}
