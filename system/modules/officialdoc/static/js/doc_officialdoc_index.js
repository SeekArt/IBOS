/**
 * Officialdoc/officialdoc/index
 */

var OfficialIndex = {
    op: {
        // 获取传送地址
        getUrl: Ibos.app.url('officialdoc/officialdoc/edit'),
        /**
         * 移动公文
         * @method moveDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        moveDoc: function(param) {
            var url = this.getUrl;
            param = $.extend({}, param, { op: "move" });
            return $.post(url, param, $.noop);
        },
        /**
         * 高亮公文
         * @method highlightDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        highlightDoc: function(param) {
            var url = this.getUrl;
            param = $.extend({}, param, { op: "highLight" });
            return $.post(url, param, $.noop);
        },
        /**
         * 顶置公文
         * @method topDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        topDoc: function(param) {
            var url = this.getUrl;
            param = $.extend({}, param, { op: "top" });
            return $.post(url, param, $.noop);
        },
        /**
         * 验证公文
         * @method verifyDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        verifyDoc: function(param) {
            var url = this.getUrl;
            param = $.extend({}, param, { op: "verify" });
            return $.post(url, param, $.noop);
        },
        /**
         * 回退公文
         * @method backDocs
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        backDocs: function(param) {
            var url = this.getUrl;
            param = $.extend({}, param, { op: "back" });
            return $.post(url, param, $.noop);
        }
    }
};

OfficialIndex.baseTable = (function() {
    return $('#officialdoc_table').DataTable($.extend({}, Ibos.settings.dataTable, {
        deferLoading: 0,
        ajax: {
            url: Ibos.app.url('officialdoc/officialdoc/getdoclist'),
            type: 'post',
            dataSrc: function(res) {
                if (res.isSuccess) {
                    return res.data;
                } else {
                    Ui.tip(res.msg, 'warning');
                    return [];
                }
            }
        },
        initComplete: function() {
            $(this).find('[data-name]').label();
        },
        rowCallback: function(row, data) {
            $(row).find("label input[type='checkbox']").label();
        },
        order: [],
        columns: [
            // 复选框
            {
                "data": "",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<label class="checkbox"><input type="checkbox" name="officialdoc[]" value="' + row.docid + '"/></label>';
                }
            },
            // 图标
            {
                "data": "type",
                "orderable": false,
                "render": function(data, type, row) {
                    var signStatus = row.signStatus,
                        readStatus = row.readStatus,
                        className = 'o-art-normal';

                    className += signStatus == 1 ? '-gray' : readStatus == 1 ? '-read' : '';

                    return '<i class="' + className + '"></i>';
                }
            },
            // 标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<a href="' + Ibos.app.url('officialdoc/officialdoc/show', { docid: row.docid }) + '" class="art-list-title" target="_self">' + row.subject + '</a>' + (row.istop == 1 ? '<span class="o-art-top"></span>' : '');
                }
            },
            // 最后修改
            {
                "data": "uptime",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="art-list-modify">' +
                        '<em>' + row.author + '</em>' +
                        '<span>' + row.uptime + '</span>' +
                        '</div>';
                }
            }, {
                "data": "clickcount",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span>' + row.clickcount + '</span>';
                }
            },
            // 操作
            {
                "data": "",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span class="art-clickcount">' + row.signNum + '</span>' +
                        '<div class="art-form-funbar">' +
                        (row.allowEdit ? '<a href="javascript:;" data-url="' + Ibos.app.url('officialdoc/officialdoc/edit', { docid: row.docid }) + '" title="编辑" target="_self" class="cbtn o-edit" data-action="editTip"></a>' : '') +
                        (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeDoc" data-id=' + row.docid + '></a>' : '') +
                        '</div>';
                }
            }
        ]
    }));
})();

OfficialIndex.approvalTable = (function() {
    return $('#approval_table').DataTable($.extend({}, Ibos.settings.dataTable, {
        deferLoading: 0, // 每个文件加上这一行
        ajax: {
            url: Ibos.app.url('officialdoc/officialdoc/getdoclist'),
            type: 'post',
            dataSrc: function(res) {
                if (res.isSuccess) {
                    return res.data;
                } else {
                    Ui.tip(res.msg, 'warning');
                    return [];
                }
            }
        },
        // --- Callback
        initComplete: function() {
            // Fixed: IE8下表格初始化后需要再次初始化 checkbox，否则触发不了change事件
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
                    return '<label class="checkbox"><input type="checkbox" name="approval[]" value="' + row.docid + '"/></label>';
                }
            },
            //标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    var tmpl = '<a href="' + Ibos.app.url('officialdoc/officialdoc/show', { docid: row.docid }) + '" class="art-list-title" target="_self">' + row.subject + '</a>';;
                    if (row.back == 1) {
                        tmpl += '<span class="noallow-tip">未通过</span>';
                    }
                    return tmpl;
                }
            },
            //审核流程
            {
                "data": "approval",
                "orderable": false,
                "render": function(data, type, row) {
                    var tmpl = '';
                    if (row.approval) {
                        tmpl = '<p class="fss mbm">' + row.approvalName + '</p><div class="art-flow-show clearfix">';
                        for (var i = 1, level = row['approval']['level']; i <= level; i++) {
                            if (row.stepNum >= i) {
                                //已审核过的步骤
                                tmpl += '<i data-toggle="tooltip" data-original-title="审核人:' + row['approval'][i]['approvaler'] + '" class="' + (i == level ? 'o-art-one-approval' : 'o-art-approval') + '"></i>';
                            } else {
                                //未审核的步骤
                                tmpl += '<i data-toggle="tooltip" data-original-title="审核人:' + row['approval'][i]['approvaler'] + '" class="' + (i == row.stepNum + 1 ? 'o-art-one-noapproval' : 'o-art-noapproval') + '"></i>';
                            }
                        }
                        tmpl += '</div>';
                    } else {
                        tmpl = '<div>无需审核，请编辑直接发布</div>';
                    }
                    return tmpl;
                }
            },
            //发布者
            {
                "data": "uptime",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="art-list-modify"><em>' + row.author + '</em><span>' + row.uptime + '</span></div>';
                }
            },
            //操作
            {
                "data": "viewctrl",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="art-form-funbar">' +
                        (row.allowEdit ? '<a href="javascript:;" data-url="' + Ibos.app.url('officialdoc/officialdoc/edit', { docid: row.docid }) + '" title="编辑" target="_self" class="cbtn o-edit" data-action="editTip"></a>' : '') +
                        (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeDoc" data-id=' + row.docid + '></a>' : '') +
                        '</div>';
                }
            }
        ]
    }));
})();

Ibos.local.remove("catid");
OfficialIndex.tableConfig = {
    curModule: 'baseTable', // ['baseTable', 'approvalTable']
    curType: 'all', // ['all', 'nosign', 'sign', 'notallow', 'draft']
    catid: Ibos.local.get('catid') || Ibos.app.g("catId"),
    draw: function(bool){
        var table = OfficialIndex[this.curModule];
        table.draw(bool);
    },
    search: function(val) {
        var table = OfficialIndex[this.curModule],
            param = {
                type: this.curType,
                catid: this.catid
            };
        table.ajax.url(Ibos.app.url('officialdoc/officialdoc/getdoclist', param));
        table.search(val).draw();
    },
    ajaxSearch: function(param) {
        var table = OfficialIndex[this.curModule];
        param = $.extend({
                type: this.curType,
                catid: this.catid
            }, param);

        table.ajax.url(Ibos.app.url('officialdoc/officialdoc/getdoclist', param)).load();
    }
};

$(function() {
    OfficialIndex.tableConfig.ajaxSearch();

    var tableConfig = OfficialIndex.tableConfig,
        doc_base = $('#doc_base'),
        doc_approval = $('#doc_approval');

    // 选中一条或多条公文时，出现操作菜单
    $(document).on("change", 'input[type="checkbox"][name="officialdoc[]"]', function() {
        var $opBtn = $('#doc_more'),
            hasSelected = !!U.getChecked('officialdoc[]').length;
        $opBtn.toggle(hasSelected);
        setTimeout(function() {
            $opBtn.toggleClass("open", hasSelected);
        }, 0);
    });

    //高级搜索
    $("#mn_search ,#dn_search").search(function(val){
        tableConfig.search(val);
    }, function() {
        Ui.dialog({
            id: "d_advance_search",
            title: Ibos.l("ADVANCED_SETTING"),
            content: document.getElementById("mn_search_advance"),
            cancel: true,
            init: function() {
                var form = this.DOM.content.find("form")[0];
                form && form.reset();
                // 初始化日期选择
                $("#date_start").datepicker({ target: $("#date_end") });
            },
            ok: function() {
                var form = this.DOM.content.find("form"),
                    param = form.serializeArray();

                tableConfig.ajaxSearch(U.serializedToObject(param));
            },
        });
    });


    Ibos.evt.add({
        "typeSelect": function(param, elem) {
            var $elem = $(elem),
                $parent = $elem.parent(),
                type = $elem.data('type');

            $parent.children().removeClass('active');
            $elem.addClass('active');

            if (tableConfig.curType === type) return true;
            if (type === 'notallow') {
                if (tableConfig.curModule !== 'approvalTable') {
                    tableConfig.curModule = 'approvalTable';
                    doc_base.stop(true, true).fadeOut(100, function(){
                        doc_approval.fadeIn();
                    });
                }
            } else {
                if (tableConfig.curModule === 'approvalTable') {
                    tableConfig.curModule = 'baseTable';
                    doc_approval.stop(true, true).fadeOut(100, function(){
                        doc_base.fadeIn();
                    });
                }
            }

            tableConfig.curType = type;
            tableConfig.ajaxSearch();
        },
        // 移动公文
        "moveDoc": function(param) {
            Ui.ajaxDialog(Ibos.app.url("officialdoc/officialdoc/move"), $.extend({},
                {
                    id: "d_doc_move",
                    title: Ibos.l("DOC.MOVETO"),
                    cancel: true,
                    ok: function() {
                        var catid = $('#articleCategory').val(),
                            docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                            param = { 'docids': docids, 'catid': catid };

                        OfficialIndex.op.moveDoc(param).done(function(res) {
                            if (res.isSuccess === true) {
                                Ui.tip(Ibos.l("CM.MOVE_SUCCEED"));
                                tableConfig.draw(false);
                            } else {
                                Ui.tip(Ibos.l("CM.MOVE_FAILED", 'danger'));
                            }
                        });
                    }
                },
            param));
        },
        // 高亮公文
        "highlightDoc": function() {
            Ui.dialog({
                id: "d_art_highlight",
                title: Ibos.l("DOC.HIGHLIGHT"),
                content: Dom.byId('dialog_art_highlight'),
                cancel: true,
                init: function() {
                    // highlightForm
                    var hf = this.DOM.content.find("form")[0],
                        $sEditor = $("#simple_editor");

                    // 防止重复初始化
                    if (!$sEditor.data('simple-editor')) {
                        //初始化简易编辑器
                        var se = new P.SimpleEditor($('#simple_editor'), {
                            onSetColor: function(hex) {
                                hf.highlight_color.value = hex;
                            },
                            onSetBold: function(status) {
                                // 转换为数字类型
                                hf.highlight_bold.value = +status;
                            },
                            onSetItalic: function(status) {
                                hf.highlight_italic.value = +status;
                            },
                            onSetUnderline: function(status) {
                                hf.highlight_underline.value = +status;
                            }
                        });
                        $sEditor.data('simple-editor', se);
                    }

                    $("#date_time_highlight").datepicker();
                },
                ok: function() {
                    var hf = this.DOM.content.find("form")[0],
                        hlData = {
                            docids: U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                            highlightEndTime: hf.highlightEndTime.value,
                            color: hf.highlight_color.value,
                            bold: hf.highlight_bold.value,
                            italic: hf.highlight_italic.value,
                            underline: hf.highlight_underline.value
                        };

                    OfficialIndex.op.highlightDoc(hlData).done(function(res) {
                        if (res.isSuccess === true) {
                            Ui.tip(res.info);
                            tableConfig.draw(false);
                        } else {
                        	Ui.tip(res.msg, 'warning');
                        	return false;
                        }
                    });
                }
            });
        },
        // 置顶公文
        "topDoc": function() {
            Ui.dialog({
                id: "d_art_top",
                title: Ibos.l('DOC.SET_TOP'),
                content: Dom.byId('dialog_art_top'),
                cancel: true,
                init: function() {
                    $("#date_time_totop").datepicker();
                },
                ok: function() {
                    // topform
                    var tf = this.DOM.content.find("form")[0],
                        param = {
                            'docids': U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                            'topEndTime': tf.topEndTime.value
                        };
                    OfficialIndex.op.topDoc(param).done(function(res) {
                        if (res.isSuccess === true) {
                            Ui.tip(res.info);
                            tableConfig.draw(false);
                        }
                    });
                }
            });
        },
        // 删除一条公文
        "removeDoc": function(param, elem) {
            Ui.confirm(Ibos.l("DOC.SURE_DEL_DOC"), function() {
                Official.op.removeDocs($(elem).data('id')).done(function(res) {
                    if (res.isSuccess === true) {
                        Ui.tip(res.info);
                        tableConfig.draw(false);
                    }
                });
            });
        },
        // 删除多条公文
        "removeDocs": function() {
            var docids = U.getCheckedValue("officialdoc[]");

            Ui.confirm(Ibos.l("DOC.SURE_DEL_DOC"), function() {
                Official.op.removeDocs(docids).done(function(res) {
                    if (res.isSuccess === true) {
                        Ui.tip(res.info);
                        tableConfig.draw(false);
                    }
                });
            });
        },
        // 审核公文
        "verifyDoc": function() {
            var docids = U.getCheckedValue("approval[]", $("#approval_table"));
            if (docids.length > 0) {
                var param = { docids: docids };

                OfficialIndex.op.verifyDoc(param).done(function(res) {
                    var hasTrue = res.isSuccess === true;
                    Ui.tip(res.msg, (hasTrue ? "" : "warning"));
                    hasTrue && window.location.reload();
                });
            } else {
                Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
            }
        },
        // 回退公文
        "backDocs": function() {
            var docids = U.getCheckedValue("approval[]", $("#approval_table"));
            if (docids.length > 0) {
                Ui.dialog({
                    id: "doc_rollback",
                    title: Ibos.l("DOC.DOC_ROLLBACK"),
                    content: document.getElementById("rollback_reason"),
                    cancel: true,
                    ok: function() {
                        var reason = $("#rollback_textarea").val(),
                            param = { docids: docids, reason: reason };

                        OfficialIndex.op.backDocs(param).done(function(res) {
                            var hasTrue = res.isSuccess === true;
                            Ui.tip(res.info, (hasTrue ? "" : "warning"));
                            hasTrue && tableConfig.draw(false);
                        });
                    }
                });
            } else {
                Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
            }
        },
        // 编辑前提示
        "editTip": function(params, elem) {
            Ui.confirm(Ibos.l("DOC.EDIT_AT_SURE"), function() {
                var url = $(elem).attr("data-url");
                location.href = url;
            });
        }
    });
});
