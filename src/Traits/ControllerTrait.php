<?php

namespace Bluora\LaravelDynamicFilter\Traits;

use Bluora\LaravelDynamicFilter\Objects\SearchViewOptions;
use Bluora\LaravelDynamicFilter\Objects\SearchViewResult;
use Bluora\LaravelDynamicFilterl;
use Html;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Pagination\Paginator;
use Request;
use Route;

trait ControllerTrait
{
    /**
     * Get current search options.
     *
     * @param mixed $use_session
     * @param array $settings
     * @param array $options
     * @param array $search_name
     *
     * @return mixed
     */
    public function getCurrentSearchDetails($use_session, $settings, $options, $search_name)
    {
        extract($options);

        $options['route_paramater'] = (empty($options['route_paramater'])) ? $current_model : $options['route_paramater'];

        // Remove plural to get route paramater
        if (substr($options['route_paramater'], -1, 1) == 's') {
            $options['route_paramater'] = substr($options['route_paramater'], 0, -1);
        }

        // Current model
        if (!isset($options['model'])) {
            $options['model'] = Route::current()->parameter($options['route_paramater']);
        }
        $options['model_id'] = (isset($options['model'])) ? $options['model']->id : '';

        $options['route_name'] = (request()->ajax()) ? request()->get('route') : Route::current()->getName();
        $options['route_name'] = (isset($settings['route-name'])) ? $settings['route-name'] : $options['route_name'];
        (!empty($options['search_tab'])) ?: $options['search_tab'] = (!isset($settings['search-tab'])) ? request()->get('search-tab') : $settings['search-tab'];
        $options['method_source'] = (!isset($settings['method-source'])) ? request()->get('method-source') : $settings['method-source'];
        $options['method_source'] = (empty($options['method_source'])) ? $current_model : $options['method_source'];

        // Use provided variables or use defaults
        $options['attached_tab'] = (isset($attached_tab)) ? $attached_tab : str_plural($search_name, 2);
        $options['attached_button_color'] = (isset($attached_button_color)) ? $attached_button_color : 'danger';
        $options['attached_button_icon'] = (isset($attached_button_icon)) ? $attached_button_icon : 'times';
        $options['attached_button_name'] = (isset($attached_button_name)) ? $attached_button_name : 'Remove '.str_plural($search_name, 1);
        $options['unattached_tab'] = (isset($unattached_tab)) ? $unattached_tab : 'available-'.str_plural($search_name, 2);
        $options['unattached_button_color'] = (isset($unattached_button_color)) ? $unattached_button_color : 'primary';
        $options['unattached_button_icon'] = (isset($unattached_button_icon)) ? $unattached_button_icon : 'plus';
        $options['unattached_button_name'] = (isset($unattached_button_name)) ? $unattached_button_name : 'Add '.str_plural($search_name, 1);

        // Get filters from session or ajax request
        $options['search_request'] = $this->getSearchAppliedFilters($current_model.'.'.$search_name.'.search', $options['model_id'].$options['search_tab'], $use_session);
        (isset($options['search_request']['filters'])) ?: $options['search_request']['filters'] = [];

        if (isset($settings) && is_array($settings)) {
            foreach ($settings as $key => $value) {
                $options['search_request'][$key] = $value;
            }
        }

        if (request()->ajax()) {
            $settings_data = request()->all();
        } else {
            $settings_data = (array) $settings;
        }
        foreach ($settings_data as $key => $value) {
            if (stripos($key, 'setting-') === 0) {
                $options[str_replace('-', '_', $key)] = $value;
            }
        }

        return $options;
    }

