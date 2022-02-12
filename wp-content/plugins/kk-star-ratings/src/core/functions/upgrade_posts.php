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

use function Bhittani\StarRating\functions\cast;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function upgrade_posts(array $args = []): array
{
    $minId = $args['min_id'] ?? 0;
    $maxId = $args['max_id'] ?? null;
    unset($args['min_id'], $args['max_id']);

    $whereMaxIdClause = function ($where) use ($minId, $maxId) {
        global $wpdb;

        if ($minId) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID >= %d", $minId);
        }

        if ($maxId) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.ID <= %d", $maxId);
        }

        return $where;
    };

    $args = array_replace_recursive([
        'cache_results' => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'suppress_filters' => false,
        'fields' => 'ids',
        'order' => 'ASC',
        'orderby' => 'ID',
        'post_type' => 'any',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => meta_prefix('avg'),
                'compare' => 'EXISTS',
            ],
            [
                'key' => meta_prefix('ratings'),
                'compare' => 'EXISTS',
            ],
            [
                'key' => meta_prefix('ratings_default'),
                'compare' => 'EXISTS',
            ],
        ],
    ], $args);

    add_filter('posts_where', $whereMaxIdClause);
    $ids = get_posts($args);
    remove_filter('posts_where', $whereMaxIdClause);

    if (! $ids) {
        return [];
    }

    foreach ($ids as $id) {
        unset($avg, $count, $ratings);

        // v5
        if (metadata_exists('post', $id, meta_prefix('count_default'))) {
            $count = max((int) post_meta($id, 'count_default'), 0);
        }

        // v5
        if (metadata_exists('post', $id, meta_prefix('ratings_default'))) {
            $ratings = max((float) post_meta($id, 'ratings_default'), 0);
        }

        // < v5
        if (! isset($count)) {
            $count = max((int) post_meta($id, 'casts'), 0);
        }

        // v3, v4
        if (! isset($ratings) && metadata_exists('post', $id, meta_prefix('ratings'))) {
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

    return $ids;
}
