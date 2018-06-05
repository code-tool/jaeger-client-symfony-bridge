<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Sampler\AdaptiveSampler;
use Jaeger\Sampler\ConstGenerator;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Sampler\OperationGenerator;
use Jaeger\Sampler\ProbabilisticSampler;
use Jaeger\Sampler\RateLimitingSampler;
use Jaeger\Sampler\SamplerInterface;

class JaegerSamplerFactory
{
    public function isApcuOn(): bool
    {
        if (false === extension_loaded('apcu')) {
            return false;
        }

        if (PHP_SAPI !== 'cli') {
            return true;
        }

        return (bool)ini_get('apc.enable_cli');
    }

    public function sampler(string $type, $param): SamplerInterface
    {
        switch ($type) {
            case 'const':
                return new ConstSampler((bool)$param);
            case 'probabilistic':
                return new ProbabilisticSampler((float)$param);
            case 'ratelimiting':
                if (false === $this->isApcuOn()) {
                    return new ProbabilisticSampler((float)$param);
                }

                return new RateLimitingSampler($param, new ConstGenerator());
            case 'adaptive':
                if (false === $this->isApcuOn()) {
                    return new ProbabilisticSampler((float)$param);
                }

                return new AdaptiveSampler(
                    new RateLimitingSampler((float)$param, new OperationGenerator()),
                    new ProbabilisticSampler((float)$param)
                );
            default:
                throw new \RuntimeException('Unknown sampler type %s');
        }
    }
}
