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
    var SelectBox = function($element, options) {
        if (!Ibos || !Ibos.data) {
            $.error("(SelectBox): 未定义数据Ibos.data");
        }
        var _this = this;
        this.$element = $element;
        this.options = $.extend(true, {}, SelectBox.defaults, options);

        this.hasInit = false;
        this.currentType = "";
        this.currentId = "";
        this.values = this.options.values;
        this.options.type = (function() {
            var types = _this.options.type;
            if ($.isArray(types)) {
                return types;
            }
            return types.split(",");
        })();
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
            // id: "select_box_department",
            icon: "select-box-department",
            text: U.lang("US.PER_DEPARTMENT"),
            data: function() {
                return Ibos.data.get("department") || {};
            },
            type: "department"
        }, {
            // id: "select_box_position",
            icon: "select-box-position",
            text: U.lang("US.PER_POSITION"),
            data: function() {
                return Ibos.data.get("position", "positioncategory") || {};
            },
            type: "position"
        }, {
            // id: "select_box_role",
            icon: "select-box-role",
            text: U.lang("US.PER_ROLE"),
            data: function() {
                return Ibos.data.get("role") || {};
            },
            type: "role"
        }],
        values: [],
        // 拓展-导航栏隐藏,自定义导航栏
        noNav: false,
        showLong: false,
        // 只可选用户
        // userOnly: false
        // maximumSelectionSize: 1, // 最大选项数只能在type 为 "user" 下使用
        type: "all" //"user" "position" "department"
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
            this._createSelectBox();
        },
        /**
         * 创建选择窗
         * @method _createSelectBox
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _createSelectBox: function() {
            var //$selectBox = $("#select_box"),
                hasInit = this.$element.attr("data-init") === "1",
                lang = SelectBox.lang,
                selectBoxTpl;
            if (!hasInit) {
                selectBoxTpl = [
                    '   <div class="select-box-header">',
                    '       <ul class="select-box-nav"></ul>',
                    '   </div>',
                    '   <div class="select-box-mainer">',
                    '       <div class="select-box-mainer-inner scroll">',
                    '           <div class="select-box-area">',
                    '               <div class="select-box-area-header">' + U.lang("US.SELECT_ALL"),
                    '                   <label class="checkbox pull-right"><span class="icon"></span><span class="icon-to-fade"></span><input type="checkbox" data-type="checkall"/></label>',
                    '               </div>',
                    '               <div class="select-box-area-mainer">',
                    '                   <ul class="select-box-list"></ul>',
                    '               </div>',
                    '           </div>',
                    '       </div>',
                    '       <div class="select-box-mainer-aside scroll"></div>',
                    '   </div>'
                ].join("");
                this.$element.addClass("select-box")
                    .attr("data-init", "1")
                    .append(selectBoxTpl)
                    .css("z-index", SelectBox.zIndex++)
                    .mousedown(function(evt) { evt.stopPropagation() });

                this._initSelectBox();
            }

            return this;
        },
        /**
         * 初始化选择窗
         * @method _initSelectBox
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _initSelectBox: function() {
            // @Todo: 让其可配置
            var settings = this.options.navSettings,
                types = this.options.type,
                // 有右侧栏
                userSelectable = ~$.inArray("all", types) || ~$.inArray("user", types),
                len = settings.length,
                typesLen = settings.length,
                i, j, setting, type;

            this.$header = this.$element.find(".select-box-header");
            this.$nav = this.$header.find(".select-box-nav");
            this.$mainer = this.$element.find(".select-box-mainer");
            this.$aside = this.$mainer.find(".select-box-mainer-aside");
            this.$inner = this.$mainer.find(".select-box-mainer-inner");
            this.$checkbox = this.$inner.find(".select-box-area-header input");
            this.$list = this.$inner.find(".select-box-list");

            for (i = 0; i < len; i++) {
                setting = settings[i];
                // 只输出对应选择方式的导航
                if (userSelectable) {
                    this._createNavItem(setting);
                    this._createTree(setting.type, setting.data());
                } else {
                    for (j = 0; j < typesLen; j++) {
                        type = types[j];
                        if (type === setting.type) {
                            this._createNavItem(setting);
                            this._createTree(setting.type, setting.data());
                        }
                    }
                }
            }

            // 当可选择用户时，需要绑定导航切换事件及列表刷新事件
            if (types[0]) {
                this._bindChangeEvent();
                this._bindNavEvent();
                this._bindScrollEvt();
            } else {
                // 否则，隐藏右侧栏
                this.$inner.hide();
            }
            this.setNav(0);
            // 隐藏导航栏
            this.options.noNav && this.$header.hide();
            if (this.options.showLong) {
                $(doc).off("mousedown.userselect.hideall", UserSelect.hideAllBox);
            }
        },
        /**
         * 绑定右侧复选框change时的事件
         * @method _bindChangeEvent
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _bindChangeEvent: function() {
            var that = this;
            this.$inner.on("change", "input[type='checkbox']", function(evt) {
                var results = [],
                    $checkbox = $(this),
                    type = $checkbox.attr("data-type"), //此属性用于判断此复选框是否全选
                    isChecked = $checkbox.prop("checked"),
                    val = $checkbox.val();

                isChecked ? that._toCheck($checkbox) : that._toUnCheck($checkbox);
                if (type && type === "checkall") {
                    results = that._toggleListCheckboxes(isChecked);
                } else {
                    if (isChecked) {
                        if (!that.options.maximumSelectionSize || that.values.length < that.options.maximumSelectionSize) {
                            that.addValue(val);
                        } else {
                            Ui.tip("已超过选择最大数，请先取消原有勾选", "warning");
                            that._toUnCheck($checkbox);
                        }
                    } else {
                        that.removeValue(val);
                    }
                    results.push(val);
                }
                $(that).trigger("slblistchange", { values: results, checked: isChecked });
                evt.stopPropagation();
            });
            return this;
        },

        /**
         * 修改复选框的状态, 全选或全取消选择
         * @_toggleListCheckboxes
         * @param  {String} toCheck true为选中， false为取消选中
         * @private
         * @return {Array}      发生改变的复选框值
         */
        _toggleListCheckboxes: function(toCheck) {
            var that = this,
                res = 0,
                len = this.values.length,
                max = that.options.maximumSelectionSize,
                $inputItem = this.$list.find('input'),
                values;

            var getListValue = function() {
                return $.map(that._listdatas, function(d) {
                    return d.id;
                });
            };

            // cancel refreshList in order to save dom_create time
            values = getListValue();
            if (toCheck) {
                this.addValue(values);
                if (!max) this._toCheck($inputItem);
                // if over the maximum, the rest will not be selected
                if (len <= max) this._toCheck($inputItem.slice(0, max - len));
            } else {
                this.removeValue(values);
                this._toUnCheck($inputItem);
            }

            return values;
        },
        /**
         * 绑定导航点击事件
         * @method _bindNavEvent
         * @private
         * @chainable
         * @return {Objec} 当前调用对象
         */
        _bindNavEvent: function() {
            var that = this;
            this._unBindNavEvent();
            this.$nav.on("click.userSelect.nav", "li", function() {
                var index = $(this).index();
                that.setNav(index);
                return false;
            });
            return this;
        },
        /**
         * 解绑导航点击事件
         * @method _unBindNavEvent
         * @private
         * @chainable
         * @return {Objec} 当前调用对象
         */
        _unBindNavEvent: function() {
            this.$nav.off("click.userSelect.nav");
            return this;
        },
        /**
         * 创建导航项
         * @method _createNavItem
         * @private
         * @chainable
         * @param  {Key-Value} setting 配置
         * @param  {String} settings.type 导航项的标识，相当于ID
         * @param  {String} settings.icon 图标样式类
         * @param  {String} settings.data 用于生成树的数据
         * @param  {String} settings.text 导航项的文本
         * @return {Objec}         当前调用对象
         */
        _createNavItem: function(setting) {
            // if (this.options.type === "all" || this.options.type === setting.type) {
            var tpl = '<li data-type="' + setting.type + '"><a href="javascript:;"><i class="' + setting.icon + '"></i><span>' + setting.text + '</span></a></li>';
            // }
            this.$nav.append(tpl);
            return this;
        },
        /**
         * 创建树
         * @method _createTree
         * @private
         * @chainable
         * @param  {String} type 与导航项对应的标识
         * @param  {Array}  data 用于生成树的数据
         * @return {Object}      当前调用对象
         */
        _createTree: function(type, data) {
            //@Debug
            if (!$.fn.zTree) {
                $.error("(UserSelect): 缺少zTree组件")
            }
            if (!data || !$.isArray(data)) {
                data = Ibos.data.converToArray(data);
            }
            var that = this,
                treeidPrefix = this.$element[0].id,
                treeClick = function(evt, treeid, node) {
                    var type = that.options.type,
                        userSelectable = (~$.inArray("user", type) || ~$.inArray("all", type));
                    // 当用户可选择时，点击树项为刷新右侧列表
                    if (userSelectable) {
                        that.currentId = node.id;
                        that.refreshList(node.id);
                        // 否则，为选中该项
                    } else {
                        // 排除不可选中的项
                        if (!node.nocheck) {
                            if (node.checked) {
                                that.removeValue(node.id);
                                node.checked = false;
                                // lastChecked = null;
                            } else {
                                // 当超过最大可选数时，将不再继续选择
                                if (that.options.maximumSelectionSize && that.options.maximumSelectionSize <= that.values.length) {
                                    Ui.tip("已超过选择最大数，请先取消原有勾选", "warning");
                                    return true;
                                }
                                that.addValue(node.id);
                                node.checked = true;
                            }
                        }
                    }
                },
                treeCheck = function(evt, treeid, node) {
                    // 排除不可选中的项
                    if (!node.nocheck) {
                        if (!node.checked) {
                            that.removeValue(node.id);
                        } else {
                            // 当超过最大可选数时，将不再继续选择
                            if (that.options.maximumSelectionSize && that.options.maximumSelectionSize <= that.values.length) {
                                node.checked = false;
                                Ui.tip("已超过选择最大数，请先取消原有勾选", "warning");
                                return true;
                            }
                            that.addValue(node.id);
                        }
                    }
                },
                treeSetting = {
                    check: {
                        enable: true,
                        chkboxType: {
                            "Y": "",
                            "N": ""
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
                        onCheck: treeCheck
                    },
                    view: {
                        showLine: false,
                        showIcon: false,
                        selectedMulti: false
                    }
                },
                $tree = $('<ul id="' + treeidPrefix + '_' + type + '_tree" class="ztree user-ztree"></ul>'),
                types = this.options.type;
            if (types[0] === "user" && types.length === 1) {
                treeSetting.check.enable = false;
            }
            this.$aside.append($tree);
            $.fn.zTree.init($tree, treeSetting, data);

            data = null;
            return this;
        },
        /**
         * 设置当前导航，相当于tab功能
         * @method setNav 
         * @param  {Number} index 导航项下标
         * @chainable
         * @return {Object} 当前调用对象
         */
        setNav: function(index) {
            var items = this.$nav.find("li"),
                trees = this.$aside.find(".ztree"),
                fixNumber = function() {
                    if (index > items.length - 1) {
                        index = 0;
                    } else if (index < 0) {
                        index += items.length;
                        fixNumber();
                    }
                },
                currentTree,
                currentZTreeObj,
                currentSelected,
                currentSelectedId;
            this.currentId = "";
            if (typeof index === "number") {
                fixNumber();
                this.currentType = items.removeClass("active").eq(index).addClass("active").attr("data-type");
                currentTree = trees.hide().eq(index).show();
                // 根据当前树选中的节点ID刷新列表
                currentZTreeObj = $.fn.zTree.getZTreeObj(currentTree[0].id);
                currentSelected = currentZTreeObj.getSelectedNodes()[0];
                currentSelectedId = (currentSelected && currentSelected.id) || "";
                this.refreshList(currentSelectedId);
                this.refreshCheckbox();
            } else {
                this.setNav(0);
            }
        },
        /**
         * 根据id刷新右侧列表
         * @method refreshList
         * @param  {String} id 目标id
         * @return {Object}    当前调用对象
         */
        refreshList: function(id) {
            id = id || this.currentId || "";
            this.clearList();
            this._initListItem(Ibos.data.getRelated(id));
            return this;
        },
        /**
         * 获取当前显示的树
         * @method _getCurrentTree 
         * @param  {String} type 导航标识
         * @return {Objec}       当前调用对象
         */
        _getCurrentTree: function(type) {
            type = type || this.currentType;
            var treeidPrefix = this.$element[0].id,
                treeid = treeidPrefix + "_" + type + "_tree",
                treeObj = $.fn.zTree.getZTreeObj(treeid);
            return treeObj || null;
        },
        /**
         * 根据ID选中或取消左侧项的选中
         * @method _checkNode
         * @private
         * @chainable
         * @param  {String} id       数据id
         * @param  {boolean} toCheck 是否选中
         * @return {Object}          当前调用对象
         */
        _checkNode: function(id, toCheck) {
            var type = this.currentType,
                values = this.values,
                treeObj = this._getCurrentTree(),
                treeNode;
            toCheck = typeof toCheck === 'undefined' ? true : toCheck;
            if (id) {
                treeNode = treeObj.getNodeByParam("id", id);
                if (treeNode !== null) {
                    treeObj.checkNode(treeNode, toCheck, false);
                }
            }
        },
        /**
         * 更新对应标识下，整体的checkbox状态
         * @method refreshCheckbox
         * @chainable 
         * @param  {String} [type] 导航项标识，不存在此参数时，设置为此前标识 
         * @return {Object}        当前调用对象
         */
        refreshCheckbox: function(type) {
            type = type || this.currentType;
            var values = this.values,
                treeObj = this._getCurrentTree(type);
            treeObj.checkAllNodes(false);
            for (var i = 0, len = values.length; i < len; i++) {
                this._checkNode(values[i], true);
            }
            // 统一触发更改
            $(this).trigger("slbchange", { id: values.slice(0), checked: true });
            return this;
        },
        dataFilter: function(datas) {
            var selfData = this.options.data,
                res = {},
                key, idx, items;

            for (key in datas) {
                items = selfData[key];
                if (items) {
                    res[key] = {};
                    for (idx in datas[key]) {
                        idx in items && (res[key][idx] = datas[key][idx]);
                    }
                }
            }

            selfData = null;
            datas = null;
            return res;
        },
        /**
         * 创建列表项
         * @method _initListItem
         * @private
         * @param  {Key-Value} data 列表项数据
         * @return {Jquery}      列表项jq对象
         */
        _initListItem: function(datas) {
            var that = this,
                values = this.values,
                max = this.options.maximumSelectionSize,
                listTpl = [];

            // 判断初始化传入的值是否超过最大值
            if (max && values.length > max) {
                values.splice(max);
            }

            datas = this.dataFilter(datas);
            if (!$.isArray(datas)) {
                datas = Ibos.data.converToArray(datas);
            }

            this._listdatas = datas.slice(0);
            this._cachedatas = datas.slice(0);
            this._cachedatas.length && this._createListTmpl(this._cachedatas.splice(0, this.options.size));

            datas = null;
            return this;
        },
        /**
         * 创建列表模板
         */
        _createListTmpl: function(datas) {
            var i, len, data, $list,
                values = this.values,
                checked,
                listTpl = [];

            for (i = 0, len = datas.length; i < len; i++) {
                data = datas[i];
                checked = $.inArray(data.id, values) < 0;
                listTpl.push([
                    '<li class="', (data.online === '1' ? "online" : "offline"), '">',
                    '<label class="checkbox ', (checked ? '' : 'checked'), '">',
                    '<span class="icon"></span><span class="icon-to-fade"></span>',
                    '<input type="checkbox" value="', data.id, '"', (checked ? '' : 'checked'), '/>',
                    '<img src="', data.avatar, '" />',
                    '<span>', data.text, '</span>',
                    '</label>',
                    '</li>'
                ].join(''));
            }

            $list = $(listTpl.join(''));

            this.$list.append($list);
            return this;
        },

        _toCheck: function($input) {
            var $label, $this;
            if ($input[0].tagName.toLowerCase() !== 'input') {
                $input = $input.find('input');
            }

            $input.each(function(i, e) {
                $this = $(this);
                $label = $this.closest('label');
                $label.addClass('checked');
                $this.prop('checked', true);

                $label = null;
            });

            $input = null;
            return true;
        },

        _toUnCheck: function($input) {
            var $label, $this;
            if ($input[0].tagName.toLowerCase() !== 'input') {
                $input = $input.find('input');
            }

            $input.each(function(i, e) {
                $this = $(this);
                $label = $this.closest('label');
                $label.removeClass('checked');
                $this.prop('checked', false);

                $label = null;
            });

            $input = null;
            return true;
        },
        /**
         * 按需加载
         */
        _cacheListData: function() {
            var datas = this._cachedatas;
            datas.length && this._createListTmpl(datas.splice(0, this.options.size));
        },
        /**
         * 滚动监听
         */
        _bindScrollEvt: function() {
            var that = this,
                innerTable = this.$inner[0];

            this.$inner.on('scroll', function(evt) {
                if ((innerTable.scrollHeight - innerTable.clientHeight - 600) <= innerTable.scrollTop) {
                    that._cacheListData();
                }
            });
        },
        /**
         * 清空列表
         * @method clearList
         * @chainable
         * @return {Object} 当前调用对象
         */
        clearList: function() {
            this.$list.empty();
            this._toUnCheck(this.$checkbox);
            return this;
        },
        /**
         * 修改当前选中值
         * @method setValue
         * @param  {String||Array} val 一个或一组有效值
         * @return {Array}     已选中的值
         */
        setValue: function(val) {
            this.values = $.isArray(val) ? val : [val];
            this.refreshList();
            this.refreshCheckbox();
            return this.values;
        },

        /**
         * 增加选中值
         * @method addValue
         * @param  {String||Array} val 一个或一组有效值
         * @return {Array}     已选中的值
         */
        addValue: function(val) {
            // 若传入数组，则循环迭代
            var that = this,
                res = [],
                // 当插入数组时禁止多次触发slbchange
                add_unit = function(value) {
                    // 如果 val 还未被增加 且 此前允许增加， 且推入values数组
                    if ($.inArray(value, that.values) === -1) {
                        // 验证是否超过选项最大长度，超过部分不处理
                        if (!that.options.maximumSelectionSize || that.values.length < that.options.maximumSelectionSize) {
                            that.values.push(value);
                            res.push(value);
                            that._checkNode(value, true);
                        } else {
                            return false;
                        }
                    }

                    return true;
                };

            if (!$.isArray(val)) { val = [val]; }
            AddValue:
                for (var i = 0, len = val.length; i < len; i++) {
                    if (!add_unit(val[i])) {
                        break AddValue;
                    }
                }

            $(this).trigger("slbchange", { id: res, checked: true });
            return this.values;
        },
        /**
         * 删除已选中值
         * @method removeValue
         * @param  {String||Array} val 一个或一组有效值
         * @return {Array}     已选中的值
         */
        removeValue: function(val) {
            var that = this,
                res = [],
                remove_unit = function(value) {
                    var index;
                    index = $.inArray(value, that.values);
                    if (index !== -1) {
                        that.values.splice(index, 1);
                        res.push(value);
                        that._checkNode(value, false);
                    }
                };
            // 若传入数组，则循环迭代
            if (!$.isArray(val)) { val = [val]; }
            RemoveValue:
                for (var i = 0, len = val.length; i < len; i++) {
                    remove_unit(val[i]);
                }

            $(this).trigger("slbchange", { id: res, checked: false });
            return this.values;
        },
        /**
         * 显示选人窗
         * @method show
         * @chainable
         * @param  {Function} callback 回调
         * @return {Object}            当前调用对象
         */
        show: function(callback) {
            this.$element.show();
            // this.refreshList();
            callback && callback.call(this, this.$element);
        },
        /**
         * 隐藏选人窗
         * @method hide
         * @chainable
         * @param  {Function} callback 回调
         * @return {Object}            当前调用对象
         */
        hide: function(callback) {
            this.$element.hide();
            callback && callback.call(this, this.$element);
        },
        /**
         * 执行options中定义的回调函数
         * @param  {String} name 函数名
         * @return {Object}      当前调用对象
         */
        _trigger: function(name /*,...*/ ) {
            var argu = Array.prototype.slice.call(arguments, 1);
            if (this.options[name] && typeof this.options[name] === "function") {
                this.options[name].apply(this, argu);
            }
            return this;
        }
    }

    $.fn.selectBox = function(options) {
        var argu = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var $el = $(this),
                data = $el.data("selectBox");
            if (!data) {
                $el.data("selectBox", data = new SelectBox($el, options))
            } else {
                if (typeof options === "string" && $.isFunction(data[options])) {
                    data[options].apply(data, argu)
                }
            }
        })
    }

    /**
     * 用户选择
     * @class UserSelect
     * @uses $.fn.select2  select2插件
     * @uses PinyinEngine  pinyinEngine插件
     * @uses SelectBox     选择框弹窗的类
     * @param  {Jquery}    $element     选择框对应jq对象
     * @param  {Key-value} options      配置，具体参考Select2
     *     @param  {Jquery}    options.box      弹窗对应jq对象
     *     @param  {Array}     options.contacts 常用联系人数组
     * @return {Object}                 UserSelect实例对象
     */
    var UserSelect = function($element, options) {
            // @Debug:
            if (!Ibos || !Ibos.data) {
                throw new Error("(SelectBox): 未找到全局数据Ibos.data")
            }
            var initialValue = $element.val(),
                max = options.maximumSelectionSize,
                i, values;

            this.$element = $element;
            if (!this.$element.get(0).id) {
                this.$element.attr("id", "userselect_" + U.uniqid());
            }

            this.options = options;
            if (!this.options.box || !this.options.box.length) {
                var $box = $("#" + $element[0].id + "_box");
                $box.length && $box.remove();
                this.options.box = $('<div id="' + $element[0].id + '_box"></div>').appendTo(doc.body);
            }

            this.btns = [];
            this.values = [];
            this.data = $.extend({}, this.options.data);
            delete this.options.data;

            if ($.trim(initialValue)) {
                this.values = initialValue.split(",");
                // 防止后端输出未名数据
                for (var i = 0; i < this.values.length; i++) {
                    if (!this._getText(this.values[i])) {
                        this.values.splice(i--, 1);
                    }
                }
                // 超过限定人数处理
                if (max && this.values.length > max) {
                    this.values.splice(max);
                }
                this.$element.val(this.values.join(','));
            }
            this._init();
        }
        /**
         * UserSelect默认配置
         * @property defaults
         * @type {Object}
         */

    UserSelect.showAllBox = function() {
        $(".select-box").show();
        $(".operate-btn .glyphicon-user").parent().addClass("active");
    }
    UserSelect.hideAllBox = function() {
        $(".select-box").hide();
        $(".operate-btn.active .glyphicon-user").parent().removeClass("active");
    }

    UserSelect.prototype = {
        constructor: UserSelect,
        /**
         * 初始化
         * @method _init
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _init: function() {
            var boxSelector = "";

            this._createSelect();

            // 配置了box属性并拥有长度时，假设其为一个jq对象
            if (!this.options.box) {
                boxSelector = this.$element.attr("data-box");
                this.options.box = $(boxSelector);
            }

            if (this.options.box && this.options.box.length) {
                this._createSelectBox();
            }
            return this;
        },
        /**
         * 创建Select2实例
         * @method _createSelect
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _createSelect: function() {
            var that = this,
                lang = UserSelect.lang,
                formatResult = function(data, $ct, query, _cache, oFragement) {
                    var $results = that.$element.data().select2.results,
                        type = data.id.charAt(0), //类别，c => company, u => user, d => department, p => position, r => role
                        tpl = "";

                    if (_cache[type]) return data.text;

                    if (!_cache.tip) {
                        // Tips
                        _cache.tip = 1;
                        tpl = '<li class="select2-tip">' + U.lang("US.INPUT_TIP") + '</li>';
                        oFragement.appendChild($(tpl)[0]);
                    }

                    switch (type) {
                        case "c": // Company
                            tpl = '<li class="select2-company">' + U.lang("COMPANY") + '</li>';
                            break;
                        case "u": // User
                            tpl = '<li class="select2-user">' + U.lang("STAFF") + '</li>';
                            break;
                        case "d": // Department
                            tpl = '<li class="select2-department">' + L.DEPARTMENT + '</li>';
                            break;
                        case "p": // Position
                            tpl = '<li class="select2-position">' + U.lang("POSITION") + '</li>';
                            break;
                        case "r": // Role
                            tpl = '<li class="select2-role">' + U.lang("ROLE") + '</li>';
                            break;
                    }

                    _cache[type] = 1;
                    oFragement.appendChild($(tpl)[0]);

                    return data.text;
                },
                formatSelection = function(data, $ct) {
                    var type = data.id.charAt(0),
                        typeMap = {
                            c: "company",
                            d: "department",
                            p: "position",
                            u: "user",
                            r: "role"
                        },
                        text = '<i class="select2-icon-' + typeMap[type] + '"></i>' + data.text;
                    return text;
                },
                formatNoMatches = function() {
                    return U.lang("US.NO_MATCH");
                },
                formatSelectionTooBig = function(limit) {
                    return U.lang("US.SELECTION_TO_BIG", { limit: limit });
                },
                initSelection = function(element, callback) {
                    var data,
                        val = (element.val() || element.context.value).split(",");

                    data = Ibos.data.includes(val);
                    callback(Ibos.data.converToArray(data));
                    data = null;
                },
                query = function(query) {
                    var data = {
                            results: []
                        },
                        term = query.term,
                        userData;
                    // 提供拼音搜索功能
                    query.matcher = function(data) {
                        // 岗位分类应排除在数据集中
                        if (data.id.charAt(0) === 'f') {
                            return false;
                        }
                        var text = data.text,
                            textArr = pinyinEngine.toPinyin(text, false),
                            termArr = term.split("");
                        for (var i = 0; i < termArr.length; i++) {
                            var inside = false;
                            //假设使用首字母拼音搜索
                            for (var j = 0; j < textArr.length; j++) {
                                if (textArr[j][i] && textArr[j][i].charAt(0) == termArr[i]) {
                                    inside = true;
                                }
                            }
                            //假设全拼或完全匹配
                            if (!inside) {
                                text += "," + pinyinEngine.toPinyin(text, false, ",");
                                return text.toUpperCase().indexOf(term.toUpperCase()) >= 0;
                            }
                        }
                        return true;
                    };

                    data.results = Ibos.data.converToArray(Ibos.data.filter(that.data, query.matcher));
                    query.callback(data);
                    data = null;
                },
                getPlaceholder = function(type) {
                    var types = (function() {
                            var types = type || "";
                            if ($.isArray(types)) {
                                return types;
                            }
                            return types.split(",");
                        })(),
                        lang = U.lang,
                        str = lang("US.PLACEHOLDER");

                    type = types[0] ? (~$.inArray("all", types) ? ["all"] : types) : ["all"];

                    if (type[0] === "all") {
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
                },
                select2Defaults = {
                    width: "100%",
                    formatResult: formatResult,
                    formatSelection: formatSelection,
                    formatSelectionTooBig: formatSelectionTooBig,
                    formatNoMatches: formatNoMatches,
                    initSelection: initSelection,
                    placeholder: getPlaceholder(this.options.type),
                    query: query
                },
                select2Options = $.extend({}, select2Defaults, this.options, { data: Ibos.data.converToArray(this.data) });

            this.$element.select2(select2Options).on("change", function(evt) {
                if (evt.added && evt.added.id) {
                    that.addValue(evt.added.id);
                } else if (evt.removed && evt.removed.id) {
                    that.removeValue(evt.removed.id);
                }
            });
            this.select = this.$element.data("select2");
            this._initSelectOperate();
        },

        /**
         * 创建SelectBox实例
         * @method _createSelectBox
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _createSelectBox: function() {
            var that = this,
                options = this.options;

            this.selectBox = new SelectBox(options.box, {
                contact: that.options.contact,
                data: that.data,
                values: [].concat(that.values),
                type: that.options.type,
                maximumSelectionSize: this.options.maximumSelectionSize
            });

            $(this.selectBox).on("slbchange", function(evt, data) {
                // if selectBox trigger this event, ignore to refresh
                data.checked ? that.addValue(data.id, null, true) : that.removeValue(data.id, null, true);
            });

            this.selectBox.hide();
            return this;
        },
        /**
         * 初始化操作层，默认会创建控件弹窗的按钮
         * @method _initSelectOperate
         * @private
         * @chainable
         * @return {Object} 当前调用对象
         */
        _initSelectOperate: function() {
            var that = this,
                options = this.options,
                $operateWrap = this._createSelectOperate(),
                $openBoxBtn, $clearBtn;

            var setPosition = function($el, $target) {
                var select2Obj = that.select,
                    select2Container = select2Obj.container,
                    // 当定义relative属性且指一个JQ对象时，则相对该JQ对象定位，否则相对select2Container;
                    relative = (options.relative && options.relative.length) ? options.relative : select2Container;
                // 如果有额外绑定显示位置，则显示在该位置上
                if ($target) relative = $target;
                $el.position($.extend({
                    of: relative
                }, options.position));
            };

            // 弹出框按钮
            $openBoxBtn = this._createOperateBtn({
                handler: function($btn) {
                    // 打开弹窗选择器时，关闭下拉列表
                    that.$element.select2("close");
                    if ($btn.hasClass("active")) {
                        that.selectBox && that.selectBox.hide();
                        $btn.removeClass("active");
                    } else {
                        UserSelect.hideAllBox();
                        that.selectBox && that.selectBox.show(function($el) {
                            setPosition($el, $btn);
                        });
                        $btn.addClass("active");
                    }
                }
            });
            $operateWrap.append($openBoxBtn);

            if (options.clearable) {
                $clearBtn = this._createOperateBtn({
                    cls: "operate-btn",
                    iconCls: "glyphicon-trash",
                    handler: function() {
                        that.setValue();
                    }
                });
                $operateWrap.append($clearBtn);
            }
        },
        /**
         * 创建一个操作按钮
         * @method _createOperateBtn
         * @private
         * @param {Key-Value} options         按钮配置
         * @param {String}    options.cls     按钮对应样式类名
         * @param {String}    options.iconCls 按钮对应图标的样式类名
         * @param {Function}  options.handler 按钮点击时的处理函数
         * @return {Jquery}                   生成的Jq对象
         */
        _createOperateBtn: function(options) {
            var that = this,
                defaults = {
                    cls: "operate-btn",
                    iconCls: "glyphicon-user"
                },
                opt = $.extend({}, defaults, options),
                $btn = $('<a href="javascript:;" class="' + opt.cls + '"><i class="' + opt.iconCls + '"></i></a>');

            if (typeof opt.handler === "function") {
                $btn.on("click", function(evt) {
                    opt.handler.call(that, $btn);
                });
                // 阻止冒泡触发下拉菜单
                $btn.on("mousedown", function(evt) {
                    evt.stopPropagation();
                })
            }

            that.btns.push($btn);
            return $btn;
        },
        /**
         * 创建操作层
         * @method _createSelectOperate
         * @private
         * @return {Jquery} 操作层对应jq对象
         */
        _createSelectOperate: function() {
            var $selection = this.select.selection,
                $operateWrap = $('<li class="select2-operate"></li>');
            return $operateWrap.prependTo($selection);
        },
        /**
         * 刷新对应SelectBox，选中或取消左侧的选中，并刷新右侧菜单
         * @method refreshSelectBox
         * @param  {String||Array} id     左侧树的要操作的一个或一组ID
         * @param  {boolean} toCheck       true为选中，false为取消选中
         * @chainable
         * @return {Object} 当前调用对象
         */
        refreshSelectBox: function(id, toCheck) {
            var that = this,
                i, len;

            var refresh_unit = function(u_id, u_toCheck) {
                that.selectBox.values = that.values;
                // 传入ID时, 更新左侧对应选择框选中状态
                that.selectBox._checkNode(u_id, u_toCheck);
            };

            if (this.selectBox) {
                if (!id) {
                    // 传入id为空
                    this.selectBox.refreshCheckbox();
                } else {
                    if (!$.isArray(id)) { id = [id]; }
                    i = 0;
                    len = id.length;

                    while (i < len) {
                        id[i] && refresh_unit(id[i], toCheck);
                        i++;
                    }
                }
                // 更新右边列表
                this.selectBox.refreshList();
            }
        },

        /**
         * 根据数据的ID获取其对应文本
         * @param  {String} id 数据ID
         * @return {String}    对应文本
         */
        _getText: function(id) {
            if (!id) {
                return false;
            }

            var type, item;
            type = Ibos.data.getType(id);
            return this.data[type][id] && ['text'];
        },

        /**
         * 修改已选中的值
         * @method setValue
         * @param  {String||Array} val 单个值或一组值
         * @return {Array}             已选中值的数组
         */
        setValue: function(val, slient) {
            this.removeValue([].concat(this.values), slient);
            if (val) {
                this.addValue(val, slient);
            }
            this.select.close();
            return this.values;
        },
        /**
         * 获取已选中的值
         * @method getValue
         * @return {Array}  已选中值的数组
         */
        getValue: function() {
            return this.values;
        },

        getUnique: function(arr) {
            var unique = [],
                i, len, temp;

            if (!$.isArray(arr)) return unique;
            for (i = 0, len = arr.length; i < len; i++) {
                temp = arr[i];
                $.inArray(temp, unique) < 0 && unique.push(temp);
            }
            return unique;
        },
        /* 合并数组 */
        _mergeArray: function(source, val) {
            if (!$.isArray(source)) {
                source = [source];
            }

            return this.getUnique(source.concat(val));
        },

        /**
         * 从源数组中删除部分值，该数组必须各值单一
         * @method _resolveArray
         * @private
         * @param  {Array}  source 源数组
         * @param  {Any}    val    要从数组中删除的值，当为数组时，将删除两个数组中共有的值
         * @return {Array}         经过删除的源数组
         */
        _resolveArray: function(source, val) {
            // 如果val为数组
            if (!$.isArray(val)) {
                val = [val];
            }
            // 将val从源数组中删除
            $(val).each(function(i, e) {
                var index = $.inArray(e, source);
                index !== -1 && source.splice(index, 1);
            });
            return source;
        },
        /**
         * 判断数组是否空数组，当是时，返回null
         * @method _fixEmptyArray
         * @private 
         * @param  {Array} arr     源数组
         * @return {Array||null}   源数组或null
         */
        _fixEmptyArray: function(arr) {
            return (arr.length && arr.length > 0) ? arr : null;
        },
        /**
         * 添加要选中的值
         * @method addValue
         * @param  {String||Array} val  一个或一组要选中的值
         * @param  {Boolean} ignore     判断是否checkbox触发，避免二次重绘
         * @return {Array}              已选中的值
         */
        addValue: function(val, slient, ignore) {
            if (typeof val !== "undefined") {
                if (!$.isArray(val)) {
                    val = [val];
                }
                this.values = this._mergeArray(this.values, val);
                if (!ignore) {
                    this.refreshSelectBox(val, true);
                }
                this.select.val(this._fixEmptyArray(this.values));
                if (!slient) {
                    this.$element.trigger("uschange", { added: val, val: this.values })
                }
            }
            return this.values;
        },
        /**
         * 移除已选中的值
         * @method removeValue
         * @param  {String||Array}  val  一个或一组要移除的值，当参数为空时，将清空已选中的值
         * @return {Array}     已选中的值
         */
        removeValue: function(val, slient, ignore) {
            if (typeof val !== "undefined") {
                if (!$.isArray(val)) {
                    val = [val];
                }
                this._resolveArray(this.values, val);
                if (!ignore) {
                    this.refreshSelectBox(val, false);
                }
                this.select.val(this._fixEmptyArray(this.values));
                if (!slient) {
                    this.$element.trigger("uschange", { removed: val, val: this.values })
                }
            }
            return this.values;
        },
        /**
         * 启用选择框
         * @method setEnabled
         * @chainable
         * @return {Object} 当前调用对象
         */
        setEnabled: function() {
            this.select.enable();
            // 显示操作按钮
            this.btns[0].show();
            return this;
        },
        /**
         * 禁用选择框
         * @method setDisabled
         * @chainable
         * @return {Object} 当前调用对象
         */
        setDisabled: function() {
            this.select.disable();
            // 隐藏操作按钮
            this.btns[0].hide();
            return this;
        },
        /**
         * TODO:修正enabled,disabled,readonly的方法 
         * @returns {_L1.UserSelect.prototype}
         */
        setReadOnly: function() {
            this.select.disable();
            // 隐藏操作按钮
            this.btns[0].hide();
            this.btns[1].hide();
            return this;
        }
    }
    $.fn.userSelect = function(options) {
        if (!$ || !$.fn.select2) {
            // @Debug;
            throw new Error("($.fn.userSelect): 未定义 '$' 或 '$.fn.select2'");
        }
        var argu = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var $el = $(this),
                data = $el.data("userSelect");
            if (!data) {
                $el.data("userSelect", data = new UserSelect($el, $.extend({}, $.fn.userSelect.defaults, options)));
            }
            if (typeof options === "string" && $.isFunction(data[options])) {
                data[options].apply(data, argu)
            }
        })
    }
    $.fn.userSelect.Constructor = UserSelect;
    $.fn.userSelect.defaults = {
        contact: $.parseJSON(G.contact),
        data: {},
        multiple: true,
        position: {
            my: "right top",
            at: "right bottom"
        },
        clearable: true
    }

    $(doc).on("mousedown.userselect.hideall", UserSelect.hideAllBox);
})(document, Ibos);
