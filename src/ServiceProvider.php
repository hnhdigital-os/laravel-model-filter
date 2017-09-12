<?php

namespace HnhDigital\LaraveModelFilter;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use View;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Views/', 'dynamic_filter');
        view()->composer('dynamic_filter::search', 'HnhDigital\LaraveModelFilter\Composers\SearchPage');
        view()->composer('dynamic_filter::filter_list', 'HnhDigital\LaraveModelFilter\Composers\SearchFilterList');
        view()->composer('dynamic_filter::filter_list_lookup', 'HnhDigital\LaraveModelFilter\Composers\SearchFilterList');
    }
}
