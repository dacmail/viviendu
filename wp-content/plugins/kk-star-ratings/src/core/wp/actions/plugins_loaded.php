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
use function Bhittani\StarRating\core\wp\functions\activate;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function plugins_loaded()
{
    // We aren't using `register_activation_hook` because it is buggy
    // and does not get called when the plugin is implictly updated.
    activate();

    // Let everyone know that the plugin is loaded and initialized.
    action('init', kksr());
}
