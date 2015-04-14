<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;

class ImportDatabase extends Migration
{
    private $databaseOptions = null;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    public function __construct()
    {
        $this->databaseOptions = config('database.connections.' . config('database.default'));
        $this->files = new Filesystem();
    }

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $this->emptyDatabase();
        $dbPath = __DIR__ . '/db.sql';
        if (!$this->files->isFile($dbPath)) {
            throw new InvalidArgumentException("DB File not found - {$dbPath}.");
        }
        $this->mysqlCommand("source {$dbPath}");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        $this->emptyDatabase();
	}

    private function emptyDatabase()
    {
        DB::statement('SET foreign_key_checks = 0');
        $tables = DB::select('SHOW TABLES');
        $migrationTable = $this->databaseOptions['prefix'] . 'migrations';
        foreach ($tables as $tableData) {
            foreach ($tableData as $tableName) {
                if ($tableName == $migrationTable) {
                    DB::statement('TRUNCATE ' . $migrationTable);
                } else {
                    DB::statement('DROP TABLE IF EXISTS ' . $tableName);
                }
            }
        }
        DB::statement('SET foreign_key_checks = 1');
    }

    private function mysqlCommand($command)
    {
        $command = "mysql --user={$this->databaseOptions['username']} --password={$this->databaseOptions['password']} --database={$this->databaseOptions['database']} -e \"{$command}\"";
        $result = shell_exec($command);
        if (strpos($result, 'ERROR ')) {
            throw new Exception($result);
        }
    }
}