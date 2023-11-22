<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Contracts\Service\ResetInterface;

class RequestNameGenerator implements NameGeneratorInterface, EventSubscriberInterface, ResetInterface
{
    /**
     * @var NameGeneratorInterface[] Key - regexp, value - name generator
     */
    private array $generators;

    private string $route = '';

    /**
     * @param NameGeneratorInterface[] $generators Key - regexp, value - name generator
     */
    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function add(string $regexp, NameGeneratorInterface $generator): RequestNameGenerator
    {
        $this->generators[$regexp] = $generator;

        return $this;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Subscribe after route was resolved and request attributes were set
            RequestEvent::class => ['onRequest', 31],
            TerminateEvent::class => ['onTerminate', -16384],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->route = $event->getRequest()->attributes->get('_route', $event->getRequest()->getRequestUri());
    }

    public function onTerminate(): RequestNameGenerator
    {
        $this->reset();

        return $this;
    }

    public function reset(): void
    {
        $this->route = '';
    }

    public function generate(): string
    {
        foreach ($this->generators as $regexp => $generator) {
            if (1 !== \preg_match($regexp, $this->route)) {
                continue;
            }

            return $generator->generate();
        }

        return '';
    }
}
