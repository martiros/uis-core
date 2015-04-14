<?php
namespace UIS\Core\DB\Console\Migrations;

use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as IlluminateMigrateMakeCommand;
use UIS\Core\DB\Migrations\MigrationCreator;

class MigrateMakeCommand extends IlluminateMigrateMakeCommand
{
    /**
     * The migration creator instance.
     *
     * @var \UIS\Core\DB\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * Create a new migration install command instance.
     *
     * @param  \UIS\Core\DB\Migrations\MigrationCreator $creator
     * @param  \Illuminate\Foundation\Composer $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = $this->input->getArgument('name');

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create');

        $import = $this->option('import');

        if (!$table && is_string($create)) {
            $table = $create;
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->createMigration($name, $table, $create, $import);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string $name
     * @param  string $table
     * @param  bool $create
     * @param  bool $import
     * @return string
     */
    protected function createMigration($name, $table, $create, $import)
    {
        $path = $this->getMigrationPath();

        $file = pathinfo($this->creator->createMigration($name, $path, $table, $create, $import), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $options[] = ['import', null, InputOption::VALUE_NONE, 'Create import migration.'];
        return $options;
    }

}
