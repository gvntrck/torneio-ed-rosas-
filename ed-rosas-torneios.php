<?php
/**
 * Plugin Name: Torneio Ed Rosas
 * Plugin URI: https://example.com/
 * Description: Plugin para criação de formulários de inscrição de torneios de xadrez integrados com o WooCommerce.
 * Version: 1.0.3
 * Author: Ed Rosas
 * Author URI: https://example.com/
 * Text Domain: torneio-ed-rosas
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('ER_TORNEIOS_VERSION', '1.0.3');
define('ER_TORNEIOS_PLUGIN_FILE', __FILE__);
define('ER_TORNEIOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ER_TORNEIOS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main instance of the plugin.
 */
class ER_Torneios
{

    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    private function includes()
    {
        // Load classes
        require_once ER_TORNEIOS_PLUGIN_DIR . 'includes/class-er-cpt.php';
        require_once ER_TORNEIOS_PLUGIN_DIR . 'includes/class-er-metabox.php';
        require_once ER_TORNEIOS_PLUGIN_DIR . 'includes/class-er-shortcode.php';
        require_once ER_TORNEIOS_PLUGIN_DIR . 'includes/class-er-woocommerce.php';
    }

    private function init_hooks()
    {
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init()
    {
        // Init classes
        new ER_CPT();
        new ER_Metabox();
        new ER_Shortcode();
        new ER_WooCommerce();
    }
}

/**
 * Helper to init plugin singleton.
 */
function ER_Torneios()
{
    return ER_Torneios::instance();
}

// Start the plugin
ER_Torneios();
