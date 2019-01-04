<?php

namespace Capco\AppBundle\GraphQL\DataLoader\Proposal;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\GraphQL\DataLoader\BatchDataLoader;
use Capco\AppBundle\Cache\RedisCache;
use Overblog\PromiseAdapter\PromiseAdapterInterface;
use Psr\Log\LoggerInterface;

class ProposalProgressStepDataLoader extends BatchDataLoader
{
    public function __construct(
        PromiseAdapterInterface $promiseFactory,
        RedisCache $cache,
        LoggerInterface $logger,
        string $cachePrefix,
        int $cacheTtl = RedisCache::ONE_MINUTE
    ) {
        parent::__construct(
            [$this, 'all'],
            $promiseFactory,
            $logger,
            $cache,
            $cachePrefix,
            $cacheTtl
        );
    }

    public function invalidate(Proposal $proposal): void
    {
        foreach ($this->getCacheKeys() as $cacheKey) {
            $decoded = $this->getDecodedKeyFromKey($cacheKey);
            if (false !== strpos($decoded, $proposal->getId())) {
                $this->cache->deleteItem($cacheKey);
                $this->clear($cacheKey);
                $this->logger->info('Invalidated cache for proposal ' . $proposal->getId());
            }
        }
    }

    public function all(array $keys)
    {
        $connections = [];

        foreach ($keys as $key) {
            $this->logger->info(
                __METHOD__ . ' called with ' . var_export($this->serializeKey($key), true)
            );

            $connections[] = $this->resolve($key);
        }

        return $this->getPromiseAdapter()->createAll($connections);
    }

    protected function serializeKey($key)
    {
        if (\is_string($key)) {
            return $key;
        }

        return [
            'proposalId' => $key->getId(),
        ];
    }

    private function resolve(Proposal $proposal)
    {
        return $proposal->getProgressSteps();
    }
}
