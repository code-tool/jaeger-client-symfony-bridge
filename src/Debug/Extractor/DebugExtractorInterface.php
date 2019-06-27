<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

interface DebugExtractorInterface
{
    public function getDebug() : string;
}
