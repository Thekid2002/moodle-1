<?php

namespace mod_livequiz\repositories;

abstract class abstract_crud_repository {
    public abstract function select($predicate): string;
    public abstract function select_all(): string;
    public abstract function insert($data): string;
    public abstract function update($data): string;
    public abstract function delete($predicate): string;
}