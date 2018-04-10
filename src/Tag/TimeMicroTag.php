<?php
namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\LongTag;

class TimeMicroTag extends LongTag
{
    /**
     * TimeMicroTag constructor.
     *
     * @param int $value
     */
    public function __construct($value)
    {
        parent::__construct('time.micro', (int)$value);
    }
}
