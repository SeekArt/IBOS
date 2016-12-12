(function(root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(function() {
            root.Contact = factory(root);
        });
    } else if (typeof exports !== 'undefined') {
        factory(root);
    } else {
        root.Contact = factory(root, {});
    }
})(window, function(win, Contact) {
    //cachedata
    var USERS = Ibos.data.get("user").user,
        DEPARTMENTS = Ibos.data.get("department").department,
        POSITIONS = Ibos.data.get("position").position;
        
    var SCREENHEIGHT =  $(win).height();

    // domcache
    var domMap = {
        $main: $("#mainer"),
        $userDatalist: $("#user_datalist"),
        $searchList: $('#user_searchlist'),
        $sidebar: $("#cl_rolling_sidebar"),
        $searchArea: $("#search_area"),
        $exportUser: $("#export_user"),
        $printUser: $("#print_user"),
        $tree: $('#utree'),
        $corpUnit: $('#corp_unit'),
        $body: $("html,body"),
        $clListHeader: $("#cl_list_header"),
        $exportForm: $("#export_form"),
        $exportUids: $("#export_form [name='uids']")
    };

    // ajaxurl
    var ajaxApi = Contact.ajaxApi = {
        getDeptList: function(data) {
            return $.get(Ibos.app.url("contact/api/deptlist"), data, $.noop, "json");
        },
        getGroupUserlist: function(data) {
            return $.get(Ibos.app.url("contact/api/groupuserlist"), data, $.noop, "json");
        },
        getCorpInfo: function(data) {
            return $.get(Ibos.app.url("contact/api/corp"), data, $.noop, "json");
        },
        getDeptInfo: function(data) {
            return $.get(Ibos.app.url("contact/api/dept"), data, $.noop, "json");
        },
        getUserInfo: function(data) {
            return $.get(Ibos.app.url("contact/api/user"), data, $.noop, "json");
        },
        printUser: function(data) {
            return $.post(Ibos.app.url("contact/default/printContact"), data, $.noop, "json");
        },
        getHiddenUser: function(data){
            return $.get(Ibos.app.url("contact/api/hiddenuidarr"), data, $.noop, "json");
        }
    };

    // template
    var TPL = Contact.TPL = {
        listTpl : '<% for(var j=0, dept; dept = depts[j]; j++){ %>'+
                    '<div class="group-item">'+
                        '<% if( dept.deptid == "0" ){ %>'+
                        '<div class="cl-letter-title" data-type="corp" data-id="<%= dept.deptid %>"><%= dept.prefix %><%= dept.deptname %> (<%= dept.deptnum %>)</div>'+
                        '<% }else{ %>'+
                        '<div class="cl-letter-title" data-type="dept" data-id="<%= dept.deptid %>"> <i><%= dept.prefix %></i><%= dept.deptname %> <% if( dept.deptnum ){ %>(<%= dept.deptnum %>)<% } %></div>'+
                        '<% } %>'+
                        '<table class="table table-hover cl-info-table">'+
                            '<tbody>'+
                                '<% for(var i=0, user; user = dept.users[i]; i++){  %>'+
                                '<tr data-id="<%= user.uid || user.id.slice(2) %>" data-type="user">'+
                                    '<td width="5">'+
                                        '<span class="avatar-circle">'+
                                            '<img src="<%= user.avatar %>">'+
                                        '</span>'+
                                    '</td>'+
                                    '<td width="120">'+
                                        '<span class="xcm"><%= user.text %> <% if(user.isadmin){ %><span class="badge">主管</span><% } %></span>'+
                                    '</td>'+
                                    '<td width="60">'+
                                        '<span class="fss"></span>'+
                                    '</td>'+
                                    '<td width="120">'+
                                        '<span class="fss"><%= user.position %></span>'+
                                    '</td>'+
                                    '<td width="40">'+
                                        '<span class="fss"></span>'+
                                    '</td>'+
                                    '<td width="133">'+
                                        '<span class="fss"><% if( user.mobileHidden ){ %>已隐藏<% }else{ %><%= user.phone %><% } %></span>'+
                                    '</td>'+
                                '</tr>'+
                                '<% } %>'+
                            '</tbody>'+
                        '</table>'+
                        '<% if( dept.deptid != "0" && !dept.users.length ){ %>'+
                        '<div class="no-data-tip"></div>'+
                        '<% } %>'+
                    '</div>'+
                '<% } %>',
        sidebarUserTpl: '<div class="personal-info" id="personal_info">'+
                        '<div class="cl-pc-top posr">'+
                            '<div class="cl-pc-banner">'+
                                '<img src=<%= data.bgbig %>>'+
                            '</div>'+
                            '<div class="cl-pc-usi">'+
                                '<div class="cl-pc-bg"></div>'+
                                '<div class="cl-pc-avatar posr">'+
                                    '<a href=<%= Ibos.app.url("user/home/index", {uid: data.uid}) %> target="_blank" class="pc-avatar"'+
                                    'id="card_home_url">'+
                                    '<img src=<%= data.avatar_big %> alt="" width="96" height="96" id="card_avatar">'+
                                    '</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="cl-uic-operate">'+
                                '<a href=<%= "javascript:Ibos.showCallingDialog(" + data.uid + ");void(0);" %> title="打电话"'+
                                'class="co-tcall"></a>'+
                                '<a target="_blank" href=\"<%= Ibos.app.url("email/content/add", {toid: data.uid}) %>\"'+
                                'title="发邮件给TA" class="co-temail mrs" id="card_email_url"></a>'+
                                '<a title="发私信给TA" href=<%='+
                                '"javascript:Ibos.showPmDialog([\'u_" + data.uid + "\'],{url:\'" + Ibos.app.url(\'message/pm/post\') +'+
                                '"\'});void(0);" %> class="co-tpm" id="card_pm">'+
                                '</a>'+
                            '</div>'+
                            '<div class="cl-pc-name">'+
                                '<i class=<%= data.gender== "1" ? "om-male" : "om-female" %> id="card_gender"></i>'+
                                '<strong id="card_realname" class="fsst"><%= data.text %></strong>'+
                                '<span id="card_deptname" class="mlm"> <%= data.deptname %> </span>'+
                                '<% if(data.deptname !== "" && data.posname !== ""){ %>'+
                                '<strong id="card_connect">·</strong>'+
                                '<% } %>'+
                                '<span id="card_posname"><%= data.positionname %></span>'+
                            '</div>'+
                        '</div>'+
                        '<div class="pc-info-content posr">'+
                            '<a href="javascript:;" class="cl-window-ctrl" data-evt="close"></a>'+
                            '<div class="pc-info-list">'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-phone"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">手机 </span>'+
                                    '<span class="ml xcm" id="care_mobile"><%= data.phone == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.phone %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-qq"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">QQ</span>'+
                                    '<span class="ml xcm" id="card_qq"> <%= data.qq == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.qq %> </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-email"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">邮箱</span>'+
                                    '<span class="ml xcm" id="card_email"> <%= data.email == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.email %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-birthday"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">生日</span>'+
                                    '<span class="ml xcm card-birthday"> <%= data.birthday == "0" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.birthday %>  </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-auxiliarydept"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">辅助部门</span>'+
                                    '<span class="ml xcm card-birthday"> <%= data.auxiliarydepts ? data.auxiliarydepts : Ibos.l("CONTACT.NOT_AVAILABLE") %>  </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-auxiliaryposition"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">辅助岗位</span>'+
                                    '<span class="ml xcm card-birthday"> <%= data.auxiliarypositions ? data.auxiliarypositions : Ibos.l("CONTACT.NOT_AVAILABLE") %>  </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-jobnumber"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">工号</span>'+
                                    '<span class="ml xcm card-fax" id="card_fax"> <%= data.jobnumber == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.jobnumber %> </span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>',
        sidebarDeptTpl: '<div class="personal-info" id="personal_info">'+
                        '<div class="cl-pc-top posr">'+
                            '<div class="cl-pc-banner">'+
                                '<img src=<%= data.bgbig %>>'+
                            '</div>'+
                            '<div class="cl-pc-usi">'+
                                '<div class="cl-pc-bg"></div>'+
                                '<div class="cl-pc-avatar posr">'+
                                    '<a class="pc-avatar"'+
                                    'id="card_home_url">'+
                                    '<img src=<%= data.avatar_big %> alt="" width="96" height="96" id="card_avatar">'+
                                    '</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="cl-pc-name">'+
                                '<strong class="fsst"><%= data.deptname %></strong>'+
                            '</div>'+
                        '</div>'+
                        '<div class="pc-info-content posr">'+
                            '<a href="javascript:;" class="cl-window-ctrl" data-evt="close"></a>'+
                            '<div class="pc-info-list">'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-home-phone"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">电话 </span>'+
                                    '<span class="ml xcm" id="care_mobile"><%= data.tel == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.tel %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-fax"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">传真</span>'+
                                    '<span class="ml xcm card-qq" id="card_qq"> <%= data.fax == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.fax %> </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-managername"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">部门主管</span>'+
                                    '<span class="ml xcm" id="card_email"> <%= data.managername == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.managername %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-address"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">地址</span>'+
                                    '<span class="ml xcm card-birthday" id="card_birthday"> <%= data.address == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.address %>  </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-func"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">部门职能</span>'+
                                    '<span class="ml xcm card-birthday" id="card_birthday"> <%= data.func == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.func %>  </span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>',
        sidebarCorpTpl: '<div class="personal-info" id="personal_info">'+
                        '<div class="cl-pc-top posr">'+
                            '<div class="cl-pc-banner">'+
                                '<img src=<%= data.bgbig %>>'+
                            '</div>'+
                            '<div class="cl-pc-usi">'+
                                '<div class="cl-pc-bg"></div>'+
                                '<div class="cl-pc-avatar posr">'+
                                    '<a class="pc-avatar"'+
                                    'id="card_home_url">'+
                                    '<img src=<%= data.logourl %> alt="" width="96" height="96" id="card_avatar">'+
                                    '</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="cl-pc-name">'+
                                '<strong class="fsst"><%= data.fullname %></strong>'+
                            '</div>'+
                        '</div>'+
                        '<div class="pc-info-content posr">'+
                            '<a href="javascript:;" class="cl-window-ctrl" data-evt="close"></a>'+
                            '<div class="pc-info-list">'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-name"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">企业简称 </span>'+
                                    '<span class="ml xcm" id="care_mobile"><%= data.corpname == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.corpname %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-url"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">系统URL</span>'+
                                    '<span class="ml xcm card-qq" id="card_qq"> <%= data.systemurl == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.systemurl %> </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-home-phone"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">电话</span>'+
                                    '<span class="ml xcm" id="card_email"> <%= data.phone == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.phone %></span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-fax"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">传真</span>'+
                                    '<span class="ml xcm card-birthday" id="card_birthday"> <%= data.fax == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.fax %>  </span>'+
                                '</div>'+
                                '<div class="mb">'+
                                    '<span>'+
                                        '<i class="o-pc-address"></i>'+
                                    '</span>'+
                                    '<span class="pc-info-title">地址</span>'+
                                    '<span class="ml xcm card-birthday" id="card_birthday"> <%= data.address == "" ? Ibos.l("CONTACT.NOT_AVAILABLE") : data.address %>  </span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'
    };

    // util
    var utils = {
        upperFirstLetter: function(str) {
            return str.replace(/^[a-z]/, function(match){return match.toUpperCase()});
        },
        getText: Ibos.data.getText
    };

    var getUids = Contact.getUids = {
        type: 'dept',
        dept: function(depts){
            var uids = [];
            for(var i=0, dept; dept = depts[i]; i++){
                for( var j= 0, user; user=dept.users[j]; j++){
                    uids.push( 'u_'+ user.uid );
                }
            }
            return uids.join(",");
        },
        search: function(){
            var uids = [];
            domMap.$main.find("tr:visible").each(function() {
                uids.push( 'u_'+ this.getAttribute("data-id") );
            });
            return uids.join(",");
        }
    };

    var getPreg = function(user) {
        if (user && user.text == '' || !user) {
            return '';
        }
        var toPinyin = pinyinEngine.toPinyin,
            pyArr = toPinyin(user.text, true),
            sinStr = "",
            i, alen;
        for (i = 0, alen = pyArr.length; i < alen; i += 1) {
            sinStr += pyArr[i].charAt(0);
        }
        return pyArr.join("") + ',' + sinStr + ',' + user.text + "," + user.jobnumber.toLowerCase();
    };

    // oa users
    (function() {
        var user,
            id,
            data = [],
            hiddenUidArr = [];

        ajaxApi.getHiddenUser().done(function(res){
            if( res.isSuccess ){
                hiddenUidArr = hiddenUidArr.concat(res.data.users);
            }
            for (var uid in USERS) {
                user = USERS[uid];
                if( ~hiddenUidArr.indexOf( uid ) ){
                    user.mobileHidden = true;
                }
                user.preg = getPreg(user);
                user.position = POSITIONS[user.posid] ? POSITIONS[user.posid].text : "";
            }
        });
    })();

    var searchUser = function(val) {
        if (!val) {
            domMap.$searchList.hide().empty();
            domMap.$userDatalist.show();
            getUids.type = 'dept';
            return false;
        }
        getUids.type = 'search';
        var searchArr = [],
            tmplData = {},
            letter, user;

        for (letter in USERS) {
            user = USERS[letter];
            if (user.preg.indexOf(val) !== -1) {
                searchArr.push(user);
            }
        }
        domMap.$userDatalist.hide();
        domMap.$searchList.html($.template(TPL.listTpl, {
            depts: [{
                deptname: Ibos.l("CONT.SEARCH_RESULT"),
                deptid: '',
                users: searchArr
            }]
        }));
        domMap.$searchList.show();

        searchArr = null;
        tmplData = null;
        return true;
    };

    var transformOutputData = function(data) {
        var users, user, crumbs, crumb = "";

        for (var i = 0 ,ilen = data.length; i < ilen; i++) {
            crumbs= data[i].crumb;
            for(var k=1, klen=crumbs.length; k < klen; k++){
                crumb += crumbs[k] + "<i class='arrow'></i>";
            }
            data[i].prefix = crumb;
            crumb = "";
            users = data[i] && data[i].users;
            for (var j = 0; user = users[j]; j++) {
                user = $.extend(user, USERS['u_' + user.uid]);
            }
        }
    };
    // 分页
    var Pagination = {
        offset: 0,
        length: 1000,
        data: [],
        id: 0,
        $parent: null,
        init: function(data, $parent, id){
            this.$parent = $parent;
            this.reset();
            this.id = id;
            this.data = this.data.concat(data);

            var depts = this.data;


            if( this.data.length == 0 ){
                this.render( [{
                    deptname: this.crumb(id),
                    deptid: id,
                    users: []
                }] );
            }else{
                this.bind();
                this.append();
            }
        },
        append: function(){
            var outputData = this.data.slice(this.length * this.offset, this.length * (this.offset + 1));
            if( outputData.length ){
                transformOutputData(outputData); 
                
                this.render(outputData);
            }else{
                this.unbind();
            }
        },
        crumb: function(id){
            var crumbs = [];
            var dept = DEPARTMENTS['d_' + id];
            crumbs.push( dept.text );
            while( dept.pid != 'c_0'){
                dept = DEPARTMENTS[dept.pid];
                crumbs.push( '<i>' + dept.text + '</i>' );
            }
            crumbs = crumbs.reverse();

            return crumbs.join("<i class='arrow'></i>");
        },
        render: function(data){
            var tmpl = $.tmpl(TPL.listTpl, {
                depts: data
            });
            this.$parent.append(tmpl);
        },
        bind: function(){
            var _this = this;
            $(window).on("scroll.page", function(){
                var scrollTop = $(this).scrollTop(),
                    bodyHeight = domMap.$body.height();

                if( bodyHeight <= scrollTop + SCREENHEIGHT + 500 ){
                    _this.offset++;
                    _this.append();
                }
            });
        },
        unbind: function(){
            $(window).off("scroll.page");
        },
        reset: function(){
            this.id = 0;
            this.offset = 0;
            this.data.length = 0;
            this.unbind();
            this.$parent.empty();
        }
    };

    var cacheDeptData = {};
    var renderCompany = function(data) {
        var id = data.deptid;
        domMap.$main.waiting(null);
        if( cacheDeptData[id] ){
            Pagination.init(cacheDeptData[id], domMap.$userDatalist, id);
            domMap.$main.waiting(false);
        }else{
            ajaxApi.getGroupUserlist({
                deptid: id
            }).done(function(res) {
                cacheDeptData[id] = res.data;
                Pagination.init(res.data, domMap.$userDatalist, id);

                domMap.$main.waiting(false);
            });
        }
    };
    
    var Sidebar = {
        cache: {user: {}, dept: {}, corp: {}},
        id: "",
        getInfo: function(type, data, callback) {
            var _this = this,
                uid = data.userid || data.deptid || data.corpid;
            if (this.cache[type][uid]) {
                callback && callback(this.cache[type][uid]);
                return;
            }
            ajaxApi["get" + utils.upperFirstLetter(type) + "Info"](data).done(function(res) {
                if (res.isSuccess) {
                    _this.cache[type][uid] = res.data;
                    callback && callback(res.data);
                } else {
                    Ui.tip(res.msg, "danger");
                }
            });
        },
        render: function(data) {
            var _this = this;
            switch (data.type) {
                case "user":
                    _this.user({
                        userid: data.id
                    });
                    break;
                case "dept":
                    _this.dept({
                        deptid: data.id
                    });
                    break;
                case "corp":
                    _this.corp({
                        corpid: 0
                    });
                    break;
            }
        },
        auxiliary: function(data) {
            var auxiliarydept = "",
                auxiliaryposition = "";

            for (var i = 0, dept; dept = data.auxiliarydept && data.auxiliarydept[i]; i++) {
                auxiliarydept += dept.deptname + " ";
            }
            for (var j = 0, pos; pos = data.auxiliaryposition && data.auxiliaryposition[j]; j++) {
                auxiliaryposition += pos.posname + " ";
            }
            data.auxiliarydepts = auxiliarydept;
            data.auxiliarypositions = auxiliaryposition;
            return data;
        },
        user: function(data) {
            var _this = this;
            this.getInfo("user", data, function(res) {
                var params = USERS["u_" + res.uid];
                domMap.$sidebar.html($.template(TPL.sidebarUserTpl, {
                    data: $.extend(params, _this.auxiliary(res), {
                        type: "user"
                    })
                }));
                _this.show(data);
            });
        },
        dept: function(data) {
            var _this = this;
            this.getInfo("dept", data, function(res) {
                var params = DEPARTMENTS["d_" + data.deptid];
                domMap.$sidebar.html($.template(TPL.sidebarDeptTpl, {
                    data: $.extend(params, res, {
                        type: "dept",
                        avatar_big: Ibos.app.getAssetUrl("contact") + "/image/dept_avatar.png"
                    })
                }));
                _this.show();
            });
        },
        corp: function(data) {
            var _this = this;
            this.getInfo("corp", data, function(res) {
                var params = DEPARTMENTS["c_" + data.corpid];
                domMap.$sidebar.html($.template(TPL.sidebarCorpTpl, {
                    data: $.extend(params, res, {
                        type: "corp",
                        avatar_big: Ibos.app.getAssetUrl("contact") + "/image/dept_avatar.png"
                    })
                }));
                _this.show();
            });
        },
        show: function(data) {
            domMap.$main.find("tr[data-id='"+ this.id +"']").removeClass("active");
            if( data && data.userid ){
                this.id = data.userid;
                domMap.$main.find("tr[data-id='"+ this.id +"']").addClass("active");
            }
            domMap.$sidebar.animate({
                width: '520px',
                marginLeft: '261px',
                height: "100%"
            }, 200);
        },
        hide: function() {
            domMap.$main.find("tr[data-id='"+ this.id +"']").removeClass("active");
            domMap.$sidebar.animate({
                width: '0',
                marginLeft: '780px'
            }, 200);
        }
    };

    var infoReset = function(){
        searchUser("");
        domMap.$searchArea.val("");
        Sidebar.hide();
    };

    // create zTree of department
    var beforeDeptId = 0;
    var treeObj = (function() {
        var ztreeOpt, settings, treeObj,
            first = true;

        ztreeOpt = {
            'nodeOnClick': function(event, treeId, treeNode) {
                domMap.$corpUnit.removeClass('dep-active');
                if (treeNode.deptid != beforeDeptId) {
                    beforeDeptId = treeNode.deptid;
                    location.hash = "deptid=" + beforeDeptId;
                    renderCompany(treeNode);
                }
                infoReset();
            },
            filter: function(treeId, parentNode, childNodes){
                var res = childNodes;
                if( res.isSuccess ){
                    var depts = res.data.depts;
                    if( first ){
                        first = false;
                        var $a = domMap.$corpUnit.find("a");
                        $a.html( $a.html() +" ("+res.data.deptnum + ")" );
                    }
                    return $.map(depts, function(data) {
                        var deptname = data.deptnum ? data.deptname + " (" + data.deptnum + ")" : data.deptname;
                        return {
                            text: deptname,
                            deptid: data.deptid,
                            isParent: data.hasmore
                        };
                    });
                }else{
                    Ui.tip(Ibos.l("CONT.REQUEST_FAIL"), "danger");
                }
            }
        };

        settings = {
            async: {
                enable: true,
                url: Ibos.app.url("contact/api/deptlist"),
                autoParam: ["deptid"],
                dataFilter: ztreeOpt.filter,
                type: "get"
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
            view: {
                showLine: false,
                selectedMulti: false,
                showIcon: false,
                addDiyDom: function(treeId, treeNode) {
                    $("#" + treeNode.tId + "_ico").remove();
                }
            },
            callback: {
                beforeAsync: ztreeOpt.beforeAsync,
                onAsyncSuccess: ztreeOpt.onAsyncSuccess,
                onAsyncError: ztreeOpt.onAsyncError,
                onClick: ztreeOpt.nodeOnClick
            }
        };
        treeObj = $.fn.zTree.init(domMap.$tree, settings);
        return treeObj;
    })();
    
    // event
    // search user
    domMap.$searchArea.on('input propertychange', (function() {
        var timer = null,
            startTime;

        return function() {
            var val = $(this).val().toLowerCase(),
                curTime = +new Date();

            clearTimeout(timer);
            // to prevent the pre search
            _do_search = false;
            if (!startTime) {
                startTime = curTime;
            }

            if (curTime - startTime >= 600) {
                // deal with the search request
                _do_search = true;
                searchUser(val);
            } else {
                startTime = curTime;
                timer = setTimeout(function() {
                    _do_search = true;
                    searchUser(val);
                }, 600);
            }
        };
    })());

    // show userinfo
    domMap.$main.on("click", '[data-type]', function(evt) {
        var data = $(this).data();
        Sidebar.render(data);
    });

    //close userinfo
    domMap.$sidebar.on("click", '[data-evt="close"]', function(evt) {
        Sidebar.hide();
    });

    // company
    domMap.$corpUnit.find("a").on("click", function(){
        renderCompany({
            deptid: 0
        });
        beforeDeptId = 0;
        $(this).closest("tr").addClass("dep-active");
        domMap.$tree.find(".curSelectedNode").removeClass("curSelectedNode");
    }).trigger("click");

    $(win).on("scroll", function(){
        var scrollTop = $(this).scrollTop();

        if( scrollTop >= 70 ){
            domMap.$clListHeader.css({
                position: 'relative',
                top: scrollTop - 70
            });
            domMap.$sidebar.css({
                top: 60
            });
        }else{
            domMap.$clListHeader.css({
                position: 'static'
            });
            domMap.$sidebar.css({
                top: 130 - scrollTop
            });
        }
    });
    
    //print
    domMap.$printUser.on("click", function() {
        ajaxApi.printUser({
            uids: getUids[getUids.type](Pagination.data)
        }).done(function(res) {
            if (res.isSuccess) {
                $('body').find('.main-content').remove().end().append(res.view);
            }
            window.print();
        });
    });

    //export
    domMap.$exportUser.on("click", function() {
        domMap.$exportUids.val(getUids[getUids.type](Pagination.data));
        domMap.$exportForm.attr("action", Ibos.app.url("contact/default/export")).submit();
    });

    return Contact;
});