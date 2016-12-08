// 在日程模块下， evt大多代表的是日程任务，而不是js事件
var Cld = {
    // 主题
    themes: ["#3497DB", "#A6C82F", "#F4C73B", "#EE8C0C", "#E76F6F", "#AD85CC", "#98B2D1", "#82939E"],
    // 日期格式
    DATE_FORMAT: "YYYY-M-DD",
    // 时间格式
    TIME_FORMAT: "HH:mm",
    // 完整时间格式
    DATETIME_FORMAT: "YYYY-M-DD HH:mm",
    // 创建日期节点
    detailMenu: new Ui.Menu($("<div></div>").appendTo(document.body), {
        id: "cal_info_menu"
    }),
    /**
     * 显示详细目录
     * @method showDetailMenu
     * @param  {Object} evt   日期任务
     * @param  {Object} jsEvt 事件对象
     */
    showDetailMenu: function(evt, jsEvt) {
        var _this = this;
        this._timer = setTimeout(function() {
            var typeLang = type = (evt.type == "1" || evt.type == "2") ?
                "CAL.LOOP_TYPE" :
                evt.acrossDay ?
                "CAL.ACROSSDAY_TYPE" :
                evt.allDay == '1' ?
                "CAL.ALLDAY_TYPE" :
                "CAL.NORMAL_TYPE";

            _this.detailMenu.setContent($.template("cal_info_menu_tpl", {
                time: Cld.formatInterval(evt),
                evt: evt.title,
                type: U.lang(typeLang)
            }));
            _this.detailMenu.show();
            _this.detailMenu.$menu.offset({
                top: jsEvt.pageY,
                left: jsEvt.pageX
            });
        }, 500);
    },
    /**
     * 隐藏详细目录
     * @method hideDetailMenu
     * @param  {Object} evt   日期任务
     * @param  {Object} jsEvt 事件对象
     */
    hideDetailMenu: function(evt, jsEvt) {
        var _this = this;
        clearTimeout(this._timer);
        // 如果鼠标进入菜单，则菜单不隐藏
        if ($.contains(this.detailMenu.$menu[0], jsEvt.toElement)) {
            // 绑定一次性事件，从菜单回到原来日程时，也不
            this.detailMenu.$menu.one("mouseleave", function(e) {
                if (!$.contains(jsEvt.currentTarget, e.toElement)) {
                    _this.detailMenu.hide();
                }
            });
            return false;
        }
        this.detailMenu.hide();
    },
    /**
     * 将日程事件属性解析为ajax参数
     * @method _parseEvtToParam
     * @param  {Object} evt 日期任务
     */
    _parseEvtToParam: function(evt) {
        var oldStartTime = (evt.prevStart ? evt.prevStart : evt.start).format(this.DATETIME_FORMAT) || null,
            oldEndTime = (evt.prevEnd ? evt.prevEnd : evt.end).format(this.DATETIME_FORMAT) || null;

        var ret = {
            calendarId: evt.id,
            CalendarTitle: evt.title,
            Subject: evt.title,
            CalendarStartTimeed: oldStartTime,
            CalendarEndTimeed: oldEndTime,
            Category: evt.category,
            IsAllDayEvent: +evt.allDay,
            type: evt.type,
            timezone: evt.start.zone() / -60
        };

        // 全天日程时，直接设置开始时分和结束时分为　"00:00:00"
        if (ret.IsAllDayEvent) {
            evt.start.hour(0).minute(0).second(0);
            // 非全天日程时，格式为 "yyyy-MM-dd HH:mm:ss"
        } else {
            // 从全天日程移动到普通日程时，evt里的 end 属性为 null 导致出错
            // 这是 fullcalendar1.1.0的bug，在新版本中已修复
            // 不过由于修改过大，目前没办法快速升级
            // 这里先使用 Hack 这种情况
            evt.end = evt.end || evt.start.clone().add(2, 'h');
        }
        ret.CalendarStartTime = evt.start.format(this.DATETIME_FORMAT);
        ret.CalendarEndTime = evt.end.format(this.DATETIME_FORMAT);

        return ret;
    },
    /**
     * 解析数据参数
     * @method _parseDataToParam
     * @param  {Object} evtData 日期数据
     * @return {Object}         返回相应的参数
     */
    _parseDataToParam: function(evtData) {
        var start = moment(evtData.start),
            end = moment(evtData.end);

        return {
            calendarId: evtData.id,
            CalendarTitle: evtData.title,
            Subject: evtData.title,
            CalendarStartTime: start.format(this.DATETIME_FORMAT),
            CalendarEndTime: end.format(this.DATETIME_FORMAT),
            Category: evtData.category,
            IsAllDayEvent: +evtData.allDay,
            type: evtData.type,
            timezone: start.zone() / -60
        };
    },
    /**
     * 格式化时间间隔
     * @method formatInterval
     * @param  {[type]} evt [description]
     * @return {[type]}     [description]
     */
    formatInterval: function(evt) {
        var LOCAL_DATE_FORMAT = U.lang("CAL.EVT_VIEW_FORMAT");
        return evt.acrossDay ?
            evt.start.format(LOCAL_DATE_FORMAT) + (evt.end ? " - " + evt.end.format(LOCAL_DATE_FORMAT) : "") :
            evt.allDay ?
            evt.start.format(LOCAL_DATE_FORMAT) :
            evt.start.format(LOCAL_DATE_FORMAT) + " " + evt.start.format(this.TIME_FORMAT) + " - " + evt.end.format(this.TIME_FORMAT);
    },
    /**
     * 保存上一次事件
     * @method saveEvtPrevTime
     * @param  {Object} evt 日期任务
     */
    saveEvtPrevTime: function(evt) {
        evt.prevStart = new moment(evt.start);
        evt.prevEnd = new moment(evt.end);
    },
    /**
     * 更新日期任务
     * @method updateEvt
     * @param  {Object} evt 日期任务
     */
    updateEvt: function(evt) {
        Cldm.update(this._parseEvtToParam(evt)).done(function(res) {
            res.isSuccess && Ui.tip(U.lang("CAL.UPDATE_EVT_SUCCESS"));
            evt.prevStart = evt.prevEnd = null;
        });
    },
    /**
     * 删除日期任务
     * @method removeEvt
     * @param  {Object} evt     日期任务
     * @param  {Object} view    视图
     * @param  {Object} doption 
     */
    removeEvt: function(evt, view, doption) {
        var _this = this;
        doption = doption || "this"; // only after all
        Ui.confirm(U.lang("CAL.REMOVE_EVT_CONFIRM"), function() {
            Cldm.remove({
                calendarId: evt.id,
                type: evt.type,
                doption: doption,
                CalendarStartTime: evt.start.format(_this.DATETIME_FORMAT)
            }).done(function(res) {
                var evts = view.calendar.clientEvents();
                var mid = evt.id.substr(11);
                // 删除所有的周期性日程
                if (doption === "all" || doption === "after") { // 删除后续的周期性日程
                    $.each(evts, function(i, e) {
                        if (e.id.length > 11 && e.id.substr(11) == mid && e.start >= evt.start) {
                            view.calendar.removeEvents(e.id);
                        }
                    });
                } else {
                    view.calendar.removeEvents(evt.id);
                }
                res.isSuccess && Ui.tip(U.lang("CAL.REMOVE_EVT_SUCCESS"));
            });
        });
    },
    /**
     * 结束日期任务
     * @method finishEvt
     * @param  {Object}   evt        日期任务
     * @param  {Object}   view       视图
     * @param  {Function} [callback] 回调函数
     */
    finishEvt: function(evt, view, callback) {
        var param = this._parseEvtToParam(evt),
            status = evt.status == "1";
        Cldm[status ? "unfinish" : "finish"](param).done(function(res) {
            if (res.isSuccess) {
                evt.status = status ? "0" : "1";
                evt.className = status ? "" : "fc-event-finish";
                view.calendar.updateEvent(evt);
                callback && callback(evt);
            }
        });
    },
    /**
     * 保存日期任务
     * @method saveEvt
     * @param  {Object}   evt        日期任务
     * @param  {Object}   view       视图
     * @param  {Function} [callback] 回调函数
     */
    saveEvt: function(evt, view, callback) {
        evt.title = U.entity.escape(evt.title);
        Cldm.update(this._parseEvtToParam(evt)).done(function(res) {
            if (res.isSuccess) {
                view.calendar.updateEvent(evt);
                Ui.tip(U.lang("CAL.UPDATE_EVT_SUCCESS"));
            }
            callback && callback(res);
        });
    },
    /**
     * 创建日期任务
     * [createEvt description]
     * @param  {Object}   evt        日期任务
     * @param  {Object}   view       视图
     * @param  {Function} [callback] 回调函数
     */
    createEvt: function(evt, view, callback) {
        evt.allDay = evt.end.diff(evt.start, "day", true) >= 1;
        // evt.title = U.entity.escape(evt.title);
        var param = this._parseEvtToParam(evt);

        Cldm.add(param).done(function(res) {
            if (res.isSuccess) {
                evt.id = res.data + "";
                // view.calendar.renderEvent(evt, true)
                view.calendar.refetchEvents();
                Ui.tip(U.lang("CAL.NEW_EVT_SUCCESS"));
            } else {
                Ui.tip(res.msg, 'danger');
            }
            callback && callback(res);
        });
    },
    /**
     * 显示窗口
     * @mehtod showDialog
     * @param  {Object} evt   日期对象
     * @param  {Object} jsEvt 事件对象
     * @param  {Object} view  视图
     * @param  {String} op    操作
     */
    showDialog: function(evt, jsEvt, view, op) {
        var _this = this,
            dialog,
            // 是否周期性日程
            isLoop = +(evt.id) < 0;
        // 时间区间
        interval = _this.formatInterval(evt);

        var $content = $.tmpl("cal_edit_tpl", {
            isNew: op === "new",
            isEdit: op === "edit",
            isLoopRemove: op === "loopRemove",
            interval: interval,
            status: evt.status,
            title: evt.title,
            color: evt.color
        });

        $content.bindEvents({
            // 删除日程
            "click [data-cal='remove']": function() {
                // 如果是周期性日程，则提供多种删除方式
                if (isLoop) {
                    _this.showDialog(evt, jsEvt, view, "loopRemove");
                } else {
                    _this.removeEvt(evt, view);
                    dialog.close();
                }
            },
            // 完成、未完成
            "click [data-cal='finish']": function() {
                _this.finishEvt(evt, view, function(res) {
                    _this.showDialog(evt, jsEvt, view);
                });
            },
            // 编辑日程
            "click .cal-dl-content-body": function() {
                _this.showDialog(evt, jsEvt, view, "edit");
            },
            // 删除周期性日程
            "click [data-cal='removeLoop']": function() {
                var loopType = $.attr(this, "data-loop");
                _this.removeEvt(evt, view, loopType);
                dialog.close();
            },
            "click [data-cal='returnEdit']": function() {
                _this.showDialog(evt, jsEvt, view);
            },
            // 新建日程、编辑日程
            "click [data-cal='save']": function() {
                var title = $("textarea", $content).val();
                var color = $(".cal-dl-colorpicker", $content).attr("data-color");
                if ($.trim(title) === "") {
                    Ui.tip(U.lang("CAL.PLEASE_INPUT_EVENT"), "warning");
                    return false;
                }
                evt.title = title;
                evt.color = color || _this.themes[0];
                evt.category = $.inArray(color, _this.themes);

                _this[evt.id ? "saveEvt" : "createEvt"](evt, view, function() {
                    dialog.close();
                });
            }
        });

        Ui.closeDialog("d_calendar_edit");
        dialog = Ui.dialog({
            id: "d_calendar_edit",
            title: false,
            content: $content[0],
            init: function() {
                var _this = this,
                    wrap = _this.DOM.wrap;

                // 可编辑时，初始化选色器，焦点聚到textarea
                if (op === "new" || op === "edit") {
                    var $picker = $(".cal-dl-colorpicker", $content);
                    $picker.colorPicker({
                        data: Cld.themes,
                        onPick: function(hex) {
                            $picker.css("background-color", hex).attr("data-color", hex);
                        }
                    });
                    $("textarea", $content).focus();
                }

                wrap.offset({
                    top: jsEvt.pageY - wrap.outerHeight() - 5,
                    left: jsEvt.pageX - wrap.outerWidth() / 2
                });

                $(document).off("mousedown.cal").on("mousedown.cal", function(e) {
                    // 点击在选色器范围内
                    if ($(e.target).closest("#jquery-colour-picker").length) {
                        return false;
                    }
                    if (!$.contains(wrap[0], e.target)) {
                        _this.close();
                    }
                });
            },
            focus: false,
            resize: false,
            padding: "15px"
        });
    }
};

