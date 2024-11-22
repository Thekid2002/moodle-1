<?php

namespace mod_livequiz\models;

abstract class abstract_db_model {
    abstract public function clone(): abstract_db_model;
}