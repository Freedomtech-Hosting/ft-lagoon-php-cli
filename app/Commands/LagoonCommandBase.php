<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use FreedomtechHosting\FtLagoonPhp\Client;
use Illuminate\Support\Facades\Storage;

abstract class LagoonCommandBase extends Command
{

    protected Client $LagoonClient;
    protected $APPDIR;

    const MAX_TOKEN_AGE_MINUTES = 10;

    public function __construct()
    {
        $HOME = getenv('HOME') ?? "/tmp/";
        $this->APPDIR = $HOME . DIRECTORY_SEPARATOR . ".ftlagoonphp";

        if(! is_dir($this->APPDIR))
        {
            mkdir($this->APPDIR);
        }

        parent::__construct();
    }

    protected function initLagoonClient($sshPrivateKeyFile = "~/.ssh/id_rsa")
    {
        $this->LagoonClient = app(Client::class, [
          'ssh_private_key_file' => $sshPrivateKeyFile
        ]);
        
        $tokenFile = $this->APPDIR . DIRECTORY_SEPARATOR . md5($sshPrivateKeyFile) . ".token";
        $tokenFileExpired = (((time() - filemtime($tokenFile)) / 60) > self::MAX_TOKEN_AGE_MINUTES);

        if(file_exists($tokenFile) && !$tokenFileExpired) {
            $this->info("Loaded token from: " . $tokenFile);
            $this->LagoonClient->setLagoonToken(file_get_contents($tokenFile));
        } else {
            $this->LagoonClient->getLagoonTokenOverSsh();
         
            if($this->LagoonClient->getLagoonToken()) {
                $this->info("Saved token to: " . $tokenFile);
                file_put_contents($tokenFile, $this->LagoonClient->getLagoonToken());
            } else {
                $this->error("Could not load a Laoon token");
            }
        }

        $this->LagoonClient->initGraphqlClient();
    }
}
