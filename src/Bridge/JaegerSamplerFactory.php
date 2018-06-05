<?php
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
    /**
     * @return bool
     */
    public function isApcuOn()
    {
        if (false === extension_loaded('apcu')) {
            return false;
        }

        if (PHP_SAPI !== 'cli') {
            return true;
        }

        return (bool)ini_get('apc.enable_cli');
    }

    /**
     * @param string $type
     * @param        $param
     *
     * @return SamplerInterface
     */
    public function sampler($type, $param)
    {
        switch ($type) {
            case 'const':
                return new ConstSampler((bool)$param);
            case 'probabilistic':
                return new ProbabilisticSampler((float)$param);
            case 'ratelimiting':
                if (false === $this->isApcuOn()) {
                    trigger_error(
                        'APCu extension is required by ratelimiting sampler, defaulting to probabilistic',
                        E_WARNING
                    );

                    return new ProbabilisticSampler((float)$param);
                }

                return new RateLimitingSampler($param, new ConstGenerator());
            case 'adaptive':
                if (false === $this->isApcuOn()) {
                    trigger_error(
                        'APCu extension is required by adaptive sampler, defaulting to probabilistic',
                        E_WARNING
                    );

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
