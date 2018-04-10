<?php
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

class GlobalSpanHandler
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

    /**
     * @param Request $request
     *
     * @return GlobalSpanHandler
     */
    public function start(Request $request)
    {
        $this->span = $this->tracer->start(
            $this->nameGenerator->generate(),
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag()
            ]
        )->start((int)(1000000 * $request->server->get('REQUEST_TIME_FLOAT', microtime(true))));

        return $this;
    }

    /**
     * @return GlobalSpanHandler
     */
    public function finish()
    {
        if (null === $this->span) {
            return $this;
        }
        $this->durationUsec = (int)(microtime(true) * 1000000 - $this->span->startTime);

        return $this;
    }

    /**
     * @return GlobalSpanHandler
     */
    public function flush()
    {
        if (null === $this->span || null === $this->durationUsec) {
            return $this;
        }

        $this->tracer->finish($this->span, $this->durationUsec);
        $this->span = $this->durationUsec = null;

        return $this;
    }
}
