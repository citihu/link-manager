<?php
/**
 * Plugin Name:       Link Manager
 * Plugin URI:        
 * Description:       Professional friend link management tool, providing complete link management functions and beautiful front - end display
 * Version:           1.0.1
 * Author:            citihai
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       link-manager
 * Domain Path:       /languages
 */

// 如果直接访问此文件，终止执行
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 定义插件基础常量
define( 'LM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 自动加载类文件
spl_autoload_register( function ( $class_name ) {
    if ( strpos( $class_name, 'LinkManager_' ) === 0 ) {
        $class_file = str_replace( '_', '-', strtolower( $class_name ) );
        require_once LM_PLUGIN_DIR . 'includes/class-' . $class_file . '.php';
    }
} );

// 初始化插件
function link_manager_init() {
    LinkManager_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'link_manager_init' );

// 卸载插件时执行清理操作
register_uninstall_hook( __FILE__, array( 'LinkManager_Plugin', 'uninstall' ) );