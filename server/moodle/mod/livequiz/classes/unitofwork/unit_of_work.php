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

defined('MOODLE_INTERNAL') || die();

class UnitOfWork {
    /**
     * @var array $newEntities
     */
    private array $newEntities = [];

    /**
     * @var array $dirtyEntities
     */
    private array $dirtyEntities = [];

    /**
     * @var array $removedEntities
     */
    private array $removedEntities = [];

    /**
     * Registers a new entity.
     *
     * @param object $entity
     */
    public function registerNew(object $entity): void {
        $this->newEntities[] = $entity;
    }

    /**
     * Registers a dirty entity.
     *
     * @param object $entity
     */
    public function registerDirty(object $entity): void {
        $this->dirtyEntities[] = $entity;
    }

    /**
     * Registers a removed entity.
     *
     * @param object $entity
     */
    public function registerRemoved(object $entity): void {
        $this->removedEntities[] = $entity;
    }

    /**
     * Gets the new entities.
     *
     * @return array
     */
    public function getNewEntities(): array {
        return $this->newEntities;
    }

    /**
     * Gets the dirty entities.
     *
     * @return array
     */
    public function getDirtyEntities(): array {
        return $this->dirtyEntities;
    }

    /**
     * Gets the removed entities.
     *
     * @return array
     */
    public function getRemovedEntities(): array {
        return $this->removedEntities;
    }

    /**
     * Clears the unit of work.
     */
    public function clear(): void {
        $this->newEntities = [];
        $this->dirtyEntities = [];
        $this->removedEntities = [];
    }
}
