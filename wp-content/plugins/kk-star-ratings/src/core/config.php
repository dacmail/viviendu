<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function Bhittani\StarRating\functions\autoload;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

$ns = 'Bhittani\StarRating\core\\';
$url = plugin_dir_url(KK_STAR_RATINGS).'src/core/';
$path = plugin_dir_path(KK_STAR_RATINGS).'src/core/';

return [
    // Manifest
    'namespace' => rtrim($ns, '\\'),
    'path' => $path,
    'url' => $url,
    'views' => $path.'views/',
] + [
    // Source
    'actions' => autoload($ns.'actions', $path.'actions'),
    'filters' => autoload($ns.'filters', $path.'filters'),
    'functions' => autoload($ns.'functions', $path.'functions'),
    'migrators' => autoload($ns.'migrators', $path.'migrators'),
    'wp' => [
        'actions' => autoload($ns.'wp\actions', $path.'wp/actions'),
        'filters' => autoload($ns.'wp\filters', $path.'wp/filters'),
        'functions' => autoload($ns.'wp\functions', $path.'wp/functions'),
        'shortcodes' => autoload($ns.'wp\shortcodes', $path.'wp/shortcodes'),
    ],
] + [
    // Config
    'post_meta' => [
        'avg_*' => 0.0,
        'count_*' => 0,
        'fingerprint_*[]' => '',
        'ratings_*' => 0.0,
        'status_*' => '',
    ],
    'options' => [
        // Internal
        'activated' => false,
        // General
        'enable' => true,
        'exclude_categories' => [],
        'locations' => ['post'],
        'strategies' => ['archives', 'guests'],
        // Appearance
        'gap' => 5,
        'greet' => 'Rate this {type}',
        'legend' => '{score}/{best} - ({count} {votes})',
        'position' => 'top-left',
        'size' => 24,
        'stars' => 5,
        // Rich snippets
        'grs' => true,
        'sd' => '
{
    "@context": "https://schema.org/",
    "@type": "CreativeWorkSeries",
    "name": "{title}",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{score}",
        "bestRating": "{best}",
        "ratingCount": "{count}"
    }
}
        ',
    ],
];
