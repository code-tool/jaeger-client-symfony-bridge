<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpCodeTag;
use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Log\ErrorLog;
use Jaeger\Symfony\Name\Generator\NameGeneratorInterface;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyMainRequestTag;
use Jaeger\Symfony\Tag\SymfonySubRequestTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

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
        if ($this->spans->isEmpty()) {
            return;
        }
        $this->spans->pop()->addTag(new HttpCodeTag($event->getResponse()->getStatusCode()))->finish();
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $span = $this->tracer->start(
            $this->nameGenerator->generate(),
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag(),
            ]
        );
        if ($this->isMainRequestEvent($event)) {
            $span->addTag(new SymfonyMainRequestTag());
        } else {
            $span->addTag(new SymfonySubRequestTag());
        }
        $this->spans->push($span);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->spans->isEmpty()) {
            return;
        }
        $exception = $event->getThrowable();
        $this->spans->top()
            ->addTag(new ErrorTag())
            ->addLog(new ErrorLog($exception->getMessage(), $exception->getTraceAsString()));
    }

    /**
     * Use non-deprecated check method if availble
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    private function isMainRequestEvent(KernelEvent $event): bool
    {
        if (\method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }
}
