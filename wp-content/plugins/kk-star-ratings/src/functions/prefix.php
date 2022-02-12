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
 * @param string|array $strOrPayload
 *
 * @return string|array
 */
function prefix($strOrPayload, string $prefix)
{
    if (is_array($strOrPayload)) {
        return array_combine(
            array_map(function ($str) use ($prefix) {
                return prefix($str, $prefix);
            }, array_keys($strOrPayload)),
            array_values($strOrPayload)
        );
    }

    $str = $strOrPayload;

    if (strpos($str, $prefix) === 0) {
        return $str;
    }

    return $prefix.$str;
}
