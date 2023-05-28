<?php

namespace josterholt\Service;

use josterholt\Service\Storage\AbstractStore;
use PHPUnit\Framework\TestCase;

class YouTubeTest extends TestCase
{
    public function testCanCreateYouTubeInstance()
    {
        /** @var AbstractStore $store  */
        $store = $this->getMockBuilder(AbstractStore::class)->getMockForAbstractClass();
        $youTube = new YouTube([], null, $store);
        $this->assertNotEmpty($youTube);
    }
}
