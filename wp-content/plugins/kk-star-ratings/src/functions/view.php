<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\functions;

use InvalidArgumentException;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function view(string $__base, string $__path, array $__payload = []): string
{
    $resolve = function (string $base, string $path): string {
        if (is_file($path)) {
            return $path;
        }

        $path = ltrim($path, '\/');
        $directory = kksr('slug').'/'.basename(dirname($base));
        $parentTheme = get_template_directory().'/'.$directory.'/'.$path;
        $childTheme = get_stylesheet_directory().'/'.$directory.'/'.$path;

        if (is_file($childTheme)) {
            return $childTheme;
        }

        if (is_file($parentTheme)) {
            return $parentTheme;
        }

        $template = $base.'/'.$path;

        if (is_file($template)) {
            return $template;
        }

        throw new InvalidArgumentException("The template '{$path}' could not be located at '{$template}'");
    };

    $__view = function (string $path, array $payload = []) use ($__payload, $__base) {
        return view($__base, $path, array_merge($__payload, $payload));
    };
    $__kksr = 'kksr';
    $__base = rtrim($__base, '\/');
    $__dusk = kksr('functions.dusk_attr');
    $__template = $resolve($__base, $__path);

    unset($resolve);

    extract($__payload);

    ob_start();

    require $__template;

    return ob_get_clean();
}
