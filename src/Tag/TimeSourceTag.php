<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\StringTag;

class TimeSourceTag extends StringTag
{
    public function __construct(string $value)
    {
        parent::__construct('time.source', $value);
    }
}
