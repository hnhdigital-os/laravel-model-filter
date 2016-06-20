# Laravel Model Traits Collection

## Installation

Require this package in your `composer.json` file:

`"bluora/laravel-model-dynamic-filter": "dev-master"`

Then run `composer update` to download the package to your vendor directory.

## Usage

```php
use Bluora\\LarvelModelDynamicFilter\\DynamicFilterTrait;

class User extends Model
{
    use DynamicFilterTrait;

    protected $default_order_by = 'name';
    protected $default_order_direction = 'asc';
}
```
