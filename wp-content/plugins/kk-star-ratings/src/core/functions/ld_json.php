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

use function Bhittani\StarRating\functions\bind;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function ld_json(array $payload = [], string $json = null): string
{
    $sd = bind($json ?: option('sd'), data($payload));

    return '<script type="application/ld+json">'.trim($sd).'</script>';
}
