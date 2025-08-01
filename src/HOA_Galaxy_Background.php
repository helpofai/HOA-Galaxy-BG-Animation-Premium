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

    private function __construct() {
        $this->settings = get_option('hoa_galaxy_settings', $this->get_default_settings());
        
        add_action('wp_body_open', [$this, 'render_galaxy_background']);
        
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'settings_init']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        }
    }

    private function get_default_settings(): array {
        return [
            'star_count' => 150,
            'star_size_min' => 1,
            'star_size_max' => 3,
            'star_colors' => '#ffffff,#ffd700,#87ceeb,#ffa07a,#98fb98,#dda0dd,#ff6347',
            'star_opacity' => 0.8,
            'shooting_count' => 10,
            'shooting_size' => 2,
            'shooting_colors' => '#ffffff,#64f0ff,#ff5e5e',
            'z_index' => -100,
            'disable_on_mobile' => false,
        ];
    }

    public function render_galaxy_background(): void {
        if (wp_is_mobile() && $this->settings['disable_on_mobile']) {
            return;
        }
        
        $settings = $this->settings;
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
            'star_colors' => ['Star Colors (comma separated)', 'text', 'hoa_galaxy_stars', ['class' => 'color-field']],
            'star_opacity' => ['Star Opacity', 'number', 'hoa_galaxy_stars', ['min' => 0.1, 'max' => 1, 'step' => 0.1]],
            'shooting_count' => ['Number of Shooting Stars', 'number', 'hoa_galaxy_shooting', ['min' => 0, 'max' => 30, 'step' => 1]],
            'shooting_size' => ['Shooting Star Size (px)', 'number', 'hoa_galaxy_shooting', ['min' => 1, 'max' => 5, 'step' => 0.5]],
            'shooting_colors' => ['Shooting Star Colors (comma separated)', 'text', 'hoa_galaxy_shooting', ['class' => 'color-field']],
            'z_index' => ['Z-Index', 'number', 'hoa_galaxy_general', ['min' => -999, 'max' => 0, 'step' => 1]],
            'disable_on_mobile' => ['Disable on Mobile', 'checkbox', 'hoa_galaxy_general', []],
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
                    $output[$key] = implode(',', array_filter(array_map('trim', explode(',', sanitize_text_field($value))), function ($color) {
                        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
                    }));
                    break;
                case 'disable_on_mobile':
                    $output[$key] = (bool) $value;
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
        wp_enqueue_script('hoa-galaxy-admin', plugin_dir_url(__DIR__) . 'js/admin.js', ['wp-color-picker'], '2.0.3', true);
    }
}
