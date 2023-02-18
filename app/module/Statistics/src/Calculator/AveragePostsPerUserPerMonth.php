<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private array $userMonthlyPosts = [];

    /**
     * Accumulate posts data.
     *
     * @param SocialPostTo $postTo
     * @return void
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $user     = $postTo->getAuthorName() . ' (' . $postTo->getAuthorId() . ')';
        $postMonth  = $postTo->getDate()->format('M, Y');

        // Check if an entry exists for the user else create an entry with default values
        $this->userMonthlyPosts[$user] =
            $this->userMonthlyPosts[$user] ?? [$postMonth => 0];

        // Increment the count for the user and month
        $this->userMonthlyPosts[$user][$postMonth] =
            ($this->userMonthlyPosts[$user][$postMonth] ?? 0) + 1;
    }

    /**
     * Calculate Average number of posts per month foreach user.
     *
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        $stats->setName($this->parameters->getStatName());

        foreach ($this->userMonthlyPosts as $user => $postsPerMonth) {
            $monthPostsTotal = array_sum($postsPerMonth);
            $monthPostsCount = count($postsPerMonth);

            // Get user posts monthly average round to 2 precision
            $monthlyAverage = round(($monthPostsTotal / $monthPostsCount), 0, PHP_ROUND_HALF_DOWN);
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($user)
                ->setValue($monthlyAverage)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
