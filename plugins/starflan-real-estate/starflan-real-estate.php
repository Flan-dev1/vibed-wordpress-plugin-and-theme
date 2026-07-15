<?php

/**
 * Plugin Name:       StarFlan Real Estate Data
 * Description:       Schema-driven forms and CSV imports for cities, properties, testimonials, and future data types.
 * Version:           2.2.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            StarFlan
 * Text Domain:       starflan-real-estate
 */

defined('ABSPATH') || exit;

function starflan_real_estate_enqueue_assets()
{
  wp_enqueue_style(
    ' starflan_real_estate',
    plugin_dir_url(__FILE__) . 'assets/css/shortcodes.css',
    array(),
    '1.0.0'
  );
}
add_action('wp_enqueue_scripts', 'starflan_real_estate_enqueue_assets');

define('STARFLAN_RE_VERSION', '2.2.1');
define('STARFLAN_RE_FILE', __FILE__);
define('STARFLAN_RE_PATH', plugin_dir_path(__FILE__));
define('STARFLAN_RE_URL', plugin_dir_url(__FILE__));

require_once STARFLAN_RE_PATH . 'src/Schema.php';
require_once STARFLAN_RE_PATH . 'src/Estatik.php';
require_once STARFLAN_RE_PATH . 'src/PostTypeRegistrar.php';
require_once STARFLAN_RE_PATH . 'src/RecordService.php';
require_once STARFLAN_RE_PATH . 'src/MetaBoxes.php';
require_once STARFLAN_RE_PATH . 'src/AdminPage.php';
require_once STARFLAN_RE_PATH . 'src/Plugin.php';
require_once STARFLAN_RE_PATH . 'includes/shortcodes/property-shortcode.php';
require_once STARFLAN_RE_PATH . 'includes/shortcodes/properties-shortcode.php';
require_once STARFLAN_RE_PATH . 'includes/shortcodes/featured-shortcode.php';

register_activation_hook(__FILE__, array('StarFlan\\RealEstate\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('StarFlan\\RealEstate\\Plugin', 'deactivate'));

StarFlan\RealEstate\Plugin::instance()->boot();
