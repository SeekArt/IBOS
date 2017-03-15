var IbosCoLogin = IbosCoLogin || {};

$(IbosCoLogin).on({
    // 酷办公注册成功
    "regsuccess": function(evt, evtData) {
        location.reload();
    },
    // 酷办公登录成功
    "loginsuccess": function(evt, evtData) {
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
            verifyUrl: function(param) {
                var url = Ibos.app.url('dashboard/wxsync/sync');
                return $.post(url, param, $.noop, "json");
            },
            // 自动同步
            autoSync: function(param) {
                var url = Ibos.app.url('dashboard/wxsync/sync');
                return $.post(url, param, $.noop, "json");
            },
            //获取详情
            getDetail: function(url, param) {
                return $.post(url, param, $.noop, "json");
            },
            checkAccess: function(param) {
                var url = Ibos.app.url('dashboard/wxbinding/checkAccess');
                return $.post(url, param, $.noop, 'json');
            },
            locationWX: function(param) {
                var url = Ibos.app.url('dashboard/wxbinding/locationWx');
                return $.post(url, param, $.noop, 'json');
            },
            getwxcount: function(param) {
                var url = Ibos.app.url('dashboard/wxsync/getwxcount');
                return $.post(url, param, $.noop, 'json');
            }
        },
        syncData: function(url, deptCount, userCount, i) {
            $.get(url, function(res) {
                if (res.isSuccess) {
                    var data = res.data,
                        tpl = data.tpl;
                    if (/sending|error|half|success/.test(tpl)) {
                        var template = $.template("result_" + tpl + "_tpl", {
                            data: data
                        });
                        $("#sync_opt_wrap").html(template);
                    } else {
                        if (~data.url.indexOf("user")) {
                            deptCount = userCount;
                        }

                        var percentage = Math.ceil(((deptCount - data.remain) / deptCount) * 100);

                        res = $.extend({}, res, {
                            percentage: percentage
                        });

                        var template = $.template("result_syncing_tpl", {
                            data: res
                        });
                        $("#sync_opt_wrap").html(template);
                        WxSync.syncData(data.url, deptCount, userCount, i);
                    }
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            }, "json");
        }
    };

    Ibos.evt.add({
        // 验证系统链接
        "sysUrlVerify": function(param, elem) {
            var $sysUrl = $("#sys_url"),
                sysUrlValue = $sysUrl.val(),
                that = this;
            if (!U.regex(sysUrlValue, "url")) {
                $sysUrl.blink().focus();
                Ui.tip("请输入正确的系统URL链接验证", 'danger');
                return false;
            }
            WxSync.op.verifyUrl({
                url: sysUrlValue
            }).done(function(res) {
                $(".verify-msg").hide();
                if (res.isSuccess) {
                    $(".verify-msg.success").show();
                    $sysUrl.attr("disabled", 'true');
                    $(that).attr("disabled", 'true');
                } else {
                    $(".verify-msg.error").show();
                }
            });
        },
        // 安装套件应用
        "installApply": function(param, elem) {
            var newTab = window.open('about:blank');

            WxSync.op.locationWX({
                domain: $('input[name="sysurl"]').val()
            }).done(function(res) {
                if (res.isSuccess) {
                    newTab.location.href = res.data.url;
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
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

                if (res.isSuccess) {
                    WxSync.syncData(data.url, data.deptCount, data.userCount, i);
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            });
        },
        "bindWXCheck": function() {
            WxSync.op.checkAccess({
                domain: $('input[name="sysurl"]').val()
            }).done(function(res) {
                if (res.isSuccess) {
                    Ui.tip('验证成功');
                    $('.wx-suite-install').removeClass('disabled').prop('disabled', false);
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            });
        }
    });

    WxSync.op.getwxcount().done(function(res) {
        if (res.isSuccess) {
            $('#wx_count').empty().append('<span class="fsg xwb">' + res.data.wxCount + '</span><span>人</span>');
        } else {
            Ui.tip(res.msg, 'warning');
            return false;
        }
    });
});