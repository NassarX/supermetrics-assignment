<?php

declare(strict_types=1);

namespace Tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use SocialPost\Client\Factory\FictionalClientFactory;
use SocialPost\Client\SocialClientInterface;

/**
 * Class ClientTest
 *
 * @package Tests\unit
 */
class ClientTest extends TestCase
{
    private SocialClientInterface $client;

    private const REGISTER_TOKEN_URI = '/assignment/register';

    private const FETCH_POSTS_URI = '/assignment/posts';

    private const FICTIONAL_SOCIAL_API_HOST = 'https://api.supermetrics.com';

    private array $registerTokenResponse;

    public function setUp(): void
    {
        // Get ClientId
        $clientId = json_decode(file_get_contents(dirname(__DIR__)
            . "/data/auth-token-response.json"), true)['data']['client_id'];

        // Set the environment variable
        $_ENV['FICTIONAL_SOCIAL_API_HOST'] = self::FICTIONAL_SOCIAL_API_HOST;
        $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID'] = $clientId;

        // Set up client
        $this->client = FictionalClientFactory::create();

        // Set up user data
        $userData = ['email' => 'your@email.address', 'name'  => 'YourName'];

        try {
            # Call authRequest to register a new token
            $response = $this->client->authRequest(self::REGISTER_TOKEN_URI, $userData);
            $this->registerTokenResponse = json_decode($response, true);
        } catch (Exception $e) {
            $this->expectExceptionMessage($e->getMessage());
            // Skip throwing the exception and move on to the assertion
        }
    }

    public function testRegisterTokenEndpoint()
    {
        // Check that the 'data' key exists in the register token response array
        $this->assertArrayHasKey('data', $this->registerTokenResponse, "Invalid SL Token");
    }

    /**
     * @depends testRegisterTokenEndpoint
     */
    public function testFetchPostsEndPoint()
    {
        $parameters = ['page' => 1, 'sl_token' => $this->registerTokenResponse['data']['sl_token']];

        # Call get with fetch url to fetch posts from a page
        $response = $this->client->get(self::FETCH_POSTS_URI, $parameters);
        $response = json_decode($response, true);

        // Check that the 'data' key exists in the fetch posts response array
        $this->assertArrayHasKey('data', $response, "INVALID_CLIENT_ID");
    }
}
