<div class="wrap hoa-galaxy-settings-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="hoa-galaxy-settings-container">
        <form method="post" action="options.php">
            <?php
            settings_fields('hoa_galaxy_settings');
            wp_nonce_field('hoa_galaxy_settings_nonce', 'hoa_galaxy_nonce');
            
            global $wp_settings_sections;

            $page = 'hoa-galaxy-settings';

            if (isset($wp_settings_sections[$page])) {
                foreach ((array) $wp_settings_sections[$page] as $section) {
                    echo '<div class="hoa-galaxy-card">';
                    if ($section['title']) {
                        echo "<h2 class=\"hoa-galaxy-card-title\">{$section['title']}</h2>";
                    }
                    if ($section['callback']) {
                        call_user_func($section['callback'], $section);
                    }
                    echo '<table class="form-table">';
                    do_settings_fields($page, $section['id']);
                    echo '</table>';
                    echo '</div>';
                }
            }

            submit_button();
            ?>
        </form>
    </div>
</div>