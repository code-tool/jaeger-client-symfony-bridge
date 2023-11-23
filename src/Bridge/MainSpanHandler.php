<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Span\Span;
use Jaeger\Symfony\Name\Generator\NameGeneratorInterface;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

class MainSpanHandler implements ResetInterface
{
    /**
     * @var Span
     */
    private $span;

    private $durationUsec;

    private $tracer;

    private $nameGenerator;

    public function __construct(TracerInterface $tracer, NameGeneratorInterface $nameGenerator)
    {
        $this->tracer = $tracer;
        $this->nameGenerator = $nameGenerator;
    }

    public function start(Request $request): MainSpanHandler
    {
        $this->span = $this->tracer->start(
            'main.start',
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag(),
            ]
        )->start((int)(1000000 * $request->server->get('REQUEST_TIME_FLOAT', microtime(true))));

        return $this;
    }

    public function name(): MainSpanHandler
    {
        if (null === $this->span) {
            return $this;
        }
        $this->span->operationName = $this->nameGenerator->generate();

        return $this;
    }

    public function finish(): void
    {
        if (null === $this->span) {
            return;
        }
        $this->durationUsec = microtime(true) * 1000000 - $this->span->startTime;
    }

    public function flush(): void
    {
        if (null === $this->span || null === $this->durationUsec) {
            return;
        }
        $this->span->finish((int)$this->durationUsec);
        $this->reset();
    }

    public function reset(): void
    {
        $this->span = null;
        $this->durationUsec = null;
    }
}
