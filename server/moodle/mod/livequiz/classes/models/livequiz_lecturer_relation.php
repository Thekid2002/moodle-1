<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_livequiz\models;

/**
 * 'Static' class, do not instantiate.
 * Class for relation between quiz and lecturer
 * @package   mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class livequiz_lecturer_relation extends abstract_db_model {
    /**
     * @var int|null $id
     */
    private int | null $id;

    /**
     * @var int $quiz_id
     */
    private int $quiz_id;

    /**
     * @var int $lecturer_id
     */
    private int $lecturer_id;

    public function __construct(int | null $id, int $quiz_id, int $lecturer_id)
    {
        $this->id = $id;
        $this->quiz_id = $quiz_id;
        $this->lecturer_id = $lecturer_id;
    }

    public function clone(): abstract_db_model
    {
        return new livequiz_lecturer_relation($this->id, $this->quiz_id, $this->lecturer_id);
    }

    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'lecturer_id' => $this->lecturer_id
        ];
    }
}
