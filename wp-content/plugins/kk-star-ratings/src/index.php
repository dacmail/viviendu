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

foreach ([
    'functions/autoload',
    'functions/autoload_class',
    'functions/autoload_function',
    'functions/dot',
    'functions/container',
    'kksr',
] as $filename) {
    require_once __DIR__.'/'.ltrim($filename, '\/').'.php';
}

kksr(require __DIR__.'/config.php');

autoload('\kk_star_ratings');
