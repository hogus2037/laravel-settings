<?php

namespace Hogus\LaravelSettings\Repositories;

use Closure;
use Hogus\LaravelSettings\Contracts\Repository;
use Hogus\LaravelSettings\Contracts\ValueSerializer;
use Illuminate\Contracts\Redis\Factory;

/**
 * Class RedisRepository
 * @package Hogus\LaravelSettings\Repositories
 */
class RedisRepository implements Repository, ValueSerializer
{
    /**
     * @var \Illuminate\Contracts\Redis\Factory
     */
    public $redis;

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var
     */
    protected $connection;

    /**
     * RedisRepository constructor.
     * @param Factory $redis
     * @param string $prefix
     * @param string $connection
     */
    public function __construct(Factory $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;

        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * @param string $key
     * @return bool|void
     */
    public function forget($key)
    {
        return  (bool) $this->connection()->del($this->prefix.$key);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $value = $this->connection()->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : $default;
    }

    /**
     * @param string $key
     * @param null $value
     * @return bool
     */
    public function set($key, $value = null)
    {
        return (bool) $this->connection()->set($this->prefix.$key, $this->serialize($value));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return (bool) $this->connection()->exists($this->prefix.$key);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  float|int  $minutes
     * @return bool
     */
    public function put($key, $value, $minutes)
    {
        return (bool) $this->connection()->setex(
            $this->prefix.$key, (int) max(1, $minutes * 60), $this->serialize($value)
        );
    }

    /**
     * @param $key
     * @param Closure $callback
     * @return mixed|null
     */
    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of minutes so it's available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix.$key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function forever($key, $value)
    {
        return (bool) $this->set($key, $value);
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * @param $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $value
     * @return string
     */
    public function serialize($value)
    {
        return is_numeric($value) ? $value : serialize($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}
