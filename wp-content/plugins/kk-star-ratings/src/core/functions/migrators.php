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

use function Bhittani\StarRating\functions\migrators as base_migrators;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function migrators(): array
{
    static $migrators;

    return is_null($migrators)
        ? ($migrators = base_migrators(kksr('core.migrators')))
        : $migrators;
}
