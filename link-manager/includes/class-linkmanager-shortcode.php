<?php
/**
 * LinkManager_Shortcode 类，处理友情链接短代码
 */
class LinkManager_Shortcode {
    // 将 __init__ 改为 __construct
    public function __construct() { 
        add_shortcode( 'link_manager', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
     }

    public function render_shortcode( $atts ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'links';
        $links = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY sort_order ASC", ARRAY_A );

        $output = '<div class="friends-links-container">';
        foreach ( $links as $link ) {
            $avatar = '';
            if ( $link['avatar_id'] ) {
                $avatar_src = wp_get_attachment_image_src( $link['avatar_id'], 'medium' );
                $avatar = '<div class="avatar-area" style="background-image: url(\'' . esc_url( $avatar_src[0] ) . '\');"></div>';
            } elseif ( $link['avatar_url'] ) {
                $avatar = '<div class="avatar-area" style="background-image: url(\'' . esc_url( $link['avatar_url'] ) . '\');"></div>';
            }

            $output .= '<div class="friend-link-card" data-site-url="' . esc_url( $link['site_url'] ) . '">';
            $output .= $avatar;
            $output .= '<div class="name-area">';
            $output .= esc_html( $link['site_name'] );
            $output .= '</div>';
            $output .= '<div class="description-area">';
            $output .= esc_html( $link['description'] );
            $output .= '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }
    // 添加版本号，可使用当前时间戳保证每次都是新的版本
    // $ftcss_version = time(); 
    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'friends-links-frontend', FLM_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.7' );
        wp_enqueue_script( 'friends-links-frontend', FLM_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), '1.0.2', true );
    }
}