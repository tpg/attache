<?php

declare(strict_types=1);

namespace TPG\Attache;

class Server
{
    protected array $config;

    /**
     * Server constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
