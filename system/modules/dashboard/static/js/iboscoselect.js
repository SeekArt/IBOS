$(function() {
    // var SITE_URL = [window.location.protocol, "//", window.location.hostname, "/"].join("");
    // Ibos.app.s("SITE_URL", SITE_URL);

    Ibos.evt.add({
        "coBindingAct": function(param, elem) {
            var url = Ibos.app.url('dashboard/cobinding/readybinding'),
                params = {
                    corpid: param.corpid,
                    corptoken: param.corptoken,
                    corpshortname: param.corpshortname,
                    corpname: param.corpname,
                    corplogo: param.corplogo,
                    isInstall: Ibos.app.g("isInstall")
                },
                tips = param.isBindOther ? Ibos.l("CO.SURE_UNBINDING_AND_LINK_NEW_ADRESS", {
                    corpname: param.corpname,
                    systemurl: param.systemUrl
                }) : Ibos.l("CO.SURE_BINDING_COMPANY", {
                    corpname: param.corpname
                });
            
            Ui.confirm(tips, function() {
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
