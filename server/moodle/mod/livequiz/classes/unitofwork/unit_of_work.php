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

use mod_livequiz\query\query_builder;
use mod_livequiz\repositories\answer_repository;
use mod_livequiz\repositories\livequiz_repository;
use mod_livequiz\repositories\question_repository;
use mod_livequiz\repositories\student_answer_repository;

class unit_of_work {
    /**
     * @var query_builder
     */
    public query_builder $livequizzes;

    /**
     * @var query_builder
     */
    public query_builder $student_answer_relations;

    /**
     * @var query_builder
     */
    public query_builder $answers;

    /**
     * @var query_builder
     */
    public query_builder $questions;

    /**
     * unit_of_work constructor.
     */
    public function __construct()
    {
        $this->livequizzes = new query_builder(new livequiz_repository($this));
        $this->student_answer_relations = new query_builder(new student_answer_repository($this));
        $this->answers = new query_builder(new answer_repository($this));
        $this->questions = new query_builder(new question_repository($this));
    }

    /**
     * Begins a transaction.
     */
    public function begin_transaction(): void {
        $this->transaction_started = true;
    }

}
