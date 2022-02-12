<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\functions;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function explode_prefix(string $str, string $prefix): array
{
    if (strpos($str, $prefix) === 0) {
        $str = substr($str, strlen($prefix));
    }

    return [$prefix, $str];
}
