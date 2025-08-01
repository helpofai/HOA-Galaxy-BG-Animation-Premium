<div class="wrap hoa-galaxy-settings-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="hoa-galaxy-three-column-layout">
        <div class="hoa-galaxy-left-sidebar">
            <div class="hoa-galaxy-card">
                <h2 class="hoa-galaxy-card-title"><?php esc_html_e('About Plugin', 'hoa-galaxy'); ?></h2>
                <p><?php esc_html_e('This plugin provides an animated galaxy background for your WordPress site.', 'hoa-galaxy'); ?></p>
                <p><?php esc_html_e('Version: ' . HOA_GALAXY_PLUGIN_VERSION, 'hoa-galaxy'); ?></p>
                <p><?php esc_html_e('Author: Rajib Adhikary', 'hoa-galaxy'); ?></p>
                <p><a href="https://helpofai.com/" target="_blank"><?php esc_html_e('Visit Author Website', 'hoa-galaxy'); ?></a></p>
            </div>
        </div>

        <div class="hoa-galaxy-main-content">
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
                            echo "<h2 class=\"hoa-galaxy-card-title\">" . esc_html($section['title']) . "</h2>";
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

        <div class="hoa-galaxy-right-sidebar">
            <div class="hoa-galaxy-card">
                <h2 class="hoa-galaxy-card-title"><?php esc_html_e('Plugin Options', 'hoa-galaxy'); ?></h2>
                <p><?php esc_html_e('This section can be used for additional plugin-specific settings or links.', 'hoa-galaxy'); ?></p>
                <ul>
                    <li><a href="#"><?php esc_html_e('Documentation', 'hoa-galaxy'); ?></a></li>
                    <li><a href="#"><?php esc_html_e('Support', 'hoa-galaxy'); ?></a></li>
                    <li><a href="#"><?php esc_html_e('Premium Features', 'hoa-galaxy'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>