var Cldm = {
    /**
     * 获取所有日期任务
     * @method getAll
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    getAll: function(param) {
        var mom = new moment();
        param = $.extend({
            timezone: mom.zone() / -60,
            uid: Ibos.app.g("calSettings").uid
        }, param);
        return $.post(Ibos.app.url("calendar/schedule/index", {
            "op": "list"
        }), param, $.noop, "json");
    },
    /**
     * 添加日期任务
     * @method add
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    add: function(param, callback) {
        param = $.extend({
            timezone: (new moment).zone() / -60,
            Category: -1,
            IsAllDayEvent: 0,
            uid: Ibos.app.g("calSettings").uid
        }, param);

        return $.post(Ibos.app.url("calendar/schedule/add"), param, $.noop, "json");
    },
    /**
     * 更新日期任务
     * @method update
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    update: function(param) {
        param = $.extend({
            timezone: (new moment).zone() / -60,
            Category: -1,
            uid: Ibos.app.g("calSettings").uid
        }, param);
        return $.post(Ibos.app.url("calendar/schedule/edit"), param, $.noop, "json");
    },
    /**
     * 删除日期任务
     * @method remove
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    remove: function(param) {
        return $.post(Ibos.app.url("calendar/schedule/del"), $.extend({
            uid: Ibos.app.g("calSettings").uid
        }, param), $.noop, "json");
    },
    /**
     * 结束日期任务
     * @method finish
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    finish: function(param, callback) {
        return $.post(Ibos.app.url("calendar/schedule/edit", {
            "op": "finish"
        }), $.extend({
            uid: Ibos.app.g("calSettings").uid
        }, param), $.noop, "json");
    },
    /**
     * 未结束日期任务
     * @method unfinish
     * @param  {Object} param 传入JSON格式数据
     * @param  {Object}       返回deffered对象
     */
    unfinish: function(param) {
        return $.post(Ibos.app.url("calendar/schedule/edit", {
            "op": "nofinish"
        }), $.extend({
            uid: Ibos.app.g("calSettings").uid
        }, param), $.noop, "json");
    },
    /**
     * 解析事件数据
     * @method parseEvtData
     * @param  {Object} evts 日期任务
     * @return {Object}      返回继承的参数
     */
    parseEvtData: function(evts) {
        return $.map(evts, function(evt) {
            return $.extend(evt, {
                allDay: evt.allDay == "1", // 是否全天日程
                acrossDay: evt.acrossDay == "1", // 是否跨天日程
                color: Cld.themes[(evt.category != "-1" ? evt.category : 0)], // 主题
                editable: evt.editable == "1", // 是否可编辑
                className: evt.status == "1" ? "fc-event-finish" : "",
            });
        });
    }
};

