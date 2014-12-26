<?php namespace UIS\Core;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('u-is/core');
        require_once __DIR__.'/../../routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->app->bindShared(
            'translator',
            function ($app) {
                $loader = $app['translation.loader'];

                // When registering the translator component, we'll need to set the default
                // locale as well as the fallback locale. So, we'll grab the application
                // configuration so we can easily get both of these values from there.
                $locale = $app['config']['app.locale'];

                $trans = new \UIS\Core\Locale\Language($loader, $locale);

                $trans->setFallback($app['config']['app.fallback_locale']);

                return $trans;
            }
        );

        $this->app->singleton(
            'uis.app',
            function () {
                $app = new \UIS\Core\Foundation\Application();
                return $app;
            }
        );

    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->bindShared(
            'translation.loader',
            function ($app) {
                $path = Config::get('app.lang_path');
                if (empty($path)) {
                    $path = $app['path'] . '/lang';
                }
                return new \UIS\Core\Locale\JsFileLoader($app['files'], $path);
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('translator', 'translation.loader', 'uis.app');
    }

}
