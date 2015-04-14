<?php
namespace UIS\Core\DB\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;
use Illuminate\Filesystem\Filesystem;

class MigrationCreator extends IlluminateMigrationCreator
{
    protected $import = false;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string $name
     * @param  string $path
     * @param  string $table
     * @param  bool $create
     * @return string
     */
    public function createMigration($name, $path, $table = null, $create = false, $import = false)
    {
        $this->import = $import;
        $name = $import === true ? 'import_database' : $name;
        return $this->create($name, $path, $table, $create);
    }

    /**
     * Get the migration stub file.
     *
     * @param  string $table
     * @param  bool $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if ($this->import === true) {
            return $this->files->get($this->getStubPath() . '/import.stub');
        }

        if (is_null($table)) {
            return $this->files->get($this->getStubPath() . '/blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        else {
            $stub = $create ? 'create.stub' : 'update.stub';

            return $this->files->get($this->getStubPath() . "/{$stub}");
        }
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function getStubPath()
    {
        if ($this->import) {
            return __DIR__.'/stubs';
        }
        return parent::getStubPath();
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        if ($this->import) {
            return '2000_01_01_000001';
        }
        return parent::getDatePrefix();
    }
}
