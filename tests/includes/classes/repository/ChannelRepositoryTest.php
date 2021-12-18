<?php

use PHPUnit\Framework\TestCase;
use josterholt\Service\GoogleAPIFetch;

class ChannelRepositoryTest extends TestCase {
    public function testCanGetSubscriptionById() {
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $readAdapter->method('get')->willReturn(['Placeholder']);

        $channelRepo = new josterholt\Repository\ChannelRepository();
        $channelRepo->setReadAdapter($readAdapter);
        $channels = $channelRepo->getBySubscriptionId(1);

        $this->assertEquals(count($channels), 1);
    }

    public function testWillThrowExceptionIfReadAdapterNotSet() {
        $readAdapter = null;

        $channelRepo = new josterholt\Repository\ChannelRepository();
        $channelRepo->setReadAdapter($readAdapter);

        $this->expectException(Error::class);
        $channelRepo->getBySubscriptionId(1);

    }
}