<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\actions;

use function Bhittani\StarRating\core\functions\migrations;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function downgrade(string $version, string $previous): void
{
    $migrations = migrations();

    while (! $migrations->isEmpty()
        && version_compare($migrations->top()->version, $version, '!=')
    ) {
        $migrations->pop();
    }

    $migrations->persist();
}
