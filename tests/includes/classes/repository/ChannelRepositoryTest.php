<?php

use PHPUnit\Framework\TestCase;
use josterholt\service\Fetch;

class ChannelRepositoryTest extends TestCase {
    public function testCanGetSubscriptionById() {
        $readAdapter = $this->createStub(Fetch::class);
        $readAdapter->method('get')->willReturn(['Placeholder']);

        $channelRepo = new josterholt\repository\ChannelRepository();
        $channelRepo->setReadAdapter($readAdapter);
        $channels = $channelRepo->getBySubscriptionId(1);

        $this->assertEquals(count($channels), 1);
    }

    public function testWillThrowExceptionIfReadAdapterNotSet() {
        $readAdapter = null;

        $channelRepo = new josterholt\repository\ChannelRepository();
        $channelRepo->setReadAdapter($readAdapter);

        $this->expectException(Error::class);
        $channelRepo->getBySubscriptionId(1);

    }
}