var op = {
    /**
     * 获取日期数组
     * @method getCalendarArray
     * @param  {Object}   param      传入JSON格式数据
     * @param  {Function} [callback] 回调函数
     */
    getCalendarArray: function(param, callback) {
        Cldm.getAll({
            startDate: param.start,
            endDate: param.end,
            viewtype: param.type
        }).done(function(res) {
            Ibos.app.s("calendarArray", res.events);
            var calendarArray = Cldm.parseEvtData(res.events);
            callback(calendarArray);
        });
    },
    /**
     * 重置日期数组
     * @method resetCalendarArray
     * @param  {Array} array 日期数组
     * @return {Array}       日期数组
     */
    resetCalendarArray: function(array) {
        var arryLength = array.length,
            resetArray = [],
            data = {};
        for (var i = 0; i < arryLength; i++) {
            var start = new moment(array[i].start).format("HH:MM"),
                end = new moment(array[i].end).format("HH:MM"),
                day = new moment(array[i].start).format("DD"),
                week = new moment(array[i].start).format("dddd"),
                yearAndMonth = new moment(array[i].start).format("YYYY-MM");
            data[i] = $.extend({}, array[i], {
                day: day,
                week: week,
                yearAndMonth: yearAndMonth,
                start: start,
                end: end
            });
            resetArray.push(data[i]);
        }
        return resetArray;
    },
    /**
     * 获取日期任务
     * @method getEvt
     * @param  {String} id 任务ID
     * @return {String}    任务ID
     */
    getEvt: function(id) {
        var evtArr = Ibos.app.g("calendarArray");
        if (evtArr && evtArr.length) {
            return $.grep(evtArr, function(e) {
                return e.id == id;
            })[0];
        }
    }
};

