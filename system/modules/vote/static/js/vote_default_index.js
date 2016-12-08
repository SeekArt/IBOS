var VoteIndex = {
    // 接口
    op: {
        /**
         * 删除投票
         * [removeVotes description]
         * @param  {[type]} ids [description]
         * @return {[type]}     [description]
         */
        removeVotes: function(ids){
            var data = VoteIndex.processData(ids);
            return $.post(Ibos.app.url("vote/form/del"), data, $.noop, "json");
        },
        /**
         * 调整时间
         * [adjustTime description]
         * @param  {[type]} data [description]
         * @return {[type]}      [description]
         */
        adjustTime: function(data){
            return $.post(Ibos.app.url("vote/form/updateendtime"), data, $.noop, "json");
        },
        /**
         * 获取未读的数据
         * [getUnread description]
         * @param  {[type]} data [description]
         * @return {[type]}      [description]
         */
        getUnread: function(data){
            return $.get(Ibos.app.url("vote/message/unread"), data, $.noop, "json");
        }
    },
    /**
     * 高级查询
     * [highSearch description]
     * @return {[type]} [description]
     */
    highSearch: function(){
        $("#mn_search").search(function(val) {
            VoteIndex.tabConfig.search(val);
        }, function() {
            Ui.dialog({
                id: "d_advance_search",
                title: U.lang("ADVANCED_SETTING"),
                content: document.getElementById("search_tpl").innerHTML,
                cancel: true,
                padding: 20,
                init: function() {
                    // // 初始化日期选择
                    $("#vot_start_date").datepicker({ 
                        target: $("#vot_end_date"),
                        format: "yyyy-mm-dd hh:ii",
                        pickTime: true,
                        pickSeconds: false
                    });
                    $("#publishScope").userSelect({
                        data: Ibos.data.get("user")
                    });
                },
                ok: function() {
                    var form = this.DOM.content.find("form"),
                        param = form.serializeArray();

                    VoteIndex.tabConfig.ajaxSearch(U.serializedToObject(param));
                }
            });
        });
    },
    /**
     * 处理删除的接收参数
     * [processData description]
     * @param  {[type]} ids [description]
     * @return {[type]}     [description]
     */
    processData: function(ids){
        var idsArr = (ids+"").split(","),
            obj = {};
        for(var i=0; i<idsArr.length; i++){
            obj['vote[voteid]['+ i +']'] = idsArr[i];
        }
        return obj;
    },
    getUnreadNum: function(){
        this.op.getUnread().done(function(res){
            if( res.isSuccess ){
                var unjoined = res.data.vote_unjoined;
                $("#unread_num").text( unjoined ? unjoined : "" );          
            }
        });
    },
    voteid: U.getUrlParam().type || 1
};


