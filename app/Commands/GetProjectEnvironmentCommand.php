<?php namespace App\Commands;

class GetProjectEnvironmentDeploymentCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-project-environment {--p|project=} {--e|environment=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get project environment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $identity_file = $this->option("identity_file");

        $this->initLagoonClient($identity_file);

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

        $environmentData = $this->LagoonClient->getProjectEnvironmentByName($project, $environment);

        if (! isset($environmentData['id'])) {
            $this->error('Environment not found');
            return 1;
        }
        
        $tableData = [
            [
                $project,
                $environmentData['id'],
                $environmentData['name'],
                $environmentData['environmentType'],
                $environmentData['created'],
                $environmentData['updated'],
                $environmentData['deleted'],
                $environmentData['route'],
                $environmentData['routes']
            ]
        ];

        $this->table(
            ['Project', 'ID', 'Name', 'Type', 'Created', 'Updated', 'Deleted', 'Route', 'Routes'],
            $tableData
        );
    }
}
