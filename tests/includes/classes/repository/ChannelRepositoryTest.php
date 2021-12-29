<?php

use Google\Service\YouTube;
use PHPUnit\Framework\TestCase;
use josterholt\Service\GoogleAPIFetch;
use josterholt\Repository\ChannelRepository;


class ChannelRepositoryTest extends TestCase
{
    /**
     * @covers ChannelRepository
     */    
    public function testCanGetSubscriptionById()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        // @php-ignore
        $readAdapter->method('get')->willReturn(['Placeholder']);

        $youTubeAPI = $this->createStub(YouTube::class);

        $channelRepo = new ChannelRepository($logger, $readAdapter, $youTubeAPI);
        $channels = $channelRepo->getBySubscriptionId(1);

        $this->assertEquals(count($channels), 1);
    }
}
