<?php

use PHPUnit\Framework\TestCase;
use josterholt\Service\GoogleAPIFetch;
use josterholt\Repository\ChannelRepository;
use josterholt\Service\GoogleService;

class ChannelRepositoryTest extends TestCase {
    public function testCanGetSubscriptionById() {
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $readAdapter->method('get')->willReturn(['Placeholder']);

        $googleService = $this->createStub(GoogleService::class);

        $channelRepo = new ChannelRepository($readAdapter, $googleService);
        $channels = $channelRepo->getBySubscriptionId(1);

        $this->assertEquals(count($channels), 1);
    }
}