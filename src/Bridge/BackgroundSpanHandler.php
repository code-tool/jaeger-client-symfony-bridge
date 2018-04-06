<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Symfony\Tag\SymfonyBackgroundTag;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;

class BackgroundSpanHandler
{
    private $span;

    private $tracer;

    public function __construct(TracerInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public function start(Request $request)
    {
        $this->span = $this->tracer->start(
            'background',
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag(),
                new SymfonyBackgroundTag(),
            ]
        );
    }

    public function finish()
    {

    }
}
