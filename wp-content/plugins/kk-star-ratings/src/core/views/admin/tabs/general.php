<?php

    if (! defined('KK_STAR_RATINGS')) {
        http_response_code(404);
        exit();
    }

    $enable = $get('enable');
    $excludeCategories = $get('exclude_categories');
    $locations = $get('locations');
    $strategies = $get('strategies');

    $postTypes = [
        'post' => 'Posts',
        'page' => 'Pages',
    ] + array_reduce(get_post_types([
        'publicly_queryable' => true,
        '_builtin' => false,
    ], 'objects'), function ($postTypes, $postType) {
        $postTypes[$postType->name] = $postType->labels->name;

        return $postTypes;
    }, []);

    $postCategories = array_reduce(get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
        'parent' => 0,
    ]), function ($categories, $category) {
        $categories[$category->term_id] = $category->name;

        return $categories;
    }, []);

    $availableLocations = [
        'home' => _x('Home', 'Label', 'kk-star-ratings'),
        'archives' => _x('Archives', 'Label', 'kk-star-ratings'),
    ] + $postTypes;

    $availableStrategies = [
        'archives' => _x('Allow voting in archives', 'Label', 'kk-star-ratings'),
        'guests' => _x('Allow guests to vote', 'Label', 'kk-star-ratings'),
        'unique' => _x('Unique votes (based on IP Address)', 'Label', 'kk-star-ratings'),
    ];
?>

<table class="form-table" role="presentation">
    <tbody>

        <!-- Status -->
        <tr>
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr($enable[0]); ?>"><?php echo esc_html_x('Status', 'Label', 'kk-star-ratings'); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($enable[0]); ?>" id="<?php echo esc_attr($enable[0]); ?>" value="1"<?php echo $enable[1] ? ' checked="checked"' : ''; ?>>
                    <?php echo esc_html_x('Active', 'Label', 'kk-star-ratings'); ?>
                </label>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo esc_html__('Globally activate/deactivate the star ratings.', 'kk-star-ratings'); ?>
                </p>
            </td>
        </tr>

        <!-- Strategies -->
        <tr>
            <th scope="row" valign="top">
                <?php echo esc_html_x('Strategies', 'Label', 'kk-star-ratings'); ?>
            </th>
            <td>
                <?php foreach ($availableStrategies as $value => $label) { ?>
                    <p>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr($strategies[0]); ?>[]" value="<?php echo esc_attr($value); ?>"<?php echo (in_array($value, $strategies[1])) ? ' checked="checked"' : ''; ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    </p>
                <?php } ?>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo esc_html__('Select the voting strategies.', 'kk-star-ratings'); ?>
                </p>
            </td>
        </tr>

        <!-- Locations -->
        <tr>
            <th scope="row" valign="top">
                <?php echo esc_html_x('Locations', 'Label', 'kk-star-ratings'); ?>
            </th>
            <td>
                <?php foreach ($availableLocations as $type => $label) { ?>
                    <p>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr($locations[0]); ?>[]" value="<?php echo esc_attr($type); ?>"<?php echo (in_array($type, $locations[1])) ? ' checked="checked"' : ''; ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    </p>
                <?php } ?>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo sprintf(esc_html__('Star ratings will be auto-embedded for the selected locations. You may still manually allow star ratings for unselected/other locations. E.g. Using %s in your theme/template file(s).', 'kk-star-ratings'), '<code>echo kk_star_ratings();</code>'); ?>
                </p>
            </td>
        </tr>

        <!-- Exclude Categories -->
        <tr>
            <th scope="row" valign="top">
                <?php echo esc_html_x('Exclude Categories', 'Label', 'kk-star-ratings'); ?>
            </th>
            <td>
                <?php foreach ($postCategories as $value => $label) { ?>
                    <p>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr($excludeCategories[0]); ?>[]" value="<?php echo esc_attr($value); ?>"<?php echo (in_array($value, $excludeCategories[1])) ? ' checked="checked"' : ''; ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    </p>
                <?php } ?>
                <p class="description" style="max-width: 22rem; margin-top: .75rem;">
                    <?php echo sprintf(esc_html__('The posts belonging to the selected categories will not auto-embed the star ratings. You may still manually show the star ratings. E.g. Using %s in your theme/template file(s).', 'kk-star-ratings'), '<code>echo kk_star_ratings();</code>'); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
