<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class TaskListRunnableCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:list-runnable {--p|project=} {--e|environment=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description'; 

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $identity_file = $this->option("identity_file");

        $project = $this->option('project');

        if (empty($project)) {
            $this->error('Project is required');
            return 1;
        }

        $environment = $this->option('environment');
        if (empty($environment)) {
            $this->error('Environment is required');
            return 1;
        }
        
        $this->initLagoonClient($identity_file);

        
        $tasks = $this->LagoonClient->getTasksForProjectEnvironment($project, $environment);

        var_dump($tasks);
        
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