VoteIndex.tabConfig =  {
    curModule: 'indexTable', // ['indexTable', 'myTable']
    curType: VoteIndex.voteid, // ['unjoin', 'join', 'all', 'ing', 'finish'] 1 2 3 4 5 6 7 8 9
    search: function(val) {
        var table = VoteIndex[this.curModule],
            param = {
                type: this.curType
            };
        table.ajax.url(Ibos.app.url('vote/default/fetchindexlist', param));
        table.search(val);
        table.draw();
    },
    draw: function(bool) {
        var table = VoteIndex[this.curModule];
        VoteIndex.getUnreadNum();
        table.draw(bool);
    },
    ajaxSearch: function(param) {
        var table = VoteIndex[this.curModule];
        param = $.extend({
            type: this.curType
        }, param);

        table.ajax.url(Ibos.app.url('vote/default/fetchindexlist', param)).load();
    }
};
VoteIndex.indexTable = function(){
    return $("#index_table").DataTable($.extend({}, Ibos.settings.dataTable, {
        // --- Data
        // deferLoading: 0, // 每个文件加上这一行
        ajax: {
            url: Ibos.app.url('vote/default/fetchindexlist', {type: VoteIndex.tabConfig.curType}),
            dataSrc: function(res) {
                if (res.isSuccess) {
                    return res.data || [];
                } else {
                    Ui.tip(res.msg, 'warning');
                    return [];
                }
            }
        },
        // --- Callback
        initComplete: function() {
            // Fixed: IE8下表格初始化后需要再次初始化 checkbox，否则触发不了change事件
            $(this).find('[data-name]').label();
        },
        rowCallback: function(row, data) {
            $(row).find("label input[type='checkbox']").label();
        },
        // order: [1, "desc"], // ID 倒序
        // --- Column
        columns: [
            // 复选框
            {
                "data": "voteid",
                "orderable": false,
                "className": 'dt-checkbox-td',
                "render": function(data, type, row) {
                    return '<label class="checkbox"><input type="checkbox" name="vote[]" value="' + row.voteid + '"/></label>';
                }
            },
            // 阅读状态
            {
                "data": "isread",
                "orderable": false,
                "render": function(data, type, row) {
                    return row.isread ? '<i class="o-vote-normal"></i>' : '<i class="o-vote-read"></i>';
                }
            },
            // 标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="ellipsis"><a class="xcm" href="' + Ibos.app.url("vote/default/show", {
                            id: row.voteid,
                            voteid: row.voteid
                        }) + '" title="' + row.subject + '">' + row.subject + '</a></div>';
                }
            },
            // 发布人
            {
                "data": "sponsor",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span  class="xcm" title="' + row.sponsor + '">' + row.sponsor + '</span>';
                }
            },
            // 截止时间
            {
                "data": "endtimestr",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span class="xcn fss">' + row.endtimestr + '</span>';
                }
            },
            // 状态
            {
                "data": "statusstr",
                "orderable": false,
                "render": function(data, type, row) {
                    var _tpl = 
                        '<% if(issponsor && status == 1){ %>'+
                        '<div class="vote-opt-btnbar">' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>, "sponsor": "<%= sponsor %>", "endtimestr": "<%= endtimestr %>", "subject": "<%= subject %>", "sponsorid": "<%= sponsorid %>"}\' title="调整时间" data-action="adjustTime" class="cbtn co-clock"></a>' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>}\' title="编辑" data-action="editVote" class="cbtn o-edit mlm"></a>' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>}\' title="删除" data-action="removeVote" class="cbtn o-trash mlm"></a>' +
                        '</div>' +
                        '<% } %>'+
                        '<span class="fss"><%= statusstr %></span>';
                    return $.template(_tpl, row);
                }
            }
        ]
    }));
};

VoteIndex.myTable = function(){
    return $("#my_table").DataTable($.extend({}, Ibos.settings.dataTable, {
        // --- Data
        // deferLoading: 0, // 每个文件加上这一行
        ajax: {
            url: Ibos.app.url('vote/default/fetchindexlist', {type: VoteIndex.tabConfig.curType}),
            dataSrc: function(res) {
                if (res.isSuccess) {
                    return res.data || [];
                } else {
                    Ui.tip(res.msg, 'warning');
                    return [];
                }
            }
        },
        // --- Callback
        initComplete: function() {
            // Fixed: IE8下表格初始化后需要再次初始化 checkbox，否则触发不了change事件
            $(this).find('[data-name]').label();
        },
        rowCallback: function(row, data) {
            $(row).find("label input[type='checkbox']").label();
        },
        // order: [1, "desc"], // ID 倒序
        // --- Column
        columns: [
            // 复选框
            {
                "data": "voteid",
                "orderable": false,
                "className": 'dt-checkbox-td ',
                "render": function(data, type, row) {
                    return '<label class="checkbox"><input type="checkbox" name="vote[]" value="' + row.voteid + '"/></label>';
                }
            },
            // 阅读状态
            {
                "data": "isread",
                "orderable": false,
                "render": function(data, type, row) {
                    return row.isread ? '<i class="o-vote-normal"></i>' : '<i class="o-vote-read"></i>';
                }
            },
            // 标题
            {
                "data": "subject",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<div class="ellipsis"><a class="xcm" href="' + Ibos.app.url("vote/default/show", {
                            id: row.voteid,
                            voteid: row.voteid
                        }) + '" title="' + row.subject + '">' + row.subject + '</a></div>';
                }
            },
            // 已投票
            {
                "data": "usernum",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span class="xcn">' + row.usernum + '</span>';
                }
            },
            // 截止时间
            {
                "data": "endtimestr",
                "orderable": false,
                "render": function(data, type, row) {
                    return '<span class="xcn  fss">' + row.endtimestr + '</span>';
                }
            },
            // 状态
            {
                "data": "statusstr",
                "orderable": false,
                "render": function(data, type, row) {
                    var _tpl =
                        '<% if(status == 1){ %>'+
                        '<div class="vote-opt-btnbar">' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>, "sponsor": "<%= sponsor %>", "endtimestr": "<%= endtimestr %>", "subject": "<%= subject %>", "sponsorid": "<%= sponsorid %>"}\' title="调整时间" data-action="adjustTime" class="cbtn co-clock"></a>' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>}\' title="编辑" data-action="editVote" class="cbtn o-edit mlm"></a>' +
                        '<a href="javascript:;" data-param=\'{"id": <%= voteid %>}\' title="删除" data-action="removeVote" class="cbtn o-trash mlm"></a>' +
                        '</div>' +
                        '<% } %>'+
                        '<span class="fss"><%= statusstr %></span>';
                    return $.template(_tpl, row);
                }
            }
        ]
    }));
};


