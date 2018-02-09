<?php
namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class HeaderContextExtractor implements ContextExtractorInterface, EventSubscriberInterface
{
    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $format;

    private $headerName;

    private $context;

    /**
     * HeaderContextExtractor constructor.
     *
     * @param CodecRegistry $registry
     * @param string        $format
     * @param string        $headerName
     */
    public function __construct(CodecRegistry $registry, $format, $headerName)
    {
        $this->registry = $registry;
        $this->format = (string)$format;
        $this->headerName = (string)$headerName;
    }

    /**
     * @return SpanContext|null
     */
    public function extract()
    {
        return $this->context;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 8192],
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            $this->context = null;

            return $this;
        }

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()
            && $request->headers->has($this->headerName)
            && ($context = $this->registry[$this->format]->decode($request->headers->get($this->headerName)))) {
            $this->context = $context;

            return $this;
        }

        return $this;
    }
}
