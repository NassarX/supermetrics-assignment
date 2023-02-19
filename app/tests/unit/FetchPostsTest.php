<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use SocialPost\Driver\FictionalDriver;
use SocialPost\Client\SocialClientInterface;
use SocialPost\Driver\SocialDriverInterface;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class FetchPostsTest extends TestCase
{
    /**
     * @var SocialDriverInterface $driver
     */
    private SocialDriverInterface $driver;

    public function setUp(): void
    {
        // Set up mock client with expected behavior
        $mockClient = $this->createMock(SocialClientInterface::class);

        // Mock the registerToken method on the driver object
        $this->driver = $this->getMockBuilder(FictionalDriver::class)
            ->setConstructorArgs([$mockClient])
            ->onlyMethods(['fetchPostsByPage'])
            ->getMock();

        // Set up the mocked posts response
        $this->mockPostsResponse = json_decode(file_get_contents(dirname(__DIR__)
            . "/data/social-posts-response.json"), true)['data']['posts'];

        // Set up mock driver with expected behavior
        $this->driver->expects($this->once())
            ->method('fetchPostsByPage')
            ->with(1)
            ->willReturn(new \ArrayIterator($this->mockPostsResponse));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFetchPostsByPage(): void
    {
        // call fetchPostsByPage method
        $expectedPageData = iterator_to_array($this->driver->fetchPostsByPage(1));

        // Assert that the array contains the expected number of posts
        $this->assertCount(count($expectedPageData), $this->mockPostsResponse);

        // Assert that each post has the expected all properties
        foreach ($expectedPageData as $i => $post) {
            $this->assertEquals($this->mockPostsResponse[$i], $post);
        }
    }
}
