<?php

namespace SocialPost\Cache\Factory;

use Psr\SimpleCache\CacheInterface;
use SocialPost\Cache\Adapters\CacheAdapter;
use SocialPost\Cache\Adapters\MemcachedAdapter;
use SocialPost\Cache\Adapters\RedisAdapter;
use SocialPost\Cache\Exception\CacheException;

/**
 * Class CacheFactory
 *
 * @package SocialPost\Cache\Factory
 */
class CacheFactory
{
    /**
     * Maps adapter type to the corresponding adapter class name.
     *
     * @var array
     */
    private const ADAPTERS_CLASS_MAP = [
        CacheAdapter::MEMCACHED                     => MemcachedAdapter::class,
        CacheAdapter::REDIS                         => RedisAdapter::class
    ];

    /**
     * Creates a cache adapter based on the specified adapter type.
     * If no adapter type is specified, the default is 'Memcached'.
     *
     * @param string $adapterType The type of the adapter to create (e.g. 'Memcached').
     * @return CacheInterface The created cache adapter.
     * @throws CacheException If the specified adapter type is not supported
     * or if the adapter class does not implement CacheInterface.
     */
    public static function create(string $adapterType = 'memcached'): CacheInterface
    {
        try {
            $adapterClass = self::getClient($adapterType);
            if (!in_array(CacheInterface::class, class_implements($adapterClass))) {
                throw new CacheException("Cache adapter type '{$adapterType}' does not implement the required interface 'CacheInterface'.");
            }

            return new $adapterClass();
        } catch (\Exception $e) {
            throw new CacheException("Error creating cache adapter of type '{$adapterType}': " . $e->getMessage());
        }
    }

    /**
     * Gets the class name of the cache adapter for the specified adapter type.
     *
     * @param string $adapterType The type of the adapter to get the class name for (e.g. 'Memcached').
     * @return string The class name of the cache adapter.
     * @throws CacheException If the specified adapter type is not supported.
     */
    protected static function getClient(string $adapterType): string
    {
        if (!array_key_exists($adapterType, self::ADAPTERS_CLASS_MAP)) {
            throw new CacheException("Cache adapter type '{$adapterType}' is not supported");
        }

        return self::ADAPTERS_CLASS_MAP[$adapterType];
    }
}
