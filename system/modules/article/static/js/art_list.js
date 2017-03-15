(function(win, Ibos) {
    'use strict';

    var ArtTable, TCheck;
    var MODULE_INDEX = 'index',
        MODULE_VERIFY = 'verify',
        MODULE_PASSED = 'passed',
        MODULE_DRAFT = 'draft',
        MODULE_PUBLISH = 'publish',
        MODULE_REBACK_TO = 'reback_to',
        MODULE_APPROVAL = 'approval',
        TYPE_ALL = 'all',
        TYPE_UNREAD = 'unread',
        TYPE_READ = 'read',
        TYPE_WAIT = 'wait',
        TYPE_PASSED = 'passed',
        TYPE_REBACK_FROM = 'reback_from',
        TYPE_PUBLISH = 'publish',
        TYPE_DRAFT = 'draft',
        TYPE_REBACK_TO = 'reback_to',
        TYPE_APPROVAL = 'approval';

    var moreOperate, valSearch, btnSwitch, getCount;

    moreOperate = function(name) {
        $('.table.article-table').on("change", 'input[type="checkbox"]', function() {
            var $opBtn = $('#art_more'),
                hasSelected = !!U.getChecked(name).length;

            $opBtn.toggle(hasSelected);
        });
    };

    valSearch = function() {
        $("#art_search").search(function(val) {
            ArtTable.search(val);
        });
    };

    ArtTable = {
        curModule: Ibos.app.g('table'),
        curType: Ibos.app.g('type'),
        catid: Ibos.local.get('art_catid') || Ibos.app.g("catid"),
        search: function(val) {
            var table = ArtTable[this.curModule],
                param = {
                    type: this.curType,
                    catid: this.catid
                };

            val = val || '';
            table.ajax.url(Ibos.app.url('article/data/index', param));
            table.search(val).draw();

            return true;
        },
        draw: function(bool) {
            var table = ArtTable[this.curModule];
            table.draw(bool);
        }
    };

    btnSwitch = function() {
        var module = ArtTable.curModule,
            type = ArtTable.curType;

        $('#art_more').hide();
        switch (module) {
            case MODULE_INDEX:
                $('button[data-action="allRead"]').toggle(type != 'read');
                break;
            case MODULE_VERIFY:
            case MODULE_PASSED:
                $('.btn-toolbar .btn').hide();
                type == TYPE_WAIT && $('button[data-action="passArticles"]').show();
                type == TYPE_PASSED && $('button[data-action="getBack"]').show();
                break;
            case MODULE_DRAFT:
            case MODULE_PUBLISH:
            case MODULE_REBACK_TO:
                $('.btn-toolbar .btn').hide();
                $('button[data-action="removeArticles"]').show();
                break;
            case MODULE_APPROVAL:
                $('.btn-toolbar .btn').hide();
                $('button[data-action="remindApprovers"]').show();
                break;
        }
    };

    getCount = function() {
        var data, key;

        Article.ajaxApi.getCount().done(function(res) {
            if (res.isSuccess) {
                data = res.data;
                for (key in data) {
                    if (data[key] != '0') {
                        $('li[data-type="' + key + '"] a').append('<span class="bubble">' + data[key] + '</span>');
                    }
                }
            } else {
                Ui.tip(res.msg, 'warning');
                return false;
            }
        });
    };

    $(function() {
        // 目前更多操作只有在index控制器下有
        moreOperate('art_index[]');
        // 列表搜索
        valSearch();
        // 当前表操作勾选id
        TCheck = 'art_' + ArtTable.curModule + '[]';
        // 列表绘制
        ArtTable.curType = win.sessionStorage.getItem('view.article') || ArtTable.curType;
        win.sessionStorage.removeItem('view.article');
        ArtTable.curModule = $('[data-type="' + ArtTable.curType + '"]').addClass('active').data('module');
        $('[data-id="' + ArtTable.curModule + '"]').show();
        ArtTable.search();

        // 页面按钮切换
        btnSwitch();
        // 显示页面未读数
        getCount();

        Ibos.evt.add({
            // 移动新闻
            'moveArticles': function(param, elem) {
                var ids = U.getCheckedValue(TCheck);

                Article.ajaxApi.getOption().done(function(res) {
                    if (res.isSuccess) {
                        Ui.dialog({
                            id: "d_art_move",
                            title: U.lang("ART.MOVETO"),
                            content: $.tmpl('dialog_art_move', {
                                optionHtml: res.data
                            }).get(0),
                            cancel: true,
                            ok: function() {
                                var catid = $('select[name="articleCategory"]').val(),
                                    param = {
                                        'articleids': ids,
                                        'catid': catid
                                    };

                                Article.ajaxApi.moveArticles(param).done(function(res) {
                                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                                    ArtTable.search();
                                });
                            }
                        });
                    } else {
                        Ui.tip('无法获取分类数据，请重试', 'warning');
                        return false;
                    }
                });
            },
            // 置顶新闻
            'topArticles': function(param, elem) {
                var param;

                Article.getTopD(function() {
                    param = {
                        articleids: U.getCheckedValue(TCheck),
                        topEndTime: $('input[name="totop"]').prop('checked') ? $('input[name="topEndTime"]').val() : ''
                    }

                    Article.ajaxApi.topArticles(param).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 高亮新闻
            'highlightArticles': function(param, elem) {
                var param = {};

                Article.getHighLightD(function() {
                    param = {
                        articleids: U.getCheckedValue(TCheck),
                        highlightEndTime: $('input[name="tohighlight"]').prop('checked') ? $('input[name="highlightEndTime"]').val() : '',
                        highlight_bold: $('input[name="highlight_color"]').val(),
                        highlight_color: $('input[name="highlight_bold"]').val(),
                        highlight_italic: $('input[name="highlight_italic"]').val(),
                        highlight_underline: $('input[name="highlight_underline"]').val()
                    };

                    Article.ajaxApi.highlightArticles(param).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 移除新闻
            'removeArticle': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Ui.confirm(Ibos.l('ART.SURE_DEL_ARTICLE'), function() {
                    Article.ajaxApi.removeArticles({
                        'articleids': id
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 批量移除新闻
            'removeArticles': function(param, elem) {
                var ids = U.getCheckedValue(TCheck);
                if (!ids) {
                    Ui.tip('请勾选后操作', 'warning');
                    return false;
                }

                Ui.confirm(Ibos.l('ART.SURE_DEL_ARTICLE'), function() {
                    Article.ajaxApi.removeArticles({
                        'articleids': ids
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 编辑新闻
            'editArticle': function(param, elem) {
                Ui.confirm(U.lang("ART.EDIT_AT_SURE"), function() {
                    var id = $(elem).data('id');
                    location.href = Ibos.app.url('article/default/edit', {
                        'articleid': id
                    });
                });
            },
            // 列表分类切换
            'typeSelect': function(param, elem) {
                var $this = $(this),
                    $parent = $this.parent(),
                    module = $this.data('module'),
                    type = $this.data('type');

                $parent.children().removeClass('active');
                $this.addClass('active');
                $('.art-list').children().hide()
                    .end().find('[data-id="' + module + '"]').fadeIn(500);
                $('input[type="checkbox"][data-name="' + TCheck + '"]').label('uncheck');

                ArtTable.curModule = module;
                ArtTable.curType = type;
                ArtTable.search();
                btnSwitch();

                TCheck = 'art_' + ArtTable.curModule + '[]';
            },
            // 全部已读标记
            'allRead': function(param, elem) {
                Article.ajaxApi.allRead({
                    'articleid': 0
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                    ArtTable.search();
                });
            },
            // 催办
            'remindApprover': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Article.ajaxApi.remindApprover({
                    'articleids': id
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                    ArtTable.search();
                });
            },
            // 批量催办
            'remindApprovers': function(param, elem) {
                var ids = U.getCheckedValue(TCheck);
                if (!ids) {
                    Ui.tip('请勾选后操作', 'warning');
                    return false;
                }

                Article.ajaxApi.remindApprover({
                    'articleids': ids
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                    ArtTable.search();
                });
            },
            // 审核通过新闻
            'passArticle': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Article.ajaxApi.verifyArticles({
                    'articleids': id
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                    ArtTable.search();
                });
            },
            // 批量审核通过新闻
            'passArticles': function(param, elem) {
                var ids = U.getCheckedValue(TCheck);
                if (!ids) {
                    Ui.tip('请勾选后操作', 'warning');
                    return true;
                }

                Article.ajaxApi.verifyArticles({
                    'articleids': ids
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                    ArtTable.search();
                });
            },
            // 审核撤回
            'getBack': function(param, elem) {
                var ids = U.getCheckedValue(TCheck);
                if (!ids) {
                    Ui.tip('请勾选后操作', 'warning');
                    return false;
                }

                Ui.confirm(Ibos.l('ART.BACK_AND_VERIFY_AGAIN'), function() {
                    Article.ajaxApi.getBack({
                        'articleids': ids
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 退回审核
            'reback': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id'),
                    value;

                Article.getBackD(function() {
                    value = $('textarea[name="backreason"]').val();
                    if (!value) {
                        Ui.tip('退回理由不能为空', 'warning');
                        return false;
                    }

                    Article.ajaxApi.reback({
                        'articleids': id,
                        'reason': value
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            },
            // 撤回新闻发起
            'pushBack': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Ui.confirm(Ibos.l('ART.APPROVING_MAKE_SURE_REBACK'), function() {
                    Article.ajaxApi.pushBack({
                        'articleid': id
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        ArtTable.search();
                    });
                });
            }
        })
    });

    win.ArtTable = ArtTable;

})(window, Ibos, undefined);