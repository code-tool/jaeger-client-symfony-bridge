<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Span\Span;
use Jaeger\Symfony\Tag\SymfonyBackgroundTag;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;

class BackgroundSpanHandler
{
    /**
     * @var Span
     */
    private $span;

    private $tracer;

    public function __construct(TracerInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public function start(Request $request): BackgroundSpanHandler
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

        return $this;
    }

    public function flush(): BackgroundSpanHandler
    {
        if (null === $this->span) {
            return $this;
        }
        $this->tracer->finish($this->span);

        return $this;
    }
}
