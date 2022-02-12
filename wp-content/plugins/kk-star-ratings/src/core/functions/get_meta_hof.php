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

use function Bhittani\StarRating\functions\get_hof;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/** @param int|string $id */
function get_meta_hof(?array $payload, $id): callable
{
    $delegate = function (string $key, $default = null) use ($id) {
        return post_meta($id, $key, $default);
    };

    return get_hof($payload, $delegate, '_'.kksr('nick').'_', array_map('gettype', kksr('core.post_meta')));
}
