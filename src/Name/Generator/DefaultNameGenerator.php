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


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 8],
            ConsoleEvents::COMMAND => ['onCommand', 8],
            KernelEvents::TERMINATE => ['onTerminate'],
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
     * @param GetResponseEvent $event
     *
     * @return DefaultNameGenerator
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (null !== ($fragment = $request->attributes->get('is_fragment'))) {
            $this->name = ($controller = $request->attributes->get('_controller', null))
                ? sprintf('fragment.%s', $controller)
                : 'fragment';

            return $this;
        }

        if (null === ($routeName = $request->attributes->get('_route', null))) {
            $this->name = $request->getRequestUri();

            return $this;
        }

        $this->name = $routeName;

        return $this;
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
