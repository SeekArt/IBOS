(function(win, Ibos) {
    'use strict';

    var TableConfig, GFIELD, ListView;
    var ajaxApi, createTable, initTree, picAction, getFormInfo, setFormInfo, getTopD, getHighLightD, getBackD;

    TableConfig = {
        deferLoading: 0,
        ajax: {
            url: Ibos.app.url('article/data/index'),
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
        columns: []
    };

    GFIELD = {
        'input': ['subject', 'istop', 'ishighlight', 'topendtime', 'highlightstyle', 'highlightendtime', 'publishscope', 'type', 'attachmentid', 'picids', 'url'],
        'select': ['catid'],
        'checkbox': ['votestatus', 'commentstatus'],
        'editor': ['content']
    };

    ListView = /article\/default\/index/.test(U.getUrlParam().r);

    ajaxApi = {
        'toVerify': function(param) {
            var url = Ibos.app.url('article/verify/verify');
            return $.post(url, param, $.noop, 'json');
        },
        // 退回审核至发起人
        'reback': function(param) {
            var url = Ibos.app.url('article/verify/back');
            return $.post(url, param, $.noop, 'json');
        },
        // 审核人撤回对新闻的审核
        getBack: function(param) {
            var url = Ibos.app.url('article/verify/cancel');
            return $.post(url, param, $.noop, 'json');
        },
        // 发布撤回
        'pushBack': function(param) {
            var url = Ibos.app.url('article/publish/cancel');
            return $.post(url, param, $.noop, 'json');
        },
        'remindApprover': function(param) {
            var url = Ibos.app.url('article/publish/call');
            return $.post(url, param, $.noop, 'json');
        },
        'allRead': function(param) {
            var url = Ibos.app.url('article/default/read');
            return $.post(url, param, $.noop, 'json');
        },
        removeArticles: function(param) {
            var url = Ibos.app.url('article/default/delete');
            return $.post(url, param, $.noop, 'json');
        },
        'verifyArticles': function(param) {
            var url = Ibos.app.url('article/verify/verify');
            return $.post(url, param, $.noop, 'json');
        },
        'topArticles': function(param) {
            var url = Ibos.app.url('article/default/top');
            return $.post(url, param, $.noop, 'json');
        },
        'highlightArticles': function(param) {
            var url = Ibos.app.url('article/default/highlight');
            return $.post(url, param, $.noop, 'json');
        },
        'moveArticles': function(param) {
            var url = Ibos.app.url('article/default/move');
            return $.post(url, param, $.noop, 'json');
        },
        'getReader': function(param) {
            var url = Ibos.app.url('article/default/getreader');
            return $.post(url, param, $.noop, 'json');
        },
        'getTree': function(param) {
            var url = Ibos.app.url('article/category/index');
            return $.post(url, param, $.noop, 'json');
        },
        'cateApproval': function(param) {
            var url = Ibos.app.url('article/category/getcurapproval');
            return $.post(url, param, $.noop, 'json');
        },
        'getOption': function(param) {
            var url = Ibos.app.url('article/data/option');
            return $.post(url, param, $.noop, 'json');
        },
        'submitForm': function(param) {
            var url = Ibos.app.url('article/default/submit');
            return $.post(url, param, $.noop, 'json');
        },
        'getFormEdit': function(param) {
            var url = Ibos.app.url('article/data/edit');
            return $.post(url, param, $.noop, 'json');
        },
        'getFormShow': function(param) {
            var url = Ibos.app.url('article/data/show');
            return $.post(url, param, $.noop, 'json');
        },
        'getFlowLog': function(param) {
            var url = Ibos.app.url('article/verify/flowlog');
            return $.post(url, param, $.noop, 'json');
        },
        'getCommentView': function(param) {
            var url = Ibos.app.url('article/comment/getcommentview');
            return $.post(url, param, $.noop, 'json');
        },
        'getVoteView': function(param) {
            var url = Ibos.app.url('article/default/vote');
            return $.post(url, param, $.noop, 'json');
        },
        'getCount': function(param) {
            var url = Ibos.app.url('article/default/getcount');
            return $.post(url, param, $.noop, 'json');
        }
    };

    createTable = function(elem, options) {
        var $elem = $(elem),
            _config = {};

        _config.columns = options.columns;

        return $elem.DataTable($.extend({}, Ibos.settings.dataTable, TableConfig, _config));
    };

    initTree = function(elem) {
        var $tree = $(elem),
            treeSettings, treeMenu, selectedNode, treeObj, sideTreeCategory, cate, data;

        treeSettings = {
            data: {
                simpleData: {
                    enable: true
                }
            },
            view: {
                showLine: false,
                selectedMulti: false,
                showIcon: false
            },
            callback: {
                onClick: function(evt, treeid, node) {
                    var catid = node.catid;

                    Ibos.local.set('art_catid', catid);
                    // 路由判断是否列表页
                    if (ListView) {
                        try {
                            ArtTable.catid = catid;
                            ArtTable.search();
                        } catch (e) {}
                    } else {
                        window.location.href = Ibos.app.url('article/default/index');
                    }
                }
            }
        };

        treeMenu = [{
            name: "add",
            text: '<i class="o-menu-add"></i> ' + U.lang("NEW"),
            handler: function(treeNode, categoryMenu) {
                var aid = $("#approval_id").val();
                sideTreeCategory.add(treeNode, {
                    url: Ibos.app.url('article/category/add'),
                    success: function(node, tree) {
                        var tNode = tree.getNodeByParam("id", node.id);
                        tNode.aid = node.aid;
                        tNode.catid = node.id;
                        tree.updateNode(tNode);
                        Ui.tip(U.lang('TREEMENU.ADD_CATELOG_SUCCESS'));
                    },
                    error: function(res) {
                        Ui.tip(res.msg, 'warning');
                        return false;
                    }
                }, {
                    aid: aid
                });
                categoryMenu.menu.hide();
            }
        }, {
            name: "update",
            text: '<i class="o-menu-edit"></i> ' + U.lang("EDIT"),
            handler: function(treeNode, categoryMenu) {
                sideTreeCategory.update(treeNode, {
                    url: Ibos.app.url('article/category/edit'),
                    success: function(node, tree) {
                        Ui.tip(U.lang('TREEMENU.EDIT_CATELOG_SUCCESS'));
                    },
                    error: function(res) {
                        Ui.tip(res.msg, 'warning');
                        return false;
                    }
                });
                categoryMenu.menu.hide();
            }
        }, {
            name: "moveup",
            text: '<i class="o-menu-up"></i> ' + U.lang("MOVEUP"),
            handler: function(treeNode, categoryMenu) {
                sideTreeCategory.moveup(treeNode, {
                    url: Ibos.app.url('article/category/move'),
                    success: function() {
                        Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
                    },
                    error: function(res) {
                        Ui.tip(res.msg, 'danger');
                    }
                });
                categoryMenu.menu.hide();
            }
        }, {
            name: "movedown",
            text: '<i class="o-menu-down"></i> ' + U.lang("MOVEDOWN"),
            handler: function(treeNode, categoryMenu) {
                sideTreeCategory.movedown(treeNode, {
                    url: Ibos.app.url('article/category/move'),
                    success: function() {
                        Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
                    },
                    error: function(res) {
                        Ui.tip(res.msg, 'danger');
                    }
                });
                categoryMenu.menu.hide();
            }
        }, {
            name: "remove",
            text: '<i class="o-menu-trash"></i> ' + U.lang("DELETE"),
            handler: function(treeNode, categoryMenu) {
                var tree = categoryMenu.tree,
                    topTreeNode = tree.getNodesByParam("pid", "0");
                // 当只有一个顶级节点且当前要删除的是该节点时，不可删除 
                if (topTreeNode.length <= 1 && topTreeNode[0].id === treeNode.id) {
                    Ui.tip(U.lang("ART.LEAVE_AT_LEAST_A_CATEGORY"), "warning");
                    return false;
                }
                Ui.confirm(U.lang("ART.SURE_DEL_CATEGORY"), function() {
                    categoryMenu.$ctrl.hide().appendTo(document.body);
                    sideTreeCategory.remove(treeNode, {
                        url: Ibos.app.url('article/category/del'),
                        success: function() {
                            Ui.tip(U.lang('TREEMENU.DEL_CATELOG_SUCCESS'));
                        },
                        error: function(res) {
                            Ui.tip(res.msg, "danger");
                        }
                    });
                    categoryMenu.menu.hide();
                });
            }
        }];

        // 左侧分类树初始化
        $tree.waiting(null, "mini");
        ajaxApi.getTree().done(function(res) {
            $tree.waiting(false);

            if (res.isSuccess) {
                data = res.data;
                // 转义，防xss
                $.map(data, function(item) {
                    item.name = U.entity.unescape(item.name);
                });

                treeObj = $.fn.zTree.init($tree, treeSettings, data);
                sideTreeCategory = new SideTreeCategory(treeObj, {
                    tpl: "tpl_category_edit"
                });
                cate = new TreeCategoryMenu(treeObj, {
                    menu: treeMenu
                });

                if (ListView) {
                    selectedNode = treeObj.getNodeByParam('id', Ibos.local.get('art_catid'));
                    selectedNode && treeObj.selectNode(selectedNode);
                    Ibos.local.remove('art_catid');
                }
            } else {
                Ui.tip(res.msg, 'warning');
                return false;
            }
        });
    };

    picAction = {
        $picIds: $("#picids"),

        _itemPrefix: "pic_item_",

        _values: [],

        _getItem: function(id) {
            return $("#" + this._itemPrefix + id);
        },

        getValues: function() {
            return this.$picIds.val().split(",");
        },

        setValues: function(vals) {
            return this.$picIds.val(vals.join(","));
        },
        /**
         * 初始化图片内容
         * [initPicItem description]
         * @param  {Object} item 传入jquery对象节点
         * @param  {Object} data 传入JSON格式数据
         */
        initPicItem: function(item, data) {
            var $item = $(item),
                $checkbox = $('<label class="checkbox"><input type="checkbox" name="pic" value="' + data.aid + '"></label>'),
                $img = $('<img class="pull-left" width="100" src="' + data.url + '" />');

            $item.find("i").replaceWith($img);
            $item.data('fileInfo', data);
            $item.prepend($checkbox).find(".o-trash").attr("data-id", data.aid);

            $checkbox.find('input[type="checkbox"]').label();

            $item.attr("id", this._itemPrefix + data.aid);
        },
        /**
         * 删除选中的图片
         * @method removeSelect
         * @param  {Array} ids 传入删除的ids数组
         */
        removeSelect: function(ids) {
            var vals = this.getValues();

            if (!vals || !vals.length) {
                return;
            }

            if (!$.isArray(ids)) {
                ids = [ids];
            }

            for (var i = 0; i < ids.length; i++) {
                var index = $.inArray(ids[i], vals);
                if (index !== -1) {
                    this._getItem(vals[index]).remove();
                    vals.splice(index, 1);
                }
            }

            this.setValues(vals);
        },
        /**
         * 图片向上移动
         * @method moveUp
         * @param  {Array} id 传入移动的id数组
         */
        moveUp: function(id) {
            var vals = this.getValues(),
                $item = this._getItem(id),
                index = $.inArray(id, vals),
                temp;
            if (index === -1) {
                return false;
            }
            // 当已为最上一项时， 移动到最后面
            if (index === 0) {
                $item.appendTo($item.parent());
                vals.push(vals.shift());
            } else {
                // 交换节点位置
                $item.insertBefore($item.prev());
                // 交换数组中的位置
                temp = vals[index];
                vals[index] = vals[index - 1];
                vals[index - 1] = temp;
            }

            this.setValues(vals);
        },
        /**
         * 图片向下移动
         * @method moveDown
         * @param  {Array} id 传入移动的id数组
         */
        moveDown: function(id) {
            var vals = this.getValues(),
                $item = this._getItem(id),
                index = $.inArray(id, vals),
                temp;

            if (index === -1) {
                return false;
            }
            // 当已为最下一项时， 移动到最前面
            if (index === vals.length - 1) {
                $item.prependTo($item.parent());
                vals.unshift(vals.pop());
            } else {
                // 交换节点位置
                $item.insertAfter($item.next());
                // 交换数组中的位置
                temp = vals[index];
                vals[index] = vals[index + 1];
                vals[index + 1] = temp;
            }

            this.setValues(vals);
        }
    };

    // 可选择拉取指定字段数据
    // 暂时弃用
    getFormInfo = function(fields) {
        var res = {},
            _method = {},
            self = this;

        $.each(['_getInput', '_getSelect', '_getCheckbox', '_getEditor'], function(i, v) {
            var type = v.match(/^_get([a-zA-Z]*)$/)[1].toLowerCase();

            _method[v] = function() {
                var value, res = {},
                    args = [].slice.call(arguments, 0);

                !args[0] && (args[0] = [].concat(GFIELD[type]));
                $.each(args[0], function(i, v) {
                    switch (type) {
                        case 'input':
                            value = $('input[name="' + v + '"]').val();
                            break;
                        case 'select':
                            value = $('select[name="' + v + '"]').val();
                            break;
                        case 'checkbox':
                            value = $('input[type="checkbox"][name="' + v + '"]').prop('checked') ? 1 : 0;
                            break;
                        case 'editor':
                            value = UE.getEditor('article_editor').getContent();
                            break;
                    }

                    ~$.inArray(v, args[1]) && (res[v] = value);
                });

                return res;
            }
        });

        $.each(GFIELD, function(i, v) {
            var _m = '_get' + i.charAt(0).toUpperCase() + i.slice(1);

            res = $.extend({}, res, _method[_m].call(null, fields, v));
        });

        return res;
    };

    // 和上面的getFormInfo相对应的方法
    // 暂时弃用
    // setFormInfo = function (data) {
    //     if (!data) {
    //         return false;
    //     }

    //     var value;
    //     $.each(GFIELD, function (type, items) {
    //         $.each(items, function (j, _input) {
    //             if (_input in data) {
    //                 value = data[_input];
    //                 switch (type) {
    //                     case 'input':
    //                         $('input[name="' + _input + '"]').val(value);
    //                         // istop/ishighlight 特殊处理
    //                         if (_input == 'istop') {
    //                             value == 1 ? $('.btn-top').addClass('active') : $('.btn-top').removeClass('active');
    //                         }

    //                         if (_input == 'ishighlight') {
    //                             value == 1 ? $('.btn-highlight').addClass('active') : $('.btn-highlight').removeClass('active');
    //                         }
    //                         // _input == 'publishscope' && $('input[name="'+_input+'"]').userSelect('setValue')
    //                         break;
    //                     case 'select':
    //                         $('select[name="' + _input + '"]').val(value).trigger('change');
    //                         break;
    //                     case 'checkbox':
    //                         $('input[data-toggle="switch"][name="' + _input + '"]').iSwitch(value ? 'turnOn' : 'turnOff');
    //                         if (_input == 'votestatus') {
    //                             value == 1 ? $('#vote').show() : $('#vote').hide();
    //                         }
    //                         break;
    //                     case 'editor':
    //                         $('script[name="content"]').text(value);
    //                         break;
    //                 }
    //             }
    //         });
    //     });

    //     return true;
    // };

    setFormInfo = function(data) {
        var name, value, $elem, tag;

        for (name in data) {
            value = data[name];

            if ($.isPlainObject(value)) {
                return setFormInfo(value);
            }

            $elem = $('[name="' + name + '"]');
            tag = $elem.get(0) ? $elem.get(0).tagName.toLowerCase() : '';

            if (!tag) {
                continue;
            }

            switch (tag) {
                case 'input':
                    $elem.val(value);
                    if (name == 'istop') {
                        value == 1 ? $('.btn-top').addClass('active') : $('.btn-top').removeClass('active');
                    }

                    if (name == 'ishighlight') {
                        value == 1 ? $('.btn-highlight').addClass('active') : $('.btn-highlight').removeClass('active');
                    }

                    if (name == 'commentstatus' || name == 'votestatus') {
                        $elem.iSwitch(value == '1' ? 'turnOn' : 'turnOff');
                    }
                    break;
                case 'select':
                    $elem.val(value).trigger('change');
                    break;
                case 'script':
                    $elem.text(value);
                    break;
            }
        }

        return true;
    };

    getTopD = function(callback) {
        var date = new Date(),
            time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();

        Ui.dialog({
            id: "d_art_top",
            title: U.lang("ART.SET_TOP"),
            content: $.tmpl('dialog_art_top', {
                date: time
            }).get(0),
            cancel: true,
            init: function() {
                $('#date_time_top').datepicker();
                $('input[name="totop"]').iSwitch().on('change', function() {
                    $('.top_mc').toggle(this.checked);
                });
            },
            ok: function() {
                return callback.call(null) !== false;
            }
        });
    };

    getHighLightD = function(callback) {
        var date = new Date(),
            time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();

        Ui.dialog({
            id: "d_art_highlight",
            title: U.lang("ART.HIGHLIGHT"),
            content: $.tmpl('dialog_art_highlight', {
                date: time
            }).get(0),
            cancel: true,
            init: function() {
                // highlightForm
                var hf = this.DOM.content.find("form")[0],
                    $sEditor = $("#simple_editor"),
                    se = new P.SimpleEditor($sEditor, { //初始化简易编辑器
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
                $('#date_time_highlight').datepicker();
                $('input[name="tohighlight"]').iSwitch().on('change', function() {
                    $('.highlight_mc').toggle(this.checked);
                });
            },
            ok: function() {
                return callback.call(null) !== false;
            }
        });
    };

    getBackD = function(callback) {
        Ui.dialog({
            id: "d_art_rollback",
            title: U.lang("ART.DOC_ROLLBACK"),
            content: $.tmpl('dialog_rollback_reason').get(0),
            cancel: true,
            ok: function() {
                return callback.call(null) !== false;
            }
        });
    };

    $(function() {
        Ibos.app.g('treeInit') == 1 && initTree('#c_tree');
    });

    win.Article = {
        ajaxApi: ajaxApi,
        GFIELD: GFIELD,
        createTable: createTable,
        initTree: initTree,
        picAction: picAction,
        getFormInfo: getFormInfo,
        setFormInfo: setFormInfo,
        getTopD: getTopD,
        getHighLightD: getHighLightD,
        getBackD: getBackD
    };

})(window, Ibos, undefined);