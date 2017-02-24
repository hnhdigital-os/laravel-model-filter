<?php

namespace Bluora\LaravelDynamicFilter\Traits;

use DB;
use Illuminate\Database\Query\Expression;

trait ModelTrait
{
    /**
     * Filter types.
     *
     * @var array
     */
    protected static $attribute_filter_types = [
        'string',
        'number',
        'date',
        'boolean',
        'list',
        'listLookup'
    ];

    /**
     * String operators.
     *
     * @var array
     */
    protected $string_operators = [
        '*=*'       => ['value' => '*=*', 'name' => 'Contains'],
        '*!=*'      => ['value' => '*!=*', 'name' => 'Not contain'],
        '='         => ['value' => '=', 'name' => 'Equals'],
        '!='        => ['value' => '!=', 'name' => 'Not equal'],
        '=*'        => ['value' => '=*', 'name' => 'Begins with'],
        '!=*'       => ['value' => '!=*', 'name' => 'Does not begin with'],
        '*='        => ['value' => '*=', 'name' => 'Ends with'],
        '*!='       => ['value' => '*!=', 'name' => 'Does not end with'],
        'IN'        => ['value' => 'IN', 'name' => 'In...', 'helper' => 'Separated by semi-colon'],
        'NOT_IN'    => ['value' => 'NOT_IN', 'name' => 'Not in...', 'helper' => 'Separated by semi-colon'],
        'EMPTY'     => ['value' => 'EMPTY', 'name' => 'Empty'],
        'NOT_EMPTY' => ['value' => 'NOT_EMPTY', 'name' => 'Not empty'],
        'NULL'      => ['value' => 'NULL', 'name' => 'NULL'],
        'NOT_NULL'  => ['value' => 'NOT_NULL', 'name' => 'Not NULL'],
    ];

    /**
     * Number operators.
     *
     * @var array
     */
    protected $number_operators = [
        '='         => ['value' => '=', 'name' => 'Equals'],
        '!='        => ['value' => '!=', 'name' => 'Not equals'],
        '>'         => ['value' => '>', 'name' => 'Greater than'],
        '>='        => ['value' => '>=', 'name' => 'Greater than and equal to'],
        '<='        => ['value' => '<=', 'name' => 'Less than and equal to'],
        '<'         => ['value' => '<', 'name' => 'Less than'],
        'IN'        => ['value' => 'IN', 'name' => 'In...', 'helper' => 'Separated by semi-colon'],
        'NOT_IN'    => ['value' => 'NOT_IN', 'name' => 'Not in...', 'helper' => 'Separated by semi-colon'],
        'EMPTY'     => ['value' => 'EMPTY', 'name' => 'Empty'],
        'NOT_EMPTY' => ['value' => 'NOT_EMPTY', 'name' => 'Not empty'],
        'NULL'      => ['value' => 'NULL', 'name' => 'NULL'],
        'NOT_NULL'  => ['value' => 'NOT_NULL', 'name' => 'Not NULL'],
    ];

    /**
     * Date operators.
     *
     * @var array
     */
    protected $date_operators = [
        // @todo
    ];

    /**
     * Boolean operators.
     *
     * @var array
     */
    protected $boolean_operators = [
        '1' => ['value' => '1', 'name' => 'True'],
        '0' => ['value' => '0', 'name' => 'False'],
    ];

    /**
     * List operators.
     *
     * @var array
     */
    protected $list_operators = [
        'IN'     => ['value' => 'IN', 'name' => 'In selected'],
        'NOT_IN' => ['value' => 'NOT_IN', 'name' => 'Not in selected'],
    ];

