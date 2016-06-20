# Laravel Model Dynamic Attribute Filtering

## Installation

Require this package in your `composer.json` file:

`"bluora/laravel-model-dynamic-filter": "dev-master"`

Then run `composer update` to download the package to your vendor directory.

## Usage

```php
use Bluora\LarvelModelDynamicFilter\DynamicFilterTrait;

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
