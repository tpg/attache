<?php

declare(strict_types=1);

namespace TPG\Attache;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\Contracts\PrinterInterface;

class Initializer
{
    /**
     * @var PrinterInterface
     */
    protected PrinterInterface $printer;

    /**
     * Initializer constructor.
     * @param PrinterInterface $printer
     */
    public function __construct(PrinterInterface $printer)
    {
        $this->printer = $printer;
    }

    public function create(string $filename): void
    {
        $config = $this->defaultConfig();

        try {
            file_put_contents(
                $filename,
                json_encode(
                    $config,
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
                    512
                )
            );
        } catch (\JsonException $e) {
            $this->printer->error('Unable to write to file.');
            exit(1);
        }
    }

    public function loadGitConfig(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));

        $config = $filesystem->read($this->defaultGitConfigFilename());
        dump($config);
    }

    protected function defaultGitConfigFilename(): string
    {
        return '.git/config';
    }

    protected function defaultConfig(): array
    {
        return [
            'repository' => 'git@remote.com:vendor/repository.git',
            'servers' => [
                'production' => $this->defaultServerConfig(),
            ],
        ];
    }

    protected function defaultServerConfig(): array
    {
        return [
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
                'local' => false,
            ],
            'branch' => 'master',
            'migrate' => false,
        ];
    }
}
