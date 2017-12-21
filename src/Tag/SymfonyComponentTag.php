<?php
namespace Jaeger\Symfony\Tag;

use Jaeger\Tag\ComponentTag;

class SymfonyComponentTag extends ComponentTag
{
    public function __construct()
    {
        parent::__construct('symfony');
    }
}
