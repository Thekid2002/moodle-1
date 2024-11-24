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

namespace mod_livequiz\dbpool;

use dml_exception;

class db_pool {
    /**
     * @var array $queries the queries to execute
     */
    private array $queries = [];

    /**
     * Adds a query to the pool
     * @param string $query the query to add
     */
    public function add_query(string $query): void {
        $this->queries[] = $query;
    }

    /**
     * Executes the queries in the pool
     * @throws dml_exception
     */
    public function commit(): void{
        $largequery = 'BEGIN;';
        $largequery .= implode(';', $this->queries);
        $this->queries = [];
        global $DB;
        try {
            $DB->execute($largequery);
            $DB->execute('COMMIT;');
        } catch (dml_exception $e) {
            $DB->execute('ROLLBACK;');
            throw $e;
        }

    }
}