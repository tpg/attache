<?php

namespace TPG\Attache\Console;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Initializer;

/**
 * Class InitCommand
 * @package TPG\Attache\Console
 */
class InitCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initialize Attaché in the current project directory')
            ->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'Name of the config file to save', '.attache.json');
    }

    /**
     * Initialize the project by creating a new config file.
     *
     * @return int
     * @throws ConfigurationException
     */
    protected function fire(): int
    {
        $filename = $this->option('filename');

        $initializer = new Initializer();

        $remote = $initializer->remote();
        if ($initializer->hasMultipleRemotes()) {
            $remote = $this->selectRemote($initializer->remotes());
        }

        $initializer->createConfig($filename, $remote);

        $this->output->writeln('Attaché initialized. Config file at <info>'.$filename.'</info>');

        return 0;
    }

    /**
     * Allow the user to select from a list of remotes.
     *
     * @param array $remotes
     * @return string
     */
    protected function selectRemote(array $remotes): string
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'There is more than one remote URL available.Please select the oen to use for Attaché:',
            array_map(static function ($key) {
                return Str::after($key, 'remote ');
            }, $remotes)
        );

        return $helper->ask($this->input, $this->output, $question);
    }
}
