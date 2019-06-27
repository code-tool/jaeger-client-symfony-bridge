<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnvDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface
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
        $this->debugId = '';

        return $this;
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
