<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\filters;

use function Bhittani\StarRating\core\functions\filter;
use function Bhittani\StarRating\core\functions\migrations;
use function Bhittani\StarRating\core\functions\option;
use Exception;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function validate(?bool $valid, int $id, string $slug, array $payload): bool
{
    if (! is_null($valid)) {
        return $valid;
    }

    if ($payload['readonly'] ?? false) {
        throw new Exception(__('The ratings are readonly.', 'kk-star-ratings'));
    }

    // if (! migrations()->isEmpty()) {
    //     throw new Exception(__('Under maintenance.', 'kk-star-ratings'));
    // }

    if (! option('enable')) {
        throw new Exception(__('Not allowed at the moment.', 'kk-star-ratings'));
    }

    $strategies = (array) option('strategies');

    if (is_archive()
        && ! in_array('archives', $strategies)
    ) {
        throw new Exception(__('You can not cast a vote in archives.', 'kk-star-ratings'));
    }

    if (! (is_user_logged_in()
        || in_array('guests', $strategies)
    )) {
        throw new Exception(__('You need to be authenticated to cast a vote.', 'kk-star-ratings'), 401);
    }

    $fingerprint = filter('fingerprint', null, $id, $slug);

    if (in_array('unique', $strategies)
        && ! filter('unique', null, $fingerprint, $id, $slug)
    ) {
        throw new Exception(__('You have already casted your vote.', 'kk-star-ratings'), 403);
    }

    return true;
}
