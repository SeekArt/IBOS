var IbosCoLogin = IbosCoLogin || {};

$(IbosCoLogin).on({
    // 酷办公注册成功
    "regsuccess": function(evt, evtData){
        location.reload();
    },
    // 酷办公登录成功
    "loginsuccess": function(evt, evtData){
        location.reload();
    }
});

$(function() {

    $("[data-toggle='tooltip']").tooltip();

    var WxSync = {
        op: {
            //获取绑定开关信息
            getBindOpt: function(param) {
                var url = Ibos.app.url('dashboard/wxbinding/toggleSwitch');
                return $.post(url, param, $.noop);
            },
            //获取同步开始时的数据
            getSyncData: function(param) {
                var url = Ibos.app.url('dashboard/wxsync/sync');
                return $.get(url, param, $.noop, "json");
            },
            //验证系统URL链接验证
            verifyUrl: function(param){
                var url = Ibos.app.url('dashboard/wxsync/sync');
                return $.post(url, param, $.noop, "json");
            },
            // 自动同步
            autoSync: function(param){
                var url = Ibos.app.url('dashboard/wxsync/sync');
                return $.post(url, param, $.noop, "json");
            },
            //获取详情
            getDetail: function(url, param){
                return $.post(url, param, $.noop, "json");
            }
        },
        syncData: function(url, deptCount, userCount, i) {
            $.get(url, function(res) {
                var data = res.data,
					tpl = data.tpl;
                if ( /sending|error|half|success/.test(tpl) ) {
                    var template = $.template("result_" + tpl + "_tpl", {
                        data: data
                    });
                    $("#sync_opt_wrap").html(template);
                } else {
					if( ~data.url.indexOf("user") ){
						deptCount = userCount;
					}

                    var percentage = Math.ceil( ( (deptCount - data.remain) / deptCount) * 100 );

                    res = $.extend({}, res, {
                        percentage: percentage
                    });

                    var template = $.template("result_syncing_tpl", {
                        data: res
                    });
                    $("#sync_opt_wrap").html(template);
                    WxSync.syncData(data.url, deptCount, userCount, i);
                }

            }, "json");
        }
    };

    Ibos.evt.add({
        // 验证系统链接
        "sysUrlVerify": function(param, elem){
            var $sysUrl = $("#sys_url"),
                sysUrlValue = $sysUrl.val(),
                that = this;
            if( !U.regex(sysUrlValue, "url") ){
                $sysUrl.blink().focus();
                Ui.tip("请输入正确的系统URL链接验证", 'danger');
                return false;
            }
            WxSync.op.verifyUrl({
                url: sysUrlValue
            }).done(function(res){
                $(".verify-msg").hide();
                if( res.isSuccess ){
                    $(".verify-msg.success").show();
                    $sysUrl.attr("disabled", 'true');
                    $(that).attr("disabled", 'true');
                }else{
                    $(".verify-msg.error").show();
                }
            });
        },
        // 安装套件应用
        "installApply": function(param, elem){
            param.url && window.open(param.url);
        },
        // 自动同步按钮
        "autoSync": function(param, elem){
            this.value = +!+this.value;

            WxSync.op.autoSync({
                status: this.value
            }).done(function(res){
                if( res.isSuccess ){
                    Ui.tip( Ibos.l("OPERATION_SUCCESS") );
                }else{
                    Ui.tip( Ibos.l("OPERATION_FAILED"), 'danger');
                }
            });
        },
        // 同步
        "syncData": function(param, elem) {
            var $this = $(this),
                status = $("#send_email").prop("checked") ? 1 : 0, //同步成功后，是否发送邮件
                paramData = {
                    'status': status,
                    'op': 'init'
                };

            WxSync.op.getSyncData(paramData).done(function(res) {
                var data = res.data,
                    i = 0;

                WxSync.syncData(data.url, data.deptCount, data.userCount, i);
            });
        },
        // 获取详情
        getDetail: function(param, elem){
            Ui.dialog({
                title: "详情",
                id: "ibosqyh_sync_dialog",
                lock: true,
                init: function(){
                    var _this = this;

                    WxSync.op.getDetail().done(function(res){
                        _this.DOM.content.append( $.template($("ibosqyh_sync_tmpl"), res.data) );
                    });
                }
            });
        },
        "changeSeting": function(param, elem) {
            var url = Ibos.app.url('dashboard/wxbinding/update');
            $.get(url, function(res) {
                if (res.isSuccess) {
                    var data = res.data;
                    $("#CorpID").val(data.corpid);
                    $("#CorpSecre").val(data.corpsecret);
                    $("#QRCode").val(data.qrcode);

                    var dialog = Ui.dialog({
                        title: "企业号绑定设置",
                        id: "d_setting",
                        width: 520,
                        content: document.getElementById("sync_setting_dialog"),
                        ok: function() {
                            var isPass = $.formValidator.pageIsValid(),
                                settingFromWrap = $("#setting_from_wrap");
                            if (isPass) {
                                var corpid = $("#CorpID").val(),
                                    corpsecret = $("#CorpSecre").val(),
                                    qrcode = $("#QRCode").val(),
                                    param = {
                                        corpid: corpid,
                                        corpsecret: corpsecret,
                                        qrcode: qrcode
                                    },
                                    url = Ibos.app.url('dashboard/wxbinding/update', {
                                        "updatesubmit": 1
                                    });
                                settingFromWrap.waiting(null, 'normal', true);
                                $.post(url, param, function(res) {
                                    if (res.isSuccess) {
                                        settingFromWrap.waiting(false);
                                        Ui.tip(res.msg);
                                        Ui.getDialog("d_setting").close();
                                    } else {
                                        Ui.tip(res.msg, "danger");
                                        settingFromWrap.waiting(false);
                                    }
                                });
                            }
                            return false;
                        },
                        cancel: function() {
                            $.formValidator.resetTipState();
                        },
                        close: function() {
                            $.formValidator.resetTipState();
                        }
                    });
                }
            });
        }
    });
});
