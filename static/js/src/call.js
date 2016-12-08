$(function() {
    var Call = {
        formValidator: function(type, param) {
            switch (type) {
                case "unidirec":
                    var phone = param.phone;
                    if (!phone) {
                        Ui.tip("请输入号码或者选人！", "warning");
                        return false;
                    }
                    return true;
                case "bidirec":
                    var myPhone = param.myPhone,
                        otherPhone = param.otherPhone;
                    if (!(myPhone || otherPhone) || !myPhone) {
                        Ui.tip("请输入我的电话号码！", "warning");
                        return false;
                    } else if (!otherPhone) {
                        Ui.tip("请输入对方电话号码！", "warning");
                        return false;
                    }
                    return true;
                case "meeting":
                    var inside = param.inside,
                        outside = param.outside;
                    if (!inside && !outside) {
                        Ui.tip("请选择人员或输入手机号码！", "warning")
                        return false;
                    }

                    var insideArr = inside ? inside.split(",") : [],
                        outsideArr = outside ? outside.split(/;|；|,|，|、| /) : [];

                    if (insideArr.length + outsideArr.length < 2) {
                        Ui.tip("语音会议至少需要 2 位参与人员", "warning");
                        return false;
                    }

                    if (outsideArr.length) {
                        var i = 0,
                            len = outsideArr.length;
                        for (; i < len; i++) {
                            if (!U.regex(outsideArr[i], "mobile") && !U.regex(outsideArr[i], "tel")) {
                                Ui.tip("手机号码格式不正确", "warning");
                                return false;
                            }
                        }
                    }
                    return true;
            }
        },
        ajax: function(ajaxUrl, param, callback) {
            var $callFrom = $("#fun_call_form");
            $callFrom.waiting(null, 'normal', true);

            $.post(ajaxUrl, param, function(res) {
                if (res.isSuccess) {
                    $callFrom.waiting(false);
                    callback && callback(res);
                } else {
                    Ui.tip(res.info, "danger");
                    $callFrom.waiting(false);
                }
            });
        },
        selectMember: function(selectBtn, $selectBox, $initInput) {
            var $selectBtn = $(selectBtn),
                memberBox,
                menu;

            $selectBox.selectBox({
                data: Ibos.data && Ibos.data.get(),
                type: 'user',
                maximumSelectionSize: "1"
            });

            memberBox = $selectBox.data("selectBox");
            $(memberBox).on("slbchange.selectBox", function(evt, data) {
                //通过选人框选人后，重置选人栏
                data.added.length > 0 && $initInput.val(data.added.join(',')).trigger("change");
                data.removed.length > 0 && $initInput.val('').trigger("change");
                menu.show();
            });
            menu = new Ui.PopMenu($selectBtn, $selectBox, {
                trigger: "click",
                position: '', //复写position
                zIndex: 1999
            });
            $(document).on("mousedown", function() {
                menu.hide();
            })
        },
        resetSelect: function($wrap) {
            var $initSelect = $wrap.find("[data-init='select']"),
                $hideInput = $wrap.find("[data-input='hideVal']");
            $hideInput.val("").trigger("change");
            $initSelect.ibosSelect("val", "");
        },
        byPhoneDial: function(list, $select) {
            var $list = $("#" + list),
                $input = $list.parent().children("input");

            $list.off("click.dial").on("click.dial", "li:not(.del-number-btn, .dial-toggle-btn)", function() {
                var clickVal = $(this).children().attr("data-value"),
                    val = $input.val();
                number = val + clickVal;
                if (number.length <= 11) {
                    $select.val(number).trigger('change');
                    $input.val(number);
                } else {
                    Ui.tip("电话号码长度超过11位！", "danger");
                }
            });
        },
        delInputNumber: function($storeNumInpt, $select) {
            var val = $storeNumInpt.val(),
                selectVal = $select.val(),
                length = val.length,
                delVal = val.substr(0, length - 1),
                isNum = U.reg.positiveInt.test(selectVal);

            if (!isNum) {
                $storeNumInpt.val("").trigger("change");
                $select.val("").trigger("change");
            } else {
                $storeNumInpt.val(delVal).trigger("change");
                $select.val(delVal).trigger("change");
            }
        },
        formatUserInfo: function(param) {
            // 内部人员，即传进来的参数为 uid 时。
            if (param.indexOf("u") == 0) {
                var arr = param.split(",");
                var data = $.map(arr, function(uid) {
                    var data = Ibos.data.getUser(uid);
                    return {
                        uid: uid.slice(2),
                        name: data.text,
                        avatar: data.avatar,
                        phone: data.phone
                    }
                });
                return data;
                // 外部人员, 为使用符号分隔的多个电话号码
            } else {
                var arr = param.split(/[;；,，、\s]/);
                var avatar = Ibos.app.g("emptyAvatar");
                var data = $.map(arr, function(phone) {
                    return {
                        name: "未知",
                        avatar: avatar,
                        phone: phone
                    }
                });
                return data;
            }
        }
    }

    /**
     * 打开通话对话框并连接作为参数的 uid 和号码
     * @method connect
     * @param {Array}  param 格式如["u_1,u_2", "13040506070,13041516171"...]
     *   数组中每个元素中包括的信息必须是同一类型
     *   uid为一组， phone为一组
     * @param {String} [mode=unidirec]  通话模式 unidirec, bidirec, meeting 分别对应单拨、双拨、会议三种模式
     * @return {Object} jquery promise
     */
    Call.connect = function(param, mode) {
        var promise = $.get(Ibos.app.url('main/call/chkConf'), function(res) {
            if (res.isSuccess) {
                var info = [];

                if (param.length) {
                    $.each(param, function(i, p) {
                        info = info.concat(Call.formatUserInfo(p));
                    });
                }

                // 判断是单向通话还是双、多向通话
                var routes = {
                    "unidirec": 'main/call/unidirec',
                    "bidirec": 'main/call/bilateral&op=bidirec',
                    "meeting": 'main/call/bilateral'
                };

                mode = routes.hasOwnProperty(mode) ? mode : "unidirec";
                // 根据类型拼接出完整路径
                var url = Ibos.app.url(routes[mode]);
                //新窗打开通话中界面
                Ui.openFrame(url, {
                    width: '580px',
                    height: '523px',
                    title: false,
                    lock: true,
                    top: "52%",
                    left: "50%",
                    skin: "call-dialog",
                    init: function() {
                        var tpl = "<form action='" + url + "' method='post' target='Open" + this.config.id + "'>" +
                            "<% for(var i=0; i<data.length; i++){ %>" +
                            "<input type='hidden' name='data[<%= i %>][uid]' value='<%= data[i].uid %>' />" +
                            "<input type='hidden' name='data[<%= i %>][name]' value='<%= data[i].name %>' />" +
                            "<input type='hidden' name='data[<%= i %>][avatar]' value='<%= data[i].avatar %>' />" +
                            "<input type='hidden' name='data[<%= i %>][phone]' value='<%= data[i].phone %>' />" +
                            "<% } %>" +
                            "</form>",
                            $form = $.tmpl(tpl, { data: info });
                        this.DOM.content.append($form[0]);

                        $form.submit();
                    }
                });

            } else {
                Ui.tip(res.msg, 'warning');
            }
        }, 'json');

        return promise;
    };

    Ibos.Call = Call;
});
