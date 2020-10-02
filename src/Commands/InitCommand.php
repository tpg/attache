<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
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

        $this->setInitializer($initializer);
    }

    protected function setInitializer(?Initializer $initializer): void
    {
        $this->initializer = $initializer ?: new Initializer($this->filesystem);
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

        $remote = $this->getGitRemote();

        $this->initializer->create($filename, $remote);

        return 0;
    }

    protected function getGitRemote(): string
    {
        $remotes = $this->initializer->discoverGitRemotes();
        if ($remotes->count() === 1) {
            return $remotes->first();
        }

        return $this->selectRemote($remotes);
    }

    protected function selectRemote(Collection $remotes): string
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'There is more than one Git remote available. Please select the one to use by typing its name:',
            array_map(static function ($key) {
                return Str::after($key, 'remote ');
            }, $remotes->toArray())
        );

        $key = $helper->ask($this->input, $this->output, $question);

        return $remotes->get($key);
    }
}
