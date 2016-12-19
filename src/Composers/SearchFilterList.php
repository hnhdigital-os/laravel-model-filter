<?php

namespace Bluora\LaravelDynamicFilter\Composers;

use Bluora\LaravelHtmlGenerator\Html;
use Illuminate\Contracts\View\View;

class SearchFilterList
{
    /**
     * Create a new common module search composer.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        if ($view_data['template']) {
            $column1 = '{PLACEHOLDER_ATTRIBUTE_NAME}';
            $column2 = Html::select()->name('{PLACEHOLDER_SEARCH_NAME}_operator[]')->addClass('search-operator form-control')
                ->style('width:100%;')->addOptionsArray($view_data['operator_options'], 'value', 'name');
            $column3 = Html::select()->name('{PLACEHOLDER_SEARCH_NAME}_value1[]')->addClass('search-value1 form-control select2-on-add')
                ->style('width:100%;')->multiple()
                ->data('placeholder', 'Select one or many...')->data('tags', 'true')->data('multiple', 'true')->data('allow-clear', 'true');

            foreach ($view_data['list_filter_options'] as $filter_name => $options) {
                foreach ($options as &$option) {
                    $option['class'] = 'filter-'.$filter_name;
                }
                $column3->addOptionsArray($options, 0, 1, null);
            }
        } else {
            $column1 = $view_data['filter_settings']['name'];
            $column2 = Html::select()->name($view_data['filter_name'].'_operator[]')->addClass('search-operator form-control')
                ->style('width:100%;')->addOptionsArray($view_data['operator_options'], 'value', 'name', $view_data['filter'][0]);
            $column3 = Html::select()->name($view_data['filter_name'].'_value1[]')->addClass('search-value1 form-control select2')
                ->style('width:100%;')->addOptionsArray($view_data['list_filter_options'][$view_data['filter_name']], 0, 1, $view_data['filter'][1])->multiple()
                ->data('placeholder', 'Select one or many...')->data('tags', 'true')->data('multiple', 'true')->data('allow-clear', 'true');
        }

        $view->with('column1', $column1)
            ->with('column2', $column2)
            ->with('column3', $column3);
    }
}
