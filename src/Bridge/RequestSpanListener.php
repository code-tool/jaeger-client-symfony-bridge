<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpCodeTag;
use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Log\ErrorLog;
use Jaeger\Symfony\Name\Generator\NameGeneratorInterface;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 29],
            ResponseEvent::class => ['onResponse', -1024],
            ExceptionEvent::class => ['onKernelException', 0],
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            return;
        }
        if ($this->spans->isEmpty()) {
            return;
        }
        $this->spans->pop()->addTag(new HttpCodeTag($event->getResponse()->getStatusCode()))->finish();
    }

    public function onRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            return;
        }
        $request = $event->getRequest();
        $this->spans->push(
            $this->tracer->start(
                $this->nameGenerator->generate(),
                [
                    new HttpMethodTag($request->getMethod()),
                    new HttpUriTag($request->getRequestUri()),
                    new SpanKindServerTag(),
                    new SymfonyComponentTag(),
                    new SymfonyVersionTag(),
                ]
            )
        );
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->spans->isEmpty()) {
            return;
        }

        $exception = $event->getThrowable();

        $this->spans->current()
            ->addTag(new ErrorTag())
            ->addLog(new ErrorLog($exception->getMessage(), $exception->getTraceAsString()))
        ;
    }
}
