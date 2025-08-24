<?php

declare(strict_types=1);

namespace HOA\Galaxy;

class HOA_Galaxy_Background {
    private static ?self $instance = null;
    private array $settings;

    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->settings = get_option('hoa_galaxy_settings', $this->get_default_settings());
        
        add_action('wp_body_open', [$this, 'render_galaxy_background']);
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_filter('admin_body_class', [$this, 'add_admin_body_class']);
        add_filter('plugin_action_links_' . plugin_basename(HOA_GALAXY_PLUGIN_FILE), [$this, 'add_auto_update_link']);
        add_action('admin_init', [$this, 'handle_auto_update_toggle']);
    }

    public function get_default_settings(): array {
        return [
            'star_count' => 150,
            'star_size_min' => 1,
            'star_size_max' => 3,
            'star_colors' => ['#ffffff','#ffd700','#87ceeb','#ffa07a','#98fb98','#dda0dd','#ff6347'],
            'star_opacity' => 0.8,
            'shooting_count' => 10,
            'shooting_size' => 2,
            'shooting_colors' => ['#ffffff','#64f0ff','#ff5e5e','#FFFF00','#00FF00','#0000FF'],
            'z_index' => -100,
            'disable_on_mobile' => false,
            'admin_theme' => 'system', // New setting for admin theme
            'auto_updates_enabled' => false,
            'background_type' => 'solid',
            'background_color_1' => '#000000',
            'background_color_2' => '#000000',
            'background_gradient_direction' => 'to right',
            'display_on' => 'everywhere',
            'display_on_pages' => '',
        ];
    }

    public function add_auto_update_link(array $links): array {
        $auto_updates_enabled = $this->settings['auto_updates_enabled'] ?? false;
        $text = $auto_updates_enabled ? __('Disable Auto-updates', 'hoa-galaxy') : __('Enable Auto-updates', 'hoa-galaxy');
        $action = $auto_updates_enabled ? 'disable_auto_updates' : 'enable_auto_updates';
        $url = wp_nonce_url(admin_url('admin.php?page=hoa-galaxy-settings&action=' . $action), 'hoa_galaxy_auto_update_nonce');
        $links[] = '<a href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
        return $links;
    }

    public function handle_auto_update_toggle(): void {
        if (!isset($_GET['action']) || !in_array($_GET['action'], ['enable_auto_updates', 'disable_auto_updates'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'hoa_galaxy_auto_update_nonce')) {
            wp_die(__('Invalid nonce specified', 'hoa-galaxy'), __('Error', 'hoa-galaxy'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'hoa-galaxy'), __('Error', 'hoa-galaxy'));
        }

        $this->settings['auto_updates_enabled'] = ('enable_auto_updates' === $_GET['action']);
        update_option('hoa_galaxy_settings', $this->settings);

        wp_redirect(admin_url('plugins.php'));
        exit;
    }

    public function render_galaxy_background(): void {
        $display_on = $this->settings['display_on'] ?? 'everywhere';
        $display_on_pages = $this->settings['display_on_pages'] ?? '';

        if ($display_on === 'homepage' && !is_front_page()) {
            return;
        }

        if ($display_on === 'specific_pages') {
            $allowed_pages = array_map('trim', explode(',', $display_on_pages));
            $current_page_id = get_queried_object_id();
            if (!in_array($current_page_id, $allowed_pages)) {
                return;
            }
        }

        if (wp_is_mobile() && $this->settings['disable_on_mobile']) {
            return;
        }
        
        wp_enqueue_style('hoa-galaxy-background', plugin_dir_url(__DIR__) . 'css/galaxy-background.css', [], HOA_GALAXY_PLUGIN_VERSION);
        wp_enqueue_script('hoa-galaxy-background', plugin_dir_url(__DIR__) . 'js/galaxy-background.js', [], '4.1.0', true);
        wp_localize_script('hoa-galaxy-background', 'hoaGalaxySettings', $this->settings);

        require_once __DIR__ . '/../views/galaxy-background.php';
    }

    public function add_admin_menu(): void {
        add_options_page(
            __('Galaxy Background Settings', 'hoa-galaxy'),
            __('Galaxy Background', 'hoa-galaxy'),
            'manage_options',
            'hoa-galaxy-settings',
            [$this, 'settings_page']
        );
    }

    public function settings_page(): void {
        require_once __DIR__ . '/../views/settings-page.php';
    }

    public function settings_init(): void {
        register_setting('hoa_galaxy_settings', 'hoa_galaxy_settings', [$this, 'sanitize_settings']);
        
        add_settings_section(
            'hoa_galaxy_general',
            __('General Settings', 'hoa-galaxy'),
            [$this, 'section_callback'],
            'hoa-galaxy-settings'
        );
        
        add_settings_section(
            'hoa_galaxy_stars',
            __('Star Settings', 'hoa-galaxy'),
            [$this, 'section_callback'],
            'hoa-galaxy-settings'
        );
        
        add_settings_section(
            'hoa_galaxy_shooting',
            __('Shooting Star Settings', 'hoa-galaxy'),
            [$this, 'section_callback'],
            'hoa-galaxy-settings'
        );
        
        $fields = [
            'star_count' => ['Number of Stars', 'number', 'hoa_galaxy_stars', ['min' => 10, 'max' => 500, 'step' => 10]],
            'star_size_min' => ['Min Star Size (px)', 'number', 'hoa_galaxy_stars', ['min' => 0.5, 'max' => 5, 'step' => 0.1]],
            'star_size_max' => ['Max Star Size (px)', 'number', 'hoa_galaxy_stars', ['min' => 1, 'max' => 10, 'step' => 0.1]],
            'star_colors' => ['Star Colors', 'multi_color', 'hoa_galaxy_stars', []],
            'star_opacity' => ['Star Opacity', 'number', 'hoa_galaxy_stars', ['min' => 0.1, 'max' => 1, 'step' => 0.1]],
            'shooting_count' => ['Number of Shooting Stars', 'number', 'hoa_galaxy_shooting', ['min' => 0, 'max' => 30, 'step' => 1]],
            'shooting_size' => ['Shooting Star Size (px)', 'number', 'hoa_galaxy_shooting', ['min' => 1, 'max' => 5, 'step' => 0.5]],
            'shooting_colors' => ['Shooting Star Colors', 'multi_color', 'hoa_galaxy_shooting', []],
            'z_index' => ['Z-Index', 'number', 'hoa_galaxy_general', ['min' => -999, 'max' => 0, 'step' => 1]],
            'disable_on_mobile' => ['Disable on Mobile', 'checkbox', 'hoa_galaxy_general', []],
            'admin_theme' => ['Admin Panel Theme', 'select', 'hoa_galaxy_general', ['options' => ['system' => 'System Default', 'light' => 'Light Mode', 'dark' => 'Dark Mode']]],
            'background_type' => ['Background Type', 'select', 'hoa_galaxy_general', ['options' => ['solid' => 'Solid', 'gradient' => 'Gradient']]],
            'background_color_1' => ['Background Color 1', 'text', 'hoa_galaxy_general', ['class' => 'color-field']],
            'background_color_2' => ['Background Color 2', 'text', 'hoa_galaxy_general', ['class' => 'color-field']],
            'background_gradient_direction' => ['Gradient Direction', 'text', 'hoa_galaxy_general', []],
            'display_on' => ['Display On', 'select', 'hoa_galaxy_general', ['options' => ['everywhere' => 'Everywhere', 'homepage' => 'Homepage Only', 'specific_pages' => 'Specific Pages/Posts']]],
            'display_on_pages' => ['Display on Pages/Posts', 'text', 'hoa_galaxy_general', []],
        ];

        foreach ($fields as $name => $field) {
            add_settings_field(
                $name,
                __($field[0], 'hoa-galaxy'),
                [$this, "{$field[1]}_callback"],
                'hoa-galaxy-settings',
                $field[2],
                ['name' => $name] + $field[3]
            );
        }
    }
    
    public function section_callback(): void {}
    
    public function number_callback(array $args): void {
        $value = $this->settings[$args['name']] ?? '';
        printf(
            '<input type="number" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="%2$s" min="%3$s" max="%4$s" step="%5$s" />',
            esc_attr($args['name']),
            esc_attr((string) $value),
            esc_attr((string) $args['min']),
            esc_attr((string) $args['max']),
            esc_attr((string) $args['step'])
        );
    }
    
    public function text_callback(array $args): void {
        $value = $this->settings[$args['name']] ?? '';
        $css_class = 'regular-text ' . ($args['class'] ?? '');
        printf(
            '<input type="text" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="%2$s" class="%3$s" />',
            esc_attr($args['name']),
            esc_attr($value),
            esc_attr($css_class)
        );
    }
    
    public function checkbox_callback(array $args): void {
        $checked = !empty($this->settings[$args['name']]);
        printf(
            '<input type="checkbox" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="1" %2$s />',
            esc_attr($args['name']),
            checked($checked, true, false)
        );
    }

    public function select_callback(array $args): void {
        $value = $this->settings[$args['name']] ?? '';
        printf(
            '<select id="%1$s" name="hoa_galaxy_settings[%1$s]">%2$s</select>',
            esc_attr($args['name']),
            implode('', array_map(function ($key, $label) use ($value) {
                return sprintf('<option value="%s" %s>%s</option>', esc_attr($key), selected($value, $key, false), esc_html($label));
            }, array_keys($args['options']), $args['options']))
        );
    }

    public function multi_color_callback(array $args): void {
        $colors = $this->settings[$args['name']] ?? [];
        if (!is_array($colors)) {
            $colors = explode(',', $colors); // Handle old comma-separated format
        }
        $colors = array_filter(array_map('trim', $colors));

        echo '<div class="hoa-galaxy-multi-color-picker" id="' . esc_attr($args['name']) . '_colors">';
        foreach ($colors as $index => $color) {
            printf(
                '<div class="hoa-galaxy-color-item"><input type="text" name="hoa_galaxy_settings[%1$s][%2$s]" value="%3$s" class="color-field" /><button type="button" class="button remove-color">-</button></div>',
                esc_attr($args['name']),
                esc_attr((string) $index),
                esc_attr($color)
            );
        }
        printf(
            '<button type="button" class="button add-color" data-field-name="%1$s">%2$s</button>',
            esc_attr($args['name']),
            __('Add Color', 'hoa-galaxy')
        );
        echo '</div>';
    }
    
    public function sanitize_settings(array $input): array {
        if (empty($_POST['hoa_galaxy_nonce']) || !wp_verify_nonce(sanitize_key($_POST['hoa_galaxy_nonce']), 'hoa_galaxy_settings_nonce')) {
            wp_die(__('Invalid nonce specified', 'hoa-galaxy'), __('Error', 'hoa-galaxy'));
        }

        $output = $this->get_default_settings();
        
        foreach ($input as $key => $value) {
            if (!isset($output[$key])) {
                continue;
            }

            switch ($key) {
                case 'star_count':
                case 'shooting_count':
                case 'z_index':
                    $output[$key] = intval($value);
                    break;
                case 'star_size_min':
                case 'star_size_max':
                case 'star_opacity':
                case 'shooting_size':
                    $output[$key] = floatval($value);
                    break;
                case 'star_colors':
                case 'shooting_colors':
                    $sanitized_colors = [];
                    if (is_array($value)) {
                        foreach ($value as $color) {
                            if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                                $sanitized_colors[] = sanitize_hex_color($color);
                            }
                        }
                    }
                    $output[$key] = $sanitized_colors;
                    break;
                case 'disable_on_mobile':
                    $output[$key] = (bool) $value;
                    break;
                case 'admin_theme':
                    $output[$key] = sanitize_key($value);
                    break;
                case 'background_type':
                    $output[$key] = sanitize_key($value);
                    break;
                case 'background_color_1':
                case 'background_color_2':
                    $output[$key] = sanitize_hex_color($value);
                    break;
                case 'background_gradient_direction':
                    $output[$key] = sanitize_text_field($value);
                    break;
                case 'display_on':
                    $output[$key] = sanitize_key($value);
                    break;
                case 'display_on_pages':
                    $output[$key] = sanitize_text_field($value);
                    break;
            }
        }
        
        return $output;
    }
    
    public function admin_enqueue_scripts(string $hook): void {
        if ('settings_page_hoa-galaxy-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('hoa-galaxy-admin', plugin_dir_url(__DIR__) . 'js/admin.js', ['wp-color-picker'], '3.0.5', true);
        wp_enqueue_style('hoa-galaxy-admin-theme', plugin_dir_url(__DIR__) . 'css/admin-theme.css', [], '3.0.5');
    }

    public function add_admin_body_class(string $classes): string {
        $theme = $this->settings['admin_theme'] ?? 'system';
        return "$classes hoa-galaxy-admin-theme-$theme";
    }
}
