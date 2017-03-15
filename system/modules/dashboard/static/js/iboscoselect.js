$(function() {
    // var SITE_URL = [window.location.protocol, "//", window.location.hostname, "/"].join("");
    // Ibos.app.s("SITE_URL", SITE_URL);

    var CoAPI = {};
    var ajaxApi, readyBinding, coBinding;

    CoAPI.SUCCESS = 0;
    ajaxApi = {
        readybinding: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/readybinding');
            return $.post(url, param, $.noop, 'json');
        },
        cobinding: function(param) {
            var access_url = 'http://api.ibos.cn/v2/corp/jsonpbinding',
                callback = param.callback;

            param.callback = null;

            $.ajax({
                type: "get",
                async: false,
                url: access_url,
                data: param,
                dataType: "jsonp",
                jsonp: "oacallback",
                jsonpCallback: "getAccess",
                success: callback,
                error: function(err) {
                    Ui.tip(err, 'warning');
                    return false;
                }
            });
        },
        createandbinding: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/createandbinding');
            return $.post(url, param, $.noop, 'json');
        },
        setCorpbinding: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/setBinding');
            return $.post(url, param, $.noop, 'json');
        }
    };

    readyBinding = function(param, callback) {
        ajaxApi.readybinding(param).done(function(res) {
            if (res.isSuccess) {
                callback.call(null, res.data);
            } else {
                Ui.tip(res.msg, "warning");
                return false;
            }
        });
    };

    coBinding = function(param, callback) {
        param.callback = callback;

        ajaxApi.cobinding(param);
    };

    Ibos.evt.add({
        "coBindingAct": function(param, elem) {
            var params = {
                    corptoken: param.corptoken,
                    systemurl: Ibos.app.g('systemUrl'),
                    aeskey: Ibos.app.g('aeskey')
                },
                tips = param.isBindOther ? Ibos.l("CO.SURE_UNBINDING_AND_LINK_NEW_ADRESS", {
                    corpname: param.corpname,
                    systemurl: param.systemUrl
                }) : Ibos.l("CO.SURE_BINDING_COMPANY", {
                    corpname: param.corpname
                });

            Ui.confirm(tips, function() {
                coBinding(params, function(res) {
                    if (res.code == CoAPI.SUCCESS) {
                        ajaxApi.setCorpbinding().done(function(res) {
                            window.location.href = Ibos.app.url('dashboard/cosync/index', {
                                isInstall: res.isInstall
                            });
                        });
                    } else {
                        Ui.tip(res.message, 'warning');
                        return false;
                    }
                });
            });
        },
        "addBindingAct": function(param, elem) {
            Ui.dialog({
                title: Ibos.l("CO.CREATE_AND_BINDING_COMPANY"),
                id: "ibosco_addcorp_dialog",
                content: document.getElementById("ibosco_addcorp_dialog"),
                lock: true,
                cancel: function() {
                    this.close();
                    return false;
                },
                okVal: "确定",
                ok: function() {
                    var url = Ibos.app.url('dashboard/cobinding/createandbinding'),
                        params = {
                            corpname: $("#new_corpname").val(),
                            corpshortname: $("#new_corpshortname").val()
                        };
                    $.post(url, params, function(res) {
                        if (res.isSuccess) {
                            window.location.href = Ibos.app.url('dashboard/cosync/index', {
                                isInstall: res.isInstall
                            });
                        } else {
                            Ui.tip(res.msg, "warning");
                            return false;
                        }
                    }, "json");
                }
            });
        }
    });
});