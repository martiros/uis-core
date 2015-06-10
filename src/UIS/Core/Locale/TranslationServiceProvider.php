<?php

namespace UIS\Core\Locale;

use Illuminate\Support\ServiceProvider;
use UIS\Core\Locale\JsFileLoader;
use UIS\Core\Locale\LanguageManager;
use Config;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->app->singleton(
            'translator',
            function ($app) {
                $loader = $app['translation.loader'];

                // When registering the translator component, we'll need to set the default
                // locale as well as the fallback locale. So, we'll grab the application
                // configuration so we can easily get both of these values from there.
                $locale = $app['config']['app.locale'];

                $trans = new LanguageManager($loader, $locale);

                $trans->setFallback($app['config']['app.fallback_locale']);

                return $trans;
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
        $this->app->singleton(
            'translation.loader',
            function ($app) {
                $path = Config::get('app.lang_path');
                if (empty($path)) {
                    return new FileLoader($app['files'], $app['path.lang']);
                }
                return new JsFileLoader($app['files'], $path);
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
        return ['translator', 'translation.loader'];
    }
}
