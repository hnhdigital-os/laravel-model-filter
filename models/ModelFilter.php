<?php

namespace App\Models;

use Bluora\LaravelDynamicFilter\Traits\ModelTrait as DynamicFilterTrait;
use Bluora\LaravelModelChangeTracking\LogChangeTrait;
use Bluora\LaravelModelChangeTracking\LogStateChangeTrait;
use Bluora\LaravelModelTraits\ModelStateTrait;
use Bluora\LaravelModelTraits\ModelValidationTrait;
use Bluora\LaravelModelTraits\OrderByTrait;
use Bluora\LaravelModelUuid\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelFilter extends BaseModel
{
    use LogChangeTrait, LogStateChangeTrait, DynamicFilterTrait, ModelStateTrait, ModelValidationTrait, OrderByTrait, UuidTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'model_filter';

    /**
     * The attributes of this model + validation rules
     * attribute => [new, existing, format].
     *
     * @var array
     */
    protected $attribute_rules = [
        'id'          => [false, false, 'integer'],
        'model_id'    => ['bail|required', false, 'integer'],
        'name'        => ['bail|required', '', 'string|max:255'],
        'description' => ['', '', 'string'],
        'filter'      => ['', '', 'json'],
        'owner_id'    => ['', false, 'integer'],
        'is_public'   => ['required', '', 'boolean'],
    ];

    /**
     * The attributes that require casting.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'filter'      => 'json',
        'owner_id'    => 'integer',
        'is_public'   => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'filter',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * A nice name for the model.
     *
     * @var array
     */
    protected $filter_name = 'Filter';

    /**
     * A list of relationships for this model.
     *
     * @var array
     */
    protected $filter_relationships = [

    ];

    /**
     * A list of attributes that can be used for the advanced filtering trait.
     *
     * @var array
     */
    protected $filter_attributes = [
        'lookup' => [
            'name'            => 'Name',
            'attribute'       => ['model_filter.name', 'model_filter.filter'],
            'filter'          => 'string',
            'search_tab_only' => true,
        ],
    ];

    /**
     * Name used for recording in the log.
     *
     * @var array
     */
    public function getLogNameAttribute()
    {
        return $this->name;
    }
}
