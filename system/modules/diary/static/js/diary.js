/**
 * diary.js
 * 工作日志
 * IBOS
 * Diary
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-11
 */

// 日志模块命名空间
var Diary = {
    //数据交互
    op: {
        /**
         * 删除日志
         * @method deldiary
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        delDiary: function(param) {
            var url = Ibos.app.url("diary/default/del");
            return $.post(url, param, $.noop, "json");
        },
        /**
         * 读取更多日志信息
         * @method loadMoreDiary
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        loadMoreDiary: function(param) {
            var url = Ibos.app.url("message/comment/getcomment");
            return $.post(url, param, $.noop);
        },
        /**
         * 关注信息获取
         * @method attentionInfo
         * @param  {[type]} param [description]
         * @return {[type]}       [description]
         */
        attentionInfo: function(param) {
            var url = Ibos.app.url("diary/attention/edit");
            return $.post(url, param, $.noop);
        }
    },
    /**
     * 切换详细日志显隐状态
     * @method toggleDetail
     * @param  {Jquery} $el            触发对象
     * @param  {String} [act="show"]   显隐，只有"show", "hide"两种状态
     */
    toggleDetail: function($el, act) {
        var toggleSpeed = 100,
            $item = $el.parents("li").eq(0),
            $summary = $item.find(".da-summary"),
            $detail = $item.find(".da-detail"),
            $act = (act === "hide");


        $detail[$act ? "slideUp" : "slideDown"](toggleSpeed);
        $summary[$act ? "slideDown" : "slideUp"](toggleSpeed);
        $item[$act ? "removeClass" : "addClass"]("open");
    },
    /**
     * 切换树显隐状态
     * @method toggleTree
     * @param  {Object}   $tree      传入Jquery节点对象
     * @param  {Function} [callback] 回调函数
     */
    toggleTree: function($tree, callback) {
        var isShowed = !$tree.is(":hidden");

        $tree[isShowed ? "hide" : "show"]();

        callback && callback(isShowed);
    },
    /**
     * 改变计划时间
     * @method changePlanDate
     * @param  {Object} date 传入Date日期对象
     * @return {Object}      返回jquery节点对象
     */
    changePlanDate: function(date) {
        var $elem = $("#da_plan_date_display"), // [data-node-type='planDate']
            tpl = '<strong><%= day %></strong>' +
            '<div class="mini-date-body">' +
            '<p><%= weekDay %></p> ' +
            '<p> <%= fullYear %>-<%= month %></p> ' +
            '</div> ',

            param = {
                day: this._fixDate(date.getDate()),
                weekDay: U.lang("TIME.WEEKS") + (U.lang("TIME.WEEKDAYS").charAt(date.getDay())),
                month: this._fixDate(date.getMonth() + 1),
                fullYear: date.getFullYear()
            };
        return $elem.html($.template(tpl, param));
    },
    /**
     * 日期补零
     * @method _fixDate
     * @param  {Number|String} num 传入数字或者数字字符串
     * @return {Number|String}     传出数字或者数字字符串
     */
    _fixDate: function(num) {
        return +num >= 10 ? num : "0" + num;
    },
    /**
     * 键盘事件处理
     * @method keyHandler
     * @param  {[type]} evt      [description]
     * @param  {[type]} handlers [description]
     */
    keyHandler: function(evt, handlers) {
        var keycodes = {
            "up": 38,
            "down": 40,
            "delete": 46,
            "enter": 13,
            "tab": 9,
            "backspace": 8
        };
        handlers = handlers || {};
        for (var i in keycodes) {
            if (keycodes.hasOwnProperty(i) && keycodes[i] === evt.which) {
                handlers[i] && handlers[i].call(evt.target, evt);
            }
        }
    },
    /**
     * 排序表格
     * @method orderTable
     * @param  {Object} $container 传入Jquery节点对象
     * @param  {String} template   传入JS模板ID
     * @param  {Object} options    传入JSON格式参数
     */
    orderTable: function($container, template, options) {
        options = $.extend({
            indexSelector: "[data-toggle='badge']",
            indexFormat: "<%=index%>."
        }, options);

        var _cache = {};

        this.reorderIndex = function() {
            $container.find(options.indexSelector).each(function(i) {
                $(this).text($.template(options.indexFormat, {
                    index: (i + 1)
                }));
            });
        };
        this.getPrevRow = function(id) {
            return _cache[id].elem.prev("tr");
        };
        this.getNextRow = function(id) {
            return _cache[id].elem.next("tr");
        };
        this.focus = function($row) {
            $("input[type='text']", $row).focus();
        };
        this.add = function(data, callback) {
            var $row;
            data = $.extend({
                id: parseInt(U.uniqid(), 16),
                subject: ""
            }, data);

            $row = $.tmpl(template, data);
            // 插入倒数第二行
            $row.insertBefore($container.find("tr:last"));

            _cache[data.id] = {
                elem: $row,
                data: data
            };

            // 重新排序
            this.reorderIndex();

            callback && callback($row);
            return $row;
        };
        this.remove = function(id, callback) {
            if (_cache[id] && _cache[id].elem) {
                this.focus(this.getPrevRow(id));
                _cache[id].elem.remove();
            }
            delete _cache[id];
            // 重新排序
            this.reorderIndex();
            callback && callback();
        };
    },
    /**
     * 创建标尺
     * @method createVernier
     * @param  {Object} $elem   传入Jquery节点对象
     * @param  {Object} options 传入JSON格式参数
     */
    createVernier: function($elem, options) {
        var $container = $("<ul class='vernier'></ul>"),
            isFormatValid,
            cellWidth,
            res,
            num;

        options = $.extend({
            cell: 10, // 总单元格数
            subcell: 1, // 每单元格里子单元格数，即没有标识的格子
            min: 0, // 单元格起点
            step: 1, // 每单元格每代表的数值
            template: "<%= num %>", // 标识模板
            format: null // 标尺格式化函数
        }, options);

        options.cell = Number(options.cell) || 10;

        isFormatValid = options.format && typeof options.format === "function";
        cellWidth = 100 / (options.cell);

        for (var i = 0; i < options.cell; i++) {
            var num = options.min + i * options.step;
            if (isFormatValid) {
                res = options.format(num);
                num = typeof res !== "undefined" ? res : num;
            }
            var isCell = i % options.subcell === 0;

            $("<li class='vernier-" + (isCell ? "cell" : "subcell") + "'>" + (isCell ? num : "") + "</li>").width(cellWidth + "%").appendTo($container);
        }
        $container.appendTo($elem);
    },
    /**
     * 删除日志
     * @param  {String} diaryId 传入日志ID
     */
    removeDiary: function(diaryId) {
        var param = {
            diaryids: diaryId
        };
        Diary.op.delDiary(param).done(function(res) {
            Ui.tip(res.msg, (res.isSuccess ? "" : "warning"));
            res.isSuccess && (window.location.href = Ibos.app.url("diary/default/index"));
        });
    },
    /**
     * 初始化表情函数
     * @method initCommentEmotion
     * @param  {Object} $context 传入Jquery节点对象
     */
    initCommentEmotion: function($context) {
        //按钮[data-node-type="commentEmotion"]
        $('[data-node-type="commentEmotion"]', $context).each(function() {
            var $elem = $(this),
                $target = $elem.closest('[data-node-type="commentBox"]').find('[data-node-type="commentText"]');
            $elem.ibosEmotion({
                target: $target
            });
        });
    }
};


