<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\functions;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/**
 * @return int
 *             1  -> The migration was processed and completed.
 *             2  -> The migration was processed but still pending.
 *             4  -> There are no migrations available.
 *             8  -> The migration is already being processed.
 *             16 -> The migration is not current.
 */
function migrate(): int
{
    $migrations = migrations();

    while (! $migrations->isEmpty()) {
        foreach (migrators() as $migrator) {
            $code = $migrations->migrate(
                $migrator->tag,
                $migrator->semver,
                $migrator->handler
            );

            if (in_array($code, [1, 2, 4, 8])) {
                return $code;
            }
        }

        $migrations->remove()->persist();
    }

    return 4;
}
