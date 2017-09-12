<?php

namespace HnhDigital\LaravelModelFilter\Objects;

class SearchViewResult extends SettingsAbstract
{
    public function __construct()
    {
        $this->setArray([
            'filters' => [],
            'rows'    => '',
            'count'   => 0,
        ]);
    }
}
