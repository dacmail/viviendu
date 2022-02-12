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

use function Bhittani\StarRating\core\functions\migrations;
use function Bhittani\StarRating\core\functions\view;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function admin_notices(): void
{
    // if (! migrations()->isEmpty()) {
    //     $icon = get_admin_url(null, 'images/wpspin_light.gif');
    //     $message = sprintf(__('%s has pending migrations. These migrations are running in the background and the ratings will remain disabled until the migrations have finished.', 'kk-star-ratings'), kksr('name'));
    //     $type = 'warning';

    //     echo view('notice.php', compact('icon', 'message', 'type'));
    // }
}
