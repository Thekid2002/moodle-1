<?php

namespace mod_livequiz\classes\querybuilder;

interface query_builder_interface
{
    public function to_sql(): string;
}