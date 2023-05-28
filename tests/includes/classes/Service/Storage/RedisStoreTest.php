<?php

use PHPUnit\Framework\TestCase;
// use josterholt\Service\Storage\RedisStore;
use josterholt\Service\Storage\RedisStore;

class RedisStoreTest extends TestCase
{
    /**
     * @covers RedisStore
     */
    public function testCanCallGet()
    {
        /** @var PSR\Log\LoggerInterface $logger */
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $redisInstance = $this->createStub('\Redislabs\Module\ReJSON\ReJSON');
        $redisInstance->method("get")->willReturn("[ \"test_value\" ]");


        $store = new RedisStore($logger, $redisInstance);
        $stored_value = $store->get("test_key");
        $this->assertIsArray($stored_value);
        $this->assertEquals($stored_value[0], "test_value");
    }

    /**
     * @covers RedisStore
     */
    public function testCanCallSet()
    {

        /** @var PSR\Log\LoggerInterface $logger */
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $redisMock = $this->createMock('\Redislabs\Module\ReJSON\ReJSON');
        $redisMock->expects($this->once())->method("set")->with("test_key", ".", "test");

        $store = new RedisStore($logger, $redisMock);
        $store->set("test_key", "test");
    }
}
