<?php

namespace mod_livequiz\classes\query_builder;

interface query_builder_interface
{
    public function to_sql(): string;
}