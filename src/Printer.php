<?php

declare(strict_types=1);

namespace TPG\Attache;

use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Contracts\PrinterInterface;

class Printer implements PrinterInterface
{
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * Printer constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function error(string $message): void
    {
        $this->output->writeln('<error>'.$message.'</error>');
    }

    public function info(string $message): void
    {
        $this->output->writeln('<info>'.$message.'</info>');
    }
}