var calendar = {
    /**
     * 获取新的模板
     * @method getNewTmpl
     * @param  {Object} $elem jquery节点对象
     * @param  {String} start 开始时间
     * @param  {String} end   结束时间
     * @param  {String} type  类型
     * @param  {boolean} iskeep 是否保持当前日期
     * @return {String}       模板
     */
    getNewTmpl: function($elem, start, end, type, iskeep) {
        if (!iskeep) {
            if (type == "add") {
                var startDay = moment(start).add(1, "month").format("YYYY-MM-DD"),
                    endDay = moment(end).add(1, "month").format("YYYY-MM-DD");
            } else if (type == "subtract") {
                var startDay = moment(start).subtract(1, "month").format("YYYY-MM-DD"),
                    endDay = moment(end).subtract(1, "month").format("YYYY-MM-DD");
            }
        }

        var param = {
                start: startDay || start,
                end: endDay || end,
                type: "month"
            },
            calendarData;

        op.getCalendarArray(param, function(res) {
            calendarData = res;
            dataArray = op.resetCalendarArray(calendarData);
            var dataHtml = $.template("tpl_calender_list", {
                dataArray: dataArray
            });
            $(".cal-content").html(dataHtml);
            if (!dataArray.length) {
                var $noDataTip = "<div class='no-data-tip'></div>";
                $(".calendar-list").after($noDataTip);
            }
        });
        return param;
    }
};

