<?php

/**
 * Wrapper for Google Service and YouTube API.
 */

namespace josterholt\Service;

use \Google\Service\YouTube;
use \Google\Client;
use Psr\Log\LoggerInterface;

/**
 * Utility class for holding Google API client
 * and methods to fetch service for API calls.
 * 
 * Use:
 * 1. GoogleService::initialize();
 * 2. GoogleService::getYouTubeAPIService();
 */
class GoogleService
{
    /**
     * Instance of Google Client used to with APIs.
     * 
     * @var Client
     */
    protected $client = null;

    /**
     * Instance of YouTubeAPI service.
     * 
     * @var YouTube
     */
    protected $youTubeAPIService = null;

    /**
     * Path to client secret file.
     * 
     * @var string
     */
    private $_clientSecretPath = null;

    /**
     * Path to access token file.
     * 
     * @var string
     */
    private $_accessTokenPath = null;

    /**
     * Instance of a logger.
     * 
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Indicates whether or not user has been authenticated.
     */
    public $isAuthenticated = false;

    /**
     * Constructor for GoogleService class.
     * 
     * @param Client          $client           Instance of Google Client
     * @param string          $clientSecretPath Path to client secret, Google client
     * @param string          $accessTokenPath  Path to auth token, used for read 
     *                                          and writing.
     * @param LoggerInterface $logger           Instance of a logger
     * 
     * @return void
     */
    public function __construct(
        Client $client,
        string $clientSecretPath,
        string $accessTokenPath,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->_clientSecretPath = $clientSecretPath;
        $this->_accessTokenPath = $accessTokenPath;
        $this->logger = $logger;
    }

    /**
     * Initializes Google client and YouTube API service
     * 
     * @return void
     */
    public function initialize($code = null)
    {
        $this->_initGoogleClient();

        if (!$this->checkClientAccess($code)) {
            $this->isAuthenticated = false;
        } else {
            $this->isAuthenticated = true;
        }

        // Automatically refresh token if it has expired
        // if (file_exists($this->_accessTokenPath)) {
        //     echo "Token removed\n";
        //     unlink($this->_accessTokenPath);
        // }

        if (!$this->checkClientAccess($code)) {
            $this->isAuthenticated = false;
        } else {
            $this->isAuthenticated = true;
        }
    }

    /**
     * Initializes Google client.
     * 
     * @return void
     */
    private function _initGoogleClient()
    {
        $this->client->setApplicationName('API code samples');
        $this->client->setScopes(
            [
                'https://www.googleapis.com/auth/youtube.readonly',
            ]
        );

        // https://cloud.google.com/iam/docs/creating-managing-service-account-keys
        $this->logger->debug("Loading config from: " . $this->_clientSecretPath);
        $this->client->setAuthConfig($this->_clientSecretPath);
        $this->client->setAccessType('offline');
    }

