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

use function Bhittani\StarRating\core\functions\migrate;
use function Bhittani\StarRating\core\functions\migrations;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/** At this point, the plugin is ready (and activated). */
function init(array $config): void
{
    // if (migrations()->isPending()) {
    //     migrate();
    // }
}
