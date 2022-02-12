<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\migrations\v5_1_0;

use function Bhittani\StarRating\core\functions\upgrade_posts;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function posts(): array
{
    return [
        // Migration callback.
        function (array $payload) {
            if (($ids = upgrade_posts($payload))
                && count($ids) >= 5
            ) {
                ++$payload['paged'];

                return $payload;
            }
        },
        // Initial payload.
        function (string $version, string $previous) {
            global $wpdb;

            $maxIdRow = $wpdb->get_row("
                SELECT ID
                FROM {$wpdb->posts}
                ORDER BY ID DESC
                LIMIT 0,1
            ");

            return [
                'max_id' => (int) ($maxIdRow ? $maxIdRow->ID : null),
                'paged' => 1,
                'posts_per_page' => 5,
            ];
        },
        // Progress. [<total>, <current>]
        // TODO: Work in progress!
        // function (array $payload) {
        //     return [
        //         $payload['max_id'],
        //         $payload['posts_per_page'] * $payload['paged'],
        //     ];
        // },
    ];
}
