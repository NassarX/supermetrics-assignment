<?php

namespace SocialPost\Cache\Adapters;

use DateTime;
use DateInterval;
use Traversable;
use Psr\SimpleCache\CacheInterface;
use SocialPost\Cache\Exception\InvalidArgumentException;

abstract class CacheAdapter implements CacheInterface
{
    public const MEMCACHED = 'memcached';
    public const REDIS = 'redis';

    /**
     * Check if an item exists in the cache
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get multiple items from the cache
     *
     * @param mixed $keys
     * @param mixed|null $default
     * @return array
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, mixed $default = null): array
    {
        $data = [];

        // Loop through each key and retrieve the data from the cache
        foreach ($this->getData($keys) as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    /**
     * Store multiple items in the cache
     *
     * @param mixed $values
     * @param mixed $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $return = [];

        // Loop through each value and store it in the cache
        foreach ($this->getData($values) as $key => $value) {
            $return[] = $this->set($key, $value, $ttl);
        }

        return $this->checkReturn($return);
    }

    /**
     * Delete multiple items from the cache
     *
     * @param mixed $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        $return = [];

        // Loop through each key and delete it from the cache
        foreach ($this->getData($keys) as $key) {
            $return[] = $this->delete($key);
        }

        return $this->checkReturn($return);
    }

    /**
     * Check if a key is valid
     *
     * @param string|mixed $key
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkKey(mixed $key): string
    {
        // Check if the key is a string and not empty
        if (!is_string($key) || empty($key)) {
            throw new InvalidArgumentException('invalid cache key: "'.$key.'"');
        }

        return $key;
    }

    /**
     * The Checks if all the keys in the given array are valid.
     *
     * @param array $keys
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function checkKeyArray(array $keys): void
    {
        foreach ($keys as $key) {
            $this->checkKey($key);
        }
    }

    /**
     * Returns an array from the given data.
     *
     * @param mixed $data
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        } elseif ($data instanceof Traversable) {
            return iterator_to_array($data);
        }

        throw new InvalidArgumentException('Invalid data');
    }

    /**
     * Get the Time To Live (TTL) value for cache items.
     *
     * @param mixed $ttl The TTL value for cache items. Can be either a number (representing seconds) or a `DateInterval` instance.
     *
     * @return int|null The TTL value in seconds or `null` if the cache item should not expire.
     * @throws InvalidArgumentException
     */
    protected function getTTL(mixed $ttl): ?int
    {
        if ($ttl instanceof DateInterval) {
            // Calculate the number of seconds from the given DateInterval instance
            $expiration = (new DateTime())->add($ttl);
            return $expiration->getTimestamp() - time();
        } elseif (is_int($ttl) && $ttl > 0) {
            return $ttl;
        } elseif ($ttl === null) {
            return null;
        }

        throw new InvalidArgumentException('Invalid TTL value: "'.$ttl.'"');
    }

    /**
     * Check the return values from cache operations.
     *
     * @param bool[] $booleans The return values from cache operations.
     *
     * @return bool `true` if all operations succeeded, `false` otherwise.
     */
    protected function checkReturn(array $booleans): bool
    {
        foreach ($booleans as $boolean) {
            if (!$boolean) {
                return false;
            }
        }

        return true;
    }
}
