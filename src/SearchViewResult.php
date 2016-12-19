<?php

namespace Bluora\LaravelModelDynamicFilter;

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