$(function() {
    // 设置日期参数
    var settings = {
        header: {
            left: "today hehe",
            center: "prev title next",
            right: "agendaDay,agendaWeek,month"
        },
        hiddenDays: Ibos.app.g("calSettings").hiddenDays,
        defaultView: U.getCookie("schedule_state") ? U.getCookie("schedule_state") : "agendaWeek", //日历初始化时默认视图 /agendaWeek（周视图/ 获取cookie的schedule_state来初始化视图
        timezone: "local",
        scrollTime: "07:00:00",
        allDayText: "", //定义日历上方显示全天信息的文本
        lang: "zh-cn",
        axisFormat: "HH:mm", //设置日历agenda视图下左侧的时间显示格式，默认显示如：5:30pm
        editable: Ibos.app.g("calSettings").editable, //是否可编辑，即进行可拖动和缩放操作。
        selectable: Ibos.app.g("calSettings").addable, //是否允许用户通过单击或拖动选择日历中的对象，包括天和时间。
        select: function(smom, emom, jsEvt, view) {
            // 选择区域跨的天数
            var days = emom.diff(smom, "day");
            Cld.showDialog({
                start: smom,
                end: emom,
                acrossDay: days > 1,
                allDay: days >= 1
            }, jsEvt, view, "new");
        },
        eventClick: function(evt, jsEvt, view) {
            var editable = typeof evt.editable !== "undefined" ? evt.editable : Ibos.app.g("calSettings").editable;
            if (editable) {
                Cld.showDialog(evt, jsEvt, view);
            }
        },
        eventDragStart: function(evt, jsEvt, ui, view) {
            Cld.saveEvtPrevTime(evt);
        },
        // revertFunc调用时，撤销修改
        eventDrop: function(evt, revertFunc, jsEvt, ui, view) {
            Cld.updateEvt(evt);
        },
        eventResizeStart: function(evt, jsEvt, ui, view) {
            Cld.saveEvtPrevTime(evt);
        },
        eventResize: function(evt, revertFunc, jsEvt, ui, view) {
            Cld.updateEvt(evt);
        },
        eventMouseover: function(evt, jsEvt, view) {
            Cld.showDetailMenu(evt, jsEvt);
        },
        eventMouseout: function(evt, jsEvt, view) {
            Cld.hideDetailMenu(evt, jsEvt);
        },
        events: function(start, end, timezone, callback) {
            var viewTypeMap = {
                "agendaDay": "day",
                "agendaWeek": "week",
                "month": "month"
            };

            Cldm.getAll({
                startDate: start.format(Cld.DATE_FORMAT),
                endDate: end.format(Cld.DATE_FORMAT),
                viewtype: viewTypeMap[this.getView().name],
            }).done(function(res) {
                var data = Cldm.parseEvtData(res.events);
                callback(data);
            });
        }
    };
    if (Ibos.app.g("calSettings").minTime && Ibos.app.g("calSettings").minTime != 0) {
        settings.minTime = moment([1970, 0, 1, Ibos.app.g("calSettings").minTime, 0, 0]).format("HH:mm:ss");
    }
    if (Ibos.app.g("calSettings").maxTime && Ibos.app.g("calSettings").maxTime != 24) {
        settings.maxTime = moment([1970, 0, 1, Ibos.app.g("calSettings").maxTime, 0, 0]).format("HH:mm:ss");
    }

    $("#calendar").fullCalendar(settings);

    var settingBtn = $('<button class="btn">' + U.lang("SETTING") + '</button>');
    settingBtn.on("click", function() {
        var setupUrl = Ibos.app.url("calendar/schedule/edit", {
            op: "setup",
            random: Math.random()
        });
        var dialog = Ui.dialog({
            id: "d_cal_setup",
            title: U.lang("CAL.SET_INTERVAL"),
            ok: function() {
                var $form = this.DOM.content.find("form"),
                    res = $form.serializeArray(),
                    interval,
                    hiddenDays = [],
                    viewuid = "",
                    edituid = "";

                $.each(res, function(i, v) {
                    switch (v.name) {
                        case "calviewinterval":
                            interval = v.value.split(",");
                            break;
                        case "calviewhiddenday":
                            hiddenDays.push(+v.value);
                            break;
                        case "viewuid":
                            viewuid = v.value;
                            break;
                        case "edituid":
                            edituid = v.value;
                            break;
                    }
                });
                $.post(setupUrl, {
                    formhash: Ibos.app.g("FORMHASH"),
                    interval: interval,
                    hiddenDays: hiddenDays,
                    viewuid: viewuid,
                    edituid: edituid
                }, function() {
                    var ins = $("#calendar").data("fullCalendar");
                    var view = ins.getView();
                    name = view.name;
                    ins.options.hiddenDays = hiddenDays;
                    ins.options.minTime = Ibos.date.numberToTime(+interval[0]);
                    ins.options.maxTime = Ibos.date.numberToTime(+interval[1]);
                    view.name = "";
                    ins.changeView(name);
                    Ui.tip("@OPERATION_SUCCESS");
                });
            },
            cancel: true,
            padding: "20px"
        });
        $.get(setupUrl, function(res) {
            if (res.isSuccess) {
                dialog.content(res.view);
            }
        });
    });
    // 设置只在个人页面显示
    if (Ibos.app.g('curPage') === 'Index') {
        $("#calendar .fc-header-left").append(settingBtn);
    }

    var $listButton = $("<button class='btn cld-list-btn'><i class='o-cld-list'></i></button>");
    $listButton.on("click", function() {
        //将日程控件的内容隐藏，显示日程列表
        $(".fc-view").children().hide();
        $(".fc-view").append("<div class='cal-content'></div>");
        $(".fc-header-center").hide();
        $(".fc-button").removeClass("fc-state-active");

        var nowDate = new moment(),
            startDay = nowDate.date(1).format("YYYY-MM-DD"),
            endDay = nowDate.date(1).add(1, "month").subtract(1, "day").format("YYYY-MM-DD"),
            param = {
                start: startDay,
                end: endDay,
                type: "month"
            },
            calendarData = op.getCalendarArray(param, function(res) {
                calendarData = res;
                var dataArray = op.resetCalendarArray(calendarData),
                    $calendarList = $.tmpl("tpl_calender_list", {
                        dataArray: dataArray
                    });
                $(".cal-content").append($calendarList);
                if (!res.length) {
                    var $noDataTip = "<div class='no-data-tip'></div>";
                    $(".calendar-list").after($noDataTip);
                }
            });

        //设置时间范围
        $("#time_range").attr({
            "data-start": startDay,
            "data-end": endDay
        });
    });

    $("#calendar .fc-header-right").append($listButton);

    //点击日程类型(日，周，月)时，显示范围选择按钮
    $(".fc-button").on("click", function() {
        var state = this.className.split(" ")[1].substr(10);
        if ($.inArray(state, settings.header.right.split(",")) >= 0) {
            U.setCookie("schedule_state", state);
        }
        $(".fc-header-center").show();
        $(".fc-view").children().show();
        $(".cal-content").hide();
        $("#calendar").data("fullCalendar").refetchEvents();
    });

    Ibos.evt.add({
        //切换上下月操作
        "changeTime": function(param, elem) {
            var $elem = $(elem),
                $range = $("#time_range"),
                start = moment($range.attr("data-start")),
                type = $elem.attr("data-type"),
                toStart, toEnd, param;

            if (type === 'add') {
                toStart = start.add(1, "month").format('YYYY-MM-DD');
            } else if (type === 'subtract') {
                toStart = start.subtract(1, "month").format('YYYY-MM-DD');
            }

            toEnd = moment(toStart).add(1, "month").subtract(1, 'day').format('YYYY-MM-DD');
            param = calendar.getNewTmpl($elem, toStart, toEnd, type, true);

            $range.attr({
                "data-start": param.start,
                "data-end": param.end
            });
        },
        //删除日程操作
        "deleteCal": function(param, elem) {
            var $elem = $(elem),
                id = $elem.attr("data-id"),
                evt = op.getEvt(id),
                $range = $("#time_range"),
                start = $range.attr("data-start"),
                end = $range.attr("data-end"),
                param = Cld._parseDataToParam(evt),
                liLength = $(".calendar-list li").length - 1;

            Cldm.remove(param).done(function(res) {
                if (res.isSuccess) {
                    //重置存储的全局数据
                    Cldm.getAll({
                        startDate: start,
                        endDate: end,
                        viewtype: "month"
                    }).done(function(res) {
                        Ibos.app.s("calendarArray", res.events);
                    });

                    //改变视图
                    var $calLi = $elem.parents("li"),
                        $nextLi = $calLi.next();
                    if ($calLi.hasClass("diff-day") && $nextLi.hasClass("same-day")) {
                        $nextLi.removeClass("same-day").addClass("diff-day");
                    }
                    $calLi.remove();

                    if (!liLength) {
                        var $noDataTip = "<div class='no-data-tip'></div>";
                        $(".calendar-list").after($noDataTip);
                    }

                    Ui.tip("@OPERATION_SUCCESS");
                }
            });
        },
        //点击完成操作
        "finishCal": function(param, elem) {
            var $elem = $(elem),
                id = $elem.attr("data-id"),
                evt = op.getEvt(id),
                $range = $("#time_range"),
                start = $range.attr("data-start"),
                end = $range.attr("data-end"),
                param = Cld._parseDataToParam(evt);

            Cldm.finish(param).done(function(res) {
                if (res.isSuccess) {
                    //重置存储的全局数据
                    Cldm.getAll({
                        startDate: start,
                        endDate: end,
                        viewtype: "month"
                    }).done(function(res) {
                        Ibos.app.s("calendarArray", res.events);
                    });

                    //改变视图
                    $elem.attr("data-action", "unfinishCal")
                        .removeClass("o-ok").addClass("o-finish")
                        .parents("li").eq(0).find(".fc-title").addClass("cal-finish");

                    Ui.tip("@OPERATION_SUCCESS");
                }
            });

        },
        //取消完成操作
        "unfinishCal": function(param, elem) {
            var $elem = $(elem),
                id = $elem.attr("data-id"),
                evt = op.getEvt(id),
                $range = $("#time_range"),
                start = $range.attr("data-start"),
                end = $range.attr("data-end"),
                param = Cld._parseDataToParam(evt);
            Cldm.unfinish(param).done(function(res) {
                if (res.isSuccess) {
                    //重置存储的全局数据
                    Cldm.getAll({
                        startDate: start,
                        endDate: end,
                        viewtype: "month"
                    }).done(function(res) {
                        Ibos.app.s("calendarArray", res.events);
                    });

                    //改变视图
                    $elem.attr("data-action", "finishCal")
                        .removeClass("o-finish").addClass("o-ok")
                        .parents("li").eq(0).find(".fc-title").removeClass("cal-finish");

                    Ui.tip("@OPERATION_SUCCESS");
                }
            });
        }
    });
});