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

function migrators(array $data): array
{
    $migrators = [];

    foreach ($data as $filename => $options) {
        $parts = explode('/', $filename, 2);
        $semver = substr($parts[0], 1);
        $tag = $parts[1];

        [$handler, $payload] = $options();

        $migrators[] = (object) compact('handler', 'payload', 'semver', 'tag');
    }

    usort($migrators, function ($a, $b) {
        if ($a->semver === $b->semver) {
            return $a->tag <=> $b->tag;
        }

        return version_compare($a->semver, $b->semver);
    });

    return $migrators;
}
