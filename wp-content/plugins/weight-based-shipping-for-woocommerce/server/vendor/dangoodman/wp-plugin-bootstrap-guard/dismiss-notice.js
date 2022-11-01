jQuery(function ($) {
    $('[data-dismissible] .notice-dismiss').click(function (e) {

        e.preventDefault();

        var noticeId = $(this).parent().attr('data-dismissible');

        //noinspection JSUnresolvedVariable
        var data = {
            'action': 'dgm_dismiss_admin_notice',
            'id': noticeId,
            'nonce': dgm_dismissible_notice.nonce
        };

        $.post(ajaxurl, data);
    });
});