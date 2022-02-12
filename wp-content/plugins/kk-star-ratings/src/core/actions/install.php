<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\actions;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function install(string $version): void
{
    // v3 and v4 did not save its version due to a bug
    // so we enforce a previous installation.
    // Hence, lets upgrade!
    upgrade($version, '5.0.0-alpha');
}
