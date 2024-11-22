<?php

namespace mod_livequiz\unitofwork;

use mod_livequiz\repositories\abstract_crud_repository;

class query_builder {
    protected $select = [];
    protected $joins = [];
    protected $where = [];
    protected $bindings = [];
    protected abstract_crud_repository $repository;

    public function __construct(abstract_crud_repository $repository) {
        $this->repository = $repository;
    }

    public function select($columns) {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function leftjoin($table, $first, $operator, $second) {
        $this->joins[] = "LEFT JOIN $table ON $first $operator $second";
        return $this;
    }

    public function rightjoin($table, $first, $operator, $second) {
        $this->joins[] = "RIGHT JOIN $table ON $first $operator $second";
        return $this;
    }

    public function innerjoin($table, $first, $operator, $second) {
        $this->joins[] = "JOIN $table ON $first $operator $second";
        return $this;
    }

    public function where($column, $operator, $value) {
        $this->where[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function toSql() {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->repository->tablename . ' ' . implode(' ', $this->joins);
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        return $sql;
    }

    /**
     * @throws \dml_exception
     */
    public function firstordefault() {
        global $DB;
        $sql = $this->toSql();
        $result = $DB->get_record_sql($sql, $this->bindings);
        return $result;
    }

    public function getBindings() {
        return $this->bindings;
    }
}