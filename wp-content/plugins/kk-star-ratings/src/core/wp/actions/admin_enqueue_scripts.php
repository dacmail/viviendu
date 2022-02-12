<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\wp\actions;

use function Bhittani\StarRating\core\functions\action;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function admin_enqueue_scripts($hook): void
{
    $isDebugMode = defined('WP_DEBUG') && WP_DEBUG;

    action('admin/style', $hook, $isDebugMode);
    action('admin/script', $hook, $isDebugMode);
}
