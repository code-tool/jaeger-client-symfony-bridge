<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

class EnvDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface, ResetInterface
{
    private $envName;

    private $debugId = '';

    public function __construct(string $envName)
    {
        $this->envName = $envName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 16384],
            ConsoleEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function onTerminate()
    {
        $this->reset();

        return $this;
    }

    public function reset(): void
    {
        $this->debugId = '';
    }

    public function getDebug(): string
    {
        return $this->debugId;
    }

    public function onCommand()
    {
        $this->debugId = $_ENV[$this->envName] ?? '';
    }
}
