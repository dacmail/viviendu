<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function Bhittani\StarRating\functions\container;

/** @param string|array|null $keyOrItems */
function kksr($keyOrItems = null, $default = null)
{
    static $config = [];

    return container($config, $keyOrItems, $default);
}
