<?php

declare(strict_types=1);

namespace TPG\Attache;

class Task
{
    protected const DELIMITER = 'ATTACHE-SCRIPT';

    protected ?string $script;

    /**
     * Task constructor.
     * @param string|null $script
     */
    public function __construct(string $script = null)
    {
        $this->setScript($script);
    }

    public function setScript(string $script): void
    {
        $this->script = $script;
    }

    public function script(): ?string
    {
        return $this->script;
    }

    public function bashScript(): string
    {
        $parts = [
            $this->getBashCommand().' << \\'.$this->getBashDelimiter(),
            '(',
            'set -e',
            $this->script(),
            ')',
            $this->getBashDelimiter()
        ];

        return implode(PHP_EOL, $parts);
    }

    public function getBashDelimiter(): string
    {
        return self::DELIMITER;
    }

    protected function getBashCommand(): string
    {
        return 'bash -se';
    }
}
