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

use function Bhittani\StarRating\functions\prefix as base_prefix;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

/**
 * @param string|array $strOrPayload
 *
 * @return string|array
 */
function prefix($strOrPayload)
{
    return base_prefix($strOrPayload, kksr('nick').'_');
}
