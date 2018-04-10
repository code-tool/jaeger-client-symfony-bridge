<?php
namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\DoubleTag;

class TimeValueTag extends DoubleTag
{
    /**
     * TimeValueTag constructor.
     *
     * @param float $value
     */
    public function __construct($value)
    {
        parent::__construct('time.value', (float)$value);
    }
}
