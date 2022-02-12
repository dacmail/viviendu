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

use function Bhittani\StarRating\core\functions\option;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function activate(string $version, ?string $previous): void
{
    $options = [];

    foreach (array_keys(kksr('core.options')) as $key) {
        $options[$key] = option($key);
    }

    option($options);
}
