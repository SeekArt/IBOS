/**
 * Organization/position/index
 */
$(document).ready(function() {
    (function() {
        function removePositions(id) {
            var $listTable = $("#org_position_table");
            $listTable.waiting(null, 'mini');
            $.post(Ibos.app.url('dashboard/position/del'), { id: id }, function(data) {
                $listTable.waiting(false);
                if (data.isSuccess) {
                    posTable.draw();
                    Ui.tip(U.lang("DELETE_SUCCESS"));
                } else {
                    Ui.tip(U.lang("DELETE_FAILED"), 'danger');
                }
            }, 'json');
        }

        Ibos.evt.add({
            // 删除选中岗位
            'removePositions': function(param, elem) {
                var uid = U.getCheckedValue('positionid');
                if (uid.length > 0) {
                    Ui.confirm(U.lang("ORG.DELETE_POSITIONS_CONFIRM"), function() {
                        removePositions(uid);
                    });
                } else {
                    Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
                }
            },
            'addPosition': function(param, elem) {
                window.location.href = Ibos.app.url('dashboard/position/add', { catid: posTable.pos.catid });
            },
            // 删除单个岗位
            'removePosition': function(param, elem) {
                Ui.confirm(U.lang("ORG.DELETE_POSITIONS_CONFIRM"), function() {
                    removePositions($(elem).data('id'));
                });
            },
            // 添加分类
            "addType": function(param, elem) {
                var dialog = Ui.dialog({
                    title: Ibos.l("ORG.CREAT_CLASSIFICATION"),
                    id: "t_dialog",
                    padding: 0,
                    content: document.getElementById("add_type_dialog"),
                    ok: function() {
                        var name = $("#tpye_name").val(),
                            pid = $("#dep_pid option:selected").val(),
                            param = { name: name, pid: pid },
                            url = Ibos.app.url('dashboard/positionCategory/add');
                        if (name) {
                            var treeObj = $.fn.zTree.getZTreeObj("ptree"),
                                pnode = treeObj.getNodeByParam("id", pid, null);
                            $.post(url, param, function(res) {
                                if (res.isSuccess) {
                                    var node = res.data;
                                    treeObj.addNodes(pnode, node);

                                    // 重置父目录下拉框
                                    var cid = Ibos.app.g("catId");
                                    ztreeOpt.updateOptions(cid);

                                    Ui.tip(Ibos.l("ORG.ADD_CLASSIFICATION_SUCEESS"));
                                } else {
                                    Ui.tip(Ibos.l("ORG.ADD_CLASSIFICATION_FAILED"), "danger");
                                }
                            });
                        } else {
                            Ui.tip("请输入分类名称", "warning");
                            return false;
                        }
                    },
                    init: function() {
                        var cid = Ibos.app.g("catId");
                        $("#dep_pid").val(cid);
                        $("#tpye_name").val("");
                    }
                });
            }
        });
    })();

    function createCateOptions(selected) {
        var treeObj = $.fn.zTree.getZTreeObj("ptree"),
            treeNodes = treeObj.getNodes();
        var _createSpace = function(level) {
            var space = "";
            level = level || 0;
            for (var i = 0; i < level; i++) {
                space += "&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            return space;
        };

        var _join = function(nodes, tpl) {
            tpl = tpl || "";
            for (var i = 0; i < nodes.length; i++) {
                tpl += '<option value="' + nodes[i].id + '"' + (nodes[i].id == selected ? "selected" : "") + '>' +
                    _createSpace(nodes[i].level) + nodes[i].name +
                    '</option>';

                if (nodes[i].children && nodes[i].children.length) {
                    tpl = _join(nodes[i].children, tpl);
                }
            }
            return tpl;
        };
        return _join(treeNodes, "");
    }

    var ztreeOpt = {
        "addDiyDom": function(treeId, treeNode) {
            var aObj = $("#" + treeNode.tId + "_a");
            var optBtn = "<span class='ptree-opt-wrap'>" +
                "<a href='javascript:;' title='" + Ibos.l("ORG.EDIT_CLASSIFICATION_INFO") + "' class='o-org-ztree-edit opt-btn opt-edit-btn' data-id='" + treeNode.id + "'></a>" +
                "<a href='javascript:;' title='" + Ibos.l("ORG.DELET_DEPARTMENT_ZTREE") + "' class='o-org-ztree-del opt-btn opt-del-btn mlm' data-action='delZtreeNode' data-deptname='" + treeNode.name + "' id='" + treeNode.id + "'></a>" +
                "</span>";
            aObj.append(optBtn);
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
            var param = { catid: id, pid: pid, index: index },
                url = Ibos.app.url('dashboard/positionCategory/edit', { 'op': 'move' });
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

            posTable.pos.catid = U.getUrlParam(url).catid;
            posTable.pos.search();
        },
        "updateOptions": function(selected) {
            var options = createCateOptions(selected);
            $("#dep_pid option").not("#dep_pid_first").remove();
            $("#pid_select option").not('#pid_select_first').remove();
            $(options).insertAfter("#dep_pid_first");
            $(options).insertAfter("#pid_select_first");
        }
    };

    $("#ptree").on("click", ".opt-del-btn", function(evt) {
        var $tree = $("#ptree"),
            treeObj = $.fn.zTree.getZTreeObj("ptree"),
            $this = $(this),
            id = $.attr(this, "id"),
            name = $this.attr("data-deptname");
        Ui.confirm(Ibos.l("ORG.SURE_DELET_POSITION", { name: name }), function() {
            var node = treeObj.getNodesByParamFuzzy("id", id, null),
                param = { catid: id },
                url = Ibos.app.url('dashboard/positionCategory/delete');
            $tree.waiting(null, 'mini', 'normal');
            $.post(url, param, function(res) {
                if (res.isSuccess) {
                    treeObj.removeNode(node[0]);

                    // 重置父目录下拉框
                    ztreeOpt.updateOptions(id);

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

    $("#ptree").on("click", ".opt-edit-btn", function(evt) {
        var id = $(this).data("id");
        var dialog = Ui.dialog({
            title: Ibos.l("ORG.EDIT_CLASSIFICATION"),
            id: "p_dialog",
            padding: 0,
            content: document.getElementById("edit_type_dialog"),
            ok: function() {
                var name = $("#edit_tpye_name").val(),
                    pid = $("#pid_select option:selected").val(),
                    param = { name: name, pid: pid, catid: id },
                    url = Ibos.app.url('dashboard/positionCategory/edit');
                if (name) {
                    var treeObj = $.fn.zTree.getZTreeObj("ptree"),
                        node = treeObj.getNodeByParam("id", id, null);
                    $.post(url, param, function(res) {
                        if (res.isSuccess) {
                            if (node.pId != pid) {
                                var pnode = treeObj.getNodeByParam("id", pid, null);
                                node.pId = pid;
                                treeObj.moveNode(pnode, node, "parent");
                            }
                            node.name = name;
                            $.extend(node, res.data);
                            treeObj.updateNode(node);
                            // 重置父目录下拉框
                            ztreeOpt.updateOptions(id);

                            Ui.tip(Ibos.l("ORG.EDIT_CLASSIFICATION_SUCEESS"));
                        } else {
                            Ui.tip(Ibos.l("ORG.EDIT_CLASSIFICATION_FAILED"), "danger");
                        }
                    });
                } else {
                    Ui.tip("请输入分类名称", "warning");
                    return false;
                }
            },
            init: function() {
                var treeObj = $.fn.zTree.getZTreeObj("ptree"),
                    node = treeObj.getNodeByParam("id", id, null);
                $("#pid_select").val(node.pId);
                $("#edit_tpye_name").val(node.name);
            }
        });
        evt.stopPropagation();
    });

    var posTable = (function() {
        var table = $('#org_position_table').DataTable($.extend({}, Ibos.settings.dataTable, {
            deferLoading: 0,
            ajax: {
                url: Ibos.app.url('dashboard/position/getpositionlist'),
                type: 'post'
            },
            initComplete: function() {
                $(this).find('[data-name]').label();
            },
            rowCallback: function(row, data) {
                $(row).find("label input[type='checkbox']").label();
            },
            order: [],
            columns: [
                //复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="positionid" value="' + row.posid + '"/></label>';
                    }
                },
                //岗位名称
                {
                    "data": "posname",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<span class="fss">' + row.posname + '</span>';
                    }
                },
                //岗位分类
                {
                    "data": "catname",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<span class="fss">' + (row.catname) ? row.catname : '--' + '</span>';
                    }
                },
                //在职人数
                {
                    "data": "num",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<span class="fss">' + row.num + '</span>';
                    }
                },
                //操作
                {
                    "data": "operate",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<a href="' + Ibos.app.url('dashboard/position/edit', { id: row.posid }) + '" class="cbtn o-edit" title="编辑"></a>' +
                            ' <a href="javascript:;" data-action="removePosition" data-id="' + row.posid + '" class="cbtn o-trash mls" title="删除"></a>';
                    }
                }
            ]
        }));

        return table;
    })();

    posTable.draw();
    posTable.pos = {
        catid: 1,
        search: function() {
            var that = this,
                url = Ibos.app.url('dashboard/position/getpositionlist', { catid: that.catid });
            posTable.ajax.url(url).load();
        }
    }

    $('#mn_search').search(function(val) {
        posTable.search(val).draw();
    });

    // 树菜单

    // 左侧分类树初始化
    var settings = {
        data: {
            simpleData: { enable: true }
        },
        view: {
            showLine: false,
            selectedMulti: false,
            showIcon: false,
            addDiyDom: ztreeOpt.addDiyDom
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
    };

    var $tree = $("#ptree");

    $tree.waiting(null, 'mini');
    $.get(Ibos.app.url('dashboard/positioncategory/index'), function(data) {

        var treeObj = $.fn.zTree.init($tree, settings, data);
        $tree.waiting(false);

        // 有catid默认选中该树节点
        // if (Ibos.app.g('catId') && Ibos.app.g('catId') > 0) {
        //     var node = treeObj.getNodeByParam("id", Ibos.app.g('catId'), null);
        //     treeObj.selectNode(node);
        // }
    }, 'json');

});
