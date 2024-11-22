<?php

namespace mod_livequiz\repositories;

use mod_livequiz\unitofwork\query_builder;

abstract class abstract_crud_repository {
    public string $tablename;
    public abstract function select(): query_builder;
    public abstract function select_all(): array;
    public abstract function insert($data): string;
    public abstract function update($data): string;
    public abstract function delete($predicate): string;
}