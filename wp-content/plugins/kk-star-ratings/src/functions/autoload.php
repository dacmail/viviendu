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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/**
 * @param string|array $namespace
 *
 * @return bool|array
 */
function autoload($namespace, string $path = null, array $excludes = [], int $depth = -1)
{
    if (is_null($path)) {
        if (! is_array($namespace)) {
            return autoload_function($namespace);
        }

        return array_map(function ($ns) {
            return autoload_function($ns);
        }, $namespace);
    }

    $path = rtrim($path, '\/');

    if (! is_dir($path)) {
        return [];
    }

    $cutoffLength = strlen($path) + 1;
    $namespace = rtrim($namespace, '\\');

    $recursiveIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $recursiveIterator->setMaxDepth($depth);
    $iterator = new RegexIterator($recursiveIterator, '/\.php$/');

    // $autoload = function (string $fn, string $fqfn = null) use (&$autoload) {
    //     $fqfn = $fqfn ?: $fn;

    //     $parts = explode('\\', $fn, 2);
    //     $head = array_shift($parts);
    //     $tail = array_shift($parts);

    //     if (! $tail) {
    //         return [$head => $fqfn];
    //     }

    //     return [$head => $autoload($tail, $fqfn)];
    // };

    // $autoloads = [];

    // foreach (iterator_to_array($iterator) as $splFileInfo) {
    //     $filepath = (string) $splFileInfo;
    //     $filename = substr($filepath, $cutoffLength);

    //     if (in_array($filename, $excludes)) {
    //         continue;
    //     }

    // $name = substr($filename, 0, -4);
    // $name = preg_replace('/[\/\\\]/', '/', $name);
    // $fn = preg_replace('/\//', '\\', $name);
    // $fn = preg_replace('/([^a-zA-Z0-9\\\]+?)/', '_', $fn);
    // $fqfn = $namespace.'\\'.$fn;

    //     if (! function_exists($fqfn)) {
    //         require_once $filepath;
    //     }

    //     $autoloads = array_replace_recursive($autoloads, $autoload($fn, $fqfn));
    // }

    // return $autoloads;

    $autoloads = [];

    foreach (iterator_to_array($iterator) as $splFileInfo) {
        $filepath = (string) $splFileInfo;
        $filename = substr($filepath, $cutoffLength);

        if (in_array($filename, $excludes)) {
            continue;
        }

        $name = substr($filename, 0, -4);
        $name = preg_replace('/[\/\\\]/', '/', $name);
        $fn = preg_replace('/\//', '\\', $name);
        $fn = preg_replace('/([^a-zA-Z0-9\\\]+?)/', '_', $fn);
        $fqFnOrCn = $namespace.'\\'.$fn;

        $parts = explode('\\', $name);
        $last = array_pop($parts);

        $exists = 'function_exists';

        if (preg_match('/^\p{Lu}/u', $last)) {
            $exists = 'class_exists';
        }

        if (! $exists($fqFnOrCn)) {
            require_once $filepath;
        }

        if ($exists($fqFnOrCn)) {
            $autoloads = [$name => $fqFnOrCn] + $autoloads;
        }
    }

    return $autoloads;
}
