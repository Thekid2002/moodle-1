<?php

namespace mod_livequiz\classes\query_builder;

class select_query_builder extends query_builder
{
    /**
     * @var array $select the columns to select in the query
     */
    protected array $select = [];

    /**
     * @var array $joins the joins to add to the query
     */
    protected array $joins = [];

    /**
     * @var array $where the where clauses to add to the query
     */
    protected array $where = [];


    /**
     * Adds a left join to the query.
     * @param string $table the table to join
     * @param string $first the first column to join on
     * @param string $operator the operator to use in the join
     * @param string $second the second column to join on
     * @return $this the query builder
     */
    public function left_join(string $table, string $first, string $operator, string $second): select_query_builder {
        $this->joins[] = "LEFT JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Adds an inner join to the query.
     * @param string $table the table to join
     * @param string $first the first column to join on
     * @param string $operator the operator to use in the join
     * @param string $second the second column to join on
     * @return $this the query builder
     */
    public function join(string $table, string $first, string $operator, string $second): select_query_builder {
        $this->joins[] = "JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Adds a where clause to the query.
     * @param string $column the column to filter on
     * @param string $operator the operator to use in the filter
     * @param mixed $value the value to filter on
     * @return delimit_query_builder the query builder
     */
    public function where(string $column, string $operator, mixed $value): delimit_query_builder {
        $delimit_query_builder = new delimit_query_builder($this->repository);
        return $delimit_query_builder->where($column, $operator, $value);
    }
    public function to_sql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->repository->tablename . ' ' . implode(' ', $this->joins);
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        return $sql;
    }
}