<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function Bhittani\StarRating\functions\to_shortcode;

/** @param int|string|array|object|WP_POST|null $idOrPostOrPayload */
function kk_star_ratings($idOrPostOrPayload = null): string
{
    $payload = [];

    if (is_array($idOrPostOrPayload)) {
        $payload = $idOrPostOrPayload;
    }

    if (is_object($idOrPostOrPayload)) {
        $payload['id'] = $idOrPostOrPayload->ID;
    }

    if ($idOrPostOrPayload) {
        $payload['id'] = $idOrPostOrPayload;
    }

    $payload['reference'] = 'template';

    return do_shortcode(to_shortcode(kksr('slug'), $payload));
}
