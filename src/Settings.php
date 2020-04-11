<?php

namespace Hogus\LaravelSettings;

use Hogus\LaravelSettings\Contracts\Repository;
use Hogus\LaravelSettings\Repositories\RedisRepository;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Class Settings
 * @package App\Libraries\Settings
 */
class Settings implements Repository
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var RedisRepository
     */
    protected $cache;

    /**
     * @var boolean
     */
    protected $cacheEnabled;

    /**
     * Settings constructor.
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Enable cache.
     *
     * @return void
     */
    public function enableCache()
    {
        $this->cacheEnabled = true;
    }

    /**
     * Disable cache.
     *
     * @return void
     */
    public function disableCache()
    {
        $this->cacheEnabled = false;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled && !is_null($this->cache) ? true : false;
    }

    /**
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->repository->has($key);
    }

    /**
     * @param array|string $key
     * @param null $value
     */
    public function set($key, $value = null)
    {
        $this->repository->set($key, $value);

        if ($this->isCacheEnabled()) {
            $this->cache->set($key, $value);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->isCacheEnabled()) {
            $value = $this->cache->rememberForever($key, function () use ($key, $default) {
                return $this->repository->get($key, $default);
            });
        } else {
            $value = $this->repository->get($key, $default);
        }

        return $value;
    }

    /**
     * @param string $key
     */
    public function forget($key)
    {
        $this->repository->forget($key);

        if ($this->isCacheEnabled()) {
            $this->cache->forget($key);
        }
    }
}
