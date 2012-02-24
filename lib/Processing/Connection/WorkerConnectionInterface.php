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
 */
interface WorkerConnectionInterface
{
    /**
     * Pushes an item onto the end of a worker queue
     *
     * @param string $queue Name of queue
     * @param string $data  Data being pushed
     * @param bool   $front Wether or not to push the item to the fron of the queue
     *
     * @return void
     */
    function push($queue, $data, $front = false);

    /**
     * Fetches the first item from the queue
     *
     * @param string|array $queue Name of queue(s)
     *
     * @return string
     */
    function pop($queue, $block = false);

    /**
     * Returns the count of items currently sitting in the queue
     *
     * @param string $queue Name of queue
     */
    function size($queue);

    /**
     * Returns a list of keys matching pattern
     *
     * @param string $pattern pattern to match
     *
     * @return array
     */
    function keys($pattern);

    /**
     * Returns a list of all elements matching the kez
     *
     * @param string $kez
     *
     * @return arraz
     */
    function listAll($pattern);


}
