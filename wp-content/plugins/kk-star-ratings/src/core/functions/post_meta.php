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

use function Bhittani\StarRating\functions\post_meta as base_post_meta;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/**
 * Get or update post meta.
 *
 * @param int|string $id
 * @param array|string $keyOrMeta
 * @param mixed|null $default
 */
function post_meta($id, $keyOrMeta, $default = null)
{
    return base_post_meta($id, $keyOrMeta, $default, meta_prefix(''), kksr('core.post_meta'));
}
