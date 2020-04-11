<?php

namespace Hogus\LaravelSettings\Repositories;

use Hogus\LaravelSettings\Contracts\Repository;
use Hogus\LaravelSettings\Contracts\ValueSerializer;
use Illuminate\Database\Connection;

/**
 * Class DatabaseRepository
 * @package App\Libraries\Settings\Repositories
 */
class DatabaseRepository implements Repository, ValueSerializer
{
    /**
     * Database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * Database table to store settings.
     *
     * @var string
     */
    protected $table;

    /**
     * Create new database repository.
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Determine if the given setting value exists.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->table()
            ->where('key', '=', $key)
            ->exists();
    }

    /**
     * Get the specified setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->table()
            ->where('key', '=', $key)
            ->value('value');

        return is_null($value) ? $default : $this->unserialize($value);
    }

    /**
     * Set a given setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $exists = $this->table()
            ->where('key', $key)
            ->where('value', $value)
            ->exists();

        $value = $this->serialize($value);

        if ($exists) {
            $this->table()->where('key', $key)->update(compact('value'));
        } else {
            $this->table()->insert(compact('key', 'value'));
        }
    }

    /**
     * Forget current setting value.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        return (bool) $this->table()->where('key', $key)->delete();
    }

    /**
     * Get a query builder for the settings table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->connection->table($this->table);
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
