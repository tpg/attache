<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class InitCommand extends Command
{
    /**
     * @var ?Initializer
     */
    protected ?Initializer $initializer;

    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null, ?Initializer $initializer = null)
    {
        parent::__construct($name, $configurationProvider);

        if ($initializer) {
            $this->setInitializer($initializer);
        }
    }

    protected function setInitializer(Initializer $initializer): void
    {
        $this->initializer = $initializer;
    }

    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialize Attaché in the current project directory')
            ->addOption(
                'filename',
                'f',
                InputOption::VALUE_REQUIRED,
                'Name of the config file to save', '.attache.json');
    }

    protected function fire(): int
    {
        $filename = $this->option('filename');

        $this->initializer->create($filename);

        return 0;
    }
}
