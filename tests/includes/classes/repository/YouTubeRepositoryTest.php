<?php

use josterholt\Repository\YouTubeRepository;
use josterholt\Service\GoogleAPIFetch;
use josterholt\Service\GoogleService;
use PHPUnit\Framework\TestCase;

class YouTubeRepositoryTest extends TestCase
{
    public function testCanCreateYouTubeRepositoryObject() {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
        ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $googleService = $this->createStub(GoogleService::class);

        $mock = $this->getMockBuilder(YouTubeRepository::class)
        ->setConstructorArgs([$logger, $readAdapter, $googleService])
        ->getMockForAbstractClass();
        $this->assertNotEmpty($mock);
    }

    public function testWillThrowExceptionIfArgumentDependenciesMissing() {
        $this->expectException(TypeError::class);
        $stub = $this->getMockBuilder(YouTubeRepository::class)
            ->setConstructorArgs([null, null])
            ->getMockForAbstractClass();
    }
    
    public function testCanEnableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
        ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $googleService = $this->createStub(GoogleService::class);
        
        $mock = $this->getMockForAbstractClass(YouTubeRepository::class, [$logger, $readAdapter, $googleService]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');
        $mock->disableReadCache(); // Cache is enabled by default

        $mock->enableReadCache();
        $this->assertTrue($mock->getReadCacheState());
    }

    public function testCanDisableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
        ->getMockForAbstractClass();
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $googleService = $this->createStub(GoogleService::class);
        
        $mock = $this->getMockForAbstractClass(YouTubeRepository::class, [$logger, $readAdapter, $googleService]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');

        $mock->disableReadCache();
        $this->assertFalse($mock->getReadCacheState());
    }

    public function testCanGetAllItems()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function testCanGetItemById()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function testCanCreateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function testCanUpdateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function testCanDeleteItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }
}
?>