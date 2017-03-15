/**
 * report.js
 * 工作计划
 * IBOS
 * Report
 * @author		inaki
 * @version		$Id$
 */
var Report = {
    op: {
        /**
         * 删除总结
         * @param  {String} ids 传入总结的IDs
         * @return {Object}     返回deffered对象
         */
        removeReport: function(ids) {
            if (ids) {
                var url = Ibos.app.url("report/default/del"),
                    param = {
                        repids: ids
                    };
                return $.post(url, param, $.noop, "json");
            }
        }
    },
    /**
     * 切换详细总结显隐状态
     * @param  {Jquery} $el          触发对象
     * @param  {String} [act="show"] 显隐，只有"show", "hide"两种状态
     */
    toggleDetail: function($el, act) {
        var toggleSpeed = 100,
            $item = $el.parents("li").eq(0),
            $summary = $item.find(".rp-summary"),
            $detail = $item.find(".rp-detail"),
            $act = act === "hide";

        $detail[$act ? 'slideUp' : 'slideDown'](toggleSpeed);
        $summary[$act ? 'slideDown' : 'slideUp'](toggleSpeed);
        $item[$act ? 'removeClass' : 'addClass']("open");
    },
    /**
     * 切换树类型显隐状态
     * @method toggleTree
     * @param  {Object}   $tree      触发对象
     * @param  {Function} [callback] 回调函数
     */
    toggleTree: function($tree, callback) {
        var isShowed = !$tree.is(":hidden")
        $tree[isShowed ? 'hide' : 'show']();
        callback && callback(isShowed);
    }
};

(function() {
    // 计划、总结列表
    var NpList = function() {
        var that = this;
        this._super.apply(this, arguments);

        this.$container.on("keydown", "input", $.proxy(this._onKeyDown, this))
            .on("click", ".o-trash", function() {
                that.removeItem($.attr(this, "data-id"));
            });
    };

    // 属性和方法的继承
    NpList.KEYCODE = Ibos.settings.KEYCODE;
    NpList.defaults = $.extend({}, Ibos.CmList.defaults, {
        start: 1
    });

    Ibos.core.inherits(NpList, Ibos.CmList);

    $.extend(NpList.prototype, {
        /**
         * 添加一行
         * @param  {Object} data  传入JSON格式数据
         * @param  {Ang} focus 所有类型
         * @return {Object}       返回自身
         */
        addItem: function(data, focus) {
            data = $.extend({
                index: this.opts.start + this.getItemCount(),
                id: U.uniqid()
            }, data);
            var ret = this._super.prototype.addItem.call(this, data);
            if (focus) {
                ret.find("input").focus();
            }
            return ret;
        },
        /**
         * 删除一行
         * @method removeItem
         * @param  {String} id 传入id
         * @return {Object}    返回自身
         */
        removeItem: function(id) {
            var that = this;
            var ret;

            this.focusPrevInput(id);

            ret = this._super.prototype.removeItem.call(this, id);

            this.$container.find("[data-toggle='badge']").each(function(i) {
                $(this).html(that.opts.start + i);
            });

            return ret;
        },
        /**
         * 自动焦点输入框
         * @method focusInput
         * @param  {String} id 输入框的id
         */
        focusInput: function(id) {
            this.getItem(id).find("input").focus();
        },
        /**
         * 自动焦点上一个输入框
         * @method focusPrevInput
         * @param  {String} id 输入框的id
         */
        focusPrevInput: function(id) {
            this.getItem(id).prev().find("input").focus();
        },
        /**
         * 自动焦点下一个输入框
         * @method focusNextInput
         * @param  {String} id 输入框的id
         */
        focusNextInput: function(id) {
            this.getItem(id).next().find("input").focus();
        },
        /**
         * 键盘事件
         * @method _onKeyDown
         * @param  {Object} evt 事件 
         */
        _onKeyDown: function(evt) {
            var KEY = this.constructor.KEYCODE;

            switch (evt.which) {
                // 后退键，没有内容时删除一行
                case KEY.BACKSPACE:
                    if ($.trim(evt.target.value) === "") {
                        this.removeItem($.attr(evt.target, "data-id"));
                        evt.preventDefault();
                    }
                    break;
                case KEY.ENTER:
                    if ($.trim(evt.target.value) !== "") {
                        this.addItem();
                    }
                    evt.preventDefault();
                    break;
            }
        }
    });

    Report.NpList = NpList;
})();


