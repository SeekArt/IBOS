(function(root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(function() {
            root.VoteShow = factory(root);
        });
    } else if (typeof exports !== 'undefined') {
        factory(root);
    } else {
        root.VoteShow = factory(root);
    }
})(this, function() {
    // 视图显示
    var View = (function() {
        var _$parent,
            voteid,
            _callback = function() {},
            _data,
            _origin = "",
            op = {
                /**
                 * 获取投票内容
                 * [getShowVote description]
                 * @param  {[type]} data [description]
                 * @return {[type]}      [description]
                 */
                getShowVote: function(data) {
                    return $.get(Ibos.app.url("vote/default/showvote"), data, $.noop, "json");
                },
                /**
                 * 获取投票参与未参与人员
                 * [getShowVoteUsers description]
                 * @param  {[type]} data [description]
                 * @return {[type]}      [description]
                 */
                getShowVoteUsers: function(data) {
                    return $.get(Ibos.app.url("vote/default/showvoteusers"), data, $.noop, "json");
                }
            },
            tmpl = {
                vote:   '<a href="javascript:" title="" class="vote-close" onclick="window.location.href = document.referrer;"></a>' +
                        '<% if( vote.status == 2 ){ %>' +
                            '<img class="vote-finish" src="' + Ibos.app.getAssetUrl("vote") + '/image/finish.png">' +
                        '<% } %>' +
                        '<h1 class="vote-title ellipsis"><%= vote.subject %></h1>' +
                        '<div class="vote-ct mbs vote-date">截止时间：<%= vote.endtimestr %></div>' +
                        '<div class="mb message-content text-break">' +
                        '   <%= vote.content %>' +
                        '</div>',
                topics:     '<div class="noprint">' +
                                '<input type="hidden" name="vote[voteid]" value="<%= vote.voteid %>">' +
                                '<div class="vote vote-text well well-lightblue">' +
                                    '<% var colors = ["#49a2df", "#f09825", "#e57e62", "#9cd346", "#98b2d1", "#ad85cc", "#82939e", "#f4c73b"]; %>' +
                                    '<% for(var i=0; i<topics.length; i++){  %>' +
                                        '<% var topic = topics[i]; var type = topic.maxselectnum == 1 ? "radio" : "checkbox"; %>' +
                                        '<% if( topic.type == 1 ){ %>' +
                                            '<% if( vote.status == 1 && !vote.isvote && vote.canvote ){ %>' +
                                                '<div class="vote-body" data-maxselectnum="<%= topic.maxselectnum %>" data-type="text">' +
                                                    '<h2 class="vote-item-title"><%= i+1 %>.<%= topic.subject %></h2>' +
                                                    '<input type="hidden" name="vote[topics][<%= i %>][topicid]" value="<%= topic.topicid %>">' +
                                                    '<% for(var j=0, item; item = topic.items[j]; j++){ %>' +
                                                        '<div class="vote-item clearfix">' +
                                                            '<label class="<%= type %>"><span class="icon"></span><span class="icon-to-fade"></span>' +
                                                                '<input type="<%= type %>" name="vote[topics][<%= i %>][itemids][<%= j %>]" data-type="vote" value="<%= item.itemid %>"><%= item.content %></label>' +
                                                            '<% if( item.number && vote.isvisible == "0" ){  %>' +
                                                                '<div class="pgb">' +
                                                                    '<div class="pgbr" style="width: <%= item.votepercent %>; background-color: <%= colors[j%colors.length] %>;"></div>' +
                                                                    '<div class="pgbs">' +
                                                                        '<%= item.number %>(<%= item.votepercent %>)' +
                                                                    '</div>' +
                                                                '</div>' +
                                                            '<% } %>' +
                                                        '</div>' +
                                                    '<% } %>' +
                                                '</div>' +
                                            '<% } %>' +
                                            '<% if( vote.status == 2 || vote.isvote || !vote.canvote ){ %>' +
                                                '<div class="vote-body">' +
                                                    '<h2 class="vote-item-title"><%= i+1 %>.<%= topic.subject %></h2>' +
                                                    '<% for(var j=0, item; item = topic.items[j]; j++){ %>' +
                                                        '<div class="vote-item clearfix">' +
                                                            '<label class="<%= type %>  <% if( !vote.canvote && !vote.preview ){ %>plz<% } %>">' +
                                                                '<% if( vote.canvote ){ %>' +
                                                                '<input type="<%= type %>" name="vote[topics][<%= i %>][itemid]" data-type="vote" disabled <% if( topic.selectitemid.indexOf( item.itemid ) >= 0 ){ %>checked<% } %> value="<%= item.itemid %>">'+
                                                                '<% } %>'+
                                                                '<% if( vote.preview ){ %>' +
                                                                '<input type="<%= type %>" name="vote[topics][<%= i %>][itemid]" data-type="vote" value="<%= item.itemid %>">'+
                                                                '<% } %>'+
                                                                '<%= item.content %>'+
                                                            '</label>' +
                                                            '<% if( item.number ){ %>'+
                                                            '<div class="pgb">' +
                                                                '<div class="pgbr" style="width: <%= item.votepercent %>; background-color: <%= colors[j%colors.length] %>;"></div>' +
                                                                '<div class="pgbs">' +
                                                                '<%= item.number %>(<%= item.votepercent %>)' +
                                                                '</div>' +
                                                            '</div>' +
                                                            '<% } %>'+
                                                        '</div>' +
                                                    '<% } %>' +
                                                '</div>' +
                                            '<% } %>' +
                                        '<% } if(topic.type == 2){ %>' +
                                            '<% if( vote.status == 1 && !vote.isvote ){ %>' +
                                                '<div class="vote-body" data-maxselectnum="<%= topic.maxselectnum %>" data-type="image">' +
                                                    '<h2 class="vote-item-title"><%= i+1 %>.<%= topic.subject %></h2>' +
                                                    '<input type="hidden" name="vote[topics][<%= i %>][topicid]" value="<%= topic.topicid %>">' +
                                                    '<ul class="vote-pic-list clearfix">' +
                                                    '<% for(var j=0, item; item = topic.items[j]; j++){ %>' +
                                                        '<li data-id="<%= item.itemid %>">' +
                                                            '<a href="javascript:;">' +
                                                                '<div class="vote-pic-img">' +
                                                                    '<img src="<%= item.picpath %>" alt="<%= item.content %>">' +
                                                                    '<i class="o-checked"></i>' +
                                                                '</div>' +
                                                                    '<p class="vote-pic-desc"><%= item.content %></p>' +
                                                                '<input type="<%= type %>" class="hide" name="vote[topics][<%= i %>][itemids][<%= j %>]" value="<%= item.itemid %>">' +
                                                            '</a>' +
                                                            '<% if( item.number && vote.isvisible == "0" ){ %>' +
                                                                '<div class="pgb">' +
                                                                    '<div class="pgbr" style="width:<%= item.votepercent %>; background-color: <%= colors[j%colors.length] %>;"></div>' +
                                                                '</div>' +
                                                                '<p><%= item.number %>(<%= item.votepercent %>)</p>' +
                                                            '<% } %>' +
                                                        '</li>' +
                                                    '<% } %>' +
                                                    '</ul>' +
                                                '</div>' +
                                            '<% } %>' +
                                            '<% if( vote.status == 2 || vote.isvote ){ %>' +
                                                '<div class="vote-body">' +
                                                    '<h2 class="vote-item-title"><%= i+1 %>.<%= topic.subject %></h2>' +
                                                    '<ul class="vote-pic-list clearfix">' +
                                                        '<% for(var j=0, item; item = topic.items[j]; j++){ %>' +
                                                            '<li <% if( topic.selectitemid.indexOf( item.itemid ) >= 0 ){ %>class="active"<% } %>>' +
                                                                '<a href="javascript:;">' +
                                                                    '<div class="vote-pic-img">' +
                                                                        '<img src="<%= item.picpath %>" alt="<%= item.content %>">' +
                                                                        '<i class="o-checked"></i>' +
                                                                    '</div>' +
                                                                    '   <p class="vote-pic-desc"><%= item.content %></p>' +
                                                                    '<input type="<%= type %>" class="hide" name="vote[]">' +
                                                                '</a>' +
                                                                '<% if( item.number){ %>'+
                                                                    '<div class="pgb">' +
                                                                        '<div class="pgbr" style="width:<%= item.votepercent %>; background-color: <%= colors[j%colors.length] %>;"></div>' +
                                                                    '</div>' +
                                                                    '<p><%= item.number %>(<%= item.votepercent %>)</p>' +
                                                                '<% } %>'+
                                                            '</li>' +
                                                        '<% } %>' +
                                                    '</ul>' +
                                                '</div>' +
                                            '<% } %>' +
                                        '<% } %>' +
                                    '<% } %>' +
                                    '<div class="clearfix pt pb">' +
                                        '<div class="row">'+
                                            '<div class="span3">'+
                                                '<% if( vote.status == 1 && !vote.isvote && !vote.preview && vote.canvote ){ %>' +
                                                    '<button data-action="voteSubmit" data-param=\'{"id": "<%= vote.voteid %>"}\' type="button" class="btn btn-block btn-primary">投票</button>' +
                                                '<% } %>' +
                                                '<% if( vote.status == 1 && vote.isvote && vote.canvote ){ %>' +
                                                    '<button  type="button" disabled class="btn btn-block btn-primary disabled">已投票</button>' +
                                                '<% } %>' +
                                                '<% if( vote.status == 2 && !vote.isvote ){ %>' +
                                                    '<button  type="button" disabled class="btn btn-block btn-primary disabled">已结束</button>' +
                                                '<% } %>' +
                                            '</div>'+
                                            '<% if( vote.canexport ){ %>' +
                                                '<div class="pull-right mrs">' +
                                                    '<button type="button" data-action="exportVote" data-param=\'{"id": <%= vote.voteid %>}\' class="btn btn-default"><i class="o-vote-down mrs"></i>导出结果</button>' +
                                                '</div>' +
                                            '<% } %>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>',
                readscopes: '<div class="vote-desc mb ">' +
                                '<ul class="vote-desc-list">' +
                                    '<li>' +
                                        '<strong>信息</strong>' +
                                        '<div class="vote-desc-body">' +
                                            '<%= vote.realname %> ' +
                                            '<span class="ilsep">发布于</span>' +
                                            '<span title="2016-10-19 11:54"><%= vote.starttimestr %></span>' +
                                        '</div>' +
                                    '</li>' +
                                    '<li>' +
                                        '<strong>范围</strong>' +
                                        '<div class="vote-desc-body">' +
                                            '<% if(vote.readscopes.departmentNames){ %>' +
                                            '   <i class="os-department"></i><%= vote.readscopes.departmentNames %>' +
                                            '<% } %>' +
                                            '<% if(vote.readscopes.positionNames){ %>' +
                                                '<i class="os-position"></i><%= vote.readscopes.positionNames %>' +
                                            '<% } %>' +
                                            '<% if(vote.readscopes.roleNames){ %>' +
                                                '<i class="os-role"></i><%= vote.readscopes.roleNames %>' +
                                            '<% } %>' +
                                            '<% if(vote.readscopes.uidNames){ %>' +
                                                '<i class="os-user"></i><%= vote.readscopes.uidNames %>' +
                                            '<% } %>' +
                                        '</div>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>',
                users: '<div class="noprint">' +
                    '<ul class="nav nav-skid fill-zn art-related-nav" id="art_related_nav">' +
                    '<li class="active">' +
                    '<a href="#joined" data-toggle="tab">' +
                    '<i class="o-vote-joined">' +
                    '</i> 已参与 </a>' +
                    '</li>' +
                    '<li class="">' +
                    '<a href="#unjoined" data-toggle="tab">' +
                    '<i class="o-vote-unjoined"></i> 未参与 </a>' +
                    '</li>' +
                    '</ul>' +
                    '<div class="tab-content">' +
                    '<div id="joined" class="tab-pane active">' +
                        '<div class="vote-reader-table">' +
                        '<% if( !users.joined.length ){ %>' +
                            '<div class="empty-info"></div>' +
                        '<% } %>' +
                        '<% for(var i=0, item, ilen = users.joined.length; i<ilen; i++){  item = users.joined[i]; %>' +
                            '<% if( item.deptname ){ %>' +
                                '<h5 class="vote-reader-dep"><%= item.deptname %></h5>' +
                            '<% } %>' +
                            '<ul class="vote-reader-list clearfix">' +
                                '<% for(var j = 0 ,user, jlen = item.users.length; j < jlen; j++){ user = item.users[j]; %>' +
                                    '<li>' +
                                        '<a href="?r=user/home/index&amp;uid=<%= user.uid %>" class="avatar-circle avatar-circle-small"> <img src="<%= user.avatar %>"> </a> <%= user.text %> ' +
                                    '</li>' +
                                '<% } %>' +
                            '</ul>' +
                        '<% } %>' +
                        '</div>' +
                    '</div>' +
                    '<div id="unjoined" class="tab-pane">' +
                    '<% if( !users.unjoined.length ){ %>' +
                        '<div class="empty-info"></div>' +
                    '<% } %>' +
                    '<% for(var i=0, item, ilen = users.unjoined.length; i<ilen; i++){  item = users.unjoined[i]; %>' +
                    '<% if( item.deptname ){ %>' +
                    '<h5 class="vote-reader-dep"><%= item.deptname %></h5>' +
                    '<% } %>' +
                    '<ul class="vote-reader-list clearfix">' +
                    '<% for(var j = 0 ,user, jlen = item.users.length; j < jlen; j++){ user = item.users[j]; %>' +
                    '<li>' +
                    '<a href="?r=user/home/index&amp;uid=<%= user.uid %>" class="avatar-circle avatar-circle-small"> <img src="<%= user.avatar %>"> </a> <%= user.text %> ' +
                    '</li>' +
                    '<% } %>' +
                    '</ul>' +
                    '<% } %>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
            },
            _tmpl = [],
            users = Ibos.data.get("user").user;


        function init(opt, callback) {
            if (!opt.parent) {
                throw error("请传父节点的id或者class");
            }
            voteid = opt.voteid;
            _data = opt.data;
            _origin = opt.origin;
            _$parent = $(opt.parent);
            _callback = callback || _callback;
            if (_data) {
                dealData(_data);
            } else {
                getShowVote(function(data){
                    dealData(data);
                });
            }
        }
        /**
         * 获取投票内容
         * [getShowVote description]
         * @param  {Function} callback [description]
         * @return {[type]}            [description]
         */
        function getShowVote(callback) {
            op.getShowVote({
                voteid: voteid
            }).done(function(res) {
                if (res && res.isSuccess) {
                    callback && callback(res.data);
                } else {
                    Ui.tip(res.msg, "danger");
                }
            }).error(function(res){
                Ui.tip(JSON.parse(res.responseText).msg, "danger");
            });
        }
        /**
         * 获取参与、未参与的人员
         * [getShowVoteUsers description]
         * @param  {Function} callback [description]
         * @return {[type]}            [description]
         */
        function getShowVoteUsers(callback){
            op.getShowVoteUsers({
                voteid: voteid
            }).done(function(res){
                if (res && res.isSuccess) {
                    callback && callback(res.data);
                } else {
                    Ui.tip(res.msg, "danger");
                }
            }).error(function(res){
                Ui.tip(JSON.parse(res.responseText).msg, "danger");
            });
        }

        /**
         * 处理请求返回的数据，进行视图的渲染
         * [dealData description]
         * @param  {[type]} data [description]
         * @return {[type]}      [description]
         */
        function dealData(data) {
            _tmpl.length = 0;
            _$parent.html("");
            _tmpl.push('<div class="vote-container">');

            if( _origin == "article" ){
                if (data.topics) {
                    _tmpl.push($.template(tmpl.topics, data));
                }
                _tmpl.push('</div>');
            }else{
                _tmpl.push($.template(tmpl.vote, data));
                if (data.topics) {
                    _tmpl.push($.template(tmpl.topics, data));
                }
                _tmpl.push('</div>');
                if (data.vote.readscopes) {
                    _tmpl.push('<div class="vote-halving-line"></div>');
                    _tmpl.push($.template(tmpl.readscopes, data));
                }
                if( voteid ){
                    dealUser();
                }
            }

            var $main = $.tmpl(_tmpl.join(""), data);
            $main.find("label input[type='checkbox']").label();
            $main.find("label input[type='radio']").label();
            _$parent.append($main);
            _callback && _callback($main, data);
            
        }
        
        function dealUser(){
            getShowVoteUsers(function(data){
                transformUserData(data.users.joined);
                transformUserData(data.users.unjoined);
                _$parent.append( $.tmpl(tmpl.users, data) ) ;
            });
        }

        function transformUserData(data){
            for(var i=0,ilen = data.length; i<ilen; i++){
                for(var j=0, item, user, jlen= data[i].users.length; j<jlen; j++){
                    item = data[i].users[j];
                    user = users['u_'+ item];

                    user.uid = item;
                    data[i].users[j] = user;
                }
            }
        }

        

        return {
            init: init,
            getShowVote: getShowVote
        };
    })();

    // 存储投票
    var VoteList = [];

    /**
     * 文字投票
     * [VoteText description]
     * @param {[type]} $ctx   [description]
     * @param {[type]} maxNum [description]
     */
    var VoteText = function($ctx, maxNum) {
        var $vote = $ctx;
        var getChecked = function() {
                return $vote.find("[data-type='vote']:checked");
            },
            getValue = function() {
                var arr = [];
                var $checked = getChecked();
                $checked.each(function() {
                    arr.push(this.value);
                });
                return arr.join(",");
            },
            getFormatValue = function() {
                var data = {},
                    values = getValue().split(",");
                $.each(values, function(i, item) {
                    json['vote[topics][' + i + '][topicid]'] = item;
                });
                return data;
            },
            check = function(id) {
                $vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("check");
            },
            uncheck = function(id) {
                $vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("uncheck");
            },
            lastId;

        $vote.on("change", '[data-type="vote"]', function() {
            var id = this.value,
                checkNum = getChecked().length;
            if (checkNum > maxNum) {
                lastId && uncheck(lastId);
            }
            lastId = id;
        });

        return {
            val: getValue,
            formatVal: getFormatValue,
            check: check,
            uncheck: uncheck
        };
    };

    /**
     * 图片投票
     * [VoteImage description]
     * @param {[type]} $ctx     [description]
     * @param {[type]} selector [description]
     * @param {[type]} maxNum   [description]
     */
    var VoteImage = function($ctx, selector, maxNum) {
        selector = selector || "[data-type='voteitem']";
        $ctx = ($ctx && $ctx.length) ? $ctx : $("#vote");
        maxNum = maxNum || 1;

        var lastId;

        var _getChecked = function() {
            return $ctx.find(selector + ".active");
        };
        var _getCheckedNum = function() {
            return _getChecked().length;
        };
        var getCheckedValue = function() {
            var $checked = _getChecked();
            var arr = [];
            $checked.each(function() {
                arr.push($.attr(this, "data-id"));
            });
            return arr.join(",");
        };

        var uncheck = function(id) {
            $ctx.find(selector).filter("[data-id='" + id + "']").removeClass("active").find("input").prop("checked", false);
        };
        var check = function(id) {
            var checkedNum = _getCheckedNum($ctx, selector);
            // 如果选项小于最大可选数
            if (checkedNum < maxNum) {
                $ctx.find(selector).filter("[data-id='" + id + "']").addClass("active").find("input").prop("checked", true);
                // 记录上次选中的id
                lastId = id;
                // 大于最大可选数时，当前选中的会替代上个选中的选项
            } else {
                if (lastId) {
                    // 取消上次选中项
                    uncheck(lastId);
                    check(id);
                }
            }
        };
        var _bind = function() {
            $ctx.on("click.vote", selector, function() {
                var id = $.attr(this, "data-id");
                if (!id) {
                    return false;
                }
                // 此处有些性能浪费
                if ($(this).hasClass("active")) {
                    uncheck(id);
                } else {
                    check(id);
                }
            });
        };

        _bind();
        return {
            val: function() {
                return getCheckedValue();
            },
            check: check,
            uncheck: uncheck,
            enable: function() {
                _bind();
            },
            disable: function() {
                $ctx.off("click.vote");
            }
        };
    };

    /**
     * 是否有选中值
     * [getSelected description]
     * @return {[type]} [description]
     */
    var getSelected = function() {
        for (var i = 0, l = VoteList.length; i < l; i++) {
            if (!VoteList[i].val()) {
                return false;
            }
        }
        return true;
    };
    return {
        render: View.init,
        getShowVote: View.getShowVote,
        VoteText: VoteText,
        VoteImage: VoteImage,
        getSelected: getSelected,
        VoteList: VoteList
    };
});

