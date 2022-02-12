<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\actions\metabox;

use function Bhittani\StarRating\core\functions\get_meta_hof;
use function Bhittani\StarRating\core\functions\view;
use WP_Post;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function content(?string $type, WP_Post $post = null): void
{
    $get = get_meta_hof(null, $post ? $post->ID : 0);

    echo view('metabox/content.php', compact('get', 'type', 'post'));
}
