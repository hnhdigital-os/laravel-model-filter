# Laravel Dynamic Filtering

Provides the ability to set filtering attributes and model relationship filtering.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-dynamic-filter/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-dynamic-filter) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-dynamic-filter/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-dynamic-filter) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-dynamic-filter/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-dynamic-filter) [![License](https://poser.pugx.org/hnhdigital-os/laravel-dynamic-filter/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-dynamic-filter)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-dynamic-filter.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-dynamic-filter) [![StyleCI](https://styleci.io/repos/61543411/shield?branch=master)](https://styleci.io/repos/61543411) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-dynamic-filter) 

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Installation

Require this package in your `composer.json` file:

`"hnhdigital-os/laravel-dynamic-filter": "dev-master"`

Then run `composer update` to download the package to your vendor directory.

## Usage

```php
use Bluora\LaravelDynamicFilter\DynamicFilterTrait;

class User extends Model
{
    use DynamicFilterTrait;

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

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-dynamic-filter/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/therocis)
* [All Contributors](https://github.com/hnhdigital-os/laravel-dynamic-filter/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-dynamic-filter/blob/master/LICENSE) for more information.
