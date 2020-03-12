<?php

namespace TPG\Attache;

class Task
{
    protected string $script;
    protected ?Server $server;

    /**
     * Task constructor.
     * @param Server $server
     * @param string $script
     */
    public function __construct(string $script, Server $server = null)
    {
        $this->script = $script;
        $this->server = $server;
    }

    public function server(): ?Server
    {
        return $this->server;
    }

    public function script(): string
    {
        return $this->script;
    }
}
