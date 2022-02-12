<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\wp\actions;

use function Bhittani\StarRating\core\wp\functions\deactivate;
use WP_Upgrader;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function upgrader_process_complete(WP_Upgrader $upgrader, array $options): void
{
    if ($options['action'] == 'update'
        && $options['type'] == 'plugin'
        && isset($options['plugin'])
        && in_array(kksr('signature'), $options['plugins'])
    ) {
        deactivate();
    }
}
