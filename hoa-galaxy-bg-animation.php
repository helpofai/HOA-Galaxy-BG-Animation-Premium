<?php
/**
 * Plugin Name: HOA Galaxy BG Animation Premium
 * Description: Premium animated galaxy background with transparent background for better visibility
 * Version: 4.0.1
 * Author: Rajib Adhikary
 * Author URI: https://helpofai.com/
 * Plugin URI: https://helpofai.com/plugin/hoa-galaxy-bg-animation-premium
 * Text Domain: hoa-galaxy
 * Domain Path: /languages
 */

defined('ABSPATH') or die('Direct script access disallowed.');

define('HOA_GALAXY_PLUGIN_FILE', __FILE__);
define('HOA_GALAXY_PLUGIN_VERSION', '4.1.0');

require_once __DIR__ . '/src/HOA_Galaxy_Background.php';
require_once __DIR__ . '/src/HOA_Galaxy_Updater.php';

add_action('plugins_loaded', function () {
    load_plugin_textdomain('hoa-galaxy', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $galaxy_background = HOA\Galaxy\HOA_Galaxy_Background::get_instance();

    if (is_admin()) {
        $updater = new HOA\Galaxy\HOA_Galaxy_Updater(__FILE__);
        $updater->set_username('helpofai');
        $updater->set_repository('HOA-Galaxy-BG-Animation-Premium');
        $updater->authorize(null); // Replace with your GitHub token if needed
        $updater->initialize();
    }
});
