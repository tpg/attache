<?php

namespace TPG\Attache;

class Task
{
    /**
     * @var string
     */
    protected const DELIMITER = 'ATTACHE-SCRIPT';

    /**
     * @var string
     */
    protected string $script;
    /**
     * @var Server|null
     */
    protected ?Server $server;

    /**
     * @param Server $server
     * @param string $script
     */
    public function __construct(string $script, Server $server = null)
    {
        $this->script = $script;
        $this->server = $server;
    }

    /**
     * Get the task server.
     *
     * @return Server|null
     */
    public function server(): ?Server
    {
        return $this->server;
    }

    /**
     * Get the task script.
     *
     * @return string
     */
    public function script(): string
    {
        return $this->script;
    }

    public function getBashScript($ssh = false): string
    {
        return $this->getBashExec($ssh)." << \\".self::DELIMITER.PHP_EOL
            .'('.PHP_EOL
            .'set -e'.PHP_EOL
            .$this->script().PHP_EOL
            .')'.PHP_EOL
            .self::DELIMITER;
    }

    protected function getBashExec($ssh = false): string
    {
        $exec = 'bash -se';
        if ($ssh) {
            return '\''.$exec.'\'';
        }

        return $exec;
    }
}
