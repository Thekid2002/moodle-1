<?php

namespace mod_livequiz\classes\query_builder;

class insert_query_builder extends query_builder
{
    public function complete(): string
    {
        return $this->repository->insert($this);
    }

    public function to_sql(): string
    {
        return '';
    }
}