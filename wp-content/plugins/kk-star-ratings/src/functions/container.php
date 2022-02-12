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

/** @param string|array|null $keyOrItems */
function container(array &$container, $keyOrItems = null, $default = null)
{
    if (is_null($keyOrItems)) {
        return $container;
    }

    $dot = dot($container, $keyOrItems, $default);

    if (is_array($keyOrItems)) {
        $container = $dot;
    }

    return $dot;
}
