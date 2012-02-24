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
 * @author Marcel Beerta <marcel@etcpasswd.de>
 */
class ProcessingTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * @covers Processing\Processor::push
     * @covers Processing\Processor::encode
     * @covers Processing\Processor::__construct
     */
    public function testPush()
    {
        $processor = $this->getMockJobProcessor();
        $processor->push('tests', 'Processing\Fixtures\MockJob', 1, 'zip');
    }

    /**
     * @covers Processing\Processor::doWork
     * @covers Processing\Processor::spawn
     */
    public function testCanProcess()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension required');
            return;
        }

        define('PHPUNIT_TESTCASE', 1);
        $conn = $this->getMockConnection();
        $conn
            ->expects($this->once())
            ->method('pop')
            ->with(array('queue:test'), true)
            ->will($this->returnValue(array(
            0 => 'queue:test',
            1 => json_encode(array('job'       => 'test',
                                   'arguments' => array()))
        )));

        $processor = new Processor($conn);

        $invocationCount = 0;
        $processor->addWorker('test', 'test', function() use ($invocationCount)
        {
            $invocationCount++;
        });
        $processor->doWork();

        $this->assertEquals(1, $invocationCount);

    }

    /**
     * @covers Processing\Processor::addWorker
     * @covers Processing\Processor::getWorkers
     */
    public function testAddingWorkers()
    {
        $processor = new Processor($this->getMockConnection());
        $processor->addWorker('example-queue', 'ls', 'dir');
        $this->assertEquals(1, count($processor->getWorkers('example-queue')));
    }

    /**
     * @covers            Processing\Processor::addWorker
     * @expectedException InvalidArgumentException
     */
    public function testAddingBrokenWorker()
    {
        $processor = new Processor($this->getMockConnection());
        $processor->addWorker('example-queue', 'ls', array());
    }

    /**
     * @covers Processing\Processor::listQueues
     */
    public function testFetchingQueueList()
    {
        $connection = $this->getMockConnection();
        $connection
            ->expects($this->once())
            ->method('keys')
            ->with('queue:*')
            ->will($this->returnValue(array(
            'queue:test'
        )));

        $connection
            ->expects($this->once())
            ->method('size')
            ->with('queue:test')
            ->will($this->returnValue(5));

        $processor = new Processor($connection);

        $list = $processor->listQueues();

        $this->assertEquals(5, $list['test']);
    }

    /**
     * @covers Processing\Processor::getJobs
     * @covers Processing\Processor::decode
     */
    public function testGetJobs()
    {
        $connection = $this->getMockConnection();
        $connection
            ->expects($this->once())
            ->method('listAll')
            ->with('queue:test')
            ->will($this->returnValue(array(
            0 => json_encode(array(
                'job'       => 'ls',
                'arguments' => array('foo')
            ))
        )));

        $processor = new Processor($connection);
        $jobs      = $processor->getJobs('test');
        $this->assertEquals('ls', $jobs[0]['job']);
    }

    /**
     * @covers Processing\Processor::encode
     */
    public function testPushingObjectInstances()
    {
        $processor = $this->getMockJobProcessor();
        $processor->push('tests', new Fixtures\MockJob, 1, 'zip');
    }

    private function getMockJobProcessor()
    {
        $connection = $this->getMockConnection();
        $connection
            ->expects($this->once())
            ->method('push')
            ->with('queue:tests', '{"job":"Processing\\\\Fixtures\\\\MockJob","arguments":[1,"zip"]}');
        return new Processor($connection);
    }

    private function getMockConnection()
    {
        return $this->getMock('Processing\Connection\WorkerConnectionInterface');
    }

}