    /**
     * Get current search query from options or use standard.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function getCurrentSearchQuery($options, $class_name, $model_filter = false)
    {
        $class_name = 'App\\Models\\'.$class_name;
        extract($options);
        $route_name = (!isset($route_name)) ? $current_model : $route_name;
        $other_model = (!isset($other_model)) ? camel_case($current_model) : $other_model;

        // Build query
        if (stripos($route_name, $current_model.'::view') !== false || $search_tab == $attached_tab) {
            if (isset($attached_allocations)) {
                return $attached_allocations;
            } else {

                if (isset($attached_method_source)) {
                    return $model->$attached_method_source();
                }

                $model = new $class_name();

                $query = $this->getRelationQuery($model, $method_source);

                if (method_exists($query, 'onlyActive')) {
                    $query = $query->onlyActive();
                }

                if (isset($attached_model_filter) && $attached_model_filter instanceof \Closure) {
                    $query = $attached_model_filter($query);
                }

                if ($model_filter instanceof \Closure) {
                    $query = $model_filter($query);
                }
            }
        } elseif ($search_tab == $unattached_tab) {
            if (isset($unattached_allocations)) {
                return $unattached_allocations;
            } else {

                if (isset($unattached_method_source)) {
                    return $model->$unattached_method_source();
                }

                $model = new $class_name();

                $list = $this->getRelationQuery($model, $method_source)->select($model->getTable().'.id')->pluck('id')->all();

                $query = $class_name::whereNotIn($model->getTable().'.id', $list);

                if (method_exists($query, 'onlyActive')) {
                    $query = $query->onlyActive();
                }

                if (isset($unattached_model_filter) && $unattached_model_filter instanceof \Closure) {
                    $query = $unattached_model_filter($query);
                }

                if ($model_filter instanceof \Closure) {
                    $query = $model_filter($query);
                }
            }
        } else {
            return ['filters' => [], 'rows' => ''];
        }

        return $query;
    }

    /**
     * Get the relation query.
     *
     * @param  mixed  $model
     * @param  string $method_source
     *
     * @return
     */
    private function getRelationQuery($model, $method_source)
    {
        $method_name = camel_case($method_source);
        $relation = $model->$method_name();
        $relation_class = basename(str_replace('\\', '/', get_class($relation)));

        switch ($relation_class) {
            case 'BelongsTo':
                $model_key_name = $relation->getForeignKey();
                break;
            case 'HasOne':
                $model_key_name = $relation->getForeignKeyName();
                break;
            case 'BelongsToMany':
                $model_key_name = $relation->getQualifiedRelatedKeyName();
                break;
        }

        $model_id = $model->id;

        return $model->whereHas($method_name, function ($sub_query) use ($model_key_name, $model_id) {
            $sub_query->where($model_key_name, $model_id);
        });
    }

    /**
     * Default skip and take to limit queries.
     *
     * @var int
     */
    protected static $default_paginate_items = 20;
    protected static $default_take_options = [20, 50, 250, 500, 'all'];

    /**
     * Get the filters from the ajax request or from the session.
     *
     * @param string $controller_name
     * @param mixed  $use_session
     *
     * @return array
     */
    protected static function getSearchAppliedFilters($controller_name, $distinct_session = '', $use_session = false)
    {

        // Filter provided.
        if (is_array($use_session)) {
            static::defaultPagination($use_session);
            (isset($use_session['filters'])) ?: $use_session['filters'] = [];

            return $use_session;
        } elseif (request()->ajax() && !is_bool($use_session)) {
            $use_session = false;
        }

        $distinct_session = (!empty($distinct_session)) ? '_'.$distinct_session : '';
        $session_name = str_replace(['.', '-'], '_', $controller_name.'_search_filters'.$distinct_session);
        $session = session($session_name);

        // Remote request
        if (request()->ajax() && $use_session === false) {
            $provided_session = request::all();
            static::defaultPagination($provided_session);
            (isset($provided_session['saved_filter'])) ? $provided_session['saved_filter'] = $session['saved_filter'] : false;
            (isset($provided_session['filters'])) ?: $provided_session['filters'] = [];

            return $provided_session;
        }

        static::defaultPagination($session);
        (isset($session['filters'])) ?: $session['filters'] = [];

        // Get the session saved filters
        return $session;
    }

