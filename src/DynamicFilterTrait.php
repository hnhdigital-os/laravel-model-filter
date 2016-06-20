<?php

namespace Bluora\LaravelModelDynamicFilter;

trait DynamicFilterTrait
{

    /**
     * Filter types.
     * @var array
     */
    protected static $attribute_filter_types = [
        'string',
        'number',
        'date',
        'boolean',
        'list'
    ];

    /**
     * String operators.
     * @var array
     */
    protected static $string_operators = [
        '*=*' => ['value' => '*=*', 'name' => 'Contains'],
        '*!=*' => ['value' => '*!=*', 'name' => 'Not contain'],
        '=' => ['value' => '=', 'name' => 'Equals'],
        '!=' => ['value' => '!=', 'name' => 'Not equal'],
        '=*' => ['value' => '=*', 'name' => 'Begins with'],
        '!=*' => ['value' => '!=*', 'name' => 'Does not begin with'],
        '*=' => ['value' => '*=', 'name' => 'Ends with'],
        '*!=' => ['value' => '*!=', 'name' => 'Does not end with'],
        'IN' => ['value' => 'IN', 'name' => 'In...'],
        'NOT_IN' => ['value' => 'NOT_IN', 'name' => 'Not in...'],
        'EMPTY' => ['value' => 'EMPTY', 'name' => 'Is empty'],
        'NOT_EMPTY' => ['value' => 'NOT_EMPTY', 'name' => 'Is not empty']
    ];

    /**
     * Number operators.
     * @var array
     */
    protected static $number_operators = [
        '=' => ['value' => '=', 'name' => 'Equals'],
        '!=' => ['value' => '!=', 'name' => 'Not equals'],
        '>' => ['value' => '>', 'name' => 'Greater than'],
        '>=' => ['value' => '>=', 'name' => 'Greater than and equal to'],
        '<=' => ['value' => '<=', 'name' => 'Less than and equal to'],
        '<' => ['value' => '<', 'name' => 'Less than'],
        'IN' => ['value' => 'IN', 'name' => 'In...'],
        'NOT_IN' => ['value' => 'NOT_IN', 'name' => 'Not in...'],
    ];

    /**
     * Date operators.
     * @var array
     */
    protected static $date_operators = [
    ];

    /**
     * Boolean operators.
     * @var array
     */
    protected static $boolean_operators = [
        '1' => ['value' => '1', 'name' => 'True'],
        '0' => ['value' => '0', 'name' => 'False'],
    ];

    /**
     * List operators.
     * @var array
     */
    protected static $list_operators = [
        'IN' => ['value' => 'IN', 'name' => 'In selected'],
        'NOT_IN' => ['value' => 'NOT_IN', 'name' => 'Not in selected'],
    ];

    /**
     * Return the delcared attributes on this model.
     * 
     * @return array
     */
    public static function getFilterTypes()
    {
        if (isset(static::$attribute_filter_types) && is_array(static::$attribute_filter_types)) {
            return static::$attribute_filter_types;
        }
        return [];
    }

    /**
     * Return the delcared attributes on this model.
     * 
     * @return array
     */
    public function getFilterModelName()
    {
        $model = (new static);
        if (isset($model->filter_name)) {
            return $model->filter_name;
        }
        return $model->getTable();
    }

    /**
     * Return the delcared attributes on this model.
     * 
     * @return array
     */
    public static function getFilterAttributes($first_call = true)
    {
        $model = (new static);
        if (isset($model->filter_attributes) && is_array($model->filter_attributes)) {
            $filters = $model->filter_attributes;


            foreach ($filters as $key => &$filter_setting) {
                if (isset($filter_setting['name']) && isset($filter_setting['attribute']) && isset($filter_setting['filter'])) {
                    $model_name = $model->getFilterModelName();
                    $filter_setting['name'] = $model_name.': '.$filter_setting['name'];
                    $filter_setting['method'] = 'self';
                    $filter_setting['filter_name'] = $key;
                } else {
                    unset($filters[$key]);
                }
                unset($filter_setting);
            }
            if ($first_call) {
                foreach (static::getFilterRelationships() as $method => $model_class) {
                    if ($model_class !== static::class) {
                        $related_model = (new $model_class);
                        $model_filters = $related_model->getFilterAttributes(false);
                        foreach ($model_filters as $filter_name => $filter_setting) {
                            $filter_setting['source'] = $model_class;
                            $filter_setting['method'] = $method;
                            $filter_setting['filter_name'] = $method.'__'.$filter_name;
                            $filters[$method.'__'.$filter_name] = $filter_setting;
                        }
                    }
                }
            }
            return $filters;
        }
        return [];
    }

