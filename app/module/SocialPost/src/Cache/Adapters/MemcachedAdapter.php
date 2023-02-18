<?php

namespace SocialPost\Cache\Adapters;

use DateInterval;
use Memcached;
use SocialPost\Cache\Exception\CacheException;
use SocialPost\Cache\Exception\InvalidArgumentException;

class MemcachedAdapter extends CacheAdapter
{
    /**
     * The underlying Memcached client.
     *
     * @var Memcached
     */
    protected Memcached $client;

    /**
     * MemcachedAdapter constructor
     *
     * @throws CacheException
     */
    public function __construct()
    {
        $this->client = new Memcached();

        try {
            $this->client->addServer('memcached', '11211');
        } catch (\Exception $e) {
            throw new CacheException('Failed to connect to Memcached server: ' . $e->getMessage());
        }

        if (empty($this->client->getServerList())) {
            throw new CacheException('No Memcached server available');
        }
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->client->get($this->checkKey($key));

        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Persists data in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param DateInterval|int|null $ttl The time-to-live for this item in the cache.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, DateInterval|int $ttl = null): bool
    {
        return $this->client->set($this->checkKey($key), $value, $this->getTTL($ttl) ?? 0);
    }

    /**
     * Deletes an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->client->delete($this->checkKey($key));
    }

    /**
     * This function is used to clear the cache.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->client->flush();
    }

    /**
     * Gets multiple cache items by their unique keys.
     *
     * @param iterable $keys
     * @param mixed $default
     *
     * @return array A list of key-value pairs.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, mixed $default = null): array
    {
        $keys = $this->getData($keys);

        $this->checkKeyArray($keys);

        $values = $this->client->getMulti($keys);
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $values[$key] ?? $default;
        }

        return $return;
    }

    /**
     * Sets multiple cache items in a single operation.
     *
     * @param iterable $values A list of key-value pairs to store in the cache.
     * @param null|int|DateInterval $ttl
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $values = $this->getData($values);

        $this->checkKeyArray(array_keys($values));

        return $this->client->setMulti($values, $this->getTTL($ttl) ?? 0);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        $keys = $this->getData($keys);

        $this->checkKeyArray($keys);

        return $this->checkReturn($this->client->deleteMulti($keys));
    }
}
