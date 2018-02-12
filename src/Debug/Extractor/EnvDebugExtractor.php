<?php

namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnvDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface
{
    private $envName;

    private $debugId = '';

    /**
     * EnvDebugExtractor constructor.
     *
     * @param string $envName
     */
    public function __construct($envName)
    {
        $this->envName = $envName;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 8192],
            ConsoleEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function onTerminate()
    {
        $this->debugId = '';

        return $this;
    }

    /**
     * @return string
     */
    public function getDebug()
    {
        return (string)$this->debugId;
    }

    public function onCommand()
    {
        if (false === array_key_exists($this->envName, $_ENV)) {
            return $this;
        }
        $this->debugId = (string)$_ENV[$this->envName];

        return $this;
    }
}
