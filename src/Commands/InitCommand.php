<?php
/**
 * attache init.
 *
 * Initialize a new Attaché script in the current directory.
 * If there is a `.git` directory then the remote will automatically
 * be inserted into the configuration file.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TPG\Attache\Contracts\UpgraderInterface;
use TPG\Attache\Initializer;
use TPG\Attache\Upgrader;

class InitCommand extends Command
{
    protected string $name = 'init';
    protected string $description = 'Initialize the current directory with a new config file.';
    protected UpgraderInterface $upgrader;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->setInitializer(new Initializer($this->filesystem));

        parent::initialize($input, $output);

        $this->setUpgrader(new Upgrader($this->filesystem));
    }

    protected function setUpgrader(UpgraderInterface $upgrader): void
    {
        $this->upgrader = $upgrader;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'The name of the configuration file to write',
            '.attache.json'
        );
    }

    protected function fire(): int
    {
        if ($this->filesystem->fileExists($this->option('config'))) {
            return $this->checkForOldConfig();
        }

        $remote = $this->getGitRemote();
        $config = $this->initializer->config($remote);

        $this->initializer->create($config, $this->option('config'));

        return 0;
    }

    protected function checkForOldConfig(): int
    {
        $config = json_decode(
            $this->filesystem->read($this->option('config')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (Arr::get($config, 'repository') !== null) {
            $helper = $this->getHelper('question');
            $this->output->writeln('<comment>An Attaché v1 config file exists in this directory</comment>');
            $question = new Question('Would you like to upgrade it? (Y/n): ', 'Y');

            if (! $helper->ask($this->input, $this->output, $question)) {
                return 0;
            }

            $upgrade = $this->upgrader->upgrade($config);

            $this->filesystem->move($this->option('config'), $this->option('config').'-old');

            $this->initializer->create($upgrade, $this->option('config'));
        }

        return 0;
    }

    protected function getGitRemote(): string
    {
        $remotes = $this->initializer->discoverGitRemotes();

        if (count($remotes) > 1) {
            return $this->selectRemote($remotes);
        }

        return Arr::first($remotes);
    }

    protected function selectRemote(array $remotes): string
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'There is more than one remote URL available. Please select the one to use by entering its name:',
            $remotes
        );

        $question->setErrorMessage('Not an appropriate choice');

        return $helper->ask($this->input, $this->output, $question);
    }
}
