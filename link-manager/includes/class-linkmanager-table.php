<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * LinkManager_Table 类，用于在后台展示友情链接列表
 */
class LinkManager_Table extends WP_List_Table {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'links';

        parent::__construct( array(
            'singular' => __( 'Link', 'link-manager' ),
            'plural'   => __( 'Links', 'link-manager' ),
            'ajax'     => false
        ) );
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count( $data );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
        $this->items = $data;
    }

    public function get_columns() {
        return array(
            'cb'             => '<input type="checkbox" />',
            'site_name'      => __( '站点名称', 'friends-links-manager' ),
            'site_url'       => __( '站点地址', 'friends-links-manager' ),
            'avatar_url'     => __( '站点头像URL', 'friends-links-manager' ),
            'avatar'         => __( '站点头像', 'friends-links-manager' ),
            'description'    => __( '站点描述', 'friends-links-manager' ),
            'actions'        => __( '操作', 'friends-links-manager' )
        );
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array(
            'site_name' => array( 'site_name', false ),
            'created_at' => array( 'created_at', false )
        );
    }

    private function table_data() {
        global $wpdb;
        $query = "SELECT * FROM $this->table_name ORDER BY sort_order ASC";
        return $wpdb->get_results( $query, ARRAY_A );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'site_name':
            case 'site_url':
            case 'avatar_url':
            case 'description':
                return $item[ $column_name ];
            case 'avatar':
                if ( $item['avatar_id'] ) {
                    $avatar = wp_get_attachment_image_src( $item['avatar_id'], 'thumbnail' );
                    return '<img src="' . esc_url( $avatar[0] ) . '" width="50" height="50" />';
                } elseif ( $item['avatar_url'] ) {
                    return '<img src="' . esc_url( $item['avatar_url'] ) . '" width="50" height="50" />';
                }
                return '';
            default:
                return print_r( $item, true );
        }
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    public function column_actions( $item ) {
        $edit_url = add_query_arg( array(
            'page' => 'friends-links-manager',
            'action' => 'edit',
            'id' => $item['id']
        ), admin_url( 'admin.php' ) );

        $actions = array(
            'edit'   => sprintf( '<a href="%s" data-id="%d">%s</a>', $edit_url, $item['id'], __( '编辑', 'friends-links-manager' ) ),
            'delete' => sprintf( '<a href="#" class="delete-link" data-id="%d">%s</a>', $item['id'], __( '删除', 'friends-links-manager' ) )
        );

        return $this->row_actions( $actions );
    }

    protected function get_bulk_actions() {
        return array(
            'bulk-delete' => __( '批量删除', 'friends-links-manager' )
        );
    }

    private function sort_data( $a, $b ) {
        $orderby = 'sort_order';
        $order = 'asc';

        if ( ! empty( $_GET['orderby'] ) ) {
            $orderby = $_GET['orderby'];
        }

        if ( ! empty( $_GET['order'] ) ) {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[ $orderby ], $b[ $orderby ] );

        if ( $order === 'asc' ) {
            return $result;
        }

        return -$result;
    }
}