<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('hoa_galaxy_settings');
        wp_nonce_field('hoa_galaxy_settings_nonce', 'hoa_galaxy_nonce');
        do_settings_sections('hoa-galaxy-settings');
        submit_button();
        ?>
    </form>
</div>
