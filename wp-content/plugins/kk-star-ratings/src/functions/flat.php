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

/** Flatten items into dot notation. */
function flat(array $items, int $depth = -1): array
{
    $flat = [];

    foreach ($items as $key => $value) {
        if (! is_array($value)
            || $depth == 0
        ) {
            $flat[$key] = $value;

            continue;
        }

        foreach (flat($value, $depth - 1) as $_key => $_value) {
            $flat[$key.'.'.$_key] = $_value;
        }
    }

    return $flat;
}
