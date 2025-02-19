<?php namespace App\Commands;

class ListProjectEnvironmentsCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-project-environments {--i|identity_file=~/.ssh/id_rsa} {--p|project=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List project environments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $identity_file = $this->option("identity_file");

        $this->initLagoonClient($identity_file);

        $projectName = $this->option('project');
        if (empty($projectName)) {
            $this->error('Project name is required');
            return 1;
        }

        $data = $this->LagoonClient->getProjectEnvironmentsByName($projectName);
        
        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);
            return 1;
        }

        $tableData = [];
        foreach ($data as $environment => $envData) {
            $tableData[] = [
                $envData['id'],
                $environment,
                $envData['environmentType'],
                $envData['created'],
                $envData['updated'],
                $envData['deleted'] === '0000-00-00 00:00:00' ? '' : $envData['deleted'],
                $envData['route'],
                $envData['routes']
            ];
        }

        $this->table(
            ['ID', 'Name', 'Type', 'Created', 'Updated', 'Deleted', 'Route', 'Routes'],
            $tableData
        );
    }
}
