<?php

namespace TPG\Attache;

use Symfony\Component\Process\Process;

class Ssh
{
    protected array $server;

    protected const DELIMITER = 'ATTACHE';

    public function __construct(array $server)
    {
        $this->server = $server;
    }

    public function run($commands, \Closure $callback = null)
    {
        if (! is_array($commands)) {
            $commands = [$commands];
        }

        $outputs = [];

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline(
                'ssh '.$this->connectionString().
                ' \'bash -s\' <<'.self::DELIMITER."\n".
                $command."\n".
                self::DELIMITER
            );

            $process->run(function ($type, $output) use (&$outputs) {
                if ($type === Process::OUT) {
                    $outputs[] = [
                        'type' => $type,
                        'data' => $output,
                    ];
                } else {
                    throw new \RuntimeException('A task did not complete');
                }
            });
        }

        $callback($outputs);
    }

    protected function connectionString(): string
    {
        return $this->server['user'].'@'.$this->server['host'].' -p'.$this->server['port'];
    }
}
