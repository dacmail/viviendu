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

use function Bhittani\StarRating\core\functions\migrations;
use function Bhittani\StarRating\core\functions\script_migrations;
use function Bhittani\StarRating\core\functions\scripts\main;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function script(bool $isDebugMode = false): void
{
    // if (! migrations()->isEmpty()) {
    //     script_migrations($isDebugMode);
    // }

    main($isDebugMode);
}
