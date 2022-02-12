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

use RuntimeException;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/** @throws RuntimeException if the function can not be autoloaded. */
function autoload_function(string $fqfn): bool
{
    if (function_exists($fqfn)) {
        return false;
    }

    $name = $fqfn;
    $prefix = 'Bhittani\StarRating\\';

    if (strpos($fqfn, '\\') !== 0
        && strpos($fqfn, $prefix) !== 0
    ) {
        $fqfn = $prefix.$fqfn;
    }

    if (strpos($fqfn, $prefix) === 0) {
        $name = substr($fqfn, strlen($prefix));
    }

    $fqfn = trim($fqfn, '\\');
    $name = trim($name, '\\');

    // kebab-Case
    $parts = array_map(function (string $part) {
        return preg_replace(['/([a-z\d])([A-Z])/', '/([^-_])([A-Z][a-z])/'], '$1-$2', $part);
    }, explode('\\', $name));

    $filepath = dirname(KK_STAR_RATINGS).'/src/'.implode('/', $parts).'.php';

    if (! is_file($filepath)) {
        throw new RuntimeException("Failed to autoload function '{$fqfn}` because the file `{$filepath}` does not exist");
    }

    require_once $filepath;

    if (! function_exists($fqfn)) {
        throw new RuntimeException("Failed to autoload function '{$fqfn}` because the file `{$filepath}` does not contain the function `${fqfn}`");
    }

    return true;
}
