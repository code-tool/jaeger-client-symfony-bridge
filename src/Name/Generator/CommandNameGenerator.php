<?php

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandNameGenerator implements NameGeneratorInterface, EventSubscriberInterface
{
    private array $generators;

    private string $name;

    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function add(string $regexp, NameGeneratorInterface $generator): CommandNameGenerator
    {
        $this->generators[sprintf('/%s/', $regexp)] = $generator;

        return $this;
    }

    public static function getSubscribedEvents(): array
    {
        return [ConsoleCommandEvent::class => ['onCommand', 30], ConsoleTerminateEvent::class => ['onTerminate'],];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        if (null === $event->getCommand()) {
            return;
        }

        $this->name = (string)$event->getCommand()->getName();
    }

    public function onTerminate(): CommandNameGenerator
    {
        $this->name = '';

        return $this;
    }

    public function generate(): string
    {
        foreach ($this->generators as $regexp => $generator) {
            if (false === \preg_match($regexp, $this->name)) {
                continue;
            }

            return $generator->generate();
        }

        return '';
    }
}