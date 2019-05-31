<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Transport;

use Thrift\Transport\TBufferedTransport;

class SymfonyBufferedTransport extends TBufferedTransport
{
    public function __construct($transport = null, $rBufSize = 512, $wBufSize = 512)
    {
        parent::__construct($transport, (int)$rBufSize, (int)$wBufSize);
    }
}
