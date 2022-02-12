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

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

use DirectoryIterator;

function autoload_blocks(string $namespace, string $path, string $slug = null): array
{
    $path = rtrim($path, '\/');
    $namespace = rtrim($namespace, '\\');

    if (! is_dir($path)) {
        return [];
    }

    if (is_null($slug)) {
        $slug = kksr('slug');
    }

    $isDebugMode = defined('WP_DEBUG') && WP_DEBUG;

    $autoloads = [];

    foreach (new DirectoryIterator($path) as $fileInfo) {
        if (! ($fileInfo->isDot()
            || $fileInfo->isFile()
        )) {
            $name = $fileInfo->getFilename();
            $path = $fileInfo->getRealPath();
            $ns = $namespace.'\\'.$name;
            $signature = $slug.'/'.$name;

            $functions = autoload($ns, $path);

            $attributes = [];

            if ($attributes = ($functions['attributes'] ?? [])) {
                unset($functions['attributes']);
                $attributes = $attributes();
            }

            $dependencies = [];

            if ($dependencies = ($functions['dependencies'] ?? [])) {
                unset($functions['dependencies']);
                $dependencies = $dependencies();
            }

            $dist = $path.'/dist/block'.($isDebugMode ? '.es5' : '.min');

            $assetFile = $dist.'.asset.php';
            $options = is_file($assetFile) ? ((array) require $assetFile) : [];

            $autoloads[$signature] = $functions + [
                // 'namespace' => $signature,
                'script' => url($dist.'.js'),
                'version' => $options['version'] ?? kksr('version'),
                'attributes' => $attributes,
                'dependencies' => array_merge($options['dependencies'] ?? [], $dependencies),
            ];
        }
    }

    return $autoloads;
}