    /**
     * Return the delcared relationships on this model.
     * 
     * @return array
     */
    public static function getFilterRelationships()
    {
        $model = (new static);
        if (isset($model->filter_relationships) && is_array($model->filter_relationships)) {
            return $model->filter_relationships;
        }
        return [];
    }

    /**
     * Return a text list of the applied filters.
     *
     * @return array
     */
    public static function getAppliedFiltersArray($search_filters)
    {
        $result = [];
        foreach (static::getFilterAttributes() as $filter_name => $filter_settings) {
            if (isset($search_filters[$filter_name]) && is_array($search_filters[$filter_name])) {
                $filters = [];
                foreach ($search_filters[$filter_name] as $value) {
                    // Boolean
                    if (empty($value[1])) {
                        $filters[] = 'is <em>'.strtolower(static::getFilterOperators($filter_settings['filter'], $value[0])['name']).'</em>';
                    }
                    // String or number
                    elseif (!empty(trim($value[1]))) {
                        $filters[] = '<em>'.strtolower(static::getFilterOperators($filter_settings['filter'], $value[0])['name']).'</em> <strong>'.$value[1].'</strong>';
                    } 
                }
                if (count($filters)) {
                    $result[] = '<strong>'.$filter_settings['name'].'</strong> '.implode(', ', $filters);
                }
            }
        }
        return $result;
    }

    /**
     * Applies the filters to the model (and model's relationships)
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyAttributeFilters($query, $search_filters)
    {
        if (is_array($search_filters) && !empty($search_filters)) {

            // Get available filters
            $filter_attributes = static::getFilterAttributes();
            $total_relationship_models = 0;

            // Group the filters by the the model's relationship method.
            $filters_by_model = [];
            foreach ($search_filters as $filter_name => $filters) {
                if (isset($filter_attributes[$filter_name]) && !empty($filter_attributes[$filter_name])) {
                    $source = explode('__', $filter_name);
                    $method_name = (count($source) == 1) ? 'self' : $source[0];
                    //$filter_name = ($method_name === 'self') ? $filter_name : $source[1];
                    $filters_by_model[$method_name][$filter_name] = $filters;
                    if ($method_name !== 'self') {
                        $total_relationship_models++;
                    }
                }
            }

            // Filters provided include fields against other models
            // Attach the model and limit that by the search filters provided.
            if ($total_relationship_models) {
                $relationships = static::getFilterRelationships();
                $query_connections = [];
                foreach ($relationships as $method_name => $model_class) {
                    if (isset($filters_by_model[$method_name])) {
                        $filters = $filters_by_model[$method_name];
                        $query->whereHas($method_name, function($query) use ($filter_attributes, $filters) {
                            foreach ($filters as $filter_name => $filter_requests) {
                                static::processAttributeFilter($query, $filter_attributes[$filter_name], $filter_requests);
                            }
                        });
                    }
                }
            }

            // Process direct filters to this model.
            if (isset($filters_by_model['self'])) {
                foreach ($filters_by_model['self'] as $filter_name => $filters) {
                    $query = static::processAttributeFilter($query, $filter_attributes[$filter_name], $filters);
                }
            }
        }
        return $query;
    }

    /**
     * Applies the filters to the builder query.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function processAttributeFilter($query, $filter_setting, $filter_requests)
    {
        $model = (new static);
        foreach ($filter_requests as $filter_request) {
            // Clean inputs
            (!isset($filter_request[1])) ? $filter_request[1] = '' : false;
            (!isset($filter_request[2])) ? $filter_request[2] = '' : false;
            list($operator, $value1, $value2) = $filter_request;

            // No operator provided, use the model default, or equals.
            if (empty($operator) && isset($this->filter_default_operator)) {
                $operator = $this->filter_default_operator;
            } elseif (empty($operator)) {
                $operator = '=';
            }

            // Select filter for this attribute
            switch ($filter_setting['filter']) {
                case 'string':
                    if (!empty(trim($value1))) {
                        $query = $model->applyStringFilter($query, $filter_setting, $operator, $value1);
                    }
                    break;
                case 'number':
                    if (!empty(trim($value1))) {
                        $query = $model->applyNumberFilter($query, $filter_setting, $operator, $value1);
                    }
                    break;
                case 'boolean':
                    $query = $model->applyBooleanFilter($query, $filter_setting, $operator);
                    break;
                case 'datetime':
                    // @todo
                    break;
            }
        }
        return $query;
    }

    /**
     * Apply the string filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $operator
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyStringFilter($query, $filter_settings, $operator, $value)
    {   
        if ($this->validateStringOperators($operator, $value)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $value, $operator) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $operator, $value);
                });
            } else {
                $query->where($filter_settings['attribute'], $operator, $value);
            }
        }
        return $query;
    }

    /**
     * Validate the provided string filter option.
     *
     * @param  string &$operator
     * @param  string &$value
     * @return boolean
     */
    private function validateStringOperators(&$operator, &$value)
    {
        switch ($operator) {
            case '=':
            case '!=':
                return true;
            case '*=*':
            case '*!=*':
                $value = '%'.$value.'%';
                $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                $operator .= 'LIKE';
                return true;
            case '*=':
            case '*!=':
                $value = '%'.$value;
                $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                $operator .= 'LIKE';
                return true;
            case '=*':
            case '!=*':
                $value = $value.'%';
                $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                $operator .= 'LIKE';
                return true;
        }
        return false;
    }

