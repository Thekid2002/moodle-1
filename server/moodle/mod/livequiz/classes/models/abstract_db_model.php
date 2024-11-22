<?php

namespace mod_livequiz\models;

/**
 * Class abstract_db_model
 *
 * This class is an abstract class for database models.
 *
 * @package mod_livequiz
 */
abstract class abstract_db_model {
    abstract public function clone(): abstract_db_model;
    abstract public function get_data(): array;
}