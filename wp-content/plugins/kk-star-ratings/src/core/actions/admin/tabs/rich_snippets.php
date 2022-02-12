<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\core\actions\admin\tabs;

use function Bhittani\StarRating\core\functions\get_hof;
use function Bhittani\StarRating\core\functions\view;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function rich_snippets(?array $old, string $tab): void
{
    $get = get_hof($old);

    echo view('admin/tabs/rich-snippets.php', compact('old', 'tab', 'get'));
}
