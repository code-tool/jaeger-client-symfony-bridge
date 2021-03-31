<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Sampler;

use Jaeger\Sampler\SamplerDecisionTag;
use Jaeger\Sampler\SamplerFlagsTag;
use Jaeger\Sampler\SamplerInterface;
use Jaeger\Sampler\SamplerParamTag;
use Jaeger\Sampler\SamplerResult;
use Jaeger\Sampler\SamplerTypeTag;

final class DenylistOperationsSampler implements SamplerInterface
{
    private $sampler;

    private $denylistedOperationsMap;

    /**
     * @var SamplerInterface $sampler
     * @var string[]         $denylistedOperations
     */
    public function __construct(SamplerInterface $sampler, array $denylistedOperations)
    {
        $this->sampler = $sampler;
        $this->denylistedOperationsMap = \array_fill_keys($denylistedOperations, true);
    }

    public function decide(int $traceId, string $operationName, string $debugId): SamplerResult
    {
        if ('' !== $debugId) {
            return $this->sampler->decide($traceId, $operationName, $debugId);
        }

        if (false === \array_key_exists($operationName, $this->denylistedOperationsMap)) {
            return $this->sampler->decide($traceId, $operationName, $debugId);
        }

        return new SamplerResult(
            false,
            0,
            [
                new SamplerTypeTag('denylist_operations'),
                new SamplerParamTag($operationName),
                new SamplerDecisionTag(false),
                new SamplerFlagsTag(0x00),
            ]
        );
    }
}
