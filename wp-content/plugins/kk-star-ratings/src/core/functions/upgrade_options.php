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

use stdClass;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function upgrade_options(): bool
{
    // v2

    if (option('grs', $fn = new stdClass) === $fn) {
        option([
            'strategies' => array_filter([
                'guests',
                option('unique') ? 'unique' : null,
                option('disable_in_archives', true) ? null : 'archives',
            ]),
            'exclude_locations' => array_filter([
                option('show_in_home', true) ? null : 'home',
                option('show_in_posts', true) ? null : 'post',
                option('show_in_pages', true) ? null : 'page',
                option('show_in_archives', true) ? null : 'archives',
            ]),
            'exclude_categories' => is_array($exludeCategories = option('exclude_categories', []))
                ? $exludeCategories : array_map('trim', explode(',', $exludeCategories)),
        ]);
    }

    // > v2

    $varRegex = '/\[([a-zA-Z0-9_-]+?)\]/';
    $varReplacement = '{$1}';

    option(array_filter([
        'greet' => preg_replace($varRegex, $varReplacement, option('greet')),
        'sd' => preg_replace($varRegex, $varReplacement, option('sd')),
        // This was used in v2 but not afterwards, hence, force to default.
        'legend' => kksr('core.options.legend'),
    ]));

    return true;
}
