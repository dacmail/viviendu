<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\functions;

use ReflectionFunction;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function hook(string $type, string $tag, string $fn, int $priority = 10): bool
{
    if (! function_exists($fn)) {
        return false;
    }

    $params = [$tag, $fn, $priority, (new ReflectionFunction($fn))->getNumberOfParameters()];

    if ($type == 'action') {
        return add_action(...$params);
    }

    if ($type == 'filter') {
        return add_filter(...$params);
    }

    return false;
}
