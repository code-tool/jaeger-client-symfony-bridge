<?php
namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\StringTag;

class TimeSourceTag extends StringTag
{
    /**
     * TimeSourceTag constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        parent::__construct('time.source', (string)$value);
    }
}
