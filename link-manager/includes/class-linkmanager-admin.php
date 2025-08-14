<?php
/**
 * LinkManager_Admin 类，负责友情链接后台管理界面
 */
class LinkManager_Admin {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_flm_add_link', array( $this, 'add_link' ) );
        add_action( 'wp_ajax_flm_edit_link', array( $this, 'edit_link' ) );
        add_action( 'wp_ajax_flm_delete_link', array( $this, 'delete_link' ) );
        add_action( 'wp_ajax_flm_bulk_delete', array( $this, 'bulk_delete' ) );
        add_action( 'wp_ajax_flm_update_sort_order', array( $this, 'update_sort_order' ) );
        add_action( 'wp_ajax_flm_import_csv', array( $this, 'import_csv' ) );
        add_action( 'wp_ajax_flm_export_csv', array( $this, 'export_csv' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Link Management', 'link-manager' ),
            __( 'Link Management', 'link-manager' ),
            'manage_options',
            'link-manager',
            array( $this, 'render_admin_page' ),
            'dashicons-admin-links',
            25
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_friends-links-manager' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
        wp_enqueue_style( 'friends-links-admin', FLM_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.0' );

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media();
        // 添加版本号，可使用当前时间戳保证每次都是新的版本
        //$js_version = time(); 
        wp_enqueue_script( 'friends-links-admin', FLM_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '1.0.3', true );

        wp_localize_script( 'friends-links-admin', 'flm_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'friends-links-manager-nonce' )
        ) );
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        if ( isset( $_GET['action'] ) && $_GET['action'] === 'add' ) {
            $this->render_add_link_page();
        } elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
            $link = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $_GET['id'] ), ARRAY_A );
            if ( $link ) {
                $this->render_edit_link_page( $link );
            } else {
                $this->render_list_page();
            }
        } else {
            $this->render_list_page();
        }
    }

    private function render_add_link_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( '添加新链接', 'friends-links-manager' ); ?></h1>
            <form id="flm-add-link-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="site_name"><?php _e( '站点名称', 'friends-links-manager' ); ?>*</label></th>
                        <td><input type="text" id="site_name" name="site_name" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="site_url"><?php _e( '站点地址', 'friends-links-manager' ); ?>*</label></th>
                        <td><input type="url" id="site_url" name="site_url" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="avatar_url"><?php _e( '站点头像URL', 'friends-links-manager' ); ?></label></th>
                        <td><input type="url" id="avatar_url" name="avatar_url"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="avatar_id"><?php _e( '站点头像', 'friends-links-manager' ); ?></label></th>
                        <td>
                            <input type="hidden" id="avatar_id" name="avatar_id">
                            <button type="button" id="upload-avatar"><?php _e( '选择头像', 'friends-links-manager' ); ?></button>
                            <div id="avatar-preview"></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php _e( '站点描述', 'friends-links-manager' ); ?></label></th>
                        <td><textarea id="description" name="description"></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( '添加链接', 'friends-links-manager' ); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    private function render_edit_link_page( $link ) {
        ?>
        <div class="wrap">
            <h1><?php _e( '编辑链接', 'friends-links-manager' ); ?></h1>
            <form id="flm-edit-link-form">
                <input type="hidden" name="id" value="<?php echo esc_attr( $link['id'] ); ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="site_name"><?php _e( '站点名称', 'friends-links-manager' ); ?>*</label></th>
                        <td><input type="text" id="site_name" name="site_name" value="<?php echo esc_attr( $link['site_name'] ); ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="site_url"><?php _e( '站点地址', 'friends-links-manager' ); ?>*</label></th>
                        <td><input type="url" id="site_url" name="site_url" value="<?php echo esc_attr( $link['site_url'] ); ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="avatar_url"><?php _e( '站点头像URL', 'friends-links-manager' ); ?></label></th>
                        <td><input type="url" id="avatar_url" name="avatar_url" value="<?php echo esc_attr( $link['avatar_url'] ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="avatar_id"><?php _e( '站点头像', 'friends-links-manager' ); ?></label></th>
                        <td>
                            <input type="hidden" id="avatar_id" name="avatar_id" value="<?php echo esc_attr( $link['avatar_id'] ); ?>">
                            <button type="button" id="upload-avatar"><?php _e( '选择头像', 'friends-links-manager' ); ?></button>
                            <div id="avatar-preview">
                                <?php if ( $link['avatar_id'] ) : ?>
                                    <?php $avatar = wp_get_attachment_image_src( $link['avatar_id'], 'thumbnail' ); ?>
                                    <img src="<?php echo esc_url( $avatar[0] ); ?>" width="50" height="50">
                                <?php elseif ( $link['avatar_url'] ) : ?>
                                    <img src="<?php echo esc_url( $link['avatar_url'] ); ?>" width="50" height="50">
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php _e( '站点描述', 'friends-links-manager' ); ?></label></th>
                        <td><textarea id="description" name="description"><?php echo esc_textarea( $link['description'] ); ?></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( '更新链接', 'friends-links-manager' ); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    private function render_list_page() {
        $table = new FriendsLinks_Table();
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( '友情链接管理', 'friends-links-manager' ); ?></h1>
            <a href="<?php echo add_query_arg( array( 'page' => 'friends-links-manager', 'action' => 'add' ), admin_url( 'admin.php' ) ); ?>" class="page-title-action"><?php _e( '添加新链接', 'friends-links-manager' ); ?></a>
            <hr class="wp-header-end">

            <form id="flm-links-filter" method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>">
                <?php $table->display(); ?>
            </form>

            <div id="flm-import-export">
                <h2><?php _e( '导入/导出', 'friends-links-manager' ); ?></h2>
                <form id="flm-import-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="csv_file" accept=".csv">
                    <input type="submit" class="button" value="<?php _e( '导入CSV', 'friends-links-manager' ); ?>">
                </form>
                <a href="#" id="flm-export-csv" class="button"><?php _e( '导出CSV', 'friends-links-manager' ); ?></a>
            </div>
        </div>
        <?php
    }

    public function add_link() {
    check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
    }

    // 直接从 $_POST 获取数据
    $site_name = sanitize_text_field( $_POST['site_name'] ?? '' );
    $site_url = esc_url_raw( $_POST['site_url'] ?? '' );
    $avatar_url = esc_url_raw( $_POST['avatar_url'] ?? '' );
    $avatar_id = isset( $_POST['avatar_id'] ) ? absint( $_POST['avatar_id'] ) : 0;
    $description = sanitize_textarea_field( $_POST['description'] ?? '' );
    error_log("site_name: " . $site_name);
    error_log("site_url: " . $site_url);

    if ( empty( $site_name ) || empty( $site_url ) ) {
        error_log("必填！！！" );
        wp_send_json_error( __( '必填字段不能为空', 'friends-links-manager' ) );
    }

    if ( ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
        wp_send_json_error( __( '站点地址格式不正确', 'friends-links-manager' ) );
    }

    if ( ! empty( $avatar_url ) && ! filter_var( $avatar_url, FILTER_VALIDATE_URL ) ) {
        wp_send_json_error( __( '站点头像URL格式不正确', 'friends-links-manager' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'friends_links';

    $result = $wpdb->insert(
        $table_name,
        array(
            'site_name' => $site_name,
            'site_url' => $site_url,
            'avatar_url' => $avatar_url,
            'avatar_id' => $avatar_id,
            'description' => $description,
            'sort_order' => 0
        ),
        array( '%s', '%s', '%s', '%d', '%s', '%d' )
    );

    if ( $result ) {
        wp_send_json_success( __( '链接添加成功', 'friends-links-manager' ) );
    } else {
        wp_send_json_error( __( '链接添加失败', 'friends-links-manager' ) );
    }
}

    public function edit_link() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        $id = absint( $_POST['id'] ?? '' );
        $site_name = sanitize_text_field( $_POST['site_name'] );
        $site_url = esc_url_raw( $_POST['site_url'] ?? '' );
        $avatar_url = esc_url_raw( $_POST['avatar_url'] ?? '' );
        $avatar_id = isset( $_POST['avatar_id'] ) ? absint( $_POST['avatar_id'] ) : 0;
        $description = sanitize_textarea_field( $_POST['description'] ?? '' );

        if ( empty( $site_name ) || empty( $site_url ) ) {
            wp_send_json_error( __( '必填字段不能为空', 'friends-links-manager' ) );
        }

        if ( ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( __( '站点地址格式不正确', 'friends-links-manager' ) );
        }

        if ( ! empty( $avatar_url ) && ! filter_var( $avatar_url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( __( '站点头像URL格式不正确', 'friends-links-manager' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        $result = $wpdb->update(
            $table_name,
            array(
                'site_name' => $site_name,
                'site_url' => $site_url,
                'avatar_url' => $avatar_url,
                'avatar_id' => $avatar_id,
                'description' => $description
            ),
            array( 'id' => $id ),
            array( '%s', '%s', '%s', '%d', '%s' ),
            array( '%d' )
        );

        if ( false !== $result ) {
            wp_send_json_success( __( '链接更新成功', 'friends-links-manager' ) );
        } else {
            wp_send_json_error( __( '链接更新失败', 'friends-links-manager' ) );
        }
    }

    public function delete_link() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        $id = absint( $_POST['id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        $link = $wpdb->get_row( $wpdb->prepare( "SELECT avatar_id FROM $table_name WHERE id = %d", $id ) );
        if ( $link && $link->avatar_id ) {
            wp_delete_attachment( $link->avatar_id, true );
        }

        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        if ( $result ) {
            wp_send_json_success( __( '链接删除成功', 'friends-links-manager' ) );
        } else {
            wp_send_json_error( __( '链接删除失败', 'friends-links-manager' ) );
        }
    }

    public function bulk_delete() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        if ( ! isset( $_POST['ids'] ) || ! is_array( $_POST['ids'] ) ) {
            wp_send_json_error( __( '无效的ID列表', 'friends-links-manager' ) );
        }

        $ids = array_map( 'absint', $_POST['ids'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        foreach ( $ids as $id ) {
            $link = $wpdb->get_row( $wpdb->prepare( "SELECT avatar_id FROM $table_name WHERE id = %d", $id ) );
            if ( $link && $link->avatar_id ) {
                wp_delete_attachment( $link->avatar_id, true );
            }

            $wpdb->delete(
                $table_name,
                array( 'id' => $id ),
                array( '%d' )
            );
        }

        wp_send_json_success( __( '批量删除成功', 'friends-links-manager' ) );
    }

    public function update_sort_order() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        if ( ! isset( $_POST['ids'] ) || ! is_array( $_POST['ids'] ) ) {
            wp_send_json_error( __( '无效的ID列表', 'friends-links-manager' ) );
        }

        $ids = array_map( 'absint', $_POST['ids'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        foreach ( $ids as $order => $id ) {
            $wpdb->update(
                $table_name,
                array( 'sort_order' => $order ),
                array( 'id' => $id ),
                array( '%d' ),
                array( '%d' )
            );
        }

        wp_send_json_success( __( '排序更新成功', 'friends-links-manager' ) );
    }

    public function import_csv() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( __( '文件上传失败', 'friends-links-manager' ) );
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen( $file, 'r' );

        if ( ! $handle ) {
            wp_send_json_error( __( '无法打开文件', 'friends-links-manager' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        $headers = fgetcsv( $handle );
        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $data = array_combine( $headers, $row );

            $site_name = sanitize_text_field( $data['site_name'] );
            $site_url = esc_url_raw( $data['site_url'] );
            $avatar_url = esc_url_raw( $data['avatar_url'] ?? '' );
            $avatar_id = isset( $data['avatar_id'] ) ? absint( $data['avatar_id'] ) : 0;
            $description = sanitize_textarea_field( $data['description'] ?? '' );

            if ( empty( $site_name ) || empty( $site_url ) ) {
                continue;
            }

            if ( ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
                continue;
            }

            if ( ! empty( $avatar_url ) && ! filter_var( $avatar_url, FILTER_VALIDATE_URL ) ) {
                continue;
            }

            $wpdb->insert(
                $table_name,
                array(
                    'site_name' => $site_name,
                    'site_url' => $site_url,
                    'avatar_url' => $avatar_url,
                    'avatar_id' => $avatar_id,
                    'description' => $description,
                    'sort_order' => 0
                ),
                array( '%s', '%s', '%s', '%d', '%s', '%d' )
            );
        }

        fclose( $handle );
        wp_send_json_success( __( 'CSV导入成功', 'friends-links-manager' ) );
    }

    public function export_csv() {
        check_ajax_referer( 'friends-links-manager-nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( '权限不足', 'friends-links-manager' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'friends_links';

        $links = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY sort_order ASC", ARRAY_A );

        $filename = 'friends-links-' . date( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array_keys( $links[0] ) );

        foreach ( $links as $link ) {
            fputcsv( $output, $link );
        }

        fclose( $output );
        exit;
    }
}