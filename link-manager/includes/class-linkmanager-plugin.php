<?php
/**
 * LinkManager_Plugin 主类，负责插件的初始化和卸载操作
 */
class LinkManager_Plugin {
    private static $instance;

    private function __construct() {
        $this->setup_hooks();
        $this->load_textdomain();
        $this->create_table();
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function setup_hooks() {
        // 加载后台管理类
        new LinkManager_Admin();
        // 加载短代码类
        new LinkManager_Shortcode();
    }

    private function load_textdomain() {
        load_plugin_textdomain(
            'link-manager',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/../languages/'
        );
    }

    private function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'links';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            site_name varchar(255) NOT NULL,
            site_url varchar(255) NOT NULL,
            avatar_url varchar(255) DEFAULT NULL,
            avatar_id mediumint(9) DEFAULT NULL,
            description text DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'links';

        // 删除数据库表
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

        // 删除所有选项
        delete_option( 'link_manager_options' );

        // 清理上传的图片
        $links = $wpdb->get_results( "SELECT avatar_id FROM $table_name WHERE avatar_id IS NOT NULL" );
        foreach ( $links as $link ) {
            wp_delete_attachment( $link->avatar_id, true );
        }
    }
}