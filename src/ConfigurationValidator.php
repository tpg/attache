<?php

declare(strict_types=1);

namespace TPG\Attache;

use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationValidator
{
    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $provider;

    /**
     * ConfigurationValidator constructor.
     * @param ConfigurationProvider $provider
     */
    public function __construct(ConfigurationProvider $provider)
    {
        $this->provider = $provider;
    }

    public function validate(): void
    {
        $this->defaultExists();
    }

    protected function defaultExists(): void
    {
        if ($this->provider->default() && !$this->provider->servers()->has($this->provider->default())) {
            throw new ConfigurationException('The default server specified does not exist');
        }
    }
}
