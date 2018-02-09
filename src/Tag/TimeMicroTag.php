<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\LongTag;

class TimeMicroTag extends LongTag
{
    public function __construct(int $value)
    {
        parent::__construct('time.micro', $value);
    }
}