    /**
     * Set the default skip & take.
     *
     * @param array &$search_request
     *
     * @return void
     */
    public static function defaultPagination(&$search_request)
    {
        if (!isset($search_request['paginate_items']) || empty($search_request['paginate_items'])) {
            $search_request['paginate_items'] = static::$default_paginate_items;
        }

        if (request::get('page', false) !== false) {
            $search_request['page'] = request::get('page');
        } elseif (!isset($search_request['page'])) {
            $search_request['page'] = 1;
        }

        $current_page = $search_request['page'];

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });
    }

    /**
     * Show the filters that have been applied for this search.
     *
     * @param string $model
     * @param \Tag   &$tbody
     * @param array  $search_request
     * @param int    $column_span
     *
     * @return void
     */
    protected static function showSearchAppliedFilters(&$tbody, &$search_request, $result, $model, $column_span = 1)
    {
        self::pagination($result, $search_request);

        // Applied filters
        if (count($search_request)) {
            $filters = (new $model())->getAppliedFiltersArray($search_request['filters']);

            //if (property_exists($model, 'mode_active')) {
            //    if (array_has($search_request, 'mode') && array_get($search_request, 'mode') > $model::$mode_active) {
            //        $mode_name = (array_get($search_request, 'mode') == $model::$mode_archived) ? 'archived' : 'removed';
            //        $filters[] = '<strong>'.$model::getFilterModelName().'</strong> <em>is</em> <strong>'.$mode_name.'</strong>';
            //    }
            //}
            if (count($filters) || isset($search_request['saved_filter']) || isset($search_request['page'])) {
                $row_html = '';
                if (count($filters)) {
                    $row_html .= 'Filtering by: '.implode('; ', $filters).'. ';
                }

                if ($search_request['page'] > 0 && $search_request['paginate_total'] > 0 && $search_request['paginate_last_page'] > 1) {
                    $row_html .= 'Showing page '.$search_request['paginate_current_page'].' of '.$search_request['paginate_last_page'].'. ';
                    if ($search_request['paginate_last_page'] > 1) {
                        $row_html .= $search_request['paginate_per_page'].' records per page.';
                    }
                }

                if (count($filters) && isset($search_request['saved_filter'])) {
                    $row_html .= 'Active saved filter - <strong class="saved-filter-name">'.$search_request['saved_filter']['name'].'</strong>.';
                }

                if (count($filters)) {
                    $row_html .= Html::div(
                        Html::a('Save')->addClass('btn btn-xs btn-info action-save-filter')->href('#action-save-filter')->data('toggle', 'modal')
                        .' '.
                        Html::a('Change')->addClass('btn btn-xs btn-success action-change-filter')->scriptLink('Change')
                        .' '.
                        Html::a('Clear')->addClass('btn btn-xs btn-warning action-cancel-filter')->scriptLink('Cancel')
                    )->addClass('pull-right');
                }

                if (!empty($row_html)) {
                    $tr = $tbody->tr();
                    $tr->td(['colspan' => $column_span], $row_html);
                }
            }
        }
    }

    /**
     * Get the pagination values.
     *
     * @return void
     */
    private static function pagination(&$result, &$search_request)
    {
        $search_request['paginate_count'] = $result->count();
        if (method_exists($result, 'currentPage')) {
            $search_request['paginate_current_page'] = $result->currentPage();
            $search_request['paginate_has_more_pages'] = $result->hasMorePages();
            $search_request['paginate_last_page'] = $result->lastPage();
            $search_request['paginate_per_page'] = $result->perPage();
            $search_request['paginate_total'] = $result->total();
        } else {
            $search_request['page'] = 0;
            $search_request['paginate_current_page'] = 1;
            $search_request['paginate_has_more_pages'] = false;
            $search_request['paginate_last_page'] = 1;
            $search_request['paginate_per_page'] = 1;
            $search_request['paginate_total'] = $result->count();
        }
    }

    /**
     * Show no results available.
     *
     * @param \Tag   &$tbody
     * @param int    $count
     * @param array  $search_request
     * @param string $name
     * @param int    $column_span
     * @param array  $config
     *
     * @return void
     */
    protected static function checkSearchResults($table, $result, &$search_request, $name, $column_span = 1, $config = [])
    {
        self::pagination($result, $search_request);

        $thead = false;
        if (is_array($tbody = $table)) {
            list($thead, $tbody) = $table;
        }

        if (isset($search_request['paginate_total']) && $search_request['paginate_total'] == 0 || count($result) == 0) {
            if (isset($search_request['filters']) && count($search_request['filters'])) {
                $template = 'No <strong>%s</strong> can be found with the <strong>applied filters</strong>';
                if (isset($config['attached_with_filter_no_results']) && $config['search_tab'] == $config['attached_tab']) {
                    $template = $config['attached_with_filter_no_results'];
                } elseif (isset($config['unattached_with_filter_no_results']) && $config['search_tab'] != $config['attached_tab']) {
                    $template = $config['unattached_with_filter_no_results'];
                } elseif (isset($config['with_filter_no_results'])) {
                    $template = $config['with_filter_no_results'];
                }
            } else {
                $template = 'No <strong>%s</strong> exist';
                if (isset($config['attached_no_filter_no_results']) && $config['search_tab'] == $config['attached_tab']) {
                    $template = $config['attached_no_filter_no_results'];
                } elseif (isset($config['unattached_no_filter_no_results']) && $config['search_tab'] != $config['attached_tab']) {
                    $template = $config['unattached_no_filter_no_results'];
                } elseif (isset($config['no_filter_no_results'])) {
                    $template = $config['no_filter_no_results'];
                }
            }

            $row_html = sprintf($template, $name);

            $tr = $tbody->tr();
            $tr->td(
                ['colspan' => $column_span, 'style' => 'line-height: 50px;text-align:center;'],
                (string) Html::span($row_html.'.', ['style' => ''])
            );
        }

        $thead = ($thead !== false) ? $thead->prepare(['ignore_tags' => 'thead']) : '';
        $tbody = $tbody->prepare(['ignore_tags' => 'tbody']);

        // Prepare suitable result
        return [
            'thead' => $thead,
            'tbody' => $tbody,
        ];
    }

    /**
     * Save the current search to session.
     *
     * @param string $controller_name
     * @param string $distinct_session
     * @param array  $response
     *
     * @return void
     */
    protected static function setSearchAppliedFilters($controller_name, $distinct_session, $response)
    {
        $distinct_session = (!empty($distinct_session)) ? '_'.$distinct_session : '';
        $session_name = str_replace(['.', '-'], '_', $controller_name.'_search_filters'.$distinct_session);
        session([$session_name => $response]);
    }

    /**
     * Update response and return.
     *
     * @param string     $controller_name
     * @param string     $distinct_session
     * @param array      $search_request
     * @param array      $response
     * @param bool|array $extra_response
     *
     * @return array
     */
    protected static function returnSearchResult($controller_name, $distinct_session, $search_request, $response, $extra_response = false)
    {
        unset($search_request['rows']);

        // Save filters to session
        static::setSearchAppliedFilters($controller_name, $distinct_session, $search_request);

        if ($search_request !== false) {
            $response['search'] = $search_request;

            if ($response['search']['paginate_total'] > 0) {
                $response['count'] = $response['search']['paginate_total'].' '.str_plural('result', $response['search']['paginate_total']).', '.$response['search']['paginate_last_page'].' '.str_plural('page', $response['search']['paginate_last_page']);
            } else {
                $response['count'] = 'No results found.';
            }

            $response['total_records'] = $response['search']['paginate_total'];

            $response['left_arrow'] = true;
            $response['left_arrow_page'] = 0;
            $response['right_arrow'] = true;
            $response['right_arrow_page'] = 0;

            if ($response['search']['paginate_current_page'] == 1) {
                $response['left_arrow'] = false;
                $response['left_arrow_page'] = 0;
            } elseif ($response['search']['paginate_current_page'] > 1) {
                $response['left_arrow_page'] = $response['search']['paginate_current_page'] - 1;
            }

            if ($response['search']['paginate_last_page'] == $response['search']['paginate_current_page'] || $response['search']['paginate_last_page'] === 0) {
                $response['right_arrow'] = false;
                $response['right_arrow_page'] = 0;
            } elseif ($response['search']['paginate_has_more_pages']) {
                $response['right_arrow_page'] = $response['search']['paginate_current_page'] + 1;
            }

            $response['items_per_page'] = '';
            foreach (static::$default_take_options as $value) {
                $response['items_per_page'] .= '<li>'.Html::a()->scriptLink('Show '.$value)->data('mode', $value)->text('Show '.$value).'</li>';
            }
        }

        if ($extra_response instanceof \Closure) {
            $response = $extra_response($response);
        }

        return $response;
    }

    /**
     * Load a filter and return search results.
     *
     * @param AppModel    $model
     * @param ModelFilter $model_filter
     *
     * @return array
     */
    public function loadFilter(HttpRequest $request, ModelFilter $model_filter)
    {
        $model_name = 'App\\Models\\'.$request->get('model');
        $method_name = $request->get('method');

        $current_filters = ((array) json_decode($model_filter->filter));

        request()->replace(['filters' => $current_filters]);

        // Get search results
        $result = $this->$method_name();

        $result['advanced_filters'] = '';
        $result['lookup'] = '';
        $result['search']['saved_filter'] = [
            'uuid'        => $model_filter->uuid,
            'name'        => $model_filter->name,
            'description' => $model_filter->description,
            'is_public'   => $model_filter->is_public,
        ];

        // Allocate lookup value
        if (isset($current_filters['lookup'][0][1])) {
            $result['lookup'] = $current_filters['lookup'][0][1];
        }

        // Generate the added filters
        $model = (new $model_name());

        $filter_types = $model->getFilterTypes();
        $filters = $model->getFilterAttributes();

        foreach ($filter_types as $type) {
            $operator_options[$type] = $model->getFilterOperators($type);
        }

        $list_filter_options = [];

        $filter_options = [['', '']];
        foreach ($filters as $filter_name => $filter_settings) {
            if (!isset($filter_settings['search_tab_only']) && $filter_name !== 'models') {
                $filter_options[] = [$filter_name.'|'.$filter_settings['filter'], $filter_settings['name']];
            }
            if ($filter_settings['filter'] == 'list') {
                $method = $filter_settings['method'];
                $list_filter_options[$filter_name] = $model->$method();
            }
        }

        // Load any saved filters
        $original_model_filter_options = ModelFilter::active()->get();
        $model_filter_options[] = ['', ''];
        foreach ($original_model_filter_options as $filter_option) {
            $model_filter_options[] = [$filter_option->uuid, $filter_option->name];
        }

        foreach ($current_filters as $filter_name => $applied_filters) {
            $filter_settings = ['search' => ''];
            if (isset($filters[$filter_name])) {
                $filter_settings = $filters[$filter_name];
            }
            if (!empty($filter_settings)) {
                $placeholder__count = 0;
                foreach ($applied_filters as $filter) {
                    if (empty($filter_settings['search_tab_only'])) {
                        $result['advanced_filters'] .= view('common.module.content.search.filter', [
                            'placeholder_id'   => $filter_name.'_'.$placeholder__count,
                            'type'             => $filter_settings['filter'],
                            'template'         => false,
                            'filter'           => $filter,
                            'filter_name'      => $filter_name,
                            'filter_settings'  => $filter_settings,
                            'operator_options' => $operator_options[$filter_settings['filter']], ]
                        );
                        $placeholder__count++;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Run a standard sub search.
     *
     * @param array &$view_data
     * @param array $config
     * @param Model $model
     *
     * @return void
     */
    public function runStandardSubSearch(&$view_data, $config, $model)
    {
        foreach ($config as $config_entry) {
            list($page, $name, $variable, $method, $class, $view_settings, $search_settings) = array_pad($config_entry, 7, null);

            $variable .= '_search';
            $method .= 'Search';

            $search_result = $this->$method(false, $search_settings);

            $view_data[$variable] = [
                'result'   => (new SearchViewResult()),
                'setup'    => (new SearchViewOptions()),
                'settings' => $search_settings,
            ];

            $view_data[$variable]['result']->setArray($search_result);

            $search_request_path = (array_has($view_settings, 'search-request-path')) ? array_get($view_settings, 'search-request-path') : '/'.$page.'/'.((array_get($view_settings, 'no_uuid', false) ? '' : $model->uuid.'/')).$method.'Result';

            $view_data[$variable]['setup']
                ->set('search.layout-style', 'inline')
                ->set('search.name', $name.'-search')
                ->set('search.search_request', $search_request_path)
                ->set('search.controller', static::class)
                ->set('search.base', $page)
                ->set('search.method', $method)
                ->set('search.model', $class)
                ->set('search.filters', array_get($search_result, 'search.filters', []))
                ->set('tab.search.title', 'Results')
                ->set('tab.advanced.show', true)
                ->set('results.name', $name)
                ->set('colgroup.total', 1)
                ->set('search.show', true)
                ->set('search.0.text', Html::createElement('input')->name('lookup_value1[]')->type('text')->placeholder('Search by name + enter')->addClass('input form-control search-filter-field')->value(isset($search_result['search']['filters']['lookup']) ? $search_result['search']['filters']['lookup'][0][1] : ''));

            foreach ((array) $view_settings as $key => $value) {
                $view_data[$variable]['setup']->set($key, $value);
            }
        }
    }
}
