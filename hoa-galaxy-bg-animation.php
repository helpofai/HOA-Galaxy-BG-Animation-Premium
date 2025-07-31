<?php
/**
 * Plugin Name: HOA Galaxy BG Animation Premium
 * Description: Premium animated galaxy background with transparent background for better visibility
 * Version: 2.0.3
 * Author: Rajib Adhikary
 */

defined('ABSPATH') or die('Direct script access disallowed.');

class HOA_Galaxy_Background {
    private static $instance = null;
    private $settings;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->settings = get_option('hoa_galaxy_settings', $this->get_default_settings());
        
        add_action('wp_body_open', array($this, 'render_galaxy_background'));
        
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'settings_init'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
    }

    private function get_default_settings() {
        return array(
            'star_count' => 150,
            'star_size_min' => 1,
            'star_size_max' => 3,
            'star_colors' => '#ffffff,#ffd700,#87ceeb,#ffa07a,#98fb98,#dda0dd,#ff6347',
            'star_opacity' => 0.8,
            'shooting_count' => 10,
            'shooting_size' => 2,
            'shooting_colors' => '#ffffff,#64f0ff,#ff5e5e',
            'z_index' => -100,
            'disable_on_mobile' => false
        );
    }

    public function render_galaxy_background() {
        if (wp_is_mobile() && $this->settings['disable_on_mobile']) {
            return;
        }
        
        $settings = $this->settings;
        ?>
        <div class="galaxy-bg" id="galaxy" style="z-index: <?php echo esc_attr($settings['z_index']); ?>"></div>
        
        <style>
        .galaxy-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            min-height: -webkit-fill-available;
            background: transparent;
            overflow: hidden;
            pointer-events: none;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            animation: twinkle 5s infinite ease-in-out;
            background: var(--star-color, #fff);
            opacity: var(--opacity, 0.8);
            z-index: 1;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: var(--opacity, 0.8); transform: scale(1.2); }
        }

        .shooting-star {
            position: absolute;
            border-radius: 50%;
            animation: shoot 3s linear infinite;
            background: var(--shooting-color, #fff);
            opacity: 0;
            z-index: 2;
            box-shadow: 0 0 6px 2px var(--shooting-color, #fff);
        }

        @keyframes shoot {
            0% {
                transform: translateX(0) translateY(0) rotate(45deg);
                opacity: 1;
            }
            70% {
                opacity: 1;
            }
            100% {
                transform: translateX(100vw) translateY(-100vh) rotate(45deg);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .galaxy-bg {
                height: 100vh;
                height: -webkit-fill-available;
            }
            .star {
                animation: twinkle-mobile 5s infinite ease-in-out;
            }
            @keyframes twinkle-mobile {
                0%, 100% { opacity: 0.2; transform: scale(1); }
                50% { opacity: var(--opacity, 0.6); transform: scale(1.1); }
            }
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const galaxy = document.getElementById('galaxy');
            const settings = <?php echo json_encode($settings); ?>;
            
            // Create stars
            const starFragment = document.createDocumentFragment();
            for (let i = 0; i < settings.star_count; i++) {
                const star = document.createElement('div');
                star.classList.add('star');
                
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const size = Math.random() * (settings.star_size_max - settings.star_size_min) + settings.star_size_min;
                const colors = settings.star_colors.split(',');
                const color = colors[Math.floor(Math.random() * colors.length)];
                const delay = Math.random() * 5;
                
                star.style.left = `${x}%`;
                star.style.top = `${y}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--star-color', color);
                star.style.setProperty('--opacity', settings.star_opacity);
                star.style.animationDelay = `${delay}s`;
                
                starFragment.appendChild(star);
            }
            
            // Create shooting stars
            const shootingFragment = document.createDocumentFragment();
            for (let i = 0; i < settings.shooting_count; i++) {
                const shootingStar = document.createElement('div');
                shootingStar.classList.add('shooting-star');
                
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const colors = settings.shooting_colors.split(',');
                const color = colors[Math.floor(Math.random() * colors.length)];
                const delay = Math.random() * 15;
                const duration = Math.random() * 2 + 1;
                const size = settings.shooting_size;
                
                shootingStar.style.left = `${x}%`;
                shootingStar.style.top = `${y}%`;
                shootingStar.style.width = `${size}px`;
                shootingStar.style.height = `${size}px`;
                shootingStar.style.setProperty('--shooting-color', color);
                shootingStar.style.animationDelay = `${delay}s`;
                shootingStar.style.animationDuration = `${duration}s`;
                
                shootingFragment.appendChild(shootingStar);
            }
            
            galaxy.appendChild(starFragment);
            galaxy.appendChild(shootingFragment);
            
            function resizeGalaxy() {
                galaxy.style.height = window.innerHeight + 'px';
            }
            
            resizeGalaxy();
            window.addEventListener('resize', resizeGalaxy);
            window.addEventListener('orientationchange', resizeGalaxy);
        });
        </script>
        <?php
    }

    /* Admin Panel Functions */
    public function add_admin_menu() {
        add_options_page(
            'Galaxy Background Settings',
            'Galaxy Background',
            'manage_options',
            'hoa-galaxy-settings',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Galaxy Background Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('hoa_galaxy_settings');
                do_settings_sections('hoa-galaxy-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function settings_init() {
        register_setting('hoa_galaxy_settings', 'hoa_galaxy_settings', array($this, 'sanitize_settings'));
        
        add_settings_section(
            'hoa_galaxy_general',
            'General Settings',
            array($this, 'section_callback'),
            'hoa-galaxy-settings'
        );
        
        add_settings_section(
            'hoa_galaxy_stars',
            'Star Settings',
            array($this, 'section_callback'),
            'hoa-galaxy-settings'
        );
        
        add_settings_section(
            'hoa_galaxy_shooting',
            'Shooting Star Settings',
            array($this, 'section_callback'),
            'hoa-galaxy-settings'
        );
        
        // Star Fields
        add_settings_field(
            'star_count',
            'Number of Stars',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_stars',
            array('name' => 'star_count', 'min' => 10, 'max' => 500, 'step' => 10)
        );
        
        add_settings_field(
            'star_size_min',
            'Min Star Size (px)',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_stars',
            array('name' => 'star_size_min', 'min' => 0.5, 'max' => 5, 'step' => 0.1)
        );
        
        add_settings_field(
            'star_size_max',
            'Max Star Size (px)',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_stars',
            array('name' => 'star_size_max', 'min' => 1, 'max' => 10, 'step' => 0.1)
        );
        
        add_settings_field(
            'star_colors',
            'Star Colors (comma separated)',
            array($this, 'text_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_stars',
            array('name' => 'star_colors')
        );
        
        add_settings_field(
            'star_opacity',
            'Star Opacity',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_stars',
            array('name' => 'star_opacity', 'min' => 0.1, 'max' => 1, 'step' => 0.1)
        );
        
        // Shooting Star Fields
        add_settings_field(
            'shooting_count',
            'Number of Shooting Stars',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_shooting',
            array('name' => 'shooting_count', 'min' => 0, 'max' => 30, 'step' => 1)
        );
        
        add_settings_field(
            'shooting_size',
            'Shooting Star Size (px)',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_shooting',
            array('name' => 'shooting_size', 'min' => 1, 'max' => 5, 'step' => 0.5)
        );
        
        add_settings_field(
            'shooting_colors',
            'Shooting Star Colors (comma separated)',
            array($this, 'text_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_shooting',
            array('name' => 'shooting_colors')
        );
        
        // General Fields
        add_settings_field(
            'z_index',
            'Z-Index',
            array($this, 'number_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_general',
            array('name' => 'z_index', 'min' => -999, 'max' => 0, 'step' => 1)
        );
        
        add_settings_field(
            'disable_on_mobile',
            'Disable on Mobile',
            array($this, 'checkbox_callback'),
            'hoa-galaxy-settings',
            'hoa_galaxy_general',
            array('name' => 'disable_on_mobile')
        );
    }
    
    public function section_callback() {
        // Section descriptions can go here
    }
    
    public function number_callback($args) {
        $value = isset($this->settings[$args['name']]) ? $this->settings[$args['name']] : '';
        printf(
            '<input type="number" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="%2$s" min="%3$s" max="%4$s" step="%5$s" />',
            esc_attr($args['name']),
            esc_attr($value),
            esc_attr($args['min']),
            esc_attr($args['max']),
            esc_attr($args['step'])
        );
    }
    
    public function text_callback($args) {
        $value = isset($this->settings[$args['name']]) ? $this->settings[$args['name']] : '';
        printf(
            '<input type="text" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="%2$s" class="regular-text" />',
            esc_attr($args['name']),
            esc_attr($value)
        );
    }
    
    public function checkbox_callback($args) {
        $checked = isset($this->settings[$args['name']]) && $this->settings[$args['name']] ? 'checked' : '';
        printf(
            '<input type="checkbox" id="%1$s" name="hoa_galaxy_settings[%1$s]" value="1" %2$s />',
            esc_attr($args['name']),
            $checked
        );
    }
    
    public function sanitize_settings($input) {
        $output = $this->get_default_settings();
        
        foreach ($input as $key => $value) {
            if (isset($output[$key])) {
                if (in_array($key, ['star_count', 'shooting_count', 'z_index'])) {
                    $output[$key] = absint($value);
                } elseif (in_array($key, ['star_size_min', 'star_size_max', 'star_opacity', 'shooting_size'])) {
                    $output[$key] = floatval($value);
                } elseif (in_array($key, ['star_colors', 'shooting_colors'])) {
                    $colors = array_map('trim', explode(',', $value));
                    $valid_colors = array();
                    foreach ($colors as $color) {
                        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                            $valid_colors[] = $color;
                        }
                    }
                    $output[$key] = implode(',', $valid_colors);
                } elseif ($key === 'disable_on_mobile') {
                    $output[$key] = (bool) $value;
                }
            }
        }
        
        return $output;
    }
    
    public function admin_enqueue_scripts($hook) {
        if ('settings_page_hoa-galaxy-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
    }
}

HOA_Galaxy_Background::get_instance();