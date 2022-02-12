<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\filters\admin;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function tabs(array $tabs): array
{
    return $tabs + [
        'general' => _x('General', 'Label', 'kk-star-ratings'),
        'appearance' => _x('Appearance', 'Label', 'kk-star-ratings'),
        'rich_snippets' => _x('Rich Snippets', 'Label', 'kk-star-ratings'),
        // 'tab' => [
        //     'name' => _x('Name', 'Label', 'kk-star-ratings'),
        //     'is_disabled' => true,
        //     'is_addon' => false,
        // ],
    ];
}
