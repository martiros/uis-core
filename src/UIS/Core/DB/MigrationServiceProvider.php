<?php
namespace UIS\Core\DB;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use UIS\Core\DB\Migrations\MigrationCreator;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use UIS\Core\DB\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use UIS\Core\DB\Console\Migrations\SaveStateCommand;
use UIS\Core\DB\Migrations\StateSaver;

class MigrationServiceProvider extends ServiceProvider
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
        $this->registerStateSaver();

        $this->registerCommands();
    }


    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerStateSaver()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('migration.state_saver', function($app)
        {
            $repository = $app['migration.repository'];

            return new StateSaver($repository, $app['db'], $app['files']);
        });
    }

    /**
     * Register all of the migration commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->registerSaveStateCommand();

        $commands = ['Make', 'SaveState'];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command)
        {
            $this->{'register'.$command.'Command'}();
        }

        $this->commands(
            'command.migrate.save-state'
        );


        return;

        $commands = array('Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status');



        // Once the commands are registered in the application IoC container we will
        // register them with the Artisan start event so that these are available
        // when the Artisan application actually starts up and is getting used.
        $this->commands(
            'command.migrate', 'command.migrate.make',
            'command.migrate.install', 'command.migrate.rollback',
            'command.migrate.reset', 'command.migrate.refresh',
            'command.migrate.status'
        );
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function($app)
        {
            return new MigrationCreator($app['files']);
        });
    }

    /**
     * Register the "make" migration command.
     *
     * @return void
     */
    protected function registerMakeCommand()
    {
        $this->registerCreator();

        $this->app->singleton('command.migrate.make', function($app)
        {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.creator'];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });
    }

    /*********************************************************************************************/
    /*********************************************************************************************/
    /*********************************************************************************************/

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.migrate', function($app)
        {
            return new MigrateCommand($app['migrator']);
        });
    }

    /**
     * Register the "rollback" migration command.
     *
     * @return void
     */
    protected function registerRollbackCommand()
    {
        $this->app->singleton('command.migrate.rollback', function($app)
        {
            return new RollbackCommand($app['migrator']);
        });
    }

    /**
     * Register the "reset" migration command.
     *
     * @return void
     */
    protected function registerResetCommand()
    {
        $this->app->singleton('command.migrate.reset', function($app)
        {
            return new ResetCommand($app['migrator']);
        });
    }

    /**
     * Register the "refresh" migration command.
     *
     * @return void
     */
    protected function registerRefreshCommand()
    {
        $this->app->singleton('command.migrate.refresh', function()
        {
            return new RefreshCommand;
        });
    }

    protected function registerStatusCommand()
    {
        $this->app->singleton('command.migrate.status', function($app)
        {
            return new StatusCommand($app['migrator']);
        });
    }

    /**
     * Register the "install" migration command.
     *
     * @return void
     */
    protected function registerSaveStateCommand()
    {
        $this->app->singleton('command.migrate.save-state', function($app)
        {
            return new SaveStateCommand($app['migration.state_saver']);
        });
    }



    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'migrator', 'migration.repository', 'command.migrate',
            'command.migrate.rollback', 'command.migrate.reset',
            'command.migrate.refresh', 'command.migrate.install',
            'command.migrate.status', 'migration.creator',
            'command.migrate.make',
        );
    }

}