var diaryComment = {
    module: 'diary',
    table: 'diary',
    offset: 0,
    limit: 10
};
// 读取更多评论
var loadMoreDiaryComment = function($button, param) {
    var offset = $button.attr("data-offset");
    param = $.extend(param, {
        offset: offset,
        loadmore: true
    });

    $button.hide().parent().waiting(null, "normal");

    Diary.op.loadMoreDiary(param).done(function(res) {
        if (res.IsSuccess) {
            $button.show().parent().waiting(false).before(res.data);
            // 如果没有更多已经没有更多了，则隐藏“加载更多”
            // 否则，更新评论起始值offset
            if (parseInt(res.count, 10) - offset < diaryComment.limit) {
                $button.parent().hide();
            } else {
                $button.parent().show();
                $button.attr("data-offset", +offset + diaryComment.limit);
            }
        }
    });
};


$(function() {
    // 阅读人员ajax
    $("[data-node-type='loadReader']").each(function() {
        $(this).ajaxPopover(Ibos.app.url("diary/default/index", {
            op: "getreaderlist",
            diaryid: $.attr(this, "data-id")
        }));
    });

    //点评人员ajax
    $("[data-node-type='loadCommentUser']").each(function() {
        $(this).ajaxPopover(Ibos.app.url("diary/default/index", {
            op: "getcommentlist",
            diaryid: $.attr(this, "data-id")
        }));
    });

    $(document).on("shown", '[data-original-title]', function() {
        var aPopover = $('[data-original-title]');
        aPopover.not(this).popover('hide');
    });

    Ibos.evt.add({
        // 删除一篇日志
        "removeDiary": function(param, elem) {
            Ui.confirm(U.lang("DA.DELETE_ONE_DIARY_CONFIRM"), function() {
                Diary.removeDiary(param.id);
            });
        },
        // 删除多篇日志
        "removeDiarys": function() {
            var diaryIds = $("input[name='diaryids']:checked").map(function() {
                return this.value;
            }).get();

            if (!diaryIds.length) {
                Ui.tip(U.lang("DA.SELECT_AT_LEAST_ONE_WORK_RECORD"), 'warning');
            } else {
                diaryIds = diaryIds.join(",");
                Ui.confirm(U.lang('DA.SURE_TO_DEL'), function() {
                    Diary.removeDiary(diaryIds);
                });
            }
        },
        // 展开详细日志
        "showDiaryDetail": function(param, elem) {
            var $elem = $(elem),
                $item = $elem.closest("li"),
                $detail = $item.find(".da-detail"),
                loaded = $item.data("loaded"),
                postData = {
                    op: 'showdiary',
                    diaryid: param.id
                };

            // 此参数用于控制是否出现图章
            param.fromController && (postData.fromController = param.fromController);


            //此参数用于判断展开视图的左上角是头像还是时间
            param.isShowDiarytime && (postData.isShowDiarytime = param.isShowDiarytime);


            // 若未有缓存，则AJAX读取内容后，缓存并显示
            if (!loaded) {
                $item.waiting(null, "normal");
                $.ajax({
                    url: Ibos.app.url("diary/default/index", postData),
                    type: "get",
                    dataType: "json",
                    cache: false,
                    success: function(res) {
                        if (res.isSuccess === true) {
                            $detail.append(res.data)
                                // 初始化进度条
                                .find("[data-toggle='bamboo-pgb']").each(function() {
                                    var $pgb = $(this),
                                        defaultValue = +$pgb.parent().find('input').val();
                                    $pgb.studyplay_star({
                                        CurrentStar: defaultValue,
                                        Enabled: false
                                    });
                                });
                            $detail.show();
                            // 展开详情
                            Diary.toggleDetail($elem, "show");
                            // 记录已缓存
                            $item.data("loaded", true).waiting(false);

                            //当点击查看时，动态的给需要查看大图的img外层添加<a>标签
                            $(".summary-td img", $detail).each(function() {
                                var $elem = $(this);
                                $elem.wrap("<a data-lightbox='diary' href='" + $elem.attr("src") + "' title='" + ($elem.attr("title") || $elem.attr('alt')) + "'></a>");
                            });

                            var $diary = $('#diary_' + param.id);
                            // 图章
                            if (Ibos.app.g('stampEnable')) {
                                var $commentBtn = $detail.find("[data-act='addcomment']");
                                var $stampBtn = $detail.find('[data-toggle="stampPicker"]');

                                if ($stampBtn.length) {
                                    Ibosapp.stampPicker($stampBtn, Ibos.app.g('stamps'));
                                    $stampBtn.on("stampChange", function(evt, data) {
                                        // Preview Stamp
                                        var stamp = '<img src="' + Ibos.app.g('stampPath') + data.stamp + '" width="150px" height="90px" />',
                                            smallStamp = '<img src="' + data.path + '" width="60px" height="24px" />',
                                            $parentRow = $stampBtn.closest("div");

                                        $("#preview_stamp_" + param.id).html(stamp);
                                        $('#diary_stamp_' + param.id).attr('src', data.path);
                                        $parentRow.find(".preview_stamp_small").html(smallStamp);
                                        $.extend($commentBtn.data("param"), {
                                            stamp: data.value
                                        });
                                    });
                                }
                                if (Ibos.app.g('autoReview') == '1') {
                                    $.get(Ibos.app.url("diary/review/edit", {
                                        'op': 'changeIsreview'
                                    }), {
                                        diaryid: param.id
                                    });
                                }
                            }
                        } else {
                            Ui.tip(res.msg, 'warning');
                        }
                    }
                });
            } else {
                Diary.toggleDetail($elem, "show");
            }
        },
        // 收起详细日志
        "hideDiaryDetail": function(param, elem) {
            Diary.toggleDetail($(elem), "hide");
        },
        // 关注/取消关注
        "toggleAsterisk": function(param, elem) {
            var $elem = $(elem),
                isAtt = $elem.hasClass("o-da-asterisk"),
                op = isAtt ? "unattention" : "attention",
                asteriskParam = {
                    op: op,
                    auid: param.id
                };
            // AJAX记录数据，回调以下
            Diary.op.attentionInfo(asteriskParam).done(function(res) {
                if (res.isSuccess) {
                    Ui.tip(res.info);
                    $elem.attr("class", isAtt ? "o-da-unasterisk" : "o-da-asterisk");
                    $("a[data-node-type='udstar'][data-id='" + param.id + "']").attr("class", isAtt ? "o-udstar pull-right" : "o-gudstar pull-right");
                }
            });
        },
        // 侧栏关注下属
        "toggleAsteriskUnderling": function(param, elem) {
            var $elem = $(elem),
                isAtt = $elem.hasClass("o-gudstar"),
                op = isAtt ? "unattention" : "attention",
                asteriskUnderlingParam = {
                    'op': op,
                    auid: param.id
                };
            // AJAX记录数据，回调以下
            Diary.op.attentionInfo(asteriskUnderlingParam).done(function(res) {
                if (res.isSuccess) {
                    Ui.tip(res.info);

                    $elem.addClass("o-" + (isAtt ? "udstar" : "gudstar")).removeClass("o-" + (isAtt ? "gudstar" : "udstar"));
                    $("i[data-id='" + param.id + "']").addClass("o-da-" + (isAtt ? "unasterisk" : "asterisk")).removeClass("o-da-" + (isAtt ? "asterisk" : "unasterisk"));
                }
            });
        }
    });
});