$(function() {
    $.getScript(Ibos.app.getAssetUrl("vote") + "/js/lang/zh-cn.js");

    var voteAEData = "",
        voteData,
        voteid = U.getUrlParam().id || Ibos.app.g("voteid"),
        voteOrigin = Ibos.app.g("origin");

    try{
        voteAEData = $(window.opener.document.voteForm).serializeArray();
    }catch(e){
        console.log(e);
    }
    /**
     * 无限添加分类
     * transformJSON(["[abc]", "bcd", "dre"], 123); 
     * {
     *     abc: {
     *         bcd: {
     *             dre: 123
     *         }
     *     }
     * }
     * [description]
     * @param  {Object} ) {                   var obj [description]
     * @return {[type]}   [description]
     */
    var transformJSON = (function() {
        var obj = {};
        return function createObj() {
            var args = arguments[0],
                value = arguments[1];
            // console.log(args, value);
            return createJson(obj, value, args);
        };

        function createJson() {
            var obj = arguments[0],
                value = arguments[1],
                args = [].slice.call(arguments, 2)[0],
                firstArg = args[0],
                name = firstArg.slice(1, firstArg.length - 1);
            if (args.length > 1) {
                obj[name] = obj[name] || {};
                createJson(obj[name], value, args.slice(1));
            } else {
                obj[name] = value;
            }
            return obj;
        }
    })();

    /**
     * 转化为可用的数据，供view使用
     * [transformDadta description]
     * @param  {[type]} data [description]
     * @return {[type]}      [description]
     */
    var transformData = function(data){
        var vote = {};
        var topics = [];

        for(var attr in data){
            if( attr == "topics" ) continue;
            vote[attr] = data[attr];
        }
        vote.endtimestr = vote.endtime;
        vote.status = 1;
        vote.preview = 1;

        for(var i in data.topics){
            var topic = data.topics[i];
            var obj = { items: [] };
            for(var a in topic){
                if( /[0-9]$/.test(a ) ) {
                    obj.items[a] = topic[a];
                    continue;
                }
                obj[a] = topic[a];
            }
            obj.type = obj.topic_type;
            obj.topicid = 0;
            topics.push(obj);
        }
        return {
            vote: vote,
            topics: topics
        };
    };

    if (voteAEData.length && voteid == "0") {
        // 预览的时候才有该值
        voteAEData.forEach(function(item) {
            var name = item.name,
                value = item.value;
            if (/]$/.test(name)) {
                voteData = transformJSON(name.match(/\[(.*?)\]/g), value);
            }
        });
        VoteShow.render({
            parent: "#vote_content",
            data: transformData(voteData)
        });
    } else {
        // 查看详情页
        if( voteid != "0" ){
            VoteShow.render({
                parent: "#vote_content",
                voteid: voteid,
                origin:voteOrigin
            }, function($main, data) {
                if(!data.vote.canvote) return;
                $(".vote-body").each(function(i, item) {
                    var data = $(item).data();
                    if (data.type == "text") {
                        VoteShow.VoteList[i] = VoteShow.VoteText($(item), data.maxselectnum);
                    }
                    if (data.type == "image") {
                        VoteShow.VoteList[i] = VoteShow.VoteImage($(item), "li", data.maxselectnum);
                    }
                });
            });
        }
    }

    VoteShow.op = {
        voteSubmit: function(data) {
            return $.post(Ibos.app.url("vote/default/vote"), data, $.noop, "json");
        }
    };

    Ibos.evt.add({
        // 提交投票
        voteSubmit: function(param, elem) {
            if (VoteShow.getSelected()) {
                VoteShow.op.voteSubmit($("form").serializeArray()).done(function(res) {
                    if (res.isSuccess) {
                        Ui.tip(Ibos.l("VOTE.VOTE_SUCCESS"));
                        VoteShow.render({
                            parent: "#vote_content",
                            voteid: U.getUrlParam().id || Ibos.app.g("voteid"),
                            origin: voteOrigin
                        });
                    } else {
                        Ui.tip(res.msg, "danger");
                    }
                });
            } else {
                Ui.tip(Ibos.l("VOTE.SELECT_PROJECT"), "danger");
            }

        },
        // 导出结果
        exportVote: function(param, elem) {
            location.href = Ibos.app.url("vote/default/export", {
                voteid: param.id
            });
        }
    });
});
