<?php
use PHPUnit\Framework\TestCase;

use josterholt\Service\GoogleService;

class GoogleServiceTest extends TestCase
{
    /**
     * Asserts that Google Service can be initialized.
     *
     * @covers GoogleService
     * 
     * @return void
     */
    public function testInitialize()
    {
        $googleClient = $this->createStub("Google\Client");
        $logger = $this->createStub("Psr\Log\LoggerInterface");

        $googleService = new GoogleService($googleClient, "client_secret.json",
            "access_token.json", $logger
        );
        $googleService->initialize("testcode");
    }

    /**
     * @covers GoogleService
     */
    public function testGetYouTubeAPIService()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers GoogleService
     */
    public function testGetAll()
    {
        $this->markTestIncomplete();
    }
}
