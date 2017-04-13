// 检查lock文件是否存在
var lockDetection = {
    op: {
        installCheck: function(){
            return $.post("api.php", $.noop, "json");
        }
    },
    init: function(){
        var _this = this;
        this.op.installCheck().done(function(res){
            $(".version-info").text(res.data.version);
            if( res.isSuccess ){
                _this.nextStep();
            }else{
                result.error(res.msg);
            }
        });
    },
    nextStep: function(){
        envCheck.init();
    }
};
lockDetection.init();
/**
 * 检查环境需求
 * envCheck
 * @type {Object}
 */
var envCheck = {
    $el: $("#envCheck"),
    first: 1,
    op: {
        validate: function(){
            return $.post("api.php?op=envCheck", $.noop, 'json');
        }
    },
    init: function(){
        var _this = this;
        this.op.validate().done(function(res){
            if(res.isSuccess){
                _this.$el.remove();
                _this.nextStep();
            }else{
                _this.render(res.data);
            }
        });
    },
    render: function(data){
        var _this = this;

        this.$el.html( $.template($("#env_check_tpl").html(), data ) );

        if(this.first){
            _this.first = 0;
            _this.next();
        }
        _this.$el.show();
    },
    next: function(){
        var _this = this;
        this.$el.on("click", "button", function(){
            _this.init();
        });

        this.$el.on("click", ".showmore", function(){
			var $elem = $(this),
				$icon = $elem.find("i");
			// 收起
			if($icon.hasClass("o-pack-up")){
				$icon.removeClass("o-pack-up").addClass("o-pack-down");
				$elem.find("span").text("展开")
				$elem.parent().next().slideUp(200);
			} else {
				$icon.removeClass("o-pack-down").addClass("o-pack-up");
				$elem.find("span").text("收起")
				$elem.parent().next().slideDown(200);
			}
		});
    },
    nextStep: function(){
        this.$el.remove();
        dbInit.init();
    }
};
/**
 * 检查是否有配置
 * [dbInit description]
 * @type {Object}
 */
var dbInit = {
    $el: $("#dbInit"),
    first: 1,
    op: {
        getdbinfo: function(){
            return $.post("api.php?op=configCheck", $.noop, 'json');
        },
        validate: function(data){
            return $.post("api.php?op=dbCheck", data, $.noop, 'json');
        }
    },
    init: function(){
        $("#customInitMoudle").hide();
        $("#user_form").data("submiting", false)
        $("#tablepre_exist_tip").hide().find("input").label("uncheck");
        var _this = this;
        if( this.first ){
            this.op.getdbinfo().done(function(res){
                var data = res.data;
                if(res.isSuccess){
                    $("#database_name").val(data.username);
                    $("#database_password").val(data.password);
                    $("#database_server").val(data.host +":"+ data.port);
                    $("#dbname").val(data.dbname);
                    _this.$el.show();
                    $.getScript("static/js/db_init.js");
                    _this.next();
                    _this.first = 0;
                }
            });
        }else{
            _this.$el.show();
        }
    },
    next: function(){
        var _this = this;
        $("#user_form").on("validate", function(evt,data){
            var that = this;
            if( $.data(this, "submiting") ) return;

            $.data(this, "submiting", true);
            _this.op.validate(data).done(function(res){
                if (res.isSuccess) {
                    _this.data = data;
                    _this.nextStep(data);
                } else {
                    $.data(that, "submiting", false);
                    _this.errorInfo(res);
                }
            }).error(function(res){
                $.data(that, "submiting", false);
                var data = JSON.parse(res.responseText.match(/{(.*?)}$/)[0]);
                _this.errorInfo(data);
            });
        });
    },
    errorInfo: function(res){
        switch (res.data.type) {
            case "dbpre":
                // 显示强制数据库插入信息
                $("#enforce_info").html(res.msg);
                $("#tablepre_exist_tip").show();
                break;
            case "dbHost":
                $("[name='"+res.data.type +"']").blink();
                Ui.tip(res.msg, "danger");
                break;
            case "engine":
                Ui.tip(res.msg, "danger");
                break;
            default:
                $("[name='"+res.data.type +"']").blink().siblings(".nomatch-tip").text(res.msg).show();
        }
    },
    nextStep: function(data){
        this.$el.hide();
        if(data.custom == "0"){
            customModules.init(data);
        }else{
            installing.init();
        }
    }
};

/** 自定义模块
 * [customModules description]
 * @type {Object}
 */
