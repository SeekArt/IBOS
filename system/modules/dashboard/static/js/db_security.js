$(function() {
    //ip
    (function() {
        var ipRecTbody = $("#ip_rec_tbody"),
            addOneItem = function(tpl) {
                ipRecTbody.append(tpl);
            };
        // 增加项
        $("#add_ip_rec").on("click", function() {
            var d = new Date(),
                ipRecTpl = $.template('ip_rec_template', { id: d.getTime() });
            addOneItem(ipRecTpl);
        });
        // 删除项
        $('#ip_rec_table').on("click", ".o-trash", function() {
            $(this).parents("tr").first().remove();
        });
        // 删除选中
        $('[data-act="del"]').on('click', function() {
            var id = '';
            $('[data-check="ip"]:checked').each(function() {
                id += this.value + ',';
            });
            if (id !== '') {
                $('#form_act').val('del');
                $('#sys_security_form').submit();
            } else {
                $.jGrowl(Ibos.l("DATABASE.AT_LEAST_ONE_RECORD"), { theme: 'error' });
                return false;
            }
        });
        // 清空
        $('[data-act="clear"]').on('click', function() {
            $('#form_act').val('clear');
            $('#sys_security_form').submit();
        });
    })();

    // table
    (function() {
        if ($.fn.DataTable) {
            var admincp = (function() {
                    var table = $('#table_admincp').DataTable($.extend({}, Ibos.settings.dataTable, {
                        deferLoading: 0,
                        ajax: {
                            url: Ibos.app.url('dashboard/security/getadmincploglist'),
                            type: 'post',
                            // 自定义数据
                            data: function(oaData) {
                                oaData.highSearch = {
                                    starttime: $('#start_time').val(),
                                    endtime: $('#end_time').val(),
                                    timescope: $('#time_scope').val(),
                                    filteract: $('#actions').val()
                                };
                            }
                        },
                        order: [],
                        columns: [
                            //操作者
                            {
                                "data": "user",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.user + '</span>';
                                }
                            },
                            //ip
                            {
                                "data": "ip",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.ip + '</span>';
                                }
                            },
                            //时间
                            {
                                "data": "logtime",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    return '<span class="fss">' + row.logtime + '</span>';
                                }
                            },
                            //动作
                            {
                                "data": "actionDesc",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.action + '</span>';
                                }
                            },
                            //其他
                            {
                                "data": "param",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span title="' + msg.param + '">' + msg.param.substr(0, 100) + '</span>';
                                }
                            }
                        ]
                    }));

                    return table;
                })(),
                login = (function() {
                    var table = $('#table_login').DataTable($.extend({}, Ibos.settings.dataTable, {
                        deferLoading: 0,
                        ajax: {
                            url: Ibos.app.url('dashboard/security/getloginloglist'),
                            type: 'post'
                        },
                        order: [],
                        columns: [
                            //时间
                            {
                                "data": "logtime",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    return '<span class="fss">' + row.logtime + '</span>';
                                }
                            },
                            //ip
                            {
                                "data": "ip",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.ip + '</span>';
                                }
                            },
                            //操作者
                            {
                                "data": "user",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.user + '</span>';
                                }
                            },
                            //登录密码
                            {
                                "data": "password",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.password + '</span>';
                                }
                            },
                            //终端
                            {
                                "data": "terminal",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.terminal + '</span>';
                                }
                            }
                        ]
                    }));

                    return table;
                })(),
                illegal = (function() {
                    var table = $('#table_illegal').DataTable($.extend({}, Ibos.settings.dataTable, {
                        deferLoading: 0,
                        ajax: {
                            url: Ibos.app.url('dashboard/security/getillegalloglist'),
                            type: 'post'
                        },
                        order: [],
                        columns: [
                            //时间
                            {
                                "data": "logtime",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    return '<span class="fss">' + row.logtime + '</span>';
                                }
                            },
                            //ip
                            {
                                "data": "ip",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.ip + '</span>';
                                }
                            },
                            //操作者
                            {
                                "data": "user",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.user + '</span>';
                                }
                            },
                            //密码
                            {
                                "data": "password",
                                "orderable": false,
                                "render": function(data, type, row) {
                                    var msg = JSON.parse(row.message);
                                    return '<span class="fss">' + msg.password + '</span>';
                                }
                            }
                        ]
                    }));

                    return table;
                })();

            return Table = {
                admincp: admincp,
                login: login,
                illegal: illegal
            }
        }
    })();

    try {
        //log
        (function() {
            //日期选择器
            $("#date_start").datepicker({
                format: 'mm-dd',
                target: $("#date_end")
            });

            var level = Ibos.app.g("level");
            var url = Ibos.app.url("dashboard/security/log", { "level": level });
            $('#query_act').on('click', function() {
                Table['admincp'].draw();
            });
            $('#actions').on('change', function() {
                Table['admincp'].draw();
            });
        })();


        //setup
        $("#psw_strength").ibosSlider({
            min: 5,
            max: 32,
            scale: 9,
            range: 'min',
            tip: true,
            tipFormat: function(value) {
                return value + '位'
            },
            target: '#minlength'
        });

        Ibos.evt.add({
            "tableTab": function(param, elem) {
                var type = param.type,
                    mc = $('.page-list-mainer'),
                    main = $('.table-' + type),
                    tip = $('.admincp-tip'),
                    search = $('#admincp_search');

                $(this).addClass("active").siblings().removeClass("active");
                if (type === 'admincp') {
                    tip.slideDown('fast');
                    search.is(':hidden') && search.show();
                } else {
                    tip.slideUp('fast');
                    search.hide();
                }
                mc.children().hide();
                main.show();

                Table[type].draw();
            }
        });

        Table['admincp'].draw();
    } catch (e) {}
})
