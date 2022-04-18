<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\StringTag;

class SymfonySubRequestTag extends StringTag
{
    public function __construct()
    {
        parent::__construct('symfony.request', 'sub');
    }
}