    /**
     * Return instance of YouTube API service.
     * 
     * @return Youtube
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Loads access code from URL or file. Prompts for new access code if not found or expired.
     * TODO: This needs to be broken apart into smaller pieces. Redirect needs to be separated.
     * 
     * Returns true on successful token retrieval and false on failure.
     * 
     * @return bool
     */
    protected function checkClientAccess($code = null): bool
    {
        $accessToken = $this->getAccessTokenFromFile($this->_accessTokenPath);

        if ($accessToken == null && !empty($code)) {
            $accessToken = $this->_getAccessTokenFromCode($code);

            if (empty($accessToken['error'])) {
                $this->storeAccessTokenToFile($this->_accessTokenPath, $accessToken);
            }
        }

        if (!empty($accessToken) && !empty($accessToken['error'])) {
            $accessToken = null;
        }

        if (empty($accessToken)) {
            return false;
        } else {
            $this->client->setAccessToken($accessToken);

            $tokenCallback = function ($cacheKey, $accessToken) {
                $accessTokenNew = $this->getAccessTokenFromFile($this->_accessTokenPath);
                $accessTokenNew['access_token'] = $accessToken;
                $this->storeAccessTokenToFile($this->_accessTokenPath, $accessTokenNew);
                $this->client->setAccessToken($accessToken);
            };
            $this->client->setTokenCallback($tokenCallback);
        }

        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if (!empty($refreshToken)) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (empty($newToken['error'])) {
                    $this->storeAccessTokenToFile($this->_accessTokenPath, $this->client->getAccessToken());
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Checks for existing access token or code (ability to get access token).
     * If one does not exist, redirects user to authorization page.
     *
     * @return bool True if redirect header is set
     */
    public function getAuthorizationPageURL(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Returns authentication error
     */
    public function getAuthenticationError(): string
    {
        // return $this->client->getAccessTokenResponse()['error'];
        echo "Authentication error\n";
    }

    /**
     * Get access token from query string.
     * 
     * @param Client $client Google client for API connection.
     * @param string $code Code from query string.
     * 
     * @return ?array Access token granted from Google API using code.
     */
    private function _getAccessTokenFromCode(string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        return $this->client->fetchAccessTokenWithAuthCode(trim($code));
    }

    /**
     * Store access token to file for later use.
     * 
     * @param string     $filePath    Path to token file
     * @param array|null $accessToken Access token data to store
     * 
     * @return bool
     */
    protected function storeAccessTokenToFile(
        string $filePath,
        array|null $accessToken
    ): bool {
        if (file_put_contents($filePath, json_encode($accessToken)) === false) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves access token from file.
     *
     * @param string $filePath Path to token file
     *
     * @return array
     */
    protected function getAccessTokenFromFile(string $filePath): array|null
    {
        if (!file_exists($filePath)) {
            return null;
        }

        return (array) json_decode(file_get_contents($filePath));
    }

    /**
     * Runs the full OAuth 2.0 loopback flow for installed/desktop apps.
     *
     * Binds a one-shot HTTP listener on a random local port, sets that port
     * as the redirect URI, opens the consent URL in the default browser,
     * captures the authorization code from the callback, exchanges it for
     * an access+refresh token, and persists the token to disk.
     *
     * @return bool True on success, false if the flow times out or fails.
     */
    public function authenticateViaLoopback(): bool
    {
        $port = 8089; // $this->_getAvailablePort();
        $this->client->setRedirectUri("http://127.0.0.1:{$port}");
        $this->client->setPrompt('consent');

        $authUrl = $this->client->createAuthUrl();

        echo "\033[32mOpening browser for Google authentication...\033[39m\n";
        echo "\033[32mIf the browser does not open, visit this URL:\033[39m\n";
        echo "\033[34m{$authUrl}\033[39m\n\n";
        echo "\033[32mWaiting for authorization (2-minute timeout)...\033[39m\n";

        $this->_openBrowser($authUrl);

        $code = $this->_waitForCallbackCode($port);

        if (empty($code)) {
            echo "\033[31mAuthentication timed out or no code was received.\033[39m\n";
            return false;
        }

        $accessToken = $this->_getAccessTokenFromCode($code);

        if (empty($accessToken) || !empty($accessToken['error'])) {
            $error = $accessToken['error'] ?? 'unknown error';
            echo "\033[31mFailed to exchange code for token: {$error}\033[39m\n";
            return false;
        }

        $this->storeAccessTokenToFile($this->_accessTokenPath, $accessToken);
        $this->client->setAccessToken($accessToken);
        $this->isAuthenticated = true;

        echo "\033[32mAuthentication successful!\033[39m\n";
        return true;
    }

    /**
     * Finds an available TCP port by binding to port 0 and reading back the
     * assigned port, then immediately releasing it.
     */
    private function _getAvailablePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if (!$socket) {
            throw new \RuntimeException("Could not bind ephemeral port: {$errstr}");
        }
        $name = stream_socket_get_name($socket, false);
        $port = (int) substr($name, strrpos($name, ':') + 1);
        fclose($socket);
        return $port;
    }

    /**
     * Attempts to open $url in the default OS browser.
     */
    private function _openBrowser(string $url): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec('rundll32 url.dll,FileProtocolHandler ' . escapeshellarg($url) . ' >NUL 2>&1');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('open ' . escapeshellarg($url) . ' >/dev/null 2>&1');
        } else {
            exec('xdg-open ' . escapeshellarg($url) . ' >/dev/null 2>&1');
        }
    }

    /**
     * Starts a one-shot HTTP listener on 127.0.0.1:$port, waits for the
     * OAuth redirect, sends a success/failure page to the browser, and
     * returns the authorization code (or null on timeout/error).
     */
    private function _waitForCallbackCode(int $port, int $timeoutSeconds = 120): ?string
    {
        $server = stream_socket_server("tcp://0.0.0.0:{$port}", $errno, $errstr);
        if (!$server) {
            throw new \RuntimeException("Could not start callback listener on port {$port}: {$errstr}");
        }

        echo "\033[32mListening for OAuth callback on http://127.0.0.1:{$port}\033[39m\n";
        $code = null;
        $conn = @stream_socket_accept($server, $timeoutSeconds);
        echo "\033[32mReceived connection from OAuth callback\033[39m\n";

        if ($conn) {
            $request = '';
            while (!feof($conn)) {
                $line = fgets($conn, 4096);
                if ($line === false || $line === "\r\n") {
                    break;
                }
                $request .= $line;
            }

            if (preg_match('/^GET [^?]*\?([^ ]*) HTTP/m', $request, $matches)) {
                parse_str($matches[1], $params);
                $code = $params['code'] ?? null;
            }

            $body = !empty($code)
                ? '<html><body><h1 style="color:green">Authorization successful!</h1><p>You may close this tab and return to the terminal.</p></body></html>'
                : '<html><body><h1 style="color:red">Authorization failed.</h1><p>No code received. Please try again.</p></body></html>';

            fwrite($conn, "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
            fclose($conn);
        }

        fclose($server);
        return $code;
    }
}
