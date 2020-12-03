<?php

declare(strict_types=1);

namespace TPG\Attache;

use TPG\Attache\Contracts\ConfigurationProviderContract;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationValidator
{
    protected ConfigurationProviderContract $provider;

    public function __construct(ConfigurationProviderContract $provider)
    {
        $this->provider = $provider;
    }

    public function validate(): void
    {
        $this->defaultExists();
    }

    protected function defaultExists(): void
    {
        if ($this->provider->default() && ! $this->provider->servers()->has($this->provider->default())) {
            throw new ConfigurationException('The default server specified does not exist');
        }
    }
}
