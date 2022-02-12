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

use function Bhittani\StarRating\functions\view as base_view;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/** @throws InvalidArgumentException If the template is not found */
function view(string $path, array $payload = []): string
{
    return base_view(kksr('core.views'), $path, $payload);
}
