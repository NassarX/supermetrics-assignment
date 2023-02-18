<?php

declare(strict_types=1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Calculator\AveragePostsPerUserPerMonth;
use Statistics\Dto\ParamsTo;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class AveragePostsTest extends TestCase
{
    private ParamsTo $params;

    private array $mockedPosts;

    public function setUp(): void
    {
        $start_date = DateTime::createFromFormat('F,Y', 'December,2022')
            ->modify('first day of this month')->setTime(0, 0, 0);
        $end_date   = DateTime::createFromFormat('F,Y', 'March,2023')
            ->modify('last day of this month')->setTime(23, 59, 59);

        // Hydrate dates params
        $this->params = (new ParamsTo())->setStartDate($start_date)->setEndDate($end_date);

        // Set up the mocked posts from posts-response json
        $mockPostsResponse = json_decode(file_get_contents(dirname(__DIR__)
            . "/data/social-posts-response.json"), true)['data']['posts'];

        // Hydrate posts instances
        $postHydrator = new FictionalPostHydrator();
        foreach ($mockPostsResponse as $postData) {
            $this->mockedPosts[] = $postHydrator->hydrate($postData);
        }
    }

    public function testAveragePostsPerUserPerMonth(): void
    {
        // Set stat name
        $this->params->setStatName("Average Posts Per User Per Month");

        // Initiate AveragePostsPerUserPerMonth class and assign parameters
        $averagePostsPerUserStat = new AveragePostsPerUserPerMonth();
        $averagePostsPerUserStat->setParameters($this->params);

        foreach ($this->mockedPosts as $post) {
            $averagePostsPerUserStat->accumulateData($post);
        }
        $statsResults = $averagePostsPerUserStat->calculate();

        $splitPeriodValues = array_map(function ($child) {
            return [$child->getSplitPeriod() => $child->getValue()];
        }, $statsResults->getChildren());
        $expectedValues = array_merge(...$splitPeriodValues);

        // Set up the true values from stats json files
        $actualValues = json_decode(file_get_contents(dirname(__DIR__)
            . "/data/average-per-user-month-values.json"), true);

        // Assert posts stats per user per month
        $this->assertEquals($expectedValues, $actualValues);
    }
}
