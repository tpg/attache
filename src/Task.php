<?php

namespace TPG\Attache;

class Task
{
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
}