    /**
     * List operators.
     *
     * @var array
     */
    protected $list_lookup_operators = [
        'IN'     => ['value' => 'IN', 'name' => 'In selected'],
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
    public static function getFilterModelName()
    {
        $model = (new static());
        if (isset($model->filter_name)) {
            return $model->filter_name;
        }

        return $model->getTable();
    }

    /**
     * Return the delcared attributes on this model.
     *
     * @param bool $first_call
     *
     * @return array
     */
    public static function getFilterAttributes($first_call = true)
    {
        $model = (new static());

        if (isset($model->filter_attributes) && is_array($model->filter_attributes)) {
            $filters = $model->filter_attributes;

            foreach ($filters as $key => &$filter_detail) {
                if (isset($filter_detail['name']) && isset($filter_detail['attribute']) && isset($filter_detail['filter'])) {
                    $model_name = $model->getFilterModelName();
                    $filter_detail['attribute_name'] = $filter_detail['name'];
                    $filter_detail['name'] = $model_name.': '.$filter_detail['attribute_name'];
                    $filter_detail['method'] = 'self';
                    $filter_detail['filter_name'] = $key;
                    if (is_array($filter_detail['attribute'])) {
                        foreach ($filter_detail['attribute'] as $key => &$value) {
                            if ($value[0] === '{') {
                                $value = new Expression(substr($value, 1));
                            } elseif (strpos($value, '.') === false) {
                                $value = $model->getTable().'.'.$value;
                            }
                        }
                    } else {
                        if ($filter_detail['attribute'][0] === '{') {
                            $filter_detail['attribute'] = new Expression(substr($filter_detail['attribute'], 1));
                        } elseif (strpos($filter_detail['attribute'], '.') === false) {
                            $filter_detail['attribute'] = $model->getTable().'.'.$filter_detail['attribute'];
                        }
                    }
                } else {
                    unset($filters[$key]);
                }
                unset($filter_detail);
            }
            if ($first_call) {
                foreach ($model->getFilterRelationships() as $method_name => $settings) {
                    if (method_exists($model, $method_name)) {
                        $relation = $model->$method_name();
                        $model_class = get_class($relation->getRelated());

                        $related_model = (new $model_class());
                        $model_filters = $related_model->getFilterAttributes(false);
                        foreach ($model_filters as $filter_name => $filter_detail) {
                            if ($model_class !== static::class) {
                                if (isset($settings['name'])) {
                                    $filter_detail['name'] = $settings['name'].': '.$filter_detail['attribute_name'];
                                }
                                $filter_detail['source_method'] = $model_class;
                                $filter_detail['method'] = $method_name;
                                $filter_detail['filter_name'] = $method_name.'__'.$filter_name;
                                $filters[$filter_detail['filter_name']] = $filter_detail;
                            }
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
    public function getFilterRelationships()
    {
        $model = (new static());
        if (isset($model->filter_relationships) && is_array($model->filter_relationships)) {
            return $model->filter_relationships;
        }

        return [];
    }

    /**
     * Return a text list of the applied filters.
     *
     * @param array $search_request
     *
     * @return array
     */
    public function getAppliedFiltersArray($search_request)
    {
        $model = (new static());
        $result = [];
        foreach ($model->getFilterAttributes() as $filter_name => $filter_settings) {
            if (isset($search_request[$filter_name]) && is_array($search_request[$filter_name])) {
                $filters = [];
                foreach ($search_request[$filter_name] as $value) {

                    // List
                    if ($filter_settings['filter'] === 'listLookup') {
                        $source_method = 'getFilter'.$filter_settings['source'].'Options';
                        if (method_exists($model, $source_method)) {
                            $filter_value_options = $model->$source_method($value[1]);

                            $value_option = array_column($filter_value_options, 1);
                            $filters[] = '<em>in</em> <strong>'.implode(',', $value_option).'</strong>';
                        }
                    }

                    // Boolean
                    elseif (empty($value[1])) {
                        $filters[] = 'is <em>'.strtolower($model->getFilterOperators($filter_settings['filter'], $value[0])['name']).'</em>';
                    }

                    // String or number
                    elseif (!empty($value[1])) {
                        if (!is_array($value[1])) {
                            $value[1] = [$value[1]];
                        }

                        $filter_list = '';
                        foreach ($value[1] as &$filter_text) {
                            if (strlen($filter_text) > 50) {
                                $filter_text = '<a href="javascript:void(0);" onclick="$(this).find(\'.small-list\').toggle();$(this).find(\'.large-list\').toggle();"><span class="small-list" title="Expand to view full list" style="text-decoration:underline;">'.substr($filter_text, 0, 50).'...</span> <span class="large-list" style="display:none;">'.$filter_text.'</span></a>';
                            }
                        }

                        $filters[] = '<em>'.strtolower($model->getFilterOperators($filter_settings['filter'], $value[0])['name']).'</em> <strong>'.implode(',', $value[1]).'</strong>';
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
     * Applies the filters to the model (and model's relationships).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $search_request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyAttributeFilters($query, $search_request)
    {
        if (isset($search_request['filters']) && is_array($search_request['filters']) && !empty($search_request['filters'])) {

            // Get available filters
            $filter_attributes = $this->getFilterAttributes();
            $total_relationship_models = 0;

            // Group the filters by the the model's relationship method.
            $filters_by_model = [];
            foreach ($search_request['filters'] as $filter_name => $filters) {
                if (isset($filter_attributes[$filter_name]) && !empty($filter_attributes[$filter_name])) {
                    $source = explode('__', $filter_name);
                    $method_name = (count($source) == 1) ? 'self' : $source[0];
                    //$filter_name = ($method_name === 'self') ? $filter_name : $source[1];
                    $filters_by_model[$method_name][$filter_name] = $filters;

                    // Count the number of lookups against other models that we need to do.
                    if ($method_name !== 'self') {
                        $total_relationship_models++;
                    }

                    // Include the relevant relationships for this filter.
                    elseif (isset($filter_attributes[$filter_name]['with'])) {
                        $query = $query->modelJoin($filter_attributes[$filter_name]['with']);
                    }
                }
            }

            // Filters provided include fields against other models
            // Attach the model and limit that by the search filters provided.
            if ($total_relationship_models) {
                $relationships = $this->getFilterRelationships();
                $query_connections = [];
                foreach ($relationships as $method_name => $model_class) {
                    if (isset($filters_by_model[$method_name])) {
                        $filters = $filters_by_model[$method_name];
                        $query = $query->whereHas($method_name, function ($query) use ($filter_attributes, $filters) {
                            foreach ($filters as $filter_name => $filter_requests) {
                                $this->processAttributeFilter($query, $filter_attributes[$filter_name], $filter_requests);
                            }
                        });
                    }
                }
            }

            // Process direct filters to this model.
            if (isset($filters_by_model['self'])) {
                foreach ($filters_by_model['self'] as $filter_name => $filters) {
                    $query = $this->processAttributeFilter($query, $filter_attributes[$filter_name], $filters);
                }
            }
        }

        return $query;
    }

    /**
     * Applies the filters to the builder query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $filter_settings
     * @param array                                 $filter_requests
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function processAttributeFilter($query, $filter_setting, $filter_requests)
    {
        $model = (new static());
        foreach ($filter_requests as $filter_request) {
            // Clean inputs
            (!isset($filter_request[1])) ? $filter_request[1] = '' : false;
            (!isset($filter_request[2])) ? $filter_request[2] = '' : false;
            list($operator, $value1, $value2) = $filter_request;

            // User can override the field being checked
            if (!empty($value1) && $value1[0] === '#' && isset($model->attribute_rules)) {
                $available_attributes = array_keys($model->attribute_rules);
                $available_operators = array_keys($model->getFilterOperators('string'));
                $value1_array = explode(' ', $value1);
                $total_input = count($value1_array);

                if ($total_input >= 2) {
                    $override_attribute = array_shift($value1_array);
                    $override_operator = array_shift($value1_array);
                    $override_value1 = implode(' ', $value1_array);

                    if ($total_input == 2
                        && in_array($override_operator, ['EMPTY', 'NOT_EMPTY', 'NULL', 'NOT NULL'])) {
                        $filter_setting['attribute'] = substr($override_attribute, 1);
                        $operator = $override_operator;
                        $value1 = '';
                    } elseif ($total_input == 2) {
                        $operator = '=';
                        $value1 = $override_operator;
                    } elseif ($total_input > 2 && in_array($override_operator, $available_operators)) {
                        $filter_setting['attribute'] = substr($override_attribute, 1);
                        $operator = $override_operator;
                        $value1 = $override_value1;
                    }
                }
            }

            // User can override the operator inline
            if (empty($operator) || $operator === '*=*') {
                $value1_array = explode(' ', $value1);
                $check_operator = array_shift($value1_array);
                if (count($value1_array)) {
                    $check_operator = trim($check_operator);
                    if (!empty($check_operator)) {
                        $available_operators = array_keys($model->getFilterOperators($filter_setting['filter']));
                        if (in_array($check_operator, $available_operators)) {
                            $operator = $check_operator;
                            $value1 = implode(' ', $value1_array);
                        }
                    }
                }
            }

            // No operator provided, use the model default, or equals.
            if (empty($operator) && isset($model->filter_default_operator)) {
                $operator = $model->filter_default_operator;
            } elseif (empty($operator)) {
                $operator = '=';
            }

            $attribute = $filter_setting['attribute'];
            $method = 'where';
            $arguments = [];
            $positive = !(stripos($operator, '!') !== false || stripos($operator, 'NOT') !== false);

            if (static::validateOperators($filter_setting['filter'], $method, $arguments, $model, $filter_setting, $operator, $value1, $value2)) {
                if (is_array($attribute)) {
                    foreach ($attribute as &$value) {
                        $value = DB::raw($value);
                    }
                    $query = $query->where(function ($sub_query) use ($attribute, $method, $arguments, $positive) {
                        return static::applyFilterAttributeArray($sub_query, $attribute, $method, $arguments, $positive);
                    });
                } else {
                    if (is_array($arguments)) {
                        if (($method === 'whereIn' || $method === 'whereNotIn')
                            && empty($arguments[0])) {
                            break;
                        }
                        array_unshift($arguments, DB::raw($attribute));
                        $query = $query->$method(...$arguments);
                    } else {
                        $query = $query->$method($attribute.$arguments);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Validate the provided filter option.
     *
     * @param string &$method
     * @param array  &$arguments
     * @param Model  $model
     * @param array  $filter_setting
     * @param array  $operator
     * @param array  $value1
     * @param array  $value2
     *
     * @return bool
     */
    private static function validateOperators($filter, &$method, &$arguments, $model, $filter_setting, $operator, $value1, $value2)
    {
        // No space search.
        if (array_has($filter_setting, 'phone_search')) {
            $value1_numeric = str_replace(' ', '', $value1);
            if (is_numeric($value1_numeric)) {
                $new_value1 = '';
                for ($pos=0; $pos < strlen($value1); $pos++) { 
                    $new_value1 .= substr($value1_numeric, $pos, 1).'%';
                }
                $value1 = $new_value1;
            }
        }

        switch ($filter) {
            case 'string':
                switch ($operator) {
                    case '=':
                    case '!=':
                        $arguments = [$operator, $value1];

                        return true;
                    case '*=*':
                    case '*!=*':
                        $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                        $operator .= 'LIKE';
                        $arguments = [$operator, '%'.$value1.'%'];

                        return true;
                    case '*=':
                    case '*!=':
                        $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                        $operator .= 'LIKE';
                        $arguments = [$operator, '%'.$value1];

                        return true;
                    case '=*':
                    case '!=*':
                        $operator = (stripos($operator, '!') !== false) ? 'NOT ' : '';
                        $operator .= 'LIKE';
                        $arguments = [$operator, $value1.'%'];

                        return true;
                    case 'EMPTY':
                        $method = 'whereRaw';
                        $arguments = "=''";

                        return true;
                    case 'NOT_EMPTY':
                        $method = 'whereRaw';
                        $arguments = "!=''";

                        return true;
                    case 'IN':
                        $method = 'whereIn';
                        $arguments = [static::getListFromString($value1)];

                        return true;
                    case 'NOT_IN':
                        $method = 'whereIn';
                        $arguments = [static::getListFromString($value1)];

                        return true;
                    case 'NULL':
                        $method = 'whereNull';

                        return true;
                    case 'NOT_NULL':
                        $method = 'whereNotNull';

                        return true;
                }
                break;
            case 'number':
                switch ($operator) {
                    case '=':
                    case '!=':
                    case '>':
                    case '>=':
                    case '<=':
                    case '<':
                        $arguments = [$operator, $value1];

                        return true;
                    case 'EMPTY':
                        $method = 'whereRaw';
                        $arguments = "=''";

                        return true;
                    case 'NOT_EMPTY':
                        $method = 'whereRaw';
                        $arguments = "!=''";

                        return true;
                    case 'IN':
                        $method = 'whereIn';
                        $arguments = [static::getListFromString($value1)];

                        return true;
                    case 'NOT_IN':
                        $method = 'whereIn';
                        $arguments = [static::getListFromString($value1)];

                        return true;
                    case 'NULL':
                        $method = 'whereNull';
                        return true;
                    case 'NOT_NULL':
                        $method = 'whereNotNull';

                        return true;
                }
                break;
            case 'listLookup':
                if (isset($filter_setting['source'])) {
                    $method_lookup = 'getFilter'.$filter_setting['source'].'Result';
                    if (!empty($value1) && method_exists($model, $method_lookup)) {
                        $value1 = $model->$method_lookup($value1);
                    } else {
                        $value1 = [];
                    }
                } else {
                    $value1 = [];
                }
            case 'list':
                switch ($operator) {
                    case 'IN':
                        $method = 'whereIn';
                        $arguments = [$value1];

                        return true;
                    case 'NOT_IN':
                        $method = 'whereNotIn';
                        $arguments = [$value1];

                        return true;
                }
                break;
            case 'boolean':
                switch ($operator) {
                    case 1:
                    case '1':
                        $arguments = ['=', true];

                        return true;
                        break;
                    case 0:
                    case '0':
                        $arguments = ['=', false];

                        return true;
                        break;
                }
                break;
        }

        return false;
    }

    /**
     * Get array of values from an input string.
     *
     * @param  string $string
     *
     * @return array
     */
    private static function getListFromString($input)
    {
        $input = str_replace([',', ' '], ';', $input);
        $input = explode(';', $input);

        return array_filter(array_map('trim', $input));
    }

    /**
     * Apply the filter using multiple attributes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $attribute_list
     * @param array                                 $operator
     * @param string                                $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function applyFilterAttributeArray($query, $attribute_list, $method, $arguments, $positive = true)
    {
        if ($positive) {
            $method = 'or'.$method;
        }

        foreach ($attribute_list as $attribute) {
            $method_argument = $arguments;
            if (is_array($method_argument)) {
                array_unshift($method_argument, $attribute);
            } else {
                $method_argument = [$attribute.$method_argument];
            }
            $query = $query->$method(...$method_argument);
        }

        return $query;
    }

    /**
     * Get an string|number|date operators as array|string.
     *
     * @param string|number|date $type
     * @param bool               $operator
     *
     * @return array|string|null
     */
    public function getFilterOperators($type, $operator = false)
    {
        $source = snake_case($type).'_operators';
        if ($operator !== false && isset($this->$source[$operator])) {
            return $this->$source[$operator];
        } elseif ($operator !== false) {
            return;
        }

        return $this->$source;
    }

    /**
     * This determines the foreign key relations automatically to prevent the need to figure out the columns.
     *
     /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $relation_name
     * @param string                             $operator
     * @param string                             $type
     * @param bool                               $where
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeModelJoin($query, $relationships, $operator = '=', $type = 'left', $where = false)
    {
        if (!is_array($relationships)) {
            $relationships = [$relationships];
        }

        if (empty($query->columns)) {
            $query = $query->selectRaw('DISTINCT '.$this->getTable().'.*');
        }

        foreach ($relationships as $relation_name) {
            $relation = $this->$relation_name();
            $relation_class = basename(str_replace('\\', '/', get_class($relation)));

            if ($relation_class === 'HasOne') {
                $table = $relation->getRelated()->getTable();
            } else {
                $table = $relation->getTable();
            }

            $qualified_parent_key_name = $relation->getQualifiedParentKeyName();
            $foreign_key = $relation->getForeignKey();

            foreach (\Schema::getColumnListing($table) as $related_column) {
                $query = $query->addSelect(new Expression("`$table`.`$related_column` AS `$table.$related_column`"));
            }
            $query = $query->join($table, $qualified_parent_key_name, $operator, $foreign_key, $type, $where);

            if ($relation_class === 'BelongsToMany') {
                $related_relation = $relation->getRelated();
                $related_table = $related_relation->getTable();
                $related_foreign_key = $table.'.'.$related_relation->getForeignKey();
                $related_qualified_key_name = $related_relation->getQualifiedKeyName();
                $query = $query->join($related_table, $related_qualified_key_name, $operator, $related_foreign_key, $type, $where);
            }
        }
        $query->groupBy($this->getQualifiedKeyName());

        return $query;
    }
}
