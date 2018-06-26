<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DefaultNameGenerator implements NameGeneratorInterface, EventSubscriberInterface
{
    private $name = '';

    const MAX_LENGTH = 64;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 30],
            ConsoleEvents::COMMAND => ['onCommand', 30],
            KernelEvents::TERMINATE => ['onTerminate', -16384],
            ConsoleEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->name = $event->getCommand()->getName();

        return $this;
    }

    public function setName(string $name): DefaultNameGenerator
    {
        if (self::MAX_LENGTH < strlen($name)) {
            $name = substr($name, 0, self::MAX_LENGTH);
        }
        $this->name = $name;

        return $this;
    }


    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (null !== ($fragment = $request->attributes->get('is_fragment'))) {
            $name = ($controller = $request->attributes->get('_controller', null))
                ? sprintf('fragment.%s', $controller)
                : 'fragment';
            return $this->setName($name);
        }

        if (null === ($routeName = $request->attributes->get('_route', null))) {
            return $this->setName($request->getRequestUri());
        }

        return $this->setName($routeName);
    }

    public function onTerminate()
    {
        $this->name = '';

        return $this;
    }

    public function generate(): string
    {
        return $this->name;
    }
}
