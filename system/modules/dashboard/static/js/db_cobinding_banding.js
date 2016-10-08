var IbosCoLogin = IbosCoLogin || {};

$(IbosCoLogin).on({
    // 酷办公注册成功
    "regsuccess": function(evt, evtData) {
        window.location.href = Ibos.app.url('dashboard/cobinding/index', {
            isInstall: Ibos.app.g('isInstall')
        });
    },
    // 酷办公登录成功
    "loginsuccess": function(evt, evtData) {
        window.location.href = Ibos.app.url('dashboard/cobinding/index', {
            isInstall: Ibos.app.g('isInstall')
        });
    }
});

$(function() {
    var CoBinding = {
        // site_url: (function() {
        //     var SITE_URL = [window.location.protocol, "//", window.location.hostname, "/"].join("");
        //     Ibos.app.s("SITE_URL", SITE_URL);
        //     return SITE_URL;
        // })(),
        op: {
            // 获取同步数据
            getsynclist: function(param) {
                var url = Ibos.app.url("dashboard/cosync/getsynclist");
                return $.post(url, param, $.noop, "json");
            },
            // 开始同步
            syncLoad: function(param) {
                var url = Ibos.app.url('dashboard/cosync/sync');
                return $.post(url, param, $.noop, "json");
            },
            // 绑定用户信息详情
            bindingDetail: function(param) {
                var url = Ibos.app.url('dashboard/cosync/getuserlistinfo');
                return $.post(url, param, $.noop, "json");
            },
            // 解除绑定
            unBinding: function(param) {
                var url = Ibos.app.url('dashboard/cobinding/unbinding');
                return $.post(url, param, $.noop, "json");
            },
            // 自动同步
            autoSync: function(param) {
                var url = Ibos.app.url("dashboard/cosync/autosync");
                return $.post(url, param, $.noop, "json");
            },
            // 同步邀请未注册用户
            syncInvite: function(param) {
                var url = Ibos.app.url("dashboard/cosync/syncinvite");
                return $.post(url, param, $.noop, "json");
            }
        },
        elem: {
            $tmpl: $("#tmpl_ctn")
        },
        syncLoad: function(op) {
            var that = this,
                progressbar = $("#progressbar"),
                showprocess = $("#show_process");

            that.op.syncLoad({
                op: op
            }).done(function(res) {
                if (res.status === 1) {
                    progressbar.css("width", res.progress);
                    showprocess.text(res.message);
                    // 同步未完成，继续同步
                    that.syncLoad(res.op);
                } else if (res.status === 0) {
                    progressbar.css("width", res.progress);
                    showprocess.text(res.message);

                    setTimeout(function() {
                        that.elem.$tmpl.empty();
                        $.tmpl("binding_success", res.data).prependTo(that.elem.$tmpl);
                    }, 800);
                } else {
                    Ui.tip(res.message, "danger");

                    if( res.cobinding === false ){
                        document.location.href = Ibos.app.url("dashboard/cobinding/index");
                    }

                    that.getsynclist(null);
                    return false;
                }
            });
        },
        getsynclist: function(param, showSync) {
            var that = this;

            that.op.getsynclist(param).done(function(res) {
                if (res.status) {
                    var ibos = res.data.ibos,
                        co = res.data.co,
                        corpinfo = {
                            ibos: {
                                count: ibos.count,
                                ibosAddNum: ibos.ibosAddNum,
                                ibosAddAct: ibos.ibosAddNum > 0 ? "co-sync-active" : "",
                                ibosDelNum: ibos.ibosDelNum,
                                ibosDelAct: ibos.ibosDelNum > 0 ? "co-sync-active" : ""
                            },
                            co: {
                                count: co.count,
                                coAddNum: co.coAddNum,
                                coAddAct: co.coAddNum > 0 ? "co-sync-active" : "",
                                coDelNum: co.coDelNum,
                                coDelAct: co.coDelNum > 0 ? "co-sync-active" : ""
                            }
                        };
                    // 不显示同步页，只更新数据
                    if (!showSync) {
                        that.elem.$tmpl.empty();
                        $.tmpl("binding_update", corpinfo).prependTo(that.elem.$tmpl);
                        $('.co-binding-content .checkbox input').label();
                    }
                } else {
                    Ui.tip(res.msg, "warning");
                    return false;
                }
            });
        }
    };

    var autosync = $('[name="autoSync"]');
    autosync.val() == 1 ? autosync.prop("checked", true) : autosync.prop("checked", false);

    autosync.on("change", function(evt) {
        var that = this;

        CoBinding.op.autoSync({
            autoSync: that.checked ? 1 : 0
        }).done(function(res) {
            if (res.status) {
                Ui.tip(res.message);
                return true;
            } else {
                Ui.tip(res.message, "danger");
                return false;
            }
        });
    });

    var cache = {};

    Ibos.evt.add({
        // 开始同步
        "startedSync": function() {
            var tmpl = CoBinding.elem.$tmpl;
            tmpl.empty();
            $.tmpl("binding_progress", null).prependTo(tmpl);
            CoBinding.syncLoad("init");
        },
        // 同步成功
        "showSyncDetail": function(param, elem) {
            var tmpl = CoBinding.elem.$tmpl;
            CoBinding.getsynclist(null);
        },
        // 显示同步详情，成员列表
        "bindingDetail": function(param, elem) {
            var listname = param.list,
                op = param.list,
                title = "",
                data, 
                count = param.count,
                $list = $("#ibosco_sync_dialog ul"),
                $page = $("#ibosco_sync_dialog .page"),
                isLoad = false,
                iboscoSyncDialog;

            $list.empty();

            switch (listname) {
                case "ibosAddList":
                    title = Ibos.l("CO.IBOS_ADD_LIST");
                    break;
                case "ibosDelList":
                    title = Ibos.l("CO.IBOS_DEL_LIST");
                    break;
                case "coAddList":
                    title = Ibos.l("CO.CO_ADD_LIST");
                    break;
                case "coDelList":
                    title = Ibos.l("CO.CO_DEL_LIST");
                    break;
            }
            iboscoSyncDialog = Ui.dialog({
                title: title,
                id: "ibosco_sync_dialog",
                lock: true,
                content: document.getElementById("ibosco_sync_dialog"),
                minHeight: "600px",
                close: function() {
                    $list.empty();
                }
            });
            getData(0);
            _initPagination();

            function _initPagination() {
                var _settings = {
                    items_per_page: 99,
                    num_display_entries: 5,
                    prev_text: false,
                    next_text: false,
                    renderer: "ibosRenderer",
                    allow_jump: true,
                    callback: function(page, elem) {
                        if (isLoad) {
                            getData(page);
                        }
                        render();
                        isLoad = true;
                        $.ajaxSetup({
                            async: true
                        });
                    }
                };

                if (!$.fn.pagination) {
                    $.getScript(Ibos.app.getStaticUrl("/js/lib/jquery.pagination.js"))
                        .done(function() {
                            $page.pagination(count, _settings);
                        });
                } else {
                    $page.pagination(count, _settings);
                }
            }

            function getData(page) {
                $.ajaxSetup({
                    async: false
                });
                var params ={
                    op: op,
                    start: (page || 0) * 99,
                    length: 99
                };

                cache[op] = cache[op] || {};
                cache[op][page] = cache[op][page] || [];
                if( cache[op][page].length ){
                    data = cache[op][page];
                }else{
                    CoBinding.op.bindingDetail(params).done(function(res) {
                        if (res.isSuccess) {
                            data = res.data || [];
                            cache[op][page] = data;
                        } else {
                            Ui.tip(res.msg, "danger");
                            return false;
                        }
                    });
                }
            }

            function render(){
                var detailflag = /^ibos/.test(listname);
                $list.empty();
                $(data).each(function(index, item) {
                    var detail = detailflag ? (item.deptname + " " + item.posname) : item.mobile,
                        user = {
                            uid: item.uid,
                            realname: item.realname || "佚名",
                            avatar: item.avatar || "static.php?type=avatar&uid=2&size=middle&engine=LOCAL",
                            detail: detail
                        };
                    $.tmpl("binding_member_tpl", user).prependTo($list);
                    iboscoSyncDialog.DOM.content.scrollTop(0);
                });
            }
        },
        // 解绑操作
        "unBinding": function() {
            Ui.confirm(Ibos.l("CO.UNBINDING_IBOSCO_CONFIRM"), function() {
                CoBinding.op.unBinding(null).done(function(res) {
                    if (res.isSuccess) {
                        Ui.tip(Ibos.l("CO.UNBINDING_SUCCESS"));
                        window.location.href = Ibos.app.url("dashboard/cobinding/index", {
                            isInstall: Ibos.app.g("isInstall")
                        });
                    } else {
                        Ui.tip(res.msg, "danger");
                        return false;
                    }
                });
            });
        }
    });

    (function() {
        var pageInit = Ibos.app.g("pageInit"),
            $ctn = $("#tmpl_ctn");

        if (pageInit === "index") {
            CoBinding.getsynclist(null);
        } else if (pageInit === "sync") {
            // 第一次绑定
            CoBinding.getsynclist(null, true);

            $ctn.empty();
            $.tmpl("binding_progress", null).prependTo($ctn);
            CoBinding.syncLoad("init", true);
        }
    })();
});
