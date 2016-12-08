(function(doc, Ibos) {
    'use strict';
    /**
     * 选择框弹出窗的类
     * @class SelectBox
     * @uses $.fn.label    label插件           性能消耗点
     * @uses $.fn.zTree    zTree插件
     * @param  {Jquery}    $element            要初始化的宿主
     * @param  {Key-Value} options             配置
     * @param  {Array}     options.navSettings 导航栏配置
     * @param  {Array}     options.data        数据
     * @param  {Array}     options.values      默认值
     * @return {Object}                        SelectBox实例对象
     */
    var KEY = {
            BACKSPACE: 8,
            ENTER: 13
        },
        TypeMap = {
            c: "company",
            d: "department",
            p: "position",
            u: "user",
            r: "role"
        },
        COMPANY = 'c_0',
        ALL = 'all',
        USER = 'user';

    // 辅助函数
    var _queryMatcher, _diffArr, _publicArr, _fixEmptyArr, _getUnique, _mergeArr, _getOthersFromArr, _evtKill;

    /**
     * 中文拼音匹配搜索
     * @param  {[String]} str  [搜索字符]
     * @param  {[Object]} data [搜索对象(格式参照Ibos.data.get()返回)]
     * @return {[Array]}      [description]
     */
    _queryMatcher = function(str, data) {
        if (!str) return [];

        var userData = Ibos.data.filter(data, function(data) {
            var text = data.text;
            if (text && data.id) {
                // 岗位分类应排除在数据集中
                if (data.id.charAt(0) === 'f') {
                    return false;
                }

                text += "," + pinyinEngine.toPinyin(text, false, ",");
                return text.toUpperCase().indexOf(str.toUpperCase()) >= 0;
            }
            return false;
        }, 15);

        return Ibos.data.converToArray(userData);
    };

    /**
     * 数组差异比较
     * @param  {[Array]} src    [源数组]
     * @param  {[Array]} target [目标数组]
     * @return {[Object]}        [{ added: [], removed: [] }]
     */
    _diffArr = function(src, target) {
        if (!$.isArray(src) || !$.isArray(target)) {
            console.error('parameter error');
            return false;
        }

        var len = Math.max(src.length, target.length),
            key, _src = {},
            _target = {},
            _diffObj = {
                added: [],
                removed: []
            };

        for (; len--;) {
            src[len] && (_src[src[len]] = '');
            target[len] && (_target[target[len]] = '');
        }

        for (key in _src) {
            !(key in _target) && _diffObj.removed.push(key);
        }

        for (key in _target) {
            !(key in _src) && _diffObj.added.push(key);
        }

        return _diffObj;
    };

    /**
     * 数组取交集
     */
    _publicArr = function(src, target) {
        var len, _flip = {},
            item, _public = [];

        for (len = src.length; len--;) {
            (item = src[len]) && (_flip[item] = '');
        }

        for (len = target.length; len--;) {
            (item = target[len]) in _flip && _public.push(item);
        }

        return _public;
    };

    _fixEmptyArr = function(vals) {
        return (vals.length && vals.length > 0) ? vals : null;
    };

    _getUnique = function(vals) {
        return $.unique(vals);
    };

    _mergeArr = function(src, target) {
        if (!$.isArray(src)) {
            src = [src];
        }

        return _getUnique(src.concat(target));
    };

    _evtKill = function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
    };

    /**
     * 获取数组差值
     * @param  {[Array]} src    
     * @param  {[Array]} target
     * @return {[Array]} 返回src中target不存在的值集合
     */
    _getOthersFromArr = function(src, target) {
        return _diffArr(src, target).removed;
    };

    var SelectBox = function($element, options) {
        var types = ['all', 'user', 'department', 'position', 'role'];

        if (!Ibos || !Ibos.data) {
            $.error("(SelectBox): 未定义数据Ibos.data");
        }

        if (!(this instanceof SelectBox)) {
            return new SelectBox($element, options);
        }

        this.$el = $element;
        this.el = $element.get(0);
        this.options = $.extend(true, {}, SelectBox.defaults, options);

        this.values = [];
        this.trees = [];
        // 兼容数组或字符串
        // 仅允许单类型
        this.options.type = typeof this.options.type !== 'string' ? this.options.type.toString() : this.options.type;
        if (!~$.inArray(this.options.type, types)) {
            this.options.type = ALL;
        }

        this._init();
    };
    /**
     * SelectBox默认配置
     * @property defaults 
     * @type {Object}
     */
    SelectBox.defaults = {
        data: [],
        size: 2000,
        navSettings: [{
            // id: "select_box_role",
            icon: "select-box-lately",
            text: U.lang("US.LATELY"),
            data: function() {
                var _cookies, userData, self = this;

                _cookies = (this._cookie('lately.SelectBox') && this._cookie('lately.SelectBox').split(',')) || [];
                this.options.type === 'user' && (_cookies = $.grep(_cookies, function(v) {
                    return v.charAt(0) == 'u' && ~$.inArray(v, self.uids);
                }));
                userData = Ibos.data.includes(_cookies);

                return $.each(Ibos.data.converToArray(userData), function(i, v) {
                    delete v.pid;
                    v.iconSkin = TypeMap[v.id.charAt(0)];
                });
            },
            type: "lately"
        }, {
            // id: "select_box_department",
            icon: "select-box-department",
            text: U.lang("US.PER_DEPARTMENT"),
            data: function() {
                var userOnly = this.options.type === 'user';
                return Ibos.data.converToArray(Ibos.data.filter(this.depts, function(data) {
                    userOnly && (data.nocheck = true);
                    data.isParent = true;
                    data.iconSkin = TypeMap[data.id.charAt(0)];
                    return true;
                }));
            },
            type: "department"
        }, {
            // id: "select_box_position",
            icon: "select-box-position",
            text: U.lang("US.PER_POSITION"),
            data: function() {
                var userOnly = this.options.type === 'user';
                return Ibos.data.converToArray(Ibos.data.get('position', 'positioncategory', function(data) {
                    userOnly && (data.nocheck = true);
                    data.isParent = true;
                    data.iconSkin = 'position';
                    return true;
                })) || [];
            },
            type: "position"
        }],
        values: [],
        // 拓展-导航栏隐藏,自定义导航栏
        type: ALL //"user" "position" "department" "role"
    };
    /**
     * SelectBox语言包
     * @property lang
     * @type {Object}
     */

    SelectBox.zIndex = 7000;
    SelectBox.prototype = {
        constructor: SelectBox,
        /**
         * 初始化
         * @method _init
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _init: function() {
            var len, val, vals, max;

            max = this.options.maximumSelectionSize || Math.pow(2, 18);
            // 初始化限制过滤
            if (vals = $.trim(this.options.values).split(',')) {
                for (len = vals.length; len--;) {
                    (val = vals[len]) && this._getText(val) && this.values.length <= max && this.values.push(val);
                }

                delete this.options.values;
            }

            //缓存一份数据，用于恢复原有状态
            this.saveValue()
                ._createSelectBox();
        },
        /**
         * 创建选择窗
         * @method _createSelectBox
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _createSelectBox: function() {
            var selectBoxTpl,
                hasInit = this.$el.attr("data-init") === "1",
                titleConfig = {
                    'all': '请选择发布范围',
                    'user': '请选择相关人员',
                    'department': '请选择部门',
                    'position': '请选择岗位',
                    'role': '请选择角色'
                };

            if (!hasInit) {
                selectBoxTpl = [
                    '   <div class="select-box-modal"></div>',
                    '   <div class="select-box-content">',
                    '       <div class="select-box-header">',
                    '           <p class="select-box-title">' + titleConfig[this.options.type] + '</p>',
                    '           <i class="select-box-close"></i>',
                    '       </div>',
                    '       <div class="select-box-body">',
                    '           <div class="select-box-mainer">',
                    '               <div class="select-box-nav"></div>',
                    '               <div class="select-box-query">',
                    '                   <div class="search"><input type="text" placeholder="请输入姓名、部门或岗位，按回车进行搜索" id="us_search"/></div>',
                    '               </div>',
                    '               <div class="select-box-list scroll"></div>',
                    '           </div>',
                    '           <div class="select-box-res">',
                    '               <div class="select-box-ctrl">',
                    '                   <span class="ml">已选择：<em class="select-box-selected-num">0</em> 项</span>',
                    '                   <a href="javascript:;" class="select-box-clear pull-right mr">清空</a>',
                    '               </div>',
                    '               <div class="select-box-acheive scroll"></div>',
                    '           </div>',
                    '       </div>',
                    '       <div class="select-box-footer">',
                    '           <button class="btn">取消</button>',
                    '           <button class="btn btn-primary">确定</button>',
                    '       </div>',
                    '   </div>'
                ].join('');

                this.$el.addClass("select-box")
                    .attr("data-init", "1")
                    .append(selectBoxTpl)
                    .css("z-index", SelectBox.zIndex++)
                    .mousedown(function(evt) {
                        evt.stopPropagation();
                    });

                this._initSelectBox();
                this.hasInit = true;
            }

            return this;
        },

        /**
         * 初始化选择窗
         */
        _initSelectBox: function() {
            var navSettings = this.options.navSettings,
                type = this.options.type,
                i, len, setting;

            this.$header = this.$el.find('.select-box-header');
            this.$nav = this.$el.find('.select-box-nav');
            this.$query = this.$el.find('.select-box-query input');
            this.$mainer = this.$el.find('.select-box-mainer');
            this.$list = this.$mainer.find('.select-box-list');
            this.$res = this.$el.find('.select-box-res');
            this.$acheive = this.$res.find('.select-box-acheive');
            this.$num = this.$res.find('.select-box-selected-num');
            this.$footer = this.$el.find('.select-box-footer');

            if (USER == type || ALL == type) {
                this.createTreePath();
                for (i = 0, len = navSettings.length; i < len; i += 1) {
                    setting = navSettings[i];

                    this._createNav(setting);
                    this._createTree(setting.type, setting.data.call(this));
                }

                this._bindNavEvt()
                    ._setNav(0);
            } else {
                this.$nav.hide();
                this._createTree(type, this._dataSetting(type));
                this._setCheckedNodes();
            }

            this._bindSelectedEvt()
                ._initSelectedList()
                ._bindBtnEvt()
                ._query();
        },

        /**
         * 创建部门结构路径
         */
        createTreePath: function() {
            // 部门导航顶级为 deptid == 'c_0'
            var userOnly = this.options.type === 'user',
                _tree = function(id) {
                    // 节点已缓存时返回空对象
                    if (collectionDept['department'][id]) {
                        return {};
                    }

                    var ret;
                    if (id === COMPANY) {
                        return Ibos.data.getItem(id);
                    } else {
                        ret = Ibos.data.getItem(id);
                        return $.extend(true, {}, ret, _tree(ret['department'][id]['pid']));
                    }
                },
                collectionDept = {
                    department: {}
                },
                uids = [];

            if (userOnly) {
                // 因为数据量大时性能消耗严重，暂时不对部门做过滤
                $.each(this.options.data.user, function(i, v) {
                    uids.push(v.id);
                    // $.extend(true, collectionDept, _tree(v.deptid));
                });
                collectionDept = Ibos.data.get('department');
            } else {
                collectionDept = Ibos.data.get('department');
            }

            this.uids = _getUnique(uids);
            this.depts = collectionDept;
            return this;
        },

        /**
         * 创建导航模板
         */
        _createNav: function(setting) {
            var navTpl;

            navTpl = [
                '<li data-type="' + setting.type + '">',
                '    <a href="javascript:;">',
                '        <i class="' + setting.icon + '"></i>',
                '        <span>' + setting.text + '</span>',
                '    </a>',
                '</li>'
            ].join('');

            this.$nav.append(navTpl);
            return this;
        },

        /**
         * 导航切换
         */
        _setNav: function(idx) {
            var navs = this.$nav.find('li'),
                trees = this.$list.find('.ztree'),
                currentType;

            if (idx < 0 || idx > navs.length - 1) {
                idx = 0;
            }

            this.$query.val('');

            if (typeof idx == 'number') {
                currentType = navs.removeClass("active")
                    .eq(idx).addClass("active")
                    .attr('data-type');
                trees.hide()
                    .eq(idx).show();
                this.currentTree = $.fn.zTree.getZTreeObj(this.el.id + '_' + currentType + '_tree');
                // 切换导航时先取消所有勾选再正确勾选所有值
                // 确保数据的正确
                this._setCheckedNodes();
                // 保存当前导航
                this.nav = idx;
            } else {
                this._setNav(0);
            }

            return this;
        },

        _setCheckedNodes: function() {
            var _cacheValues;
            // @TODO: 差异化比较，减少勾选操作，但是在导航切换时要关联id
            _cacheValues = [].concat(this.values);
            this.setValue($.map(this.currentTree.getCheckedNodes(), function(v) {
                return v.id;
            }), false);
            this.setValue(_cacheValues, true);
            this.currentTree.cancelSelectedNode();
        },

        /**
         * 创建左侧树
         */
        _createTree: function(type, data) {
            if (!$.fn.zTree) {
                $.error("(SelectBox): 缺少zTree组件");
            }

            var treeClick, treeCheck, beforeExpand, beforeCheck;
            var $tree, treeSetting, treeid,
                self = this;

            treeClick = function(evt, treeid, node) {
                if (!node.nocheck) {
                    self._checkNode(node.id, !node.checked);
                }
            };

            treeCheck = function(evt, treeid, node) {
                // 排除不可选中的项
                if (!node.nocheck) {
                    node.checked ? self.addValue(node.id) : self.removeValue(node.id);
                    self.$num.text(self.values.length);
                }
            };

            beforeCheck = function(treeid, node) {
                var len, children = self.currentTree.getNodesByParamFuzzy('id', '', node);

                if (!node.nocheck) {
                    if (!node.checked && !~$.inArray(node.id, self.values) &&
                        self.options.maximumSelectionSize &&
                        self.options.maximumSelectionSize < self.values.length + 1) {
                        Ui.tip('已超过选择限制最大值，若要选择请先取消原有勾选', 'warning');
                        return false;
                    }

                    // 在勾选之前先对子集进行锁定或解锁
                    if (self.options.type == ALL) {
                        for (len = children.length; len--;) {
                            self.currentTree.setChkDisabled(children[len], !node.checked, false, false);
                        }
                    }

                    return true;
                }
            };

            // 用于处理自定义添加节点
            // 将部门和岗位用户的数据分开添加
            // type === 'user' || type === 'all'
            beforeExpand = (function() {
                return ~$.inArray(self.options.type, [ALL, USER]) ? function(treeid, node) {
                    var userData, relatedids = [],
                        parentCheck = node.checked,
                        chkDisabled = node.chkDisabled,
                        nodeid = node.id === COMPANY ? 'd_0' : node.id,
                        that = this;

                    if (!node.extendChild) {
                        // 获取关联人员
                        // 人员过滤
                        userData = Ibos.data.filter(Ibos.data.getRelated(nodeid), function(data) {
                            data.iconSkin = 'user';
                            data.id && relatedids.push(data.id);
                            (parentCheck || chkDisabled) && (data.chkDisabled = true);
                            return self.uids.length > 0 ? ~$.inArray(data.id, self.uids) : true;
                        });

                        that.getZTreeObj(treeid).addNodes(node, Ibos.data.converToArray(userData), true);
                        // 展开节点时将关联树节点都勾选上
                        // 获取公有数值并勾选
                        self.setValue(_publicArr(relatedids, self.values), true);
                        node.extendChild = true;
                    }

                    return true;
                } : function() {};
            })();

            treeid = this.el.id + '_' + type + '_tree';
            $tree = $('<ul id="' + treeid + '" class="ztree user-ztree"></ul>');
            treeSetting = {
                check: {
                    enable: true,
                    chkboxType: {
                        Y: '',
                        N: ''
                    }
                },
                data: {
                    key: {
                        name: 'text'
                    },
                    simpleData: {
                        enable: true,
                        pIdKey: 'pid'
                    }
                },
                callback: {
                    onClick: treeClick,
                    onCheck: treeCheck,
                    beforeExpand: beforeExpand,
                    beforeCheck: beforeCheck
                },
                view: {
                    showLine: false,
                    showIcon: true,
                    selectedMulti: true,
                    dblClickExpand: false
                }
            };

            $tree.appendTo(self.$list);
            this.currentTree = $.fn.zTree.init($tree, treeSetting, data);

            if (this.currentTree) {
                this.trees.push(treeid);
            }

            data = null;
            return $tree;
        },

        /**
         * 左侧树数据获取 for department/position/role
         */
        _dataSetting: function(type) {
            var userData, src;

            src = type === 'position' ? $.extend(this.options.data, Ibos.data.get('positioncategory')) : this.options.data;
            userData = Ibos.data.filter(src, function(data) {
                data.iconSkin = type;
                return true;
            });

            return Ibos.data.converToArray(userData);
        },

        /**
         * 侧栏树勾选操作
         */
        _checkNode: function(id, toCheck) {
            var treeObj, treeNode, _inside, self = this,
                hasValue = false,
                curTreeId = this.currentTree.setting.treeId;

            _inside = function(treeid) {
                treeObj = $.fn.zTree.getZTreeObj(treeid);
                treeNode = treeObj.getNodeByParam("id", id);
                if (treeNode !== null) {
                    treeObj.checkNode(treeNode, toCheck, false, true);
                    return true;
                }

                return false;
            };

            toCheck = typeof toCheck === 'undefined' ? true : toCheck;
            if (!id) {
                return false;
            }
            // 对type === 'all'时，要遍历所有树
            // 以当前树为优先，找不到节点的条件下再搜索其他树
            if (!(hasValue = _inside(curTreeId))) {
                $.each(_getOthersFromArr(this.trees, [curTreeId]), function(i, v) {
                    ~$.inArray(id, self.values) && _inside(v) && (hasValue = true);
                });
            }

            if (!hasValue) {
                toCheck ? this.addValue(id) : this.removeValue(id);
            }

            return true;
        },

        /**
         * 初始化已选列表
         */
        _initSelectedList: function() {
            var vals = this.values;

            this._createSelectedList(vals);
            return this;
        },

        /**
         * 创建已选列表模板
         */
        _createSelectedList: function(vals) {
            var len, data, tpl = [],
                userData = Ibos.data.converToArray(Ibos.data.includes(vals));

            for (len = userData.length; len--;) {
                data = userData[len];
                tpl.push([
                    '   <div class="select-box-selected-item" data-val=' + data.id + '>',
                    '       <i class="select-box-' + TypeMap[data.id.charAt(0)] + '-icon"></i>',
                    '       <span>' + data.text + '</span>',
                    '       <i class="select-box-item-close"></i>',
                    '   </div>'
                ].join(''));
            }

            this.$acheive.append(tpl);
            return this;
        },

        /**
         * 移除已选列表模板
         */
        _removeSelectedList: function(vals) {
            var $v, $items = this.$acheive.find('.select-box-selected-item');

            $items.each(function(i, v) {
                $v = $(v);
                if (~$.inArray($v.attr('data-val'), vals)) {
                    $v.remove();
                }
            });

            return this;
        },

        /**
         * 添加值
         */
        addValue: function(val) {
            var idx = $.inArray(val, this.values);
            if (!~idx) {
                this._createSelectedList([val])
                    .values.push(val);
            }
            return this;
        },

        /**
         * 移除值
         */
        removeValue: function(val) {
            var idx = $.inArray(val, this.values);
            if (~idx) {
                this._removeSelectedList([val])
                    .values.splice(idx, 1);
            }
            return this;
        },

        /**
         * 多值设置
         * 提供给外部修改内部值的一个方法
         */
        setValue: function(vals, checked) {
            if (!$.isArray(vals)) {
                vals = [vals];
            }

            var len;
            for (len = vals.length; len--;) {
                this._checkNode(vals[len], checked);
            }

            return this;
        },

        saveValue: function() {
            var _cookie = (this._cookie('lately.SelectBox') && this._cookie('lately.SelectBox').split(',')) || [];
            this._previous = [].concat(this.values);
            this._cookie('lately.SelectBox', _mergeArr(_cookie, this.values).join(','));
            return this;
        },

        /**
         * 清空所有值
         */
        clearAll: function() {
            this.setValue([].concat(this.values), false);
            return this;
        },

        _query: function() {
            var self = this,
                results, $this, $tree = $(''),
                $trees = this.$list.find('.ztree');

            // 对输入进行监听
            this.$query.on('keyup', function(evt) {
                evt.stopPropagation();
                $this = $(this);

                if (evt.which === KEY.BACKSPACE && $this.val().length < 1) {
                    $tree.remove();

                    if (self.$nav.is(':hidden')) {
                        $trees.eq(0).show();
                        self.currentTree = $.fn.zTree.getZTreeObj(self.el.id + '_' + self.options.type + '_tree');
                        self._setCheckedNodes();
                    } else {
                        self._setNav(self.nav);
                    }

                    return false;
                }

                if (evt.which === KEY.ENTER) {
                    $tree.remove();
                    $trees.hide();

                    results = _queryMatcher($this.val(), self.options.data);
                    $.each(results, function(i, v) {
                        delete v.pid;
                        !v.iconSkin && (v.iconSkin = TypeMap[v.id.charAt(0)]);
                    });

                    $tree = self._createTree('query', results);
                    self._setCheckedNodes();
                }

                return false;
            });
        },

        /**
         * 获取已选列表的当前值
         */
        getSelectedList: function() {
            return this.values;
        },

        /**
         * 获取最近的选择值
         */
        getLatelyList: function() {
            return this._cookie('lately.SelectBox') || [];
        },

        /**
         * 选人框按钮事件监听：取消、确定、关闭窗口(取消)
         */
        _bindBtnEvt: function() {
            var self = this;

            this.$header.on('click', 'i.select-box-close', function(evt) {
                _evtKill(evt);
                self.doCancel();
            });

            this.$res.on('click', 'a', function(evt) {
                _evtKill(evt);
                self.clearAll();
            });

            this.$footer.on('click', 'button', function(evt) {
                _evtKill(evt);
                $(this).index() === 0 ? self.doCancel() : self.doOk();
            });

            return this;
        },

        /**
         * 右侧已选列表的点击监听
         * 取消原有勾选状态
         */
        _bindSelectedEvt: function() {
            var $this, val, self = this;

            this._unBindSelectedEvt();
            this.$acheive.on('click.selectBox', 'i.select-box-item-close', function() {
                $this = $(this);
                val = $this.closest('.select-box-selected-item').attr('data-val');
                self._checkNode(val, false);
            });

            return this;
        },

        _unBindSelectedEvt: function() {
            this.$acheive.off('click.selectBox');
            return this;
        },

        _bindNavEvt: function() {
            var self = this;

            this._unBindNavEvt();
            this.$nav.on('click.nav.selectBox', 'li', function() {
                var index = $(this).index();
                self._setNav(index);
                return false;
            });

            return this;
        },

        _unBindNavEvt: function() {
            this.$nav.off('click.nav.selectBox');
            return this;
        },

        /**
         * 右侧已选列表
         * 不存在列表多次所有值更新的情况，所有暂时不需要这个事件
         */
        _bindScrollEvt: function() {
            var self = this,
                selectedList = this.$acheive.get(0);

            this._unBindScrollEvt();
            this.$acheive.on('scroll.selectBox', function() {
                if ((selectedList.scrollHeight - selectedList.clientHeight - 600) <= selectedList.scrollTop) {
                    self._cacheListData();
                }
            });

            return this;
        },

        _unBindScrollEvt: function() {
            this.$acheive.off('scroll.selectBox');
            return this;
        },

        /**
         * cookie设置
         * 用于缓存最近联系人
         */
        _cookie: function(name, val) {
            if (!name) {
                return false;
            }

            if (name && !val) {
                return unescape(U.getCookie(name));
            }

            // 缓存周期为一个月
            U.setCookie(name, escape(val));
            return this;
        },

        _getText: function(val) {
            return Ibos.data.getText(val);
        },

        doOk: function() {
            var diffObj = _diffArr(this._previous, this.values);

            $(this).trigger('slbchange.selectBox', {
                added: diffObj.added,
                removed: diffObj.removed
            });
            this.saveValue().hide();
            return true;
        },

        /**
         * 取消操作
         * 恢复原有值，关闭弹窗
         */
        doCancel: function() {
            var diffObj = _diffArr(this._previous, this.values); // 数组差异化比较

            // 新增的值移除
            // 移除的值添加回去
            this.setValue(diffObj.added, false)
                .setValue(diffObj.removed, true)
                .hide();
            return true;
        },

        show: function(silent, callback) {
            this.$el.show();
            !silent && $(this).trigger('showbox.selectBox');
            callback && callback.call(this, this.$element);
        },

        hide: function(silent, callback) {
            this.$el.hide();
            !silent && $(this).trigger('hidebox.selectBox');
            callback && callback.call(this, this.$element);
        }
    };

    $.fn.selectBox = function(options) {
        var args = Array.prototype.slice.call(arguments, 1);

        return this.each(function() {
            var $element = $(this),
                data = $element.data("selectBox");

            if (!data) {
                $element.data("selectBox", data = new SelectBox($element, options));
            } else {
                typeof options === "string" && $.isFunction(data[options]) && data[options].apply(data, args);
            }
        });
    };

    /**
     * 用户选择
     * @class UserSelect
     * @uses $.fn.select2  select2插件
     * @uses PinyinEngine  pinyinEngine插件
     * @uses SelectBox     选择框弹窗的类
     * @param  {Jquery}    $element     选择框对应jq对象
     * @param  {Key-value} options      配置，具体参考Select2
     *     @param  {Jquery}    options.box      弹窗对应jq对象
     * @return {Object}                 UserSelect实例对象
     */
    var UserSelect = function($element, options) {
        if (!Ibos || !Ibos.data) {
            $.error("(UserSelect): 未定义数据Ibos.data");
        }

        if (!(this instanceof UserSelect)) {
            return new UserSelect($element, options);
        }

        this.$el = $element;
        this.el = $element.get(0);
        this.options = options;

        this.btns = [];
        this.values = [];
        this.data = this.options.data;

        this._init();
    };

    UserSelect.prototype = {
        constructor: UserSelect,

        /**
         * 用户选择器初始化
         */
        _init: function() {
            var initVal = this.$el.val(),
                max = this.options.maximumSelectionSize || Math.pow(2, 18),
                $box, len, val, vals, self = this;

            // 初始化限制过滤
            if (vals = $.trim(initVal).split(',')) {
                for (len = vals.length; len--;) {
                    (val = vals[len]) && this._getText(val) && this.values.length <= max && this.values.push(val);
                }

                this.$el.val(this.values.join(','));
            }

            // selectBox初始化准备
            if (!this.options.box || !this.options.box.length) {
                $box = $('#' + this.el.id + '_box');
                $box.length && $box.remove();

                this.options.box = $('<div id="' + this.el.id + '_box"></div>').appendTo(doc.body);
                // this.$el.data('box', this.options.box);
            }

            this._createSelect2();
            // 延迟加载
            setTimeout(function() {
                self._createSelectBox();
            }, 600);
            return this;
        },

        /**
         * 创建select2实例
         */
        _createSelect2: function() {
            var self = this,
                lang = U.lang;

            var formatResult, formatSelection, formatNoMatches, formatSelectionTooBig, initSelection, query, getPlaceholder;

            formatResult = function(data, $ct, query, _cache, oFragement) {
                //类别，c => company, u => user, d => department, p => position, r => role
                var type = TypeMap[data.id.charAt(0)],
                    tpl = "";

                if (_cache[type]) return data.text;

                if (!_cache.tip) {
                    // Tips
                    _cache.tip = 1;
                    tpl = '<li class="select2-tip">' + lang("US.INPUT_TIP") + '</li>';
                    oFragement.appendChild($(tpl).get(0));
                }

                tpl = '<li class="select2-' + type + '">' + lang('US.' + type.toUpperCase()) + '</li>';
                oFragement.appendChild($(tpl)[0]);

                _cache[type] = 1;
                return data.text;
            };

            formatSelection = function(data) {
                return '<i class="select2-icon-' + TypeMap[data.id.charAt(0)] + '"></i>' + data.text;
            };

            formatNoMatches = function() {
                return lang("US.NO_MATCH");
            };

            formatSelectionTooBig = function(limit) {
                return lang("US.SELECTION_TO_BIG", {
                    limit: limit
                });
            };

            initSelection = function(element, callback) {
                var val = (element.val() || element.context.value).split(","),
                    data = Ibos.data.includes(val);

                callback(Ibos.data.converToArray(data));
                data = null;
            };

            query = function(query) {
                var data = {
                    results: []
                };

                data.results = _queryMatcher(query.term, self.data);
                query.callback(data);
                data = null;
            };

            getPlaceholder = function(type) {
                var types = (function() {
                        var types = type || "";
                        if ($.isArray(types)) {
                            return types;
                        }
                        return types.split(",");
                    })(),
                    str = lang("US.PLACEHOLDER");

                type = types[0] ? (~$.inArray(ALL, types) ? ["all"] : types) : ["all"];

                if (type[0] === ALL) {
                    str = lang("US.PLACEHOLDER_ALL");
                } else {
                    for (var i = 0, len = type.length; i < len; i++) {
                        str += lang("US." + type[i].toUpperCase());
                        if (i != len - 1) {
                            str += "、";
                        }
                    }
                }

                return str;
            };

            this.$el.select2($.extend({
                width: "100%",
                formatResult: formatResult,
                formatSelection: formatSelection,
                formatSelectionTooBig: formatSelectionTooBig,
                formatNoMatches: formatNoMatches,
                initSelection: initSelection,
                placeholder: getPlaceholder(self.options.type),
                query: query,
                data: Ibos.data.converToArray(self.data)
            }, self.options)).on("change", function(e) {
                self.evtLinked('select2', e);
            });

            this.select = this.$el.data("select2");
            this._initOpBtn();

            return this;
        },

        /**
         * 创建selectBox实例
         */
        _createSelectBox: function() {
            var self = this,
                options = this.options;

            this.selectBox = new SelectBox(options.box, {
                data: self.data,
                values: [].concat(self.values),
                type: options.type,
                maximumSelectionSize: options.maximumSelectionSize
            });

            $(this.selectBox).on("slbchange.selectBox", function(evt, data) {
                self.evtLinked('selectBox', data);
            });

            $(this.selectBox).on('hidebox.selectBox', function() {
                self.select.selection.find('.operate-btn .glyphicon-user').click();
            });

            this.selectBox.hide(true);
            return this;
        },

        /**
         * 创建外部操作按钮
         */
        _createOperateBtn: function(options) {
            var self = this,
                defaults = {
                    cls: "operate-btn",
                    iconCls: "glyphicon-user"
                },
                opt = $.extend({}, defaults, options),
                $btn = $('<a href="javascript:;" class="' + opt.cls + '"><i class="' + opt.iconCls + '"></i></a>');

            if (typeof opt.handler === "function") {
                $btn.on("click", function(evt) {
                    _evtKill(evt);
                    opt.handler.call(self, $btn);
                });
                // 阻止冒泡触发下拉菜单
                $btn.on("mousedown", function(evt) {
                    _evtKill(evt);
                });
            }

            self.btns.push($btn);
            return $btn;
        },

        _createSelectOperate: function() {
            var $selection = this.select.selection,
                $operateWrap = $('<li class="select2-operate"></li>');
            return $operateWrap.prependTo($selection);
        },

        _initOpBtn: function() {
            var self = this,
                options = this.options,
                $operateWrap = this._createSelectOperate();

            // 弹出框按钮
            this._createOperateBtn({
                handler: function($btn) {
                    // 打开弹窗选择器时，关闭下拉列表
                    self.$el.select2("close");

                    if ($btn.hasClass("active")) {
                        self.selectBox && self.selectBox.hide(true);
                        $btn.removeClass("active");
                    } else {
                        self.selectBox && self.selectBox.show(true);
                        $btn.addClass("active");
                    }
                }
            }).appendTo($operateWrap);
            // 清空按钮
            options.clearable && this._createOperateBtn({
                cls: "operate-btn",
                iconCls: "glyphicon-trash",
                handler: function() {
                    self.clearAll();
                }
            }).appendTo($operateWrap);
            return this;
        },

        addValue: function(val) {
            var idx = $.inArray(val, this.values);
            if (!~idx) {
                this.values.push(val);
            }
            return this;
        },

        removeValue: function(val) {
            var idx = $.inArray(val, this.values);
            if (~idx) {
                this.values.splice(idx, 1);
            }
            return this;
        },

        setValue: function(vals, checked, silent) {
            if (!$.isArray(vals)) {
                vals = [vals];
            }

            var len, fn = checked ? this.addValue : this.removeValue;
            for (len = vals.length; len--;) {
                fn.call(this, vals[len]);
            }

            if (!silent) {
                this.evtLinked('outside', (checked ? {
                    added: vals
                } : {
                    removed: vals
                }), true);
            }

            return this;
        },

        getSelectedList: function() {
            return this.values;
        },

        clearAll: function() {
            this.setValue([].concat(this.values), false);
            return this;
        },

        /**
         * 负责实例之间的通信
         */
        evtLinked: function(src, data, silent) {
            var checkid;

            switch (src) {
                case 'selectBox':
                    this.setValue(data.added, true, true);
                    this.setValue(data.removed, false, true);
                    this.select.val(_fixEmptyArr(this.values));
                    break;
                case 'select2':
                    data.added = (data.added && [data.added.id]) || [];
                    data.removed = (data.removed && [data.removed.id]) || [];
                    checkid = data.added.length > 0 ? data.added : data.removed;
                    this.setValue(checkid, !!data.added.length, true) && this.selectBox.setValue(checkid, !!data.added.length).saveValue();
                    break;
                default:
                    checkid = data.added || data.removed;
                    this.select.val(_fixEmptyArr(this.values));
                    this.selectBox.setValue(checkid, !!data.added).saveValue();
                    break;
            }

            if (!silent) {
                this.$el.trigger('uschange.userSelect', data);
            }

            return this;
        },

        _getText: function(val) {
            return Ibos.data.getText(val);
        },

        /**
         * 启用选择窗
         */
        setEnabled: function() {
            this.select.enable();
            // 显示操作按钮
            this.btns[0].show();
            return this;
        },

        /**
         * 禁用选择窗
         */
        setDisabled: function() {
            this.select.disable();
            // 隐藏操作按钮
            this.btns[0].hide();
            return this;
        },
    };

    $.fn.userSelect = function(options) {
        if (!$ || !$.fn.select2) {
            throw new Error("($.fn.userSelect): 未定义 '$' 或 '$.fn.select2'");
        }

        var args = Array.prototype.slice.call(arguments, 1);

        return this.each(function() {
            var $el = $(this),
                data = $el.data("userSelect");

            if (!data) {
                $el.data("userSelect", data = new UserSelect($el, $.extend({}, $.fn.userSelect.defaults, options)));
            }

            typeof options === "string" && $.isFunction(data[options]) && data[options].apply(data, args);
        });
    };

    $.fn.userSelect.constructor = UserSelect;
    $.fn.userSelect.defaults = {
        data: {},
        multiple: true,
        clearable: true
    };
})(document, Ibos);