<?php namespace UIS\Core;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UIS\Core\Locale\LanguageManager;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
//    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publish();
        $this->registerLogger();
//        $this->package('u-is/core');
        require_once __DIR__ . '/../../routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
       // $this->commands('App\Console\Commands\CheckLocaleCommand');

        $this->registerLoader();

        $this->app->bindShared(
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
        $this->app->singleton(
            'uis.app',
            function () {
                $app = new \UIS\Core\Foundation\Application();
                return $app;
            }
        );

        $this->registerAppProfiler();
    }

    protected function registerAppProfiler()
    {
        if (App::environment() === 'testing') {
            return;
        }
//        App::before(
//            function () {
        \Carbon\Carbon::setToStringFormat(\Carbon\Carbon::ISO8601);
        $_SERVER['REQUEST_TIME_FLOAT'] = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(
            true
        );
        $_SERVER['APP_START_TIME_FLOAT'] = microtime(true);
        app('uis.app')->profileStart();
//            }
//        );

        register_shutdown_function(
            function () {
                $_SERVER['APP_END_TIME_FLOAT'] = microtime(true);
                app('uis.app')->profileEnd();
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
                    return new \Illuminate\Translation\FileLoader($app['files'], $path);
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

    protected function registerLogger()
    {
        App::resolving(
            function (Logger $monolog, $app) {
                $handlers = $monolog->getHandlers();
                foreach ($handlers as $handler) {
                    if (!$handler instanceof StreamHandler) {
                        continue;
                    }
                    $handler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, true));
                }
            }
        );
    }


    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    protected function publish()
    {
        $this->publishMigrations();
    }

    protected function publishMigrations()
    {
        $pathToPackage = __DIR__ . '/../..';

        // Publish migrations
        $this->publishes([ $pathToPackage . '/database/migrations' => base_path('/database/migrations')], 'migrations');

        // Publish seeds
        $this->publishes([ $pathToPackage . '/database/seeds' => base_path('/database/seeds')], 'migrations');
    }
}