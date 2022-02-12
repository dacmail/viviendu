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

/**
 * Get or set items using dot notation.
 *
 * @param int|string|array $keyOrItems
 * @param mixed|null $default
 */
function dot(array $items, $keyOrItems, $default = null)
{
    if (is_array($keyOrItems)) {
        foreach ($keyOrItems as $key => $value) {
            $parts = explode('.', $key, 2);
            $head = array_shift($parts);
            $tail = array_shift($parts);

            if (! $tail) {
                $items[$head] = $value;

                continue;
            }

            $items[$head] = dot((array) ($items[$head] ?? []), [$tail => $value]);
        }

        return $items;
    }

    $key = $keyOrItems;

    $parts = explode('.', $key, 2);
    $head = array_shift($parts);
    $tail = array_shift($parts);

    if (! $tail) {
        return $items[$head] ?? $default;
    }

    return dot((array) ($items[$head] ?? []), $tail, $default);
}
