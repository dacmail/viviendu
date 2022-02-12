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

use function Bhittani\StarRating\core\functions\filter;
use function Bhittani\StarRating\core\functions\migrations;
use function Bhittani\StarRating\core\functions\option;
use function count;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function okay(?bool $okay, int $id, string $slug, array $payload): bool
{
    if (! is_null($okay)) {
        return $okay;
    }

    // if (! migrations()->isEmpty()) {
    //     return false;
    // }

    if (! option('enable')) {
        return false;
    }

    $reference = $payload['reference'] ?? null;

    if ($reference == 'template') {
        return true;
    }

    $locations = (array) option('locations');

    if ((is_front_page() || is_home()) && ! in_array('home', $locations)) {
        return false;
    }

    if (is_archive() && ! in_array('archives', $locations)) {
        return false;
    }

    if ($reference == 'shortcode') {
        return true;
    }

    $status = filter('status', null, $id, $slug);

    if ($status == 'disable') {
        return false;
    }

    if ($status == 'enable') {
        return true;
    }

    $type = get_post_type($id);

    if (! in_array($type, $locations)) {
        return false;
    }

    $categories = array_map(function ($category) {
        return $category->term_id;
    }, get_the_category($id));

    $excludedCategories = (array) option('exclude_categories');

    if (count($categories) != count(array_diff($categories, $excludedCategories))) {
        return false;
    }

    return true;
}
