<?php namespace UIS\Core\Page;

use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('page', 'UIS\Core\Page\PageContainer');
        $this->app->singleton('uis.core.page.scripts', 'UIS\Core\Page\Scripts');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('page', 'uis.core.page.scripts');
    }

}
