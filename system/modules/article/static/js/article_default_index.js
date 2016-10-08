/**
 * Article/default/index
 */

var ArticleIndex = {
    /**
     * 选中一条或多条新闻时，出现操作菜单
     * @method selectNewsShowMenu
     * @return {[type]} [description]
     */
    selectNewsShowMenu: function() {
        $(document).on("change", 'input[type="checkbox"][name="article[]"]', function() {
            var $opBtn = $('#art_more'),
                hasSelected = !!U.getChecked('article[]').length;
            $opBtn.toggle(hasSelected);
            setTimeout(function() {
                $opBtn.toggleClass("open", hasSelected);
            }, 0);
        });
    },
    /**
     * 高级搜索
     * @method highSearch
     */
    highSearch: function() {
        $("#mn_search, #an_search").search(function(val) {
            ArticleIndex.tableConfig.search(val);
        }, function() {
            Ui.dialog({
                id: "d_advance_search",
                title: U.lang("ADVANCED_SETTING"),
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

                    ArticleIndex.tableConfig.ajaxSearch(U.serializedToObject(param));
                },
            });
        });
    }
};

ArticleIndex.baseTable = (function() {
    return $('#article_table').DataTable($.extend({}, Ibos.settings.dataTable, {
        deferLoading: 0,
        ajax: {
            url: Ibos.app.url('article/default/getarticlelist'),
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
                    return '<label class="checkbox"><input type="checkbox" name="article[]" value="' + row.articleid + '"/></label>';
                }
            },
            // 图标
            {
                "data": "type",
                "orderable": false,
                "render": function(data, type, row) {
                    var votestatus = row.votestatus,
                        readStatus = row.readStatus,
                        type = row.type,
                        className;

                    className = type == 1 && votestatus == 0 ? 'o-art-pic' : votestatus == 1 ? 'o-art-vote' : 'o-art-normal';
                    if (readStatus == 1) className += '-gray';

                    return '<i class="' + className + '"></i>';
                }
            },
            // 标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<a href="' + Ibos.app.url('article/default/show', { articleid: row.articleid }) + '" class="art-list-title" target="_self">' + row.subject + '</a>' + (row.istop == 1 ? '<span class="o-art-top"></span>' : '');
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
            },
            // 操作
            {
                "data": "",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="art-list-clickcount">' + row.clickcount + '</div>' +
                        '<div class="art-list-funbar">' +
                        (row.allowEdit ? '<a href="javascript:;" data-url="' + Ibos.app.url('article/default/edit', { articleid: row.articleid }) + '" title="编辑" target="_self" class="cbtn o-edit" data-action="editTip"></a>' : '') +
                        (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeArticle" data-id=' + row.articleid + '></a>' : '') +
                        '</div>';
                }
            }
        ]
    }));
})();

ArticleIndex.approvalTable = (function() {
    return $('#approval_table').DataTable($.extend({}, Ibos.settings.dataTable, {
        deferLoading: 0, // 每个文件加上这一行
        ajax: {
            url: Ibos.app.url('article/default/getarticlelist'),
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
                    return '<label class="checkbox"><input type="checkbox" name="approval[]" value="' + row.articleid + '"/></label>';
                }
            },
            //标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    var tmpl = '<a href="' + Ibos.app.url('article/default/show', { articleid: row.articleid }) + '" class="art-list-title" target="_self">' + row.subject + '</a>';
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
                    var tmpl = '<div class="art-list-funbar">' +
                        '<a href="javascript:;" data-url="' + Ibos.app.url('article/default/edit', { articleid: row.articleid }) + '" title="编辑" target="_self" class="cbtn o-edit" data-action="editTip"></a>' +
                        '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeArticle" data-id="' + row.articleid + '"></a>' +
                        '</div>';
                    return tmpl;
                }
            }
        ]
    }));
})();
Ibos.local.remove('catid');
ArticleIndex.tableConfig = {
    curModule: 'baseTable', // ['baseTable', 'approvalTable']
    curType: 'done', // ['done', 'new', 'old', 'notallow', 'draft']
    catid: Ibos.local.get('catid') || Ibos.app.g("catId"),
    search: function(val) {
        var table = ArticleIndex[this.curModule],
            param = {
                type: this.curType,
                catid: this.catid
            };
        table.ajax.url(Ibos.app.url('article/default/getarticlelist', param));
        table.search(val).draw();
    },
    draw: function(bool) {
        var table = ArticleIndex[this.curModule];
        table.draw(bool);
    },
    ajaxSearch: function(param) {
        var table = ArticleIndex[this.curModule];
        param = $.extend({
            type: this.curType,
            catid: this.catid
        }, param);

        table.ajax.url(Ibos.app.url('article/default/getarticlelist', param)).load();
    }
};


