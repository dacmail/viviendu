<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\functions;

use function Bhittani\StarRating\functions\option as base_option;

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
function option($keyOrOptions, $default = null)
{
    return base_option($keyOrOptions, $default, kksr('nick').'_', kksr('core.options'));
}
