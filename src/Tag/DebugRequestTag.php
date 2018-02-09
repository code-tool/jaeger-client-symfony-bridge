<?php
namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\StringTag;

class DebugRequestTag extends StringTag
{
    /**
     * DebugRequestTag constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        parent::__construct('debug.request', (string)$value);
    }
}
