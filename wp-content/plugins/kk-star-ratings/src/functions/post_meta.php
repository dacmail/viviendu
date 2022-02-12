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
 * Get or update post meta.
 *
 * @param int|string $id
 * @param array|string $keyOrMeta
 * @param mixed|null $default
 */
function post_meta($id, $keyOrMeta, $default = null, string $prefix = null, array $fallback = null)
{
    $prefix = (string) $prefix;
    $fallback = (array) $fallback;

    $explodeSingle = function ($key) {
        if (strpos($key, '[]') === (strlen($key) - 2)) {
            return [substr($key, 0, -2), false];
        }

        return [$key, true];
    };

    if (is_array($keyOrMeta)) {
        foreach ($keyOrMeta as $key => &$value) {
            $key = strip_prefix($key, $prefix);
            [$key, $isSingle] = $explodeSingle($key);
            $type = gettype(find($fallback, $key));
            $value = type_cast($value, $type == 'boolean' ? 'integer' : $type);

            if ($isSingle) {
                update_post_meta($id, $prefix.$key, $value);

                continue;
            }

            add_post_meta($id, $prefix.$key, $value);
        }

        return $keyOrMeta;
    }

    $key = strip_prefix($keyOrMeta, $prefix);
    $fallbackValue = find($fallback, $key);
    [$key, $isSingle] = $explodeSingle($key);

    if (is_null($default)) {
        $default = $fallbackValue;
    }

    $value = $default;

    if (metadata_exists('post', $id, $prefix.$key)) {
        $value = get_post_meta($id, $prefix.$key, $isSingle);
    }

    if (! $isSingle) {
        return (array) ($value ?: null);
    }

    return type_cast($value, gettype($fallbackValue));
}
