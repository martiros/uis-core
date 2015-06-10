<?php

namespace UIS\Core\Foundation;

use Illuminate\Support\Facades\Config;
use DB;
use DateTime;

class Application
{
    /**
     * @var bool
     */
    protected $profileStarted = false;
    protected $dbQueriesDurations = 0;
    protected $dbQueriesCount = 0;
    protected $profileLog = '';
    protected $profileIndex = 0;
    protected $runTime = null;
    protected $lastProfileTime = null;

    public function profileStart()
    {
        $profile = Config::get('app.profile');
        if (!$profile) {
            return;
        }
        $this->profileStarted = rand(1, $profile) === 1;
        if (!$this->profileStarted) {
            return;
        }
        $this->runTime = $this->lastProfileTime = microtime(true);
        DB::listen(
            function ($sql, $bindings, $time) {
                $this->profileDdQuery($sql, $bindings, $time);
            }
        );
    }

    public function profileEnd()
    {
        if (!$this->profileStarted) {
            return;
        }
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cron';
        $includedFiles = get_included_files();
        $runDate = new DateTime();
        $runDate->setTimestamp($_SERVER['REQUEST_TIME_FLOAT']);

        $this->profileLog .= 'Includes files'.PHP_EOL;
        $this->profileLog .= print_r($includedFiles, true);

        $this->profileLog .= 'POST Data'.PHP_EOL;
        $this->profileLog .= print_r($_POST, true);

        $this->profileLog .= 'SERVER Data'.PHP_EOL;
        $this->profileLog .= print_r($_SERVER, true);

        DB::table('app_profile')->insert(
            [
                'url' => $url,
                'total_duration' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 5),
                'app_logic_duration' => round(microtime(true) - $this->runTime, 5),
                'db_queries_duration' => round($this->dbQueriesDurations, 5),
                'db_queries_count' => $this->dbQueriesCount,
                'memory_usage' => round(memory_get_usage() / 1048576, 5),
                'included_files_count' => count($includedFiles),
                'run_date' => $runDate,
                'log' => $this->profileLog,
            ]
        );
    }

    public function profile($profileString)
    {
        $this->profileIndex++;
        $profileString = 'STEP '.$this->profileIndex.': '.$profileString;
        $profileString .= ', from last profile - '.(microtime(true) - $this->lastProfileTime);
        $profileString .= ', from start - '.(microtime(true) - $this->runTime);
        if (empty($this->profileLog)) {
            $this->profileLog .= $profileString;
        } else {
            $this->profileLog .= PHP_EOL.$profileString;
        }
        $this->lastProfileTime = microtime(true);
    }

    public function profileDdQuery($sql, $bindings, $time)
    {
        $time /= 1000;
        $this->dbQueriesDurations += $time;
        $this->dbQueriesCount++;
        $profileStr = "QUERY - {$sql}, bindings - ".json_encode($bindings).', duration - '.$time;
        $this->profile($profileStr);
    }

    public function getName()
    {
        $appName = Config::get('app.uis_app_name');

        return $appName ?: 'application';
    }
}
