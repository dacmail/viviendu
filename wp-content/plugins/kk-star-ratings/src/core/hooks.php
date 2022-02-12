<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function Bhittani\StarRating\functions\hook;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

foreach (kksr('core.actions') as $fn) {
    hook('action', $fn, $fn, 9);
}

foreach (kksr('core.filters') as $fn) {
    hook('filter', $fn, $fn, 9);
}
