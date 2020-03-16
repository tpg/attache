<?php

namespace TPG\Attache\Console;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InitCommand extends SymfonyCommand
{
    use Command;

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initialize Attaché in the current project directory')
            ->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'Name of the config file to save', '.attache.json');
    }

    protected function fire(): int
    {
        $url = $this->getGitRemote();

        $config = $this->getConfig($url);

        $filename = $this->option('filename');

        file_put_contents(
            $filename,
            json_encode(
                $config,
                JSON_THROW_ON_ERROR
                | JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES,
                512
            )
        );

        $this->output->writeln('Attaché initialized. Config file at <info>'.$filename.'</info>');

        return 0;
    }

    protected function getGitRemote(): string
    {
        if (! file_exists('.git/config')) {
            $this->output->writeln('<error>Not a Git repository</error>');
            exit(1);
        }

        $ini = parse_ini_file('.git/config', true);

        return $this->selectRemote($ini);
    }

    protected function selectRemote($ini): string
    {
        $keys = array_values(array_filter(array_keys($ini), function ($key) {
            return Str::startsWith($key, 'remote');
        }));

        if (count($keys) === 1) {
            return Arr::get($ini, $keys[0].'.url');
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'There is more than one remote URL available.Please select the oen to use for Attaché:',
            array_map(function ($key) {
                return Str::after($key, 'remote ');
            }, $keys)
        );

        return $helper->ask($this->input, $this->output, $question);
    }

    protected function getConfig(string $remote): array
    {
        return [
            'repository' => $remote,
            'servers' => [
                [
                    'name' => 'production',
                    'host' => 'example.test',
                    'port' => 22,
                    'user' => 'user',
                    'root' => '/path/to/application',
                    'paths' => [
                        'releases' => 'releases',
                        'serve' => 'live',
                        'storage' => 'storage',
                        'env' => '.env',
                    ],
                    'php' => [
                        'bin' => 'php',
                    ],
                    'composer' => [
                        'bin' => 'composer',
                        'local' => 'false',
                    ],
                    'branch' => 'master',
                    'migrate' => false,
                ],
            ],
        ];
    }
}
