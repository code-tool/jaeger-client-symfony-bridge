<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Span\SpanInterface;
use Jaeger\Symfony\Tag\SymfonyBackgroundTag;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

class BackgroundSpanHandler implements ResetInterface
{
    private ?SpanInterface $span = null;

    private TracerInterface $tracer;

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
        $this->span->finish();
        $this->reset();

        return $this;
    }

    public function reset(): void
    {
        $this->span = null;
    }
}
