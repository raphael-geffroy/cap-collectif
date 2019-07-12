<?php

namespace Capco\AppBundle\Twig;

use Capco\AppBundle\SiteParameter\Resolver;
use Capco\AppBundle\Cache\RedisCache;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteParameterExtension extends AbstractExtension
{
    public const CACHE_KEY = 'getSiteParameterValue';
    protected $resolver;
    protected $cache;

    public function __construct(Resolver $resolver, RedisCache $cache)
    {
        $this->resolver = $resolver;
        $this->cache = $cache;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'capco_site_parameter_value',
                [$this, 'getSiteParameterValue'],
                ['is_safe' => ['html']]
            )
        ];
    }

    public function getSiteParameterValue($key)
    {
        $cachedItem = $this->cache->getItem(self::CACHE_KEY . $key);

        if (!$cachedItem->isHit()) {
            $data = $this->resolver->getValue($key);
            $cachedItem->set($data)->expiresAfter(RedisCache::ONE_MINUTE);
            $this->cache->save($cachedItem);
        }

        return $cachedItem->get();
    }
}
