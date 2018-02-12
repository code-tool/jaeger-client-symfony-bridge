<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

interface NameGeneratorInterface
{
    public function generate() : string;
}
