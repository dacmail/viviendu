<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\wp\actions;

use function Bhittani\StarRating\core\functions\meta_prefix;
use function Bhittani\StarRating\core\functions\option;
use function Bhittani\StarRating\core\functions\post_meta;
use function Bhittani\StarRating\functions\cast;
use WP_Post;
use WP_Query;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function the_post(WP_Post $post, WP_Query $query = null): void
{
    $id = $post->ID;

    if (metadata_exists('post', $id, meta_prefix('avg'))) {
        // v5
        // if (metadata_exists('post', $id, meta_prefix('count_default'))) {
        //     $count = max((int) post_meta($id, 'count_default'), 0);
        // }

        // // v5
        // if (metadata_exists('post', $id, meta_prefix('ratings_default'))) {
        //     $ratings = max((float) post_meta($id, 'ratings_default'), 0);
        // }

        // < v5
        // if (! isset($count)) {
        $count = max((int) post_meta($id, 'casts'), 0);
        // }

        // v3, v4
        if (/*! isset($ratings) && */ metadata_exists('post', $id, meta_prefix('ratings'))) {
            $ratings = max((float) post_meta($id, 'ratings'), 0);
        }

        // < v3
        if (! isset($ratings)) {
            $stars = max((int) option('stars', 5), 1);
            $avg = min(max((float) post_meta($id, 'avg'), 0), $stars);
            // 4 * 3 / 5 * 5 => 12
            // 8 * 3 / 10 * 5 => 12
            $ratings = cast($avg, 5, $stars) * $count; // Reset to base 5.
            // $ratings = $avg * $count / $stars * 5; // Reset to base 5.
        }

        // Calculate fresh average.
        $avg = $count ? ($ratings / $count) : 0;

        post_meta($id, [
            'avg' => $avg,
            'casts' => $count,
            'avg_default' => $avg,
            'count_default' => $count,
            'ratings_default' => $ratings,
        ]);
    }
}
