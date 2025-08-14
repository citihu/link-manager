jQuery(document).ready(function($) {

    // 为卡片添加点击事件
    $('.friend-link-card').on('click', function() {
        var url = $(this).data('site-url');
        if (url) {
            window.open(url, '_blank');
        }
    });
});