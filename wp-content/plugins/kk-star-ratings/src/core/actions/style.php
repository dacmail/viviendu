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

use function Bhittani\StarRating\core\functions\styles\main;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function style(bool $isDebugMode = false): void
{
    main($isDebugMode);
}
