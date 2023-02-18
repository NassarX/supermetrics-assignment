<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        // accumulate posts stats
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        return new StatisticsTo();
    }
}
