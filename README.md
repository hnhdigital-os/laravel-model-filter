# Laravel Dynamic Filtering

Provides the ability to set filtering attributes and model relationship filtering.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-filter/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-filter) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-model-filter/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-filter) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-filter/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-filter) [![License](https://poser.pugx.org/hnhdigital-os/laravel-model-filter/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-filter)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-model-filter.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-model-filter) [![StyleCI](https://styleci.io/repos/61543411/shield?branch=master)](https://styleci.io/repos/61543411) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-filter) 

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Install

Via composer:

`$ composer require hnhdigital-os/laravel-model-filter ~1.0`

## Usage

```php
use HnhDigital\LaravelModelFilter\Traits\ModelTrait;

class User extends Model
{
    use ModelTrait;

    /**
     * A nice name for the model.
     *
     * @var array
     */
    protected $filter_name = 'User';

    /**
     * A list of relationships for this model.
     *
     * @var array
     */
    protected $filter_relationships = [
        'Organization' => 'App\Models\Organization'
    ];

    /**
     * A list of attributes that can be used for the advanced filtering trait.
     *
     * @var array
     */
    protected $filter_attributes = [
        'lookup' => [
            'name' => 'Name or email',
            'attribute' => ['first_name', 'last_name', 'email', 'organization.name'],
            'filter' => 'string',
            'search_tab_only' => true,
            'with' => 'organization'
        ],
        'first_name' => ['name' => 'First name', 'attribute' => 'first_name', 'filter' => 'string'],
        'last_name' => ['name' => 'Last name', 'attribute' => 'last_name', 'filter' => 'string'],
        'email' => ['name' => 'Email', 'attribute' => 'email', 'filter' => 'string'],
        'is_active' => ['name' => 'Active user', 'attribute' => 'is_active', 'filter' => 'boolean']
    ];
}
```

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-model-filter/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/therocis)
* [All Contributors](https://github.com/hnhdigital-os/laravel-model-filter/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-model-filter/blob/master/LICENSE) for more information.
