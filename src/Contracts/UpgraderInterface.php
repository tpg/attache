<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use League\Flysystem\Filesystem;

interface UpgraderInterface
{
    public function __construct(Filesystem $filesystem);
}
