<?php

namespace UIS\Core\DB\Migrations;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use InvalidArgumentException;
use Artisan;
use Exception;

class StateSaver
{
    /**
     * The migration repository implementation.
     *
     * @var \Illuminate\Database\Migrations\MigrationRepositoryInterface
     */
    protected $repository;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The name of the default connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * The notes for the current operation.
     *
     * @var array
     */
    protected $notes = [];

    /**
     * Max db size, allowed to dump in MB.
     * @var int
     */
    protected $maxDbSize = 20;

    /**
     * @var array
     */
    protected $connectionOptions;

    /**
     * Create a new StateSaver instance.
     *
     * @param  \Illuminate\Database\Migrations\MigrationRepositoryInterface $repository
     * @param  \Illuminate\Database\ConnectionResolverInterface $resolver
     * @param  \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(
        MigrationRepositoryInterface $repository,
        Resolver $resolver,
        Filesystem $files
    ) {
        $this->files = $files;
        $this->resolver = $resolver;
        $this->repository = $repository;
        $this->connectionOptions = config('database.connections.'.config('database.default'));
    }

    /**
     * Run the outstanding migrations at a given path.
     *
     * @param  string $path
     * @return void
     */
    public function save($path)
    {
        $this->notes = [];

        $this->emptyAppProfile();

        $dbSize = $this->getDatabaseSize();
        if ($dbSize > $this->maxDbSize) {
            $this->note('<error>Too big database for dump - '.$dbSize.' MB.</error>');

            return;
        }

        $migrations = $this->getRun($path);
        $this->archiveFiles($path, $migrations);

        $this->dumpDatabase($path);
        Artisan::call('make:migration', ['name' => 'import', '--import' => true]);
    }

    protected function getRun($path)
    {
        $files = $this->getMigrationFiles($path);
        $ran = $this->repository->getRan();

        return array_intersect($files, $ran);
    }

    protected function archiveFiles($path, $migrations)
    {
        $archivePath = storage_path('/archive/migrations/'.date('Y_m_d_His'));
        $this->files->makeDirectory($archivePath, 0775, true);

        foreach ($migrations as $migrationFile) {
            $this->files->move(
                $path.'/'.$migrationFile.'.php',
                $archivePath.'/'.$migrationFile.'.php'
            );
        }

        if ($this->files->isFile($path.'/db.sql')) {
            $this->files->move(
                $path.'/db.sql',
                $archivePath.'/db.sql'
            );
        }
        $connection = $this->resolver->connection();
        $connection->statement('TRUNCATE TABLE '.$connection->getTablePrefix().'migrations');
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param  string $path
     * @return array
     */
    public function getMigrationFiles($path)
    {
        $files = $this->files->glob($path.'/*_*.php');

        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.
        if ($files === false) {
            return [];
        }

        $files = array_map(
            function ($file) {
                return str_replace('.php', '', basename($file));
            },
            $files
        );

        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        return $files;
    }

    /**
     * Raise a note event for the SaveStater.
     *
     * @param  string $message
     * @return void
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Resolve the database connection instance.
     *
     * @param  string $connection
     * @return \Illuminate\Database\Connection
     */
    public function resolveConnection($connection)
    {
        return $this->resolver->connection($connection);
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     * @return void
     */
    public function setConnection($name)
    {
        if (!is_null($name)) {
            $this->resolver->setDefaultConnection($name);
            $this->connectionOptions = config('database.connections.'.$name);
        }
        $this->repository->setSource($name);

        $this->connection = $name;
    }

    /**
     * Get the migration repository instance.
     *
     * @return \Illuminate\Database\Migrations\MigrationRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }

    /**
     * Get the file system instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    protected function getDatabaseSize()
    {
        $connection = $this->resolver->connection();
        $databaseName = $connection->getDatabaseName();
        $sql = 'SELECT
                    sum( data_length + index_length ) / 1024 / 1024 AS db_size
                FROM information_schema.TABLES
                WHERE table_schema = ? ';
        $data = $connection->select($sql, [$databaseName]);
        if (empty($data)) {
            throw new InvalidArgumentException('Database not found - '.$databaseName);
        }

        return (float) $data[0]->db_size;
    }

    protected function emptyAppProfile()
    {
        $connection = $this->resolver->connection();
        if (!$connection->getSchemaBuilder()->hasTable('app_profile')) {
            return;
        }
        $connection->statement('TRUNCATE TABLE '.$connection->getTablePrefix().'app_profile');
    }

    protected function dumpDatabase($path)
    {
        $connection = $this->resolver->connection();
        $command = 'mysqldump -u '.$this->connectionOptions['username'].' -p'.$this->connectionOptions['password'].' -h'.$this->connectionOptions['host'].' '.$connection->getDatabaseName(
            ).' > '.$path.'/db.sql';
        $result = shell_exec($command);
        if (strpos($result, 'ERROR ')) {
            throw new Exception($result);
        }
    }
}
