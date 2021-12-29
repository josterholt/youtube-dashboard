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
    public function testWillHandleSuccessfulAuthentication()
    {
        $googleClient = $this->createStub("Google\Client");
        $authResponse = [
            "access_token" => "testtoken",
            "expires_in" => 3600,
            "token_type" => "Bearer",
            "refresh_token" => "testrefreshtoken"
        ];
        $googleClient->method("fetchAccessTokenWithAuthCode")
            ->willReturn($authResponse);


        $logger = $this->createStub("Psr\Log\LoggerInterface");

        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->getMockBuilder("josterholt\Service\GoogleService")
            ->setConstructorArgs(
                [
                    $googleClient, 
                    "client_secret.json",
                    "access_token.json",
                    $logger
                ]
            )
            ->onlyMethods(["storeAccessTokenToFile", "getAccessTokenFromFile"])
            ->getMock();
        $googleService->method("storeAccessTokenToFile")->willReturn(true);
        $googleService->method("getAccessTokenFromFile")->willReturn(null);
        $googleService->initialize("testcode");

        $this->assertTrue($googleService->isAuthenticated);
    }

    /**
     * Asserts that Google Service can handle failed authentication.
     * 
     * @covers GoogleService
     * 
     * @return void
     */
    public function testWillHandleFailedAuthentication()
    {
        $googleClient = $this->createStub("Google\Client");
        $authResponse = [
            "error" => "invalid_grant"
        ];
        $googleClient->expects($this->once())
            ->method("fetchAccessTokenWithAuthCode")
            ->willReturn($authResponse);


        $logger = $this->createStub("Psr\Log\LoggerInterface");

        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->getMockBuilder("josterholt\Service\GoogleService")
            ->setConstructorArgs(
                [
                    $googleClient, "client_secret.json",
                    "access_token.json", $logger
                ]
            )
            ->onlyMethods(["storeAccessTokenToFile", "getAccessTokenFromFile"])
            ->getMock();
        $googleService->method("storeAccessTokenToFile")->willReturn(true);
        $googleService->method("getAccessTokenFromFile")->willReturn(null);
        $googleService->initialize("testcode");

        $this->assertFalse($googleService->isAuthenticated);
    }

    // /**
    //  * Tests ability to fetch YouTubeAPIService instance from GoogleService.
    //  * 
    //  * @covers GoogleService
    //  * 
    //  * @return void
    //  */
    // public function testGetYouTubeAPIService()
    // {
    //     $googleClient = $this->createStub("Google\Client");
    //     $authResponse = [
    //         "error" => "invalid_grant"
    //     ];
    //     $googleClient->expects($this->once())
    //         ->method("fetchAccessTokenWithAuthCode")
    //         ->willReturn($authResponse);


    //     $logger = $this->createStub("Psr\Log\LoggerInterface");

    //     /**
    //      * @var GoogleService $googleService
    //      */
    //     $googleService = $this->getMockBuilder("josterholt\Service\GoogleService")
    //         ->setConstructorArgs(
    //             [
    //                 $googleClient, "client_secret.json",
    //                 "access_token.json", $logger
    //             ]
    //         )
    //         ->onlyMethods(["storeAccessTokenToFile", "getAccessTokenFromFile"])
    //         ->getMock();
    //     $googleService->method("storeAccessTokenToFile")->willReturn(true);
    //     $googleService->method("getAccessTokenFromFile")->willReturn(null);
    //     $googleService->initialize("testcode");

    //     $youTubeService = $googleService->getYouTubeAPIService();

    //     $this->assertNotEmpty($youTubeService);
    //     $this->assertInstanceOf("Google\Service\YouTube", $youTubeService);
    // }


}
