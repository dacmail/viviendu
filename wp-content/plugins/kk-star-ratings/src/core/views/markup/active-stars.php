<?php
    if (! defined('KK_STAR_RATINGS')) {
        http_response_code(404);
        exit();
    }
?>

<div class="kksr-stars-active" style="width: <?php echo esc_attr($width); ?>px;">
    <?php for ($i = 1; $i <= $best; ++$i) { ?>
        <div class="kksr-star" <?php echo isset($gap) ? ('style="padding-'.(is_rtl() ? 'left' : 'right').': '.esc_attr($gap).'px"') : ''; ?>>
            <?php echo $__view('markup/active-star.php'); ?>
        </div>
    <?php } ?>
</div>
