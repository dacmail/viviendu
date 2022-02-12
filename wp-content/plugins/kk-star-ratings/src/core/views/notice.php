<?php
    if (! defined('KK_STAR_RATINGS')) {
        http_response_code(404);
        exit();
    }

    $icon = $icon ?? null;
    $type = $type ?? 'info';
    $dismissible = (bool) ($dismissible ?? false);
?>

<div class="update-nag inline notice notice-<?php echo esc_attr($type); ?>"<?php echo $dismissible ? ' is-dismissible' : ''; ?>
    style="display: flex; align-items: center; font-weight: 600;">
    <?php if ($icon) { ?>
        <img src="<?php echo esc_url($icon); ?>" alt="Spinning icon" style="margin-right: .75rem;">
    <?php } ?>

    <?php echo esc_html($message); ?>
</div>
