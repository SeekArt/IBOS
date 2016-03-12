/**
 * Dashboard/user/index
 */

$(document).ready(function() {
    $("#edit_corporation").on("click", function() {
        location.href = Ibos.app.url("dashboard/unit/index");
        return false;
    });

    // 接
    if (U.getCookie('hooksyncuser') == '1') {
        parent.Ui.openFrame(U.getCookie('syncurl'), {
            title: U.lang("ORG.SYNC_USER"),
            cancel: true
        });
        U.setCookie('hooksyncuser', '');
        U.setCookie('syncurl', '');
    }
    //搜索
    $("#mn_search").search();

    //初始化上传
    Ibos.upload.attach({
        post_params: { module: 'dashboard' },
        file_types: "*.xls; *.xlsx;",
        file_upload_limit: 1,
        custom_settings: {
            containerId: "file_target",
            inputId: "attachmentid"
        }
    });

    var importUser = {
        op: {
            // 获取可登录用户数等信息
            "getUserInfo": function() {
                var url = Ibos.app.url("dashboard/user/getavailable");
                return $.get(url, $.noop, "json");
            }
        },
        // 更新可登录用户数等信息
        "updateUserInfo": function($wrap) {
            var _this = this,
                $currentNum = $wrap.find(".current-num"),
                $remainNum = $wrap.find(".remain-num");
            _this.op.getUserInfo().done(function(res) {
                if (res.isSuccess) {
                    $currentNum.text(res.current);
                    $remainNum.text(res.remain);
                } else {
                    Ui.tip(res.msg, "danger");
                }
            });
        }
    };

    Ibos.evt.add({
        "setUserStatus": function(param, elem) {
            var uid = U.getCheckedValue("user");
            if (!uid) {
                Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
                return false;
            }
            $("#org_user_table").waiting(null, "normal");
            $.get(Ibos.app.url('dashboard/user/edit'), { op: param.op, uid: uid }, function(res) {
                $("#org_user_table").waiting(false);
                if (res.isSuccess) {
                    Ui.tip(Ibos.l("OPERATION_SUCCESS"));
                    window.location.reload();
                } else {
                    Ui.tip(res.msg, "danger");
                }
            }, 'json');
        },
        "exportUser": function() {
            var uid = U.getCheckedValue("user");
            if (!uid) {
                Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
                return false;
            }
            window.location.href = Ibos.app.url('dashboard/user/export', { uid: encodeURI(uid) });
        },
        "batchImport": function(param, elem) {
            var dialog = Ui.dialog({
                title: Ibos.l("ORG.BATCH_IMPORT_USER"),
                id: "import_dialog",
                padding: 0,
                width: "480px",
                height: "382px",
                lock: true,
                content: document.getElementById("batch_import_dialog"),
                init: function() {
                    var $wrap = $("#batch_import_wrap");
                    $("#batch_result_wrap").hide();
                    $("#attachmentid").val("");
                    $("#download_error_info").attr("href", "");
                    $("#file_target").children().remove();
                    importUser.updateUserInfo($wrap);
                    $wrap.show();
                },
                close: function() {
                    window.location.reload();
                }
            });
        },
        "closeDialog": function(param, elem) {
            var dialog = Ui.dialog.get("import_dialog");
            dialog.close();
            window.location.reload();
        },
        "againImport": function(param, elem) {
            var type = param.type,
                dialog = Ui.dialog.get("import_dialog"),
                $wrap = $("#batch_import_wrap");
            dialog.DOM.title.html(Ibos.l("ORG.BATCH_IMPORT_USER"));
            if (type == "success") {
                $("#batch_result_wrap").hide();
                $("#download_error_info").attr("href", "");
                importUser.updateUserInfo($wrap);
            } else {
                var $wrap = $("#batch_falure_wrap");
                $wrap.hide();
            }
            $("#batch_import_wrap").show();
            $("#attachmentid").val("");
            $("#file_target").children().remove();
        },
        "importExel": function(param, elem) {
            var dialog = Ui.dialog.get("import_dialog"),
                attachmentid = $("#attachmentid").val(),
                $importwrap = $("#batch_import_wrap"),
                $wrap = $("#upload_wrap");
            if (attachmentid) {
                var param = { aid: attachmentid },
                    url = Ibos.app.url('dashboard/user/import', { 'op': 'import' });
                $wrap.waiting(null, "mini", "normal");
                $.post(url, param, function(res) {
                    dialog.DOM.title.html(Ibos.l("ORG.BATCH_IMPORT_RESULT"));
                    if (res.isSuccess) {
                        var url = res.url,
                            success = res.successCount,
                            failure = res.errorCount;
                        $wrap.waiting(false);
                        $('#download_error_info').toggle(!!failure).attr("href", url);
                        $('#download_error_tip').toggle(!!failure);
                        $("#batch_result_wrap").show();
                        $importwrap.hide();
                        $("#import_success").text(success);
                        $("#import_failure").text(failure);
                    } else {
                        var $falureWrap = $("#batch_falure_wrap"),
                            $tip = $falureWrap.find(".info-wrap"),
                            $link = $falureWrap.find(".website-address");
                        $wrap.waiting(false);
                        $importwrap.hide();
                        $tip.text(res.msg);
                        $link.attr("href", res.url).text(res.url);
                        $falureWrap.show();
                    }
                });
            } else {
                Ui.tip(Ibos.l("ORG.SELECT_IMPORT_FILE"), "warning");
            }
        },
        // 查看上下级关系
        "checkRelationship": function(param, elem) {
            var url = Ibos.app.url('dashboard/user/relation');
            var dialog = Ui.dialog({
                title: Ibos.l("ORG.VIEW_SUBORDINATE_RELATIONSHIP"),
                id: "r_dialog",
                padding: 0,
                lock: true,
                width: "560px"
            });
            $.get(url)
                .done(function(res) {
                    dialog.content(res.html);
                });
        },
        "updateUserInfo": function(param, elem) {
            var uid = U.getCheckedValue("user"),
                deptid = $("[name=deptid]"),
                posid = $("[name=posid]"),
                type = $("[name=type]");

            uid = uid.split(",").map(function(val) {
                return "u_" + val;
            });

            var dialog = Ui.dialog({
                title: "修改用户信息",
                id: "update_dialog",
                padding: 0,
                margin: 0,
                lock: false,
                content: document.getElementById("update_userinfo_dialog"),
                ok: function() {
                    var url = type.val() === "dept" ? Ibos.app.url("dashboard/department/batchalteruserdept") : Ibos.app.url("dashboard/position/batchalteruserpos"),
                        param = {
                            member: uid,
                            id: type.val() === "dept" ? deptid.val() : posid.val()
                        };

                    $.post(url, param, function(res) {
                        if (res.isSuccess) {
                            Ui.tip("操作设置成功");
                        } else {
                            Ui.tip(res.msg, "warning");
                        }
                        window.location.reload();
                    }, "json");
                }
            });
        }
    });


    var ztreeOpt = {
        "addDiyDom": function(treeId, treeNode) {
            var aObj = $("#" + treeNode.tId + "_a");
            var optBtn = "<span class='utree-opt-wrap'>" +
                "<a href='" + Ibos.app.url('dashboard/department/edit', { 'op': 'get', 'id': treeNode.deptid }) + "' title='" + Ibos.l("ORG.EDIT_DEPARTMENT_INFO") + "' class='o-org-ztree-edit opt-btn opt-edit-btn'></a>" +
                "<a href='javascript:;' title='" + Ibos.l("ORG.DELETE_DEPARTMENT_TIP") + "' class='o-org-ztree-del opt-btn opt-del-btn' data-action='delZtreeNode' data-deptname='" + treeNode.deptname + "' id='" + treeNode.deptid + "'></a>" +
                "</span>";

            aObj.append(optBtn);

            //绑定删除节点操作
            $("#utree").on("click", ".opt-del-btn", function(evt) {
                var $tree = $("#utree"),
                    treeObj = $.fn.zTree.getZTreeObj("utree"),
                    $this = $(this),
                    id = $.attr(this, "id"),
                    name = $this.attr("data-deptname");
                Ui.confirm(Ibos.l("ORG.SURE_DELETE_DEPARTMENT", { name: name }), function() {
                    var node = treeObj.getNodesByParamFuzzy("id", id, null),
                        param = { id: id },
                        url = Ibos.app.url('dashboard/department/del');
                    $tree.waiting(null, 'mini', 'normal');
                    $.post(url, param, function(res) {
                        if (res.isSuccess) {
                            treeObj.removeNode(node[0]);
                            Ui.tip(res.msg);
                            $tree.waiting(false);
                        } else {
                            Ui.tip(res.msg, "danger");
                            $tree.waiting(false);
                        }
                    });
                });
                evt.stopPropagation();
            });

            //阻止点击编辑跳转时的冒泡事件
            $("#utree").on("click", ".opt-edit-btn", function(evt) {
                evt.stopPropagation();
            });
        },
        "zTreeOnDrop": function(event, treeId, treeNodes, targetNode, moveType) {
            var node = treeNodes[0],
                tid = node.tId,
                index = $("#" + tid).index(),
                id = node.id,
                pid;
            if (moveType == "inner") {
                pid = targetNode ? targetNode.id : 0;
            } else {
                pid = targetNode ? targetNode.pid : 0;
            }
            var param = { id: id, pid: pid, index: index },
                url = Ibos.app.url('dashboard/department/edit', { 'op': 'structure' });
            $.post(url, param, function(res) {
                if (res.isSuccess) {
                    Ui.tip(Ibos.l("OPERATION_SUCCESS"));
                } else {
                    Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
                    window.location.reload();
                }
            });
        },
        "nodeOnClick": function(event, treeId, treeNode) {
            var url = treeNode.url;
            window.location.href = url;
        },
        "getFontCss": function(treeId, treeNode) {
            return (!!treeNode.highlight) ? { "font-weight": "700" } : { "font-weight": "normal" };
        },
        "selectAuxiliaryNode": function(array) {
            var treeObj = $.fn.zTree.getZTreeObj("utree");
            for (var i = 0; i < array.length; i++) {
                var node = treeObj.getNodesByParam("id", array[i], null);
                if (node.length) {
                    node[0].highlight = true;
                    treeObj.updateNode(node[0]);
                }
            }
        }
    };

    // 初始化右栏树
    var settings = {
            data: {
                simpleData: { enable: true }
            },
            view: {
                showLine: false,
                selectedMulti: false,
                showIcon: false,
                addDiyDom: ztreeOpt.addDiyDom,
                fontCss: ztreeOpt.getFontCss
            },
            edit: {
                enable: true,
                drag: {
                    isCopy: false,
                    isMove: true
                }
            },
            callback: {
                onDrop: ztreeOpt.zTreeOnDrop,
                onClick: ztreeOpt.nodeOnClick
            }
        },
        $tree = $("#utree");
    $tree.waiting(null, 'mini');
    $.get(Ibos.app.url('dashboard/user/index', { 'op': 'tree' }), function(data) {
        var selectedDeptId = Ibos.app.g("selectedDeptId");
        $.fn.zTree.init($tree, settings, data);
        $tree.waiting(false);
        var treeObj = $.fn.zTree.getZTreeObj("utree");

        var auxiliaryId = Ibos.app.g("auxiliaryId");
        ztreeOpt.selectAuxiliaryNode(auxiliaryId);

        // 有catid才初始化选中
        if (selectedDeptId && selectedDeptId > 0) {
            var treeObj = $.fn.zTree.getZTreeObj("utree");
            var node = treeObj.getNodeByParam("id", selectedDeptId, null);
            treeObj.selectNode(node);
        }
    }, 'json');

    /**
     * 编辑总公司，原来这里是可以编辑总公司的，为了更好管理，把这里的删除，只留下“全局设置”的
     */

    /**
     *  批量更新用户部门和岗位信息
     *
     */
    var deptAndposOrg = (function() {
        var valueManager = function(values) {
            // 必须为Array
            if (!$.isArray(values)) {
                values = [];
            }
            var _add = function(id, callback) {
                // 已存在Id时返回
                if ($.inArray(id, values) === -1) {
                    values.push(id);
                    if ($.isFunction(callback)) {
                        callback(id);
                    }
                }
            };
            var _remove = function(id, callback) {
                // 已存在Id时返回
                var index = $.inArray(id, values);
                if (index !== -1) {
                    values.splice(index, 1);
                    if ($.isFunction(callback)) {
                        callback(id);
                    }
                }
            };


            return {
                add: function(ids, callback) {
                    ids = $.isArray(ids) ? ids : [ids];
                    for (var i = 0; i < ids.length; i++) {
                        _add(ids[i], callback);
                    }
                },
                remove: function(ids, callback) {
                    ids = $.isArray(ids) ? ids : [ids];
                    for (var i = 0; i < ids.length; i++) {
                        _remove(ids[i], callback);
                    }
                },
                get: function() {
                    return values.join(",");
                }
            }
        };

        var init = function() {
            var $user_dept = $("#update_user_dept"),
                $user_pos = $("#update_user_pos"),
                deptid = $("[name=deptid]"),
                posid = $("[name=posid]"),
                type = $("[name=type]"),
                deptVal = valueManager(),
                posVal = valueManager();

            $user_dept.selectBox({
                data: Ibos.data && Ibos.data.get("department"),
                type: "department",
                noNav: true,
                showLong: true,
                values: [],
                maximumSelectionSize: 1
            });

            $user_pos.selectBox({
                data: Ibos.data && Ibos.data.get("position"),
                type: "position",
                noNav: true,
                showLong: true,
                values: [],
                maximumSelectionSize: 1
            });

            deptBox = $user_dept.data("selectBox");
            posBox = $user_pos.data("selectBox");

            // 监控selectBox
            $(deptBox).on("slbchange", function(evt, data) {
                if (data.checked) {
                    deptVal.add(data.id);
                } else {
                    deptVal.remove(data.id);
                }
                deptid.val(deptVal.get());
            });

            $(posBox).on("slbchange", function(evt, data) {
                if (data.checked) {
                    posVal.add(data.id);
                } else {
                    posVal.remove(data.id);
                }
                posid.val(posVal.get());
            });

            // 监控tab
            $(".dialog-form-header a").on("click", function() {
                var $this = $(this),
                    parentLi = $this.parent(),
                    tabType = $this.data("type");

                type.val(tabType);
                parentLi.siblings().removeClass('active');
                parentLi.addClass('active');

                if (tabType === "dept") {
                    deptBox.show();
                    posBox.hide();
                } else {
                    deptBox.hide();
                    posBox.show();
                }
            })
        }

        return {
            init: init
        }

    })();

    deptAndposOrg.init();
});