var customModules = {
    $el: $("#customInitMoudle"),
    first: 1,
    op: {
        getModule: function(){
            return $.post("api.php?op=moduleCheck", $.noop, "json");
        },
        handleInstall: function(data){
            return $.post("api.php?op=dbCheck", data, $.noop, "json");
        }
    },
    init: function(data){
        this.render();
        this.data = data;
    },
    render: function(){
        var _this = this;
        if( this.first ){
            this.op.getModule().done(function(res){
                if(res.isSuccess){
                    var data = res.data,
                        tmpl = $("#module_tpl").html();
                    $("#coreModules").html( $.template(tmpl, {
                        module: data.coreModule,
                        name: "coreModules"
                    }) );
                    $("#customModules").html( $.template(tmpl, {
                        module: data.customModule,
                        name: "customModules"
                    }) );
                    _this.$el.find("[type='checkbox']").label();
                    _this.$el.show();
                    _this.first = 0;
                    _this.next();
                }
            });
        }else{
            _this.$el.show();
        }
    },
    next: function(){
        var _this = this;
        this.$el.on("click", "[type='submit']", function(){
            var customModules = U.getCheckedValue("customModules[]");
            var coreModules = U.getCheckedValue("coreModules[]");
			var modules = coreModules;
			if( customModules !== ''  ){
				modules += "," + customModules;
			}
            _this.data["customModules[]"] = modules;
            _this.op.handleInstall(_this.data).done(function(res){
                if(res.isSuccess){
                    _this.nextStep(modules);
                }
            });
        });
    },
    nextStep: function(modules){
        this.$el.hide();
        installing.init(modules);
    }
};

/**
 * 安装中
 * [installing description]
 * @type {Object}
 */
var installing = {
    $el: $("#installing"),
    op: {
        updateData: function(data){
            return $.post("api.php?op=handleUpdateData", data, $.noop, "json");
        },
        installResult: function(){
            return $.post("api.php?op=handleInstallResult", $.noop, "json");
        }
    },
    modules: "",
    init: function(data){
        var _this = this;
        this.$el.show();
        if( data != undefined ){
            this.modules = data;
            this.install();
        }else{
            customModules.op.getModule().done(function(res){
                if(res.isSuccess){
                    var customModule = res.data.customModule;
                    for(var attr in customModule){
                        _this.modules += attr + ",";
                    }
                    _this.modules = _this.modules.slice(0, -1);
                    _this.install();
                }
            });
        }
    },
    // 循环安装模块
    install: function(){
        var _this = this,
            installModules = this.modules,
            installUrl = "api.php?op=handleInstall",
            $progressbar = $("#progressbar"),
            $show_process = $("#show_process"),
            $install_info = $("#install_info");

        function install(module) {
            $.post(installUrl, {modules: installModules, installingModule: module}, function (res) {
                var data = res.data;
                if (data.complete) {
                    $progressbar.css("width", data.process);
                    $show_process.text(data.process);
                    $install_info.text("模块安装完成，正在初始化系统...");
                    _this.updateData(installModules);
                } else {
                    if (res.isSuccess) {
                        $progressbar.css("width", data.process);
                        $show_process.text(data.process);
						$install_info.text("正在安装 "+ data.nextModuleName +" ,请稍等...");
                        install(data.nextModule);
                    } else {
                        result.error(res.msg);
                    }
                }
            }, 'json');
        }
		$install_info.text("准备安装...");
        install();
    },
    /**
     * 处理数据更新
     * [function description]
     * @return {[type]} [description]
     */
    updateData: function(data){
        var _this = this;
        this.op.updateData({
            modules: data
        }).done(function(res){
            if(res.isSuccess){
                result.success();
            }
        });
    }
};

/**
 * 安装结果
 * [result description]
 * @type {Object}
 */
var result = {
    op:{
        ajaxLogin: function(root, data){
            return $.post(root + "/?r=user/default/ajaxlogin", data, $.noop, 'json');
        }
    },
    success: function(){
        installing.$el.remove();
        var href = location.href,
            root = href.slice(0, href.lastIndexOf("/install"));

        this.op.ajaxLogin(root, {
            username: dbInit.data.adminName,
            password: dbInit.data.adminPassword
        }).done(function(){
            var iframe = document.getElementById('binding');
            iframe.src = root + "/?r=dashboard/cobinding/index&isInstall=1";
        });
		 $(".install-login").on("click", function(){
			location.href = root;
		});
        $("#result_success").show();
    },
    error: function(text){
        installing.$el.remove();
        $(".failure-msg").text(text);
        $("#result_error").show();
    }
};
