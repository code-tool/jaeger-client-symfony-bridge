<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\BoolTag;

class SymfonyBackgroundTag extends BoolTag
{
    public function __construct()
    {
        parent::__construct('symfony.background', true);
    }
}
