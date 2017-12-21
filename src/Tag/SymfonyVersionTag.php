<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\StringTag;
use Symfony\Component\HttpKernel\Kernel;

class SymfonyVersionTag extends StringTag
{
    public function __construct()
    {
        parent::__construct('symfony.version', Kernel::VERSION);
    }
}
