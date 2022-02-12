<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\filters;

use function Bhittani\StarRating\core\functions\post_meta;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function ratings(?float $ratings, int $id, string $slug): float
{
    if (! is_null($ratings)) {
        return $ratings;
    }

    return (float) post_meta($id, "ratings_{$slug}");
}
