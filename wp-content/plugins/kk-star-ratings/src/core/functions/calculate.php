<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\functions;

use function Bhittani\StarRating\functions\cast;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function calculate(int $id, string $slug, int $best = 5): array
{
    $count = (int) filter('count', null, $id, $slug);
    $ratings = (float) filter('ratings', null, $id, $slug);
    $score = $count ? ($ratings / $count) : 0;
    $score = (float) min(max(0, cast($score, $best)), $best);
    $score = filter('score', $score);

    return [$count, $score];
}
