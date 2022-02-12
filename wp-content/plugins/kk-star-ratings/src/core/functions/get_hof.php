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

use function Bhittani\StarRating\functions\get_hof as base_get_hof;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function get_hof(?array $payload): callable
{
    return base_get_hof($payload, kksr('core.functions.option'), kksr('nick').'_', array_map('gettype', kksr('core.options')));
}
