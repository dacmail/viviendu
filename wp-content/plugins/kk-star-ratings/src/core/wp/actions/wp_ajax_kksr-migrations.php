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

use function Bhittani\StarRating\core\functions\migrate;
use Exception;

if (! defined('KK_STAR_RATINGS')) {
    http_response_code(404);
    exit();
}

function wp_ajax_kksr_migrations()
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (! check_ajax_referer(__FUNCTION__, 'nonce', false)) {
            throw new Exception(__('This action is forbidden.', 'kk-star-ratings'), 403);
        }

        $code = migrate();

        if ($code == 4) {
            wp_send_json_success(['status' => 'complete']);
        }

        if ($code == 8) {
            wp_send_json_success(['status' => 'busy']);
        }

        // 0, 1, 2, 16
        wp_send_json_success(['status' => 'pending']);
    } catch (Exception $e) {
        wp_send_json_error(['error' => $e->getMessage()], $e->getCode() ?: 406);
    }
}
