<?php
    if (! defined('KK_STAR_RATINGS')) {
        http_response_code(404);
        exit();
    }

    $grs = $get('grs');
    $sd = $get('sd');
?>

<table class="form-table" role="presentation">
    <tbody>

        <!-- Status -->
        <tr>
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr($grs[0]); ?>"><?php echo esc_html_x('Status', 'Label', 'kk-star-ratings'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($grs[0]); ?>" id="<?php echo esc_attr($grs[0]); ?>" value="1"<?php echo $grs[1] ? ' checked="checked"' : ''; ?>>
                    <?php echo esc_html_x('Enable', 'Label', 'kk-star-ratings'); ?>
                </label>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo esc_html__('Enable/disable rich snippets.', 'kk-star-ratings'); ?>
                </p>
            </td>
        </tr>

        <!-- Structured Data -->
        <tr>
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr($sd[0]); ?>"><?php echo esc_html_x('Content', 'Label', 'kk-star-ratings'); ?></label>
            </th>
            <td>
                <p>
                    <textarea type="text" name="<?php echo esc_attr($sd[0]); ?>" id="<?php echo esc_attr($sd[0]); ?>" rows="12" placeholder="ld+json" class="regular-text" style="font-family: monospace;"
                        ><?php echo esc_textarea($sd[1]); ?></textarea>
                </p>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo esc_html__('Provide the ld+json structure.', 'kk-star-ratings'); ?>
                    <br><br>
                    <?php echo esc_html__('The following variables are available:', 'kk-star-ratings'); ?>
                    <br>
                    <?php echo sprintf(esc_html_x('%s Post title', 'Label', 'kk-star-ratings'), '<code>{title}</code>'); ?>
                    <br>
                    <?php echo sprintf(esc_html_x('%s Average ratings', 'Label', 'kk-star-ratings'), '<code>{score}</code>'); ?>
                    <br>
                    <?php echo sprintf(esc_html_x('%s Number of votes casted', 'Label', 'kk-star-ratings'), '<code>{count}</code>'); ?>
                    <br>
                    <?php echo sprintf(esc_html_x('%s Total amount of stars', 'Label', 'kk-star-ratings'), '<code>{best}&nbsp;</code>'); ?>
                </p>
            </td>
        </tr>

    </tbody>
</table>
