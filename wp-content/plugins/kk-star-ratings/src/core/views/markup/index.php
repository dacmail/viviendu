<?php
    if (! defined('KK_STAR_RATINGS')) {
        http_response_code(404);
        exit();
    }
?>

<div class="kk-star-ratings
    <?php echo $valign ? (' kksr-valign-'.esc_attr($valign)) : ''; ?>
    <?php echo $align ? (' kksr-align-'.esc_attr($align)) : ''; ?>
    <?php echo $readonly ? ' kksr-disabled' : ''; ?>"
    data-payload="<?php echo esc_attr(json_encode(array_map('esc_attr', $__payload))); ?>">
    <?php echo $__view('markup/stars.php'); ?>
    <?php echo $__view('markup/legend.php'); ?>
</div>
