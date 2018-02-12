<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

interface DebugExtractorInterface
{
    /**
     * @return string
     */
    public function getDebug() : string;
}
