<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Contracts\PrinterContract;
use TPG\Attache\Contracts\ResultContract;

class Printer implements PrinterContract
{
    protected OutputInterface $output;

    /**
     * Printer constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function output(): OutputInterface
    {
        return $this->output;
    }

    public function fromResult(ResultContract $result): void
    {
        if (!$result->isSuccess()) {
            $this->error($this->friendlyErrorMessage($result->output()));
        }
    }

    public function error(string $message): void
    {
        $formatter = new FormatterHelper();
        $text = $formatter->formatBlock($message, 'error', true);
        $this->output->writeln($text);
    }

    public function friendlyErrorMessage(string $message): string
    {
        $errors = require(ATTACHE_ROOT.'resources/lang/errors.php');

        $e = collect(array_keys($errors))->filter(fn($em) => strpos($message, $em) !== false)->first();

        if ($e) {
            return Arr::get($errors, $e);
        }

        return $message;
    }
}
