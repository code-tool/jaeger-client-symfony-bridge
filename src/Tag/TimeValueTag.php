<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\DoubleTag;

class TimeValueTag extends DoubleTag
{
    public function __construct(float $value)
    {
        parent::__construct('time.value', $value);
    }
}
