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

use mod_livequiz\repositories\livequiz_repository;
use mod_livequiz\classes\querybuilder\query_builder;

class unit_of_work {
    /**
     * @var query_builder
     */
    public query_builder $livequiz;

    /**
     * @var int the order of the queries
     */
    public int $order = 0;

    /**
     * @var array $queries the queries to execute
     */
    public array $queries = [];

    /**
     * @var array $data the data to update
     */
    public array $data = [];

    /**
     * @var array $data_clones the cloned data
     */
    public array $data_clones = [];

    /**
     * @var bool $transaction_started whether the transaction has started
     */
    private bool $transaction_started = false;

    /**
     * unit_of_work constructor.
     */
    public function __construct()
    {
        $this->livequiz = new query_builder(new livequiz_repository($this));
    }

    /**
     * Commits the changes to the database.
     */
    public function commit(): void {
        $queries = [];
        if ($this->transaction_started) {
            $queries[] = 'BEGIN;';
        }
        foreach ($this->data_clones as $clone) {
            difference($clone, $this->data);
        }
        if ($this->transaction_started) {
            $queries[] = 'COMMIT;';
        }
        global $DB;
        $DB->execute(implode(' ', $queries));
    }

    public function rollback()
    {

    }

    /**
     * Begins a transaction.
     */
    public function begin_transaction(): void {
        $this->transaction_started = true;
    }

}
