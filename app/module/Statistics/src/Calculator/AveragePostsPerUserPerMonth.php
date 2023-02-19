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
    private array $monthlyPosts = [];

    /**
     * Accumulate posts statics.
     *
     * @param SocialPostTo $postTo
     * @return void
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $userId = $postTo->getAuthorId();
        $monthPosts = $postTo->getDate()->format('M, Y');

        // Check if an entry exists for the user else create an entry with default values
        $this->monthlyPosts[$monthPosts]['count'] = ($this->monthlyPosts[$monthPosts]['count'] ?? 0) + 1;

        // Add unique user Ids
        if (!in_array($userId, $this->monthlyPosts[$monthPosts]['users'] ?? [], true)) {
            $this->monthlyPosts[$monthPosts]['users'][] = $userId;
        }
    }

    /**
     * Calculate Average number of posts per user for each month.
     *
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        $stats->setName($this->parameters->getStatName());

        foreach ($this->monthlyPosts as $month => $monthDetails) {
            $postsCount = $monthDetails['count'] ?? 0;
            $postsUsersCount = count($monthDetails['users'] ?? []);

            // Get user posts monthly average rounded
            $monthlyAverage = round(($postsCount / $postsUsersCount), 0, PHP_ROUND_HALF_DOWN);
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($month)
                ->setValue($monthlyAverage)
                ->setUnits(self::UNITS);
            $stats->addChild($child);
        }

        return $stats;
    }
}
