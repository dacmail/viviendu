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
use function Bhittani\StarRating\core\functions\migrators;
use function Bhittani\StarRating\core\functions\option;
use function Bhittani\StarRating\core\functions\upgrade_options;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function upgrade(string $version, string $previous): void
{
    if (version_compare($previous, '5.0.2', '<')) {
        upgrade_options();
    }

    if (version_compare($previous, '5.2.1', '<')) {
        option(['migrations' => '']);
    }

    $migrations = migrations();

    foreach (migrators() as $migrator) {
        if (version_compare($migrator->semver, $previous, '>')
            && version_compare($migrator->semver, $version, '<=')
            && (
                $migrations->isEmpty()
                || version_compare($migrator->semver, $migrations->top()->version, '>')
            )
        ) {
            $migrations->create(
                $migrator->tag,
                $migrator->semver,
                $migrator->payload($version, $previous)
            );
        }
    }

    $migrations->persist();
}
