<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpCodeTag;
use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Symfony\Name\Generator\NameGeneratorInterface;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Symfony\Tag\TimeMicroTag;
use Jaeger\Symfony\Tag\TimeSourceTag;
use Jaeger\Symfony\Tag\TimeValueTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSpanListener implements EventSubscriberInterface
{
    private $spans;

    private $nameGenerator;

    private $tracer;

    public function __construct(\SplStack $stack, NameGeneratorInterface $nameGenerator, TracerInterface $tracer)
    {
        $this->spans = $stack;
        $this->nameGenerator = $nameGenerator;
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 29],
            KernelEvents::RESPONSE => ['onResponse'],
        ];
    }

    public function onResponse(FilterResponseEvent $event)
    {
        if ($this->spans->isEmpty()) {
            return $this;
        }

        $this->tracer->finish(
            $this->spans->pop()->addTag(new HttpCodeTag($event->getResponse()->getStatusCode()))
        );

        return $this;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $requestSpan = $this->tracer->start(
            $this->nameGenerator->generate(),
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag()
            ]
        );
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $source = $request->server->has('REQUEST_TIME_FLOAT') ? 'header' : 'microtime';
            $value = $request->server->get('REQUEST_TIME_FLOAT', microtime(true));
            $startTime = (int)($value * 1000000);
            $requestSpan
                ->addTag(new TimeSourceTag($source))
                ->addTag(new TimeValueTag($value))
                ->addTag(new TimeMicroTag($startTime))
                ->start($startTime);
        }

        $this->spans->push($requestSpan);

        return $this;
    }
}
