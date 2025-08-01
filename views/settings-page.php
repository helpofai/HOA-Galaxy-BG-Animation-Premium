<div class="wrap hoa-galaxy-settings-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="hoa-galaxy-settings-container">
        <form method="post" action="options.php">
            <?php
            settings_fields('hoa_galaxy_settings');
            wp_nonce_field('hoa_galaxy_settings_nonce', 'hoa_galaxy_nonce');
            
            // General Settings Section
            echo '<div class="hoa-galaxy-card">';
            echo '<h2 class="hoa-galaxy-card-title">' . esc_html__('General Settings', 'hoa-galaxy') . '</h2>';
            echo '<table class="form-table">';
            do_settings_sections('hoa-galaxy-settings'); // This will render all sections
            echo '</table>';
            echo '</div>';

            submit_button();
            ?>
        </form>
    </div>
</div>