$(function() {
    //高级搜索
    VoteIndex.highSearch();

    var tabConfig = VoteIndex.tabConfig;


    if( VoteIndex.voteid == 1 ){
        tabConfig.curModule = "indexTable";
        VoteIndex.indexTable = VoteIndex.indexTable();
        VoteIndex.getUnreadNum();
    }else if( /4|7/.test(VoteIndex.voteid) ){
        tabConfig.curModule = "myTable";
        VoteIndex.myTable = VoteIndex.myTable();
    }

    $("#main").on("shown", showTab);

    function showTab(ev){
        var target = ev.target;
            type = target.getAttribute("data-type");

        tabConfig.curType = type;
        tabConfig.search();
    }

    Ibos.evt.add({
        // 删除一条投票
        "removeVote": function(param, elem) {
            Ui.confirm(Ibos.l("VOTE.SURE_DEL_VOTEICLE"), function() {
                VoteIndex.op.removeVotes(param.id).done(function(res) {
                    res.isSuccess && tabConfig.draw(false);
                    Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                });
            });
        },
        // 删除多条投票
        "removeVotes": function() {
            var ids = U.getCheckedValue("vote[]");
            if( ids ){
                Ui.confirm(Ibos.l("VOTE.SURE_DEL_VOTEICLE"), function() {
                    VoteIndex.op.removeVotes(ids).done(function(res) {
                        res.isSuccess && tabConfig.draw(false);
                        Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                    });
                });
            }else{
                Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
            }
        },
        // 调整时间
        "adjustTime": function(param, elem) {
            param.avatar = Ibos.data.get().user["u_"+ param.sponsorid ].avatar;
            var adjustTime = Ui.dialog({
                id: "art_rollback",
                title: Ibos.l("VOTE.UPDATE_ENDTIME"),
                content: $.template("adjust_dialog_tpl", param),
                cancel: true,
                padding: 0,
                init: function(){
                    $("#end_time").datepicker({ 
                        startDate: new Date(),
                        format: "yyyy-mm-dd hh:ii",
                        pickTime: true,
                        pickSeconds: false
                    });
                },
                ok: function() {
                    var $time = $("#time"),
                        time = $.trim($time.val());
                    if( !time ){
                        Ui.tip(Ibos.l("VOTE.ENDTIME_EMPTY"), "danger");
                        return false;
                    }
                    VoteIndex.op.adjustTime({
                        'vote[voteid]': param.id,
                        'vote[endtime]':  time
                    }).done(function(res) {
                        if( res.isSuccess ){
                            tabConfig.draw(false);
                            adjustTime.close();
                        }
                        Ui.tip(res.msg, res.isSuccess ? "" : "warning");
                    });
                    return false;
                }
            });
        },
        // 编辑投票
        "editVote": function(param, elem) {
            Ui.confirm(Ibos.l("VOTE.EDIT_CLEAR_DATA"), function() {
                location.href = Ibos.app.url("vote/form/edit", {voteid: param.id});
            });
        }
    });
});
