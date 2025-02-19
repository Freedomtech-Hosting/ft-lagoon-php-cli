<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use FreedomtechHosting\FtLagoonPhp\Client;

/**
 * Base command class for Lagoon CLI commands
 * 
 * Provides common functionality for authenticating and interacting with the Lagoon API
 */
abstract class LagoonCommandBase extends Command
{
    /** @var Client The Lagoon API client instance */
    protected Client $LagoonClient;

    /** @var string The application directory path for storing tokens */
    protected $APPDIR;

    /** @var int Maximum age in minutes before a token is considered expired */
    const MAX_TOKEN_AGE_MINUTES = 5;

    /**
     * Constructor
     * 
     * Sets up the application directory for storing authentication tokens
     */
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

    /**
     * Initialize the Lagoon API client
     *
     * Sets up authentication using an SSH key and manages token caching
     * 
     * @param string $sshPrivateKeyFile Path to SSH private key file
     */
    protected function initLagoonClient($sshPrivateKeyFile = "~/.ssh/id_rsa")
    {
        $HOME = getenv('HOME') ?? "/tmp/";

        if(preg_match("/^~/", $sshPrivateKeyFile)) {
            $sshPrivateKeyFile = $HOME . substr($sshPrivateKeyFile, 1);
        }   

        $this->LagoonClient = app(Client::class, [
          'ssh_private_key_file' => $sshPrivateKeyFile
        ]);
        
        $tokenFile = $this->APPDIR . DIRECTORY_SEPARATOR . md5($sshPrivateKeyFile) . ".token";

        if(file_exists($tokenFile) && !(((time() - filemtime($tokenFile)) / 60) > self::MAX_TOKEN_AGE_MINUTES)) {
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
