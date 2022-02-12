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

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/** Bind values based on provided key value pairs. */
function bind(string $subject, array $payload): string
{
    foreach ($payload as $key => $value) {
        $subject = str_replace('{'.$key.'}', $value, $subject);
    }

    return $subject;
}
