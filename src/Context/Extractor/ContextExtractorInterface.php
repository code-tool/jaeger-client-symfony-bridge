<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Span\Context\SpanContext;

interface ContextExtractorInterface
{
    public function extract(): ?SpanContext;
}