$(function() {
    //选中一条或多条新闻时，出现操作菜单
    ArticleIndex.selectNewsShowMenu();
    //高级搜索
    ArticleIndex.highSearch();

    var tableConfig = ArticleIndex.tableConfig,
        article_base = $('#article_base'),
        article_approval = $('#article_approval');

    tableConfig.ajaxSearch();

    Ibos.evt.add({
        // 列表切换
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
                    article_base.stop(true, true).fadeOut(100, function(){
                        article_approval.fadeIn();
                    });
                }
            } else {
                if (tableConfig.curModule === 'approvalTable') {
                    tableConfig.curModule = 'baseTable';
                    article_approval.stop(true, true).fadeOut(100, function(){
                        article_base.fadeIn();
                    });
                }
            }

            tableConfig.curType = type;
            tableConfig.ajaxSearch();
        },
        // 移动新闻
        "moveArticle": function(param) {
            Ui.ajaxDialog(Ibos.app.url("article/default/move"), $.extend({}, {
                    id: "d_art_move",
                    title: U.lang("ART.MOVETO"),
                    cancel: true,
                    ok: function() {
                        var catid = $('#articleCategory').val(),
                            articleids = U.getCheckedValue("article[]", $("#article_table")),
                            param = { 'articleids': articleids, 'catid': catid };

                        Article.op.moveArticle(param).done(function(res) {
                            if (res.isSuccess === true) {
                                Ui.tip(U.lang("CM.MOVE_SUCCEED"));
                                tableConfig.draw(false);
                            } else {
                                Ui.tip(U.lang("CM.MOVE_FAILED"), 'warning');
                            }
                        });
                    }
                },
                param));
        },
        // 高亮新闻
        "highlightArticle": function() {
            Ui.dialog({
                id: "d_art_highlight",
                title: U.lang("ART.HIGHLIGHT"),
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
                        param = {
                            articleids: U.getCheckedValue("article[]", $("#article_table")),
                            highlightEndTime: hf.highlightEndTime.value,
                            highlight_color: hf.highlight_color.value,
                            highlight_bold: hf.highlight_bold.value,
                            highlight_italic: hf.highlight_italic.value,
                            highlight_underline: hf.highlight_underline.value
                        };

                    Article.op.highLight(param).done(function(res) {
                        res.isSuccess && tableConfig.draw(false);
                        Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                    });
                }
            });
        },
        // 置顶新闻
        "topArticle": function() {
            Ui.dialog({
                id: "d_art_top",
                title: U.lang('ART.SET_TOP'),
                content: Dom.byId('dialog_art_top'),
                cancel: true,
                init: function() {
                    $("#date_time_top").datepicker();
                },
                ok: function() {
                    // topform
                    var tf = this.DOM.content.find("form")[0],
                        param = {
                            'articleids': U.getCheckedValue("article[]", $("#article_table")),
                            'topEndTime': tf.topEndTime.value
                        };

                    Article.op.topArticle(param).done(function(res) {
                        res.isSuccess && tableConfig.draw(false);
                        Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                    });
                }
            });
        },
        // 删除一条新闻
        "removeArticle": function(param, elem) {
            Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
                Article.op.removeArticles($(elem).data('id')).done(function(res) {
                    res.isSuccess && tableConfig.draw(false);
                    Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                });
            });
        },
        // 删除多条新闻
        "removeArticles": function() {
            var aids = U.getCheckedValue("article[]");
            Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
                Article.op.removeArticles(aids).done(function(res) {
                    res.isSuccess && tableConfig.draw(false);
                    Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                });
            });
        },
        // 审核新闻
        "verifyArticle": function() {
            var articleids = U.getCheckedValue("approval[]", $("#approval_table")),
                param = { articleids: articleids };
            if (articleids.length > 0) {
                Article.op.verifyArticle(param).done(function(res) {
                    res.isSuccess && tableConfig.draw(false);
                    Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                });
            } else {
                Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
            }
        },
        // 退回新闻
        "backArticle": function() {
            var articleids = U.getCheckedValue("approval[]", $("#approval_table"));
            if (articleids.length > 0) {
                Ui.dialog({
                    id: "art_rollback",
                    title: L.ART.DOC_ROLLBACK,
                    content: document.getElementById("rollback_reason"),
                    cancel: true,
                    ok: function() {
                        var reason = $("#rollback_textarea").val(),
                            param = { articleids: articleids, reason: reason };
                        Article.op.backArticle(param).done(function(res) {
                            res.isSuccess && tableConfig.draw(false);
                            Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                        });
                    }
                });
            } else {
                Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
            }
        },
        // 编辑前提示
        "editTip": function(params, elem) {
            Ui.confirm(U.lang("ART.EDIT_AT_SURE"), function() {
                var url = $(elem).attr("data-url");
                location.href = url;
            });
        }
    });
});
