$(function(){
    //qq
    (function() {
        $("#QQ_enable").on("change", function() {
            $("#QQ_setup, #syncs").toggle();
        });
        Ibos.events.add({
            'mb': function(param, $elem) {
                var url = Ibos.app.url('dashboard/im/bindinguser');
                Ui.ajaxDialog(url, {
                    id: "d_bindinguser",
                    title: "绑定用户",
                    padding: 0,
                    ok: function() {
                        var data = parent.$('#bind_form').serializeArray();
                        $.post(url, data, function(data) {
                            if (data.isSuccess) {
                                Ui.tip('绑定成功', 'success');
                                Ui.closeDialog();
                            } else {
                                Ui.tip('未知错误，绑定失败', 'error');
                            }
                        });
                        return false;
                    },
                    cancel: true
                });
            }
        });
    })();
    

    //RTX
    (function() {
        $("#rtx_enable").on("change", function() {
            $("#rtx_setup, #synctortx,#synctooa").toggle();
        });
        $("[data-act='syncrtx']").on('click', function() {
            if ($('#rtx_init_pwd').val() == '') {
                $('#rtx_init_pwd').blink();
                return false;
            }
            Ui.confirm(U.lang("DB.RTX_SYNC_CONFIRM"), function() {
                var pwd = $('#rtx_init_pwd').val();
                var url = Ibos.app.url('dashboard/im/syncrtx', {pwd: pwd});
                Ui.dialog({
                    title: U.lang("DB.RTX_SYNC_TITLE"),
                    content: U.lang("DB.RTX_SYNC_CONTENT"),
                    cancel: false,
                    ok: false,
                    modal: true
                });
                $.get(url, function(data) {
                    if (data.isSuccess) {
                        Ui.tip(U.lang("DB.RTX_SYNC_SUCCESS"), 'success');
                    } else {
                        Ui.tip(data.msg, 'danger');
                    }
                    Ui.closeDialog();
                }, 'json');
            });
        });
        $("[data-act='syncoa']").on('click', function() {
            var url = Ibos.app.url('dashboard/im/syncoa');
            Ui.openFrame(url,{title:Ibos.l("IM.SYNCHRONIZE_OA")});
        });
    })();
})