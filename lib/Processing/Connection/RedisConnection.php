<?php
/*
 * This file is part of the Processing library.
 *
 * (c) Marcel Beerta <marcel@etcpasswd.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Processing\Connection;

/**
 * Implements a worker connection which is using the php redis library
 * to communicate with back end servers
 *
 * @author Marcel Beerta <marcel@etcpasswd.de>
 */
class RedisConnection implements WorkerConnectionInterface
{
    private $connection;
    private $options;
    private $isConnected = false;

    /**
     * @param array $options connection options
     */
    public function __construct($options = null)
    {
        if (null === $options) {
            $options = array(
                'host' => 'localhost',
                'port' => 6379
            );
        }

        $this->options    = $options;
        $this->connection = new \Redis();
    }

    public function keys($pattern)
    {
        $this->connect();
        return $this->connection->keys($pattern);
    }

    public function listAll($key)
    {
        $this->connect();
        return $this->connection->lRange($key, 0, -1);
    }

    public function isConnected()
    {
        try {
            return $this->connection->ping() === '+PONG';
        } catch (\RedisException $e) {
            return false;
        }
    }

    public function connect()
    {
        if (!$this->isConnected()) {
            $this->connection->connect($this->options['host'], $this->options['port']);
            $this->connection->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            $this->isConnected = true;
        }
    }

    public function push($queue, $data, $front = false)
    {
        $this->connect();
        if ($front) {
            $this->connection->lpush($queue, $data);
        } else {
            $this->connection->rpush($queue, $data);
        }
    }

    public function pop($queue, $block = false)
    {
        $this->connect();
        if ($block) {
            return $this->connection->brpop($queue, 1);
        }
        return $this->connection->rpop($queue);
    }

    public function size($queue)
    {
        $this->connect();
        return $this->connection->lSize($queue);
    }

}
