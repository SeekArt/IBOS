//access
(function() {
    /**
     * 交叉选择器方法集
     * 使用：手机短信管理
     * @class crossSelect
     */
    var crossSelect = {
        leftSelect: $("#select_left"),
        rightSelect: $("#select_right"),
        /**
         * 左侧选择
         * @property    leftSelect 
         * @type        {Jquery}
         */
        leftSelect: null,
        /**
         * 右侧选择
         * @property    rightSelect 
         * @type        {Jquery}
         */
        rightSelect: null,
        /**
         * 移动到左侧选择器
         * @method moveToLeft
         * @param {Jquery} jelem 要移动的节点
         */
        moveToLeft: function(jelem) {
            this.leftSelect.append(jelem);
        },
        /**
         * 移动到右侧选择器
         * @method moveToLeft
         * @param {Jquery} jelem 要移动的节点
         */
        moveToRight: function(jelem) {
            this.rightSelect.append(jelem);
        },
        /**
         * 将左侧选中的移到到右侧
         * @method leftToRight
         */
        leftToRight: function() {
            var selected = this.leftSelect.find(":selected");
            this.moveToRight(selected);
        },
        /**
         * 将右侧选中的移到到左侧
         * @method rightToLeft
         */
        rightToLeft: function() {
            var selected = this.rightSelect.find(":selected");
            this.moveToLeft(selected);
        },
        /**
         * 左侧全选
         * @method leftSelectAll
         */
        leftSelectAll: function() {
            this.leftSelect.find("option").prop("selected", true);
        },
        /**
         * 右侧全选
         * @method leftSelectAll
         */
        rightSelectAll: function() {
            this.rightSelect.find("option").prop("selected", true);
        }
    };

    if ($.fn.DataTable) {
        var smsTable = (function() {
            var table = $('#sms_manage_table').DataTable($.extend({}, Ibos.settings.dataTable, {
                deferLoading: 0,
                ajax: {
                    url: Ibos.app.url('dashboard/sms/getsmsmanagerlist'),
                    type: 'post'
                },
                initComplete: function() {
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
                            return '<label class="checkbox"><input type="checkbox" name="sms[]" value="' + row.id + '"/></label>';
                        }
                    },
                    //用户
                    {
                        "data": "fromname",
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<span class="fss">' + row.fromname + '</span>';
                        }
                    },
                    //通知用户
                    {
                        "data": "toname",
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<span class="fss">' + row.toname + '</span>';
                        }
                    },
                    //内容
                    {
                        "data": "content",
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<span class="fss">' + row.content + '</span>';
                        }
                    },
                    //结果
                    {
                        "data": "status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<span class="fss">' + (row.status ? '成功' : '失败') + '</span>';
                        }
                    },
                    //发送时间
                    {
                        "data": "sendtime",
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<span class="fss">' + row.sendtime + '</span>';
                        }
                    }
                ]
            }));

            return table;
        })();
    }

    try {
        smsTable.draw();
        smsTable.sms = {
            search: function(param) {
                var url = param ? param : Ibos.app.url('dashboard/sms/getsmsmanagerlist');
                smsTable.ajax.url(url).load();
            }
        };
    } catch (e) {}


    function beforeSubmit() {
        var enabled = new Array();
        $('#select_left').find('option').each(function(i, n) {
            enabled.push($(n).val());
        });
        $('#enabled_module').val(enabled.join(','));
    };

    $("#toLeftBtn").on("click", function() {
        crossSelect.rightToLeft();
    });
    $("#toRightBtn").on("click", function() {
        crossSelect.leftToRight()
    });
    $("#select_all_left").on("click", function() {
        crossSelect.leftSelectAll()
    });
    $("#select_all_right").on("click", function() {
        crossSelect.rightSelectAll();
    });
    $("#select_left").on('dblclick', 'option', function() {
        crossSelect.moveToRight(this);
    });
    $("#select_right").on('dblclick', 'option', function() {
        crossSelect.moveToLeft(this);
    });
    //setup
    $("#sms_enable").on("change", function() {
        $("#sms_setup").toggle();
    });

    //manager
    $("#date_start").datepicker({ target: $("#date_end") });
    $('#sender').userSelect({
        data: Ibos.data.get("user"),
        type: "user",
        maximumSelectionSize: 1
    });

    function removeRows(ids) {
        var arr = ids.split(',');
        for (var i = 0, len = arr.length; i < len; i++) {
            $('#list_tr_' + arr[i]).remove();
        }
    }
    $('#exportsms').on('click', function() {
        var val = U.getCheckedValue('sms[]');
        if ($.trim(val) !== '') {
            /*var url = '<?php echo $this->createUrl( 'sms/export' ); ?>';
            url += '&id=' + val;*/
            var url = Ibos.app.url("dashboard/sms/export", { "id": val });
            window.location.href = url;
        } else {
            Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
        }
    });
    $('#delsms').on('click', function() {
        var val = U.getCheckedValue('sms[]');
        if ($.trim(val) !== '') {
            Ui.confirm(Ibos.l("SMS.SMS_DEL_CONFIRM"), function() {
                // var url = '<?php echo $this->createUrl( 'sms/del' ); ?>';
                var url = Ibos.app.url("dashboard/sms/del");
                $.get(url, { id: val }, function(data) {
                    if (data.isSuccess) {
                        try {
                            smsTable.draw();
                        } catch (e) {}
                        Ui.tip(U.lang("DELETE_SUCCESS"));
                    } else {
                        Ui.tip(U.lang("DELETE_FAILED"), 'danger');
                    }
                }, 'json');
            });
        } else {
            Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
        }
    });

    var smsSearchDialog = Dom.byId('d_sms_search'),
        smsSearchDialogOptions = {
            title: Ibos.l("SMS.SMS_ADVANCED_SEARCH"),
            content: smsSearchDialog,
            width: 500,
            ok: function() {
                $('#type').val('');
                $('#select_type').find('a').each(function() {
                    var id = $(this).data('id');
                    if ($(this).hasClass('active')) {
                        $('#type').val($('#type').val() + id);
                    }
                });
                var url = Ibos.app.url("dashboard/sms/getsmsmanagerlist", { "type": "search" });
                var param = $('#d_sms_search_form').serializeArray();
                try {
                    smsTable.sms.search(url + '&' + $.param(param));
                    // 高级搜索完成后配置回普通搜索路径
                    smsTable.ajax.url(Ibos.app.url('dashboard/sms/getsmsmanagerlist'));
                } catch (e) {}
                return true;
            },
            cancel: true
        };

    $("#sms_search").search(function(val) {
        try {
            smsTable.search(val).draw();
        } catch (e) {}
    }, function() {
        $.artDialog(smsSearchDialogOptions);
    });
})();
