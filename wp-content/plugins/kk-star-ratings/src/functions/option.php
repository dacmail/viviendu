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
 * Get or update options.
 *
 * @param array|string $keyOrOptions
 * @param mixed|null $default
 */
function option($keyOrOptions, $default = null, string $prefix = null, array $fallback = null)
{
    $prefix = (string) $prefix;
    $fallback = (array) $fallback;

    if (is_array($keyOrOptions)) {
        foreach ($keyOrOptions as $key => &$value) {
            $key = strip_prefix($key, $prefix);
            $type = gettype(find($fallback, $key));
            $value = type_cast($value, $type == 'boolean' ? 'integer' : $type);
            update_option($prefix.$key, $value);
        }

        return $keyOrOptions;
    }

    $key = strip_prefix($keyOrOptions, $prefix);
    $fallbackValue = find($fallback, $key);
    $value = get_option($prefix.$key, $default ?? $fallbackValue);

    return type_cast($value, gettype($fallbackValue));
}
