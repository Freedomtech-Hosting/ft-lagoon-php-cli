<?php namespace App\Commands;

class GetProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-project {--i|identity_file=~/.ssh/id_rsa} {--p|project=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get project';

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

        $project = $this->LagoonClient->getProjectByName($project);

        if (isset($project['error'])) {
            $this->error($project['error'][0]['message']);
            return 1;
        }

        $projectData = $project['projectByName'];
        $projectOutput = [
            'id' => $projectData['id'],
            'name' => $projectData['name'], 
            'productionEnvironment' => $projectData['productionEnvironment'] ?? null,
            'branches' => $projectData['branches'] ?? null,
            'gitUrl' => $projectData['gitUrl'] ?? null,
            'openshift_id' => isset($projectData['openshift']) ? ($projectData['openshift']['id'] ?? null) : null,
            'openshift_name' => isset($projectData['openshift']) ? ($projectData['openshift']['name'] ?? null) : null,
            'openshift_cloudProvider' => isset($projectData['openshift']) ? ($projectData['openshift']['cloudProvider'] ?? null) : null,
            'openshift_cloudRegion' => isset($projectData['openshift']) ? ($projectData['openshift']['cloudRegion'] ?? null) : null,
            'created' => $projectData['created'] ?? null,
            'availability' => $project['availability'] ?? null
        ];

        $this->table(
            ['ID', 'Name', 'Production Environment', 'Branches', 'Git URL', 'ID', 'Cluster', 'Cloud Provider', 'Cloud Region', 'Created', 'Availability'],
            [$projectOutput]
        );
    }
}
