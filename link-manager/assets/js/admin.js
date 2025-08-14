jQuery(document).ready(function($) {
    // 媒体选择器
    $('#upload-avatar').click(function(e) {
        e.preventDefault();
        var custom_uploader = wp.media({
            title: '选择头像',
            button: {
                text: '选择'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#avatar_id').val(attachment.id);
            $('#avatar-preview').html('<img src="' + attachment.url + '" width="50" height="50">');
        }).open();
    });

    // 添加链接表单提交
$('#flm-add-link-form').submit(function(e) {

    console.log('开始提交————！！！！'); 
    e.preventDefault();
    var formData = $(this).serialize() + '&action=flm_add_link&nonce=' + flm_ajax.nonce;
    // 打印序列化后的数据，方便调试
    console.log('序列化后的表单数据:', formData); 

    $.ajax({
        url: flm_ajax.ajax_url,
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                alert(response.data);
                $('#flm-add-link-form')[0].reset();
                $('#avatar-preview').html('');
            } else {
                alert(response.data);
            }
        },
        error: function() {
            alert('请求失败');
        }
    });
});

    // 编辑链接表单提交
    $('#flm-edit-link-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=flm_edit_link&nonce=' + flm_ajax.nonce;

        $.ajax({
            url: flm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    window.location.href = 'admin.php?page=friends-links-manager';
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('请求失败');
            }
        });
    });

    // 删除链接
    $(document).on('click', '.delete-link', function(e) {
        e.preventDefault();
        if (confirm('确定要删除这个链接吗？')) {
            var id = $(this).data('id');

            $.ajax({
                url: flm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'flm_delete_link',
                    nonce: flm_ajax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('请求失败');
                }
            });
        }
    });

    // 批量删除
    $('#doaction, #doaction2').click(function(e) {
        var action = $(this).attr('id') === 'doaction' ? $('#bulk-action-selector-top').val() : $('#bulk-action-selector-bottom').val();
        if (action === 'bulk-delete') {
            e.preventDefault();
            if (confirm('确定要批量删除选中的链接吗？')) {
                var ids = [];
                $('input[name="id[]"]:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length > 0) {
                    $.ajax({
                        url: flm_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'flm_bulk_delete',
                            nonce: flm_ajax.nonce,
                            ids: ids
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.data);
                                location.reload();
                            } else {
                                alert(response.data);
                            }
                        },
                        error: function() {
                            alert('请求失败');
                        }
                    });
                }
            }
        }
    });

    // 拖拽排序
    $('.wp-list-table tbody').sortable({
        items: 'tr',
        cursor: 'move',
        axis: 'y',
        update: function() {
            var ids = [];
            $('.wp-list-table tbody tr').each(function() {
                ids.push($(this).find('input[name="id[]"]').val());
            });

            $.ajax({
                url: flm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'flm_update_sort_order',
                    nonce: flm_ajax.nonce,
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        console.log(response.data);
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('请求失败');
                }
            });
        }
    });

    // 导入CSV
    $('#flm-import-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: flm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('请求失败');
            }
        });
    });

    // 导出CSV
    $('#flm-export-csv').click(function(e) {
        e.preventDefault();
        window.location.href = flm_ajax.ajax_url + '?action=flm_export_csv&nonce=' + flm_ajax.nonce;
    });
});