    /**
     * Apply the number filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $operator
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyNumberFilter($query, $filter_settings, $operator, $value)
    {   
        if ($this->validateNumberOperators($operator, $value)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $operator, $value) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $operator, $value);
                });
            } else {
                $query->where($filter_settings['attribute'], $operator, $value);
            }
        }
        return $query;
    }

    /**
     * Validate the provided number filter option.
     *
     * @param  string &$operator
     * @param  string &$value
     * @return boolean
     */
    private function validateNumberOperators(&$operator, &$value)
    {
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<=':
            case '<':
                return true;
        }
        return false;
    }

    /**
     * Apply the list filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $operator
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyListFilter($query, $filter_settings, $operator)
    {
        $value = '';
        if ($this->validateBooleanOperators($operator, $value)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $operator, $value) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $operator, $value);
                });
            } else {
                $query->where($filter_settings['attribute'], $operator);
            }
        }
        return $query;
    }

    /**
     * Validate the provided list filter option.
     *
     * @param  string &$operator
     * @param  string &$value
     * @return boolean
     */
    private function validateListOperators(&$operator, &$value)
    {
        switch ($operator) {
            case 'IN':
                $operator = 'IN';
                return true;
                break;
            case 'NOT_IN':
                $operator = 'NOT IN';
                return true;
                break;
        }
        return false;
    }

    /**
     * Apply the boolean filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $operator
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyBooleanFilter($query, $filter_settings, $operator)
    {
        $value = '';
        if ($this->validateBooleanOperators($operator, $value)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $operator, $value) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $operator, $value);
                });
            } else {
                $query->where($filter_settings['attribute'], $operator);
            }
        }
        return $query;
    }

    /**
     * Validate the provided boolean filter option.
     *
     * @param  string &$operator
     * @param  string &$value
     * @return boolean
     */
    private function validateBooleanOperators(&$operator, &$value)
    {
        switch ($operator) {
            case '1':
                $operator = '=';
                $value = '1';
                return true;
                break;
            case '0':
                $operator = '!=';
                $value = '0';
                return true;
                break;
        }
        return false;
    }

    /**
     * Apply the filter using multiple attributes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $operator
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilterAttributeArray($query, $attribute_list, $operator, $value)
    {
        foreach ($attribute_list as $attribute_name) {
            if (stripos($operator, 'NOT') === false && stripos($operator, '!') === false) {
                $query->orWhere($attribute_name, $operator, $value);
            } else {
                $query->where($attribute_name, $operator, $value);
            }
        }
        return $query;
    }

    /**
     * Get an string|number|date operators as array|string.
     *
     * @param  $type string|number|date
     * @return array|string|null
     */
    public static function getFilterOperators($type, $operator = false)
    {
        $source = $type.'_operators';
        if ($operator !== false && isset(static::$$source[$operator])) {
            return static::$$source[$operator];
        } elseif ($operator !== false) {
            return null;
        }
        return static::$$source;
    }
}
