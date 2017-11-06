<?php

namespace HnhDigital\LaravelModelFilter\Composers;

use App\Models\AppModel;
use App\Models\ModelFilter;
use HnhDigital\LaravelModelFilter\Objects\SearchViewOptions;
use HnhDigital\LaravelModelFilter\Objects\SearchViewResult;
use Illuminate\Contracts\View\View;

class SearchPage
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
        // Get the data assigned to this view
        $view_data = $view->getData();
        $filter_types = [];
        $filters = [];
        $filter_options = [];
        $mode_name = '';
        $app_model_uuid = '';
        $app_model_title = '';
        $operator_options = [];
        $list_filter_options = [];
        $model_filter_options = [];
        $layout_div_class = '';

        // Search data available
        if (!empty($view_data['search_data']) && !empty($view_data['search_data']['setup'])) {
            // Get the filter setup
            $setup = &$view_data['search_data']['setup'];

            // Update controller space
            $setup->set('search.controller', class_basename($setup->get('search.controller', '')));
            $setup->set('search.model', class_basename($setup->get('search.model', '')));

            if ($setup->get('search.show', false)) {
                for ($c = 0; $c < $setup->get('colgroup.total'); $c++) {
                    if ($setup->get('search.'.$c.'.columns', 1) > 1) {
                        $colspan = $setup->get('search.'.$c.'.columns', 1);
                        $setup->set('search.'.$c.'.td', 'colspan="'.$colspan.'"');
                        $c++;
                        for ($cg = $c; $cg < $colspan; $cg++) {
                            $setup->set('search.'.$cg.'.hide', true);
                            $c++;
                        }
                    }
                }
            }

            // Check the model has been provided
            if ($setup->get('search.model', false) && class_exists($model_name = 'App\\Models\\'.$setup->get('search.model', ''))) {
                // Create empty model
                $model = (new $model_name());

                // Get the database entry for the model
                $base_model_name = class_basename($model_name);
                $app_model = AppModel::where('name', '=', $base_model_name)->firstOrFail();
                $app_model_uuid = $app_model->uuid;
                $app_model_title = $app_model->title;

                // Advanced filtering is enabled
                if ($setup->get('tab.advanced.show', false)) {
                    $filter_types = $model->getFilterTypes();
                    $filters = $model->getFilterAttributes();

                    foreach ($filter_types as $type) {
                        $operator_options[$type] = $model->getFilterOperators($type);
                    }

                    $list_filter_options = [];

                    $filter_options = [['', '']];
                    foreach ($filters as $filter_name => $filter_settings) {
                        $filter_type = snake_case($filter_settings['filter']);
                        if (!isset($filter_settings['search_tab_only']) && $filter_name !== 'models') {
                            $filter_options[] = [$filter_name.'|'.$filter_type, $filter_settings['name']];
                        }
                        if ($filter_settings['filter'] == 'list' || $filter_settings['filter'] == 'listLookup') {
                            $source_method = 'getFilter'.$filter_settings['source'].'Options';
                            if (method_exists($model, $source_method)) {
                                $list_filter_options[$filter_name] = $model->$source_method();
                            } else {
                                $list_filter_options[$filter_name] = [];
                            }
                        }
                    }

                    $model_attribute = 'model_id';

                    if (isset(ModelFilter::$modelKey)) {
                        $model_attribute = ModelFilter::$modelKey;
                    }

                    // Load any saved filters
                    $original_model_filter_options = ModelFilter::where($model_attribute, $app_model->id)->get();
                    $model_filter_options[] = ['', ''];
                    foreach ($original_model_filter_options as $filter_option) {
                        $model_filter_options[] = [$filter_option->uuid, $filter_option->name];
                    }
                }
            }

            // Default name for the search filter options
            if (count($setup->get('tab.search_filter.options', []))) {
                $mode_name = $setup->get('tab.search_filter.options.'.$setup->get('search.filters.mode', '0'), '');
            }

            if ($setup->get('search.layout-style', '') != 'inline') {
                $layout_div_class = 'wrapper wrapper-content animated fadeInRight';
            }
        } else {
            $view_data['search_data'] = ['setup' => new SearchViewOptions(), 'result' => new SearchViewResult()];
            $setup = $view_data['search_data']['setup'];
        }

        $view->with('layout_div_class', $layout_div_class)
            ->with('search_data', $view_data['search_data'])
            ->with('setup', $view_data['search_data']['setup'])
            ->with('result', $view_data['search_data']['result'])
            ->with('search_filters', $setup->get('search.filters', []))
            ->with('filter_types', $filter_types)
            ->with('filters', $filters)
            ->with('filter_options', $filter_options)
            ->with('mode_name', $mode_name)
            ->with('operator_options', $operator_options)
            ->with('list_filter_options', $list_filter_options)
            ->with('app_model_title', $app_model_title)
            ->with('app_model_uuid', $app_model_uuid)
            ->with('model_filter_options', $model_filter_options);
    }
}
