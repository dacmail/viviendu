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

function get_hof(?array $payload, callable $delegate, string $prefix = null, array $types = []): callable
{
    return function (string $key, $default = null) use ($payload, $delegate, $prefix, $types) {
        $key = strip_prefix($key, $prefix);

        $value = is_array($payload)
            ? ($payload[$prefix.$key] ?? null)
            : $delegate($key, $default);

        return [$prefix.$key, type_cast($value, $types[$key] ?? '')];
    };
}
