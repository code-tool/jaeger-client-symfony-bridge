<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Transport;

use Jaeger\Transport\TUDPTransport;

class SymfonyUDPTransport extends TUDPTransport
{
    public function __construct($host, $port)
    {
        parent::__construct((string)$host, (int)$port);
    }
}
