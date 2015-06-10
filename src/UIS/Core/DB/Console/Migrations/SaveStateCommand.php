<?php

namespace UIS\Core\DB\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Console\Migrations\BaseCommand;
use UIS\Core\DB\Migrations\StateSaver;
use Symfony\Component\Console\Input\InputOption;

class SaveStateCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:save-state';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show a list of migrations up/down';

    /**
     * The StateSaver instance.
     *
     * @var \UIS\Core\DB\Migrations\StateSaver
     */
    protected $stateSaver;

    /**
     * Create a new migration rollback command instance.
     *
     * @param  \UIS\Core\DB\Migrations\StateSaver $stateSaver
     */
    public function __construct(StateSaver $stateSaver)
    {
        parent::__construct();

        $this->stateSaver = $stateSaver;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->prepareDatabase();

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        if (!is_null($path = $this->input->getOption('path'))) {
            $path = $this->laravel['path.base'].'/'.$path;
        } else {
            $path = $this->getMigrationPath();
        }

        $this->stateSaver->save($path);

        foreach ($this->stateSaver->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        $this->stateSaver->setConnection($this->input->getOption('database'));

        if (!$this->stateSaver->repositoryExists()) {
            $options = ['--database' => $this->input->getOption('database')];
            $this->call('migrate:install', $options);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path of migrations files to be executed.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],

        ];
    }
}
