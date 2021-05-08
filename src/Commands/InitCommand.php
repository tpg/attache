<?php
/**
 * attache init
 *
 * Initialize a new Attaché script in the current directory.
 * If there is a `.git` directory then the remote will automatically
 * be inserted into the configuration file.
 *
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InitCommand extends Command
{
    protected string $name = 'init';
    protected string $description = 'Initialize the current directory with a new config file.';
    protected bool $requireConfig = true;

    protected function fire(): int
    {
        $remote = $this->getGitRemote();
        $config = $this->initializer->config($remote);

        $this->initializer->create($config, $this->option('config'));

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
