<?php

namespace SocialPost\Cache\Adapters;

use SocialPost\Cache\Exception\CacheException;

class RedisAdapter extends CacheAdapter
{
    /**
     * MemcachedAdapter constructor
     *
     * @throws CacheException
     */
    public function __construct()
    {
        throw new CacheException('No Memcached server available');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // TODO: Implement get() method.
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        // TODO: Implement set() method.
    }

    public function delete(string $key): bool
    {
        // TODO: Implement delete() method.
    }

    public function clear(): bool
    {
        // TODO: Implement clear() method.
    }
}
