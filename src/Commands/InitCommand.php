<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Arr;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use TPG\Attache\Contracts\InitializerInterface;
use TPG\Attache\Initializer;

class InitCommand extends Command
{
    protected InitializerInterface $initializer;

    public function __construct(InitializerInterface $initializer = null, string $name = null)
    {
        $this->setInitializer($initializer);

        parent::__construct($name);
    }

    public function setInitializer(?InitializerInterface $initializer = null): void
    {
        $this->initializer = $initializer ?? new Initializer(
            new Filesystem(
                new LocalFilesystemAdapter(getcwd())
            )
        );
    }

    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialize the current project with an Attache config file.')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'The name of the config file to write', '.attache.json'
            );
    }

    protected function fire(): int
    {
        $config = $this->initializer->config($this->getRemote());

        $this->initializer->create(
            $config,
            $this->option('config')
        );

        return 0;
    }

    protected function getRemote(): string
    {
        $remotes = $this->initializer->discoverGitRemotes();

        return match (count($remotes)) {
            0 => throw new \Exception('Not a Git repository.'),
            1 => Arr::first($remotes),
            default => $this->selectRemote($remotes)
        };
    }

    protected function selectRemote(array $remotes): string
    {
        $helper = $this->getHelper('question');

        $default = Arr::first(array_keys($remotes));

        $question = new ChoiceQuestion(
            'Select the project Git remote to clone from (default: '.$default.')',
            $remotes,
            $default
        );

        $selected = $helper->ask($this->input, $this->output, $question);

        return Arr::get($remotes, $selected);
    }
}
