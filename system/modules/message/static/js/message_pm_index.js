var PmIndex = {
    op: {
        /**
         * 阅读私信
         * @method readPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        readPm: function(param) {
            var url = Ibos.app.url('message/pm/setisread');
            return $.get(url, param, $.noop, "json");
        },
        /**
         * 发送私信
         * @method sendPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        sendPm: function(param) {
            var url = Ibos.app.url('message/pm/post');
            return $.post(url, param, $.noop, "json");
        },
        /**
         * 删除私信
         * @method delPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        delPm: function(param) {
            var url = Ibos.app.url('message/pm/delete');
            return $.post(url, param, $.noop, "json");
        },
        /**
         * 标记所有私信
         * @method markAllPmRead
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        markAllPmRead: function(param) {
            var url = Ibos.app.url('message/pm/setallread');
            return $.get(url, param, $.noop, "json");
        }
    },
    /**
     * 检查私信提交
     * @method checkPmForm
     */
    checkPmForm: function() {
        if ($.trim($('#to_uid').val()) === '') {
            Ui.tip(Ibos.l('RECEIVER_CANNOT_BE_EMPTY'), 'danger');
            return false;
        }
        if ($.trim($('#to_message').val()) === '') {
            Ui.tip(Ibos.l('CONTENT_CANNOT_BE_EMPTY'), 'danger');
            return false;
        }
        return true;
    },

    /**
     * 发送私信
     * @method sendPm
     */
    sendPm: function() {
        var param = $('#pm_form').serializeArray();
        PmIndex.op.sendPm(param).done(function(res) {
            if (res.IsSuccess) {
                document.getElementById('pm_form').reset();
                $("#to_uid").userSelect("removeValue");
            }
            Ui.tip(res.data, res.IsSuccess ? "" : "danger");
        });
    },

    /**
     * 删除
     * @method delPm
     * @param {type} ids 传入删除私信的IDs
     */
    delPm: function(ids) {
        Ui.confirm(Ibos.l("MESSAGE.DELETE_MESSAGE_OPERATE_NOT_RESTORE"), function() {
            var param = {
                id: ids
            };
            PmIndex.op.delPm(param).done(function(data) {
                if (data.IsSuccess) {
                    $.each(ids.split(','), function(n, i) {
                        $('#pm_' + i).fadeOut(function() {
                            $(this).remove();
                        });
                    });
                    Ui.tip(Ibos.l('DELETE_SUCCESS'));
                } else {
                    Ui.tip(Ibos.l('DELETE_FAILED'), 'danger');
                }
            });
        });
    },
    /**
     * 阅读私信
     * @method readPm
     * @param  {String} ids 传入阅读私信的IDs
     */
    readPm: function(ids) {
        var param = {
            id: ids
        };
        PmIndex.op.readPm(param).done(function(data) {
            if (data.IsSuccess) {
                $.each(ids.split(','), function(n, i) {
                    $('#pm_' + i + ' span.bubble').remove();
                });
                Ui.tip(Ibos.l('OPERATION_SUCCESS'));
            } else {
                Ui.tip(Ibos.l('OPERATION_FAILED'), 'danger');
            }
        });
    }
};

$(function() {
    // 跳转私信详情
    $(document).on("click", ".main-list-item", function(evt) {
            location.href = Ibos.app.url('message/pm/detail') + '&' + $.attr(this, "data-url");
        })
        .on("click", ".main-list-item .checkbox, .main-list-item a", function(evt) {
            evt.stopPropagation();
        });

    // 用户数据，过滤掉自己
    var userData = Ibos.data.get('user', function(userData) {
        return userData.id !== Ibos.app.g('dataUid');
    });
    $("#to_uid").userSelect({
        type: "user",
        maximumSelectionSize: "1",
        data: userData
    });
    // Ctrl + enter 发送，
    $("#to_message").on("keydown", function(evt) {
        if (evt.ctrlKey && evt.which === 13) {
            if (PmIndex.checkPmForm()) {
                PmIndex.sendPm();
                $(this).blur();
            }
        }
    });

    Ibos.evt.add({
        // 标识已读
        'markPmsRead': function() {
            // 标识已读
            var ids = U.getCheckedValue("pm");
            if (ids) {
                PmIndex.readPm(ids);
            } else {
                Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
            }
        },
        // 标识所有已读
        "markAllPmRead": function() {
            PmIndex.op.markAllPmRead(null).done(function(data) {
                if (data.IsSuccess) {
                    $('#pm_list span.bubble').remove();
                    $('.band-primary').remove();
                    Ui.tip(Ibos.l("SAVE_SUCCESS"));
                } else {
                    Ui.tip(Ibos.l("OPERATION_FAILED"), 'danger');
                }
            });
        },
        // 删除私信
        "removePm": function(param, elem, evt) {
            PmIndex.delPm(param.id);
            evt.stopPropagation();
        },
        // 批量删除
        "removePms": function() {
            // 标识已读
            var ids = U.getCheckedValue("pm");
            if (ids) {
                PmIndex.delPm(ids);
            } else {
                Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
            }
        },
        // 发送私信
        "sendPm": function() {
            Ui.dialog({
                title: Ibos.l("SEND_PM"),
                content: document.getElementById('pm_message'),
                id: 'pmbox',
                padding: 0,
                lock: true,
                okVal: Ibos.l("SEND"),
                ok: function() {
                    if (PmIndex.checkPmForm()) {
                        PmIndex.sendPm();
                        return true;
                    }
                    return false;
                }
            });
        }
    });
});