//初始化表情函数
function initCommentEmotion($context) {
    //按钮[data-node-type="commentEmotion"]
    $('[data-node-type="commentEmotion"]', $context).each(function() {
        var $elem = $(this),
            $target = $elem.closest('[data-node-type="commentBox"]').find('[data-node-type="commentText"]');
        $elem.ibosEmotion({
            target: $target
        });
    });
}

$(function() {
    // 阅读人员ajax
    $("[data-node-type='loadReader']").each(function() {
        $(this).ajaxPopover(Ibos.app.url("report/default/index", {
            op: "getReaderList",
            repid: $.attr(this, "data-id")
        }));
    });

    //点评人员ajax
    $("[data-node-type='loadCommentUser']").each(function() {
        $(this).ajaxPopover(Ibos.app.url("report/default/index", {
            op: "getCommentList",
            repid: $.attr(this, "data-id")
        }));
    });

    Ibos.evt.add({
        // 从查看页删除总结
        "removeReport": function(param, elem) {
            Ui.confirm(Ibos.l('RP.SURE_DEL_REPORT'), function() {
                Report.op.removeReport(param.id).done(function(res) {
                    if (res.isSuccess) {
                        Ui.tip(Ibos.l("OPERATION_SUCCESS"));
                        window.location.href = Ibos.app.url("report/default/index");
                    } else {
                        Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
                    }
                });
            });
        },

        // 从列表页删除总结
        "removeReportFromList": function(param, elem) {
            Ui.confirm(Ibos.l('RP.SURE_DEL_REPORT'), function() {
                Report.op.removeReport(param.id).done(function(res) {
                    var hasSuccess = res.isSuccess;
                    Ui.tip(res.msg, hasSuccess ? "" : "danger");
                    hasSuccess && window.location.reload();
                });
            });
        },

        // 从列表页删除多篇总结
        "removeReportsFromList": function(param, elem) {
            var repids = U.getCheckedValue('report[]');
            if (repids) {
                Ui.confirm(Ibos.l('RP.SURE_DEL_REPORT'), function() {
                    Report.op.removeReport(repids).done(function(res) {
                        var hasSuccess = res.isSuccess;
                        Ui.tip(res.msg, hasSuccess ? "" : "danger");
                        hasSuccess && window.location.reload();
                    });
                });
            } else {
                Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
            }
        },

        "showReportDetail": function(param, elem) {
            var $el = $(elem),
                $item = $el.closest("li"),
                $detail = $item.find(".rp-detail"),
                hasInit = $item.attr("data-init") === "1" ? true : false;

            // 若已缓存，则直接显示
            // 否则AJAX读取内容后，缓存并显示
            if (!hasInit) {
                $item.waiting(null, 'normal');
                $.ajax({
                    url: Ibos.app.url('report/default/index', {
                        op: 'showDetail',
                        repid: param.id,
                        fromController: param.fromController
                    }),
                    type: "get",
                    dataType: "json",
                    cache: false,
                    success: function(res) {
                        if (res.isSuccess === true) {
                            $detail.append(res.data);
                            // 读取内容后初始化进度条
                            $detail.find("[data-toggle='bamboo-pgb']").each(function() {
                                var $pgb = $(this),
                                    defaultValue = +$pgb.parent().find('input').val();
                                $pgb.studyplay_star({
                                    CurrentStar: defaultValue,
                                    Enabled: false
                                });
                            });
                            $item.attr("data-init", "1");

                            //当点击查看时，动态的给需要查看大图的img外层添加<a>标签
                            $(".summary-td img", $detail).each(function() {
                                var $elem = $(this);
                                $elem.wrap("<a data-lightbox='report' href='" + $elem.attr("src") + "' title='" + ($elem.attr("title") || $elem.attr('alt')) + "'></a>");
                            });

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
                                        $('#report_stamp_' + param.id).attr('src', data.path);
                                        $parentRow.find(".preview_stamp_small").html(smallStamp);
                                        $.extend($commentBtn.data("param"), {
                                            "stamp": data.value
                                        });
                                    });
                                }
                                if (Ibos.app.g('autoReview') == '1') {
                                    $.get(Ibos.app.url("report/review/edit", {
                                        'op': 'changeIsreview'
                                    }), {
                                        repid: param.id
                                    });
                                }
                            }
                            Report.toggleDetail($el, "show");
                            $detail.show();
                            $item.waiting(false);
                        } else {
                            Ui.tip(res.msg, 'warning');
                        }
                    }
                });
            } else {
                Report.toggleDetail($el, "show");
            }
        },
        // 收起总结详细
        "hideReportDetail": function(param, elem) {
            Report.toggleDetail($(elem), "hide");
        }
    });
});