<?php
namespace UIS\Core\Foundation\Console;

use Illuminate\Console\Command;
use UIS\Core\Foundation\Profiler;

class ClearOldDataCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'uis:clear-old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old log and profiling data.';

    /**
     * @var Profiler
     */
    protected $profiler;

    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->profiler->deleteOldData();
    }
}
