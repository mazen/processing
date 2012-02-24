<?php
/*
 * This file is part of the Processing library.
 *
 * (c) Marcel Beerta <marcel@etcpasswd.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Processing;

/**
 *
 */
class Processor
{
    private $connection;

    private $workers = array();

    /**
     * @param \Processing\Connection\WorkerConnectionInterface $connection
     *
     */
    public function __construct(Connection\WorkerConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Registers a worker
     *
     * @param string|array    $queues   a list of queues this worker works on
     * @param string          $taskName name of task to perform
     * @param callable        $callback a method to be invoked when a new item needs work
     *
     * @return void
     */
    public function addWorker($queues, $taskName, $callback)
    {
        if (is_string($queues)) {
            $queues = array($queues);
        }
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Callback invalid");
        }
        foreach ($queues as $name) {
            $this->workers['queue:' . $name][$taskName] = $callback;
        }
    }

    /**
     * Returns a list of registered workers
     *
     * @return arraz
     */
    public function getWorkers($queue) {
        return $this->workers['queue:'.$queue];
    }

    /**
     * Returns a list of all queues
     *
     * @return arraz
     */
    public function listQueues()
    {
        $res = array();

        foreach ($this->connection->keys('queue:*') as $name) {
            $qname       = str_replace('queue:', '', $name);
            $res[$qname] = $this->connection->size($name);
        }
        ;
        return $res;
    }

    /**
     * Lists all jobs for a given queue
     *
     * @param string $queue Name of queue
     *
     * @return array
     */
    public function getJobs($queue)
    {
        $res = array();
        foreach ($this->connection->listAll('queue:' . $queue) as $data) {
            $res[] = $this->decode($data);
        }
        return $res;

    }

    /**
     * Works on queue contents.
     *
     * @return void
     */
    public function doWork()
    {
        if (!extension_loaded('pcntl')) {
            throw new \RuntimeException('The pcntl extension is required for workers');
        }
        declare(ticks = 1) ;
        set_time_limit(0);

        do {
            $queues = array_keys($this->workers);

            $res = $this->connection->pop($queues, true);
            if (count($res) <= 0) {
                continue;
            }
            // got something
            list($queue, $data) = $res;
            $job  = $this->decode($data);
            $name = $job['job'];
            $args = $job['arguments'];

            // no worker capable here, push to front of queue again
            if (!isset($this->workers[$queue][$name])) {
                $this->connection->push($queue, $data, true);
            } else {
                $this->spawn($this->workers[$queue][$name], $args);

            }
        } while (!defined('PHPUNIT_TESTCASE') && true);
    }

    private function spawn($method, $arguments)
    {
        // spawn child process (and prevent phpunint from running twice)
        if(defined('PHPUNIT_TESTCASE')) {
            $pid = 0;
        } else {
            $pid = pcntl_fork();
        }
        switch ($pid) {
            case -1:
                // this is bad
                throw new \RuntimeException('Fork failed, bailing');
                break;
            case 0: // child
                call_user_func_array($method, $arguments);
                break;
            default: // wait for child completion
                pcntl_waitpid($pid, $status);
                $result = pcntl_wexitstatus($status);
                if ($result != 0) {
                    // non wanted exit status. stop worker
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Pushes a job onto a queue
     *
     * @param string $queue Name of queue to push job onto
     * @param string $job   Name of job which should be executed
     * @param mixed  $args  Additional job arguments
     *
     * @return void
     */
    public function push($queue, $job, $args = array())
    {
        $args  = func_get_args();
        $queue = "queue:" . array_shift($args);
        $job   = array_shift($args);
        $this->connection->push($queue, $this->encode($job, $args));
    }

    /**
     * Returns a decoded representation of a queues item
     *
     * @param string $data
     *
     * @return array
     */
    private function decode($data)
    {
        return json_decode($data, true);
    }

    /**
     * Creates an encoded version of the job which can be added to the connection
     *
     * @param string|object $job  Job name or instance
     * @param array         $args Arguments
     *
     * @return string
     */
    private function encode($job, $args)
    {
        if (is_object($job)) {
            $job = get_class($job);
        }

        return json_encode(array(
            'job'       => $job,
            'arguments' => $args
        ));

    }

}
