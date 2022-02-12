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

$ns = 'Bhittani\StarRating\\';
$path = plugin_dir_path(KK_STAR_RATINGS);
$src = $path.'src/';

return [
    // Manifest
    'file' => KK_STAR_RATINGS,
    'namespace' => rtrim($ns, '\\'),
    'path' => $path,
    'signature' => plugin_basename(KK_STAR_RATINGS),
    'url' => plugin_dir_url(KK_STAR_RATINGS),
] + get_file_data(KK_STAR_RATINGS, [
    // Metadata
    'author' => 'Author',
    'author_url' => 'Author URI',
    'domain' => 'Text Domain',
    'name' => 'Plugin Name',
    'nick' => 'Plugin Nick',
    'slug' => 'Plugin Slug',
    'version' => 'Version',
]) + [
    'classes' => autoload($ns.'classes', $src.'classes'),
    'functions' => autoload($ns.'functions', $src.'functions'),
];
