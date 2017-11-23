<?php
declare(strict_types=1);

namespace CodeTool\Opentracing\Symfony\Bridge;

use CodeTool\OpenTracing\Codec\CodecInterface;
use CodeTool\OpenTracing\Codec\CodecRegistry;
use CodeTool\OpenTracing\Tracer\InjectableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextInjector implements EventSubscriberInterface
{
    private $tracer;

    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $requestStack;

    private $format;

    private $envName;

    private $headerName;

    public function __construct(
        InjectableInterface $tracer,
        CodecRegistry $registry,
        RequestStack $requestStack,
        string $format,
        string $envName,
        string $headerName
    ) {
        $this->tracer = $tracer;
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->format = $format;
        $this->envName = $envName;
        $this->headerName = $headerName;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => [['onCommand']],
            KernelEvents::REQUEST => [['onRequest']],
        ];
    }

    public function onCommand()
    {
        if (null === ($data = $_ENV[$this->envName] ?? null)) {
            return $this;
        }
        if (null === ($context = $this->registry[$this->format]->decode($data))) {
            return $this;
        }
        $this->tracer->assign($context);

        return $this;
    }

    public function onRequest()
    {
        if ($this->requestStack->getMasterRequest() !== ($request = $this->requestStack->getCurrentRequest())) {
            return $this;
        }
        if (false === $request->headers->has($this->headerName)) {
            return $this;
        }
        if (null === ($context = $this->registry[$this->format]->decode($request->headers->get($this->headerName)))) {
            return $this;
        }
        $this->tracer->assign($context);

        return $this;
    }
}
