<?php
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

    /**
     * @param ConsoleCommandEvent $event
     *
     * @return DefaultNameGenerator
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->name = $event->getCommand()->getName();

        return $this;
    }

    /**
     * @param string $name
     *
     * @return DefaultNameGenerator
     */
    public function setName($name)
    {
        if (self::MAX_LENGTH < strlen($name)) {
            $name = substr($name, 0, self::MAX_LENGTH);
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return DefaultNameGenerator
     */
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

    /**
     * @return DefaultNameGenerator
     */
    public function onTerminate()
    {
        $this->name = '';

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return $this->name;
    }
}
