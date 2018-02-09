<?php
namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Span\Context\SpanContext;

interface ContextExtractorInterface
{
    /**
     * @return SpanContext|null
     */
    public function extract();
}
