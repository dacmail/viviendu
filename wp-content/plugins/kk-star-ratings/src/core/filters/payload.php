<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\filters;

use function Bhittani\StarRating\core\functions\calculate;
use function Bhittani\StarRating\core\functions\option;
use function Bhittani\StarRating\core\functions\width;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function payload(array $payload): array
{
    $payload['id'] = (int) ($payload['id'] ?? get_post_field('ID'));

    if (! $payload['id']) {
        if (! ($payload['id'] = get_the_ID())) {
            $url = home_url(add_query_arg([]));
            $payload['id'] = url_to_postid($url);
        }
    }

    $payload['slug'] = $payload['slug'] ?? 'default';
    $payload['best'] = $payload['best'] ?? option('stars');

    [$count, $score] = calculate($payload['id'], $payload['slug'], $payload['best']);

    if (! isset($payload['count']) || ! (is_numeric($payload['count']) || $payload['count'])) {
        $payload['count'] = $count;
    }

    if (! isset($payload['score']) || ! (is_numeric($payload['score']) || $payload['score'])) {
        $payload['score'] = $score;
    }

    $payload['size'] = $payload['size'] ?? option('size');
    $payload['gap'] = $payload['gap'] ?? option('gap');

    $payload['width'] = width($payload['score'], $payload['size'], $payload['gap']);

    return $payload;
}
