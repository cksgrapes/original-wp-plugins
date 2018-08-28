<?php
/*
Plugin Name: Default Settings & Utility
Plugin URI: http://www.example.com/plugin
Description: テーマファイルの初期設定と便利機能
Author: Ai Shimizu
Version: 0.1
Author URI: http://chikashi.org
*/

/**
 * ============================================================
 */

if (!class_exists('DefaultSettingsAndUtilyty')) :
    class DefaultSettingsAndUtilyty
    {

        public function __construct ()
        {
            // Set some helpful constants
            $this->defineConstants();

            //Start the plugin
            add_action('admin_menu', array(&$this, 'start'));

            // Load the scripts
//            add_action('admin_enqueue_scripts', array(&$this, 'adminScripts'));

            // Load the CSS
//            add_action('admin_enqueue_scripts', array(&$this, 'adminCSS'));
        }

        /*
		 * defineConstants
		 * Defines a few static helper values we might need
		 */
        protected function defineConstants() {
            define('DSAU_VERSION', '1.0.0');
            define('DSAU_HOME', 'http://chikashi.org');
            define('DSAU_FILE', plugin_basename(dirname(__FILE__)));
            define('DSAU_URLPATH', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
        }

        /**
         * start
         * Main function
         */
        public function start ()
        {
            add_menu_page('テーマ初期設定', 'テーマ初期設定', 'administrator', __FILE__, 'dsau_options_page', 'dashicons-layout', 81);
        }

        /*
		 * Load JavaScript for Admin
		 */
        public function adminScripts() {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('related-scripts', DSAU_URLPATH .'/js/scripts.js', false, DSAU_VERSION, true);
        }


        /*
         * Load CSS for Admin
         */
        public function adminCSS() {
            wp_enqueue_style('related-admin-css', DSAU_URLPATH .'/css/admin-style.css', false, DSAU_VERSION, 'all');
        }

    }
endif;

/*
 * 初期化
 */
function dsau_init ()
{
    require_once(__DIR__ . '/inc/functions.php');
    require_once(__DIR__ . '/inc/static-options.php');
    require_once(__DIR__ . '/inc/dynamic-options.php');
    require_once(__DIR__ . '/inc/view.php');

//    load_plugin_textdomain('DefaultSettingsAndUtilyty', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');

    // Start the plugin
    global $dsau;
    $dsau = new DefaultSettingsAndUtilyty();
}

add_action('plugins_loaded', 'dsau_init');