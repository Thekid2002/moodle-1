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
 * Class answer.
 *
 * This class represents an answer in the LiveQuiz module.
 * It handles the creation, retrieval, and updates of answer associated with quiz questions.
 *
 * @package mod_livequiz
 * @copyright 2024 Software AAU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class answer extends abstract_db_model {
    /**
     * @var int | null $id
     */
    private int | null $id;
    /**
     * @var int $correct
     */
    public int $correct;
    /**
     * @var string $description
     */
    public string $description;

    /**
     * @var string $explanation
     */
    public string $explanation;

    /**
     * Constructor for the answer class. Returns the object.
     *
     * @param int | null $id
     * @param int $correct // Expects 1 or 0.
     * @param string $description
     * @param string $explanation
     */
    public function __construct(int | null $id, int $correct, string $description, string $explanation) {
        $this->id = $id;
        $this->correct = $correct;
        $this->description = $description;
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Gets the ID of the answer.
     *
     * @return int | null
     */
    public function get_id(): int | null {
        return $this->id;
    }
    /**
     * Clones the answer object.
     *
     * @return answer the cloned answer object
     */
    public function clone(): answer
    {
        return new answer($this->correct, $this->description, $this->explanation);
    }

    /**
     * Get the data of the answer object.
     *
     * @return array the data of the answer object
     */
    public function get_data(): array
    {
        return [
            'id' => $this->id,
            'correct' => $this->correct,
            'description' => $this->description,
            'explanation' => $this->explanation,
        ];
    }
}
