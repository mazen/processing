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

class RedisConnectionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension required');
        }
        $this->object = new RedisConnection(array('host' => 'localhost',
                                                  'port' => 6379));
    }

    /**
     * @covers Processing\Connection\RedisConnection::__construct
     */
    public function testConstructorArgs()
    {
        $conn = new RedisConnection();
        $conn -> connect();
        $this->assertTrue($conn->isConnected());

        $conn = new RedisConnection(array(
            'host' => '127.0.0.1',
            'port' => 6379
        ));
        $conn -> connect();
        $this->assertTrue($conn->isConnected());
    }

    /**
     * @covers Processing\Connection\RedisConnection::isConnected
     * @covers Processing\Connection\RedisConnection::connect
     */
    public function testCanCreateConnection()
    {
        $this->object->connect();
        $this->assertTrue($this->object->isConnected());
    }

    /**
     * @covers Processing\Connection\RedisConnection::push
     * @covers Processing\Connection\RedisConnection::pop
     */
    public function testCanPushItems()
    {
        $res = $this->object->push('tests', '123');
        $this->assertEquals('123', $this->object->pop('tests'));
    }

    /**
     * @covers Processing\Connection\RedisConnection::keys
     */
    public function testCanGetKeys() {
        $this->object->push('tests', '123');
        $this->assertEquals(array('tests'), $this->object->keys('test*'));
        $this->object->pop('tests');
    }

    /**
     * @covers Processing\Connection\RedisConnection::listAll
     */
    public function testCanListKeys() {

        $this->object->push('tests', '123');
        $this->assertEquals(array(123), $this->object->listAll('tests'));
        $this->object->pop('tests');
    }

    /**
     * @covers Processing\Connection\RedisConnection::size
     */
    public function testCanFetchSizes()
    {
        $this->assertEquals(0, $this->object->size('tests'));
        $this->object->push('tests', '123');
        $this->assertEquals(1, $this->object->size('tests'));
        $this->object->pop('tests', '123');
    }
}
