<?php

namespace Bluora\LaravelDynamicFilter\Objects;

class SearchViewOptions extends SettingsAbstract
{
    public function __construct()
    {
        $this->setArray([
            'tab.advanced.show'   => false,
            'tab.selections.show' => false,
            'tab.export.show'     => false,
            'colgroup.total'      => 1,
        ]);
    }
}
