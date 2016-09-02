// JavaScript Document
(function(){
    //正则表达式规则集合(可扩展)
    var rNoEmpty = /\S+/; //不为空
    var reg = {
        username: rNoEmpty,
        DBpassword: rNoEmpty,
        account: /^1\d{10}$/,
        ADpassword: /^.{5,32}$/, //5到32位数字或者字母组成
        ADname: rNoEmpty,
        shortname: /^.{2,10}$/,
        fullname: rNoEmpty,
        qycode: /^[a-zA-Z0-9]{4,20}$/,
        mobile: /^1\d{10}$/
    };

    // 对表单中每项进行验证
    var validate = {
        common: function(id, type){
            var value = $("#" + id).val();
            if (!reg[type].test(value)) {
                $("#" + id + "_tip").show();
                return false;
            }
            return true;
        },
        // 对数据库用户名进行验证
        username: function(id) {
            return this.common(id, "username");
        },
        // 对数据库密码进行验证
        DBpassword: function(id) {
            return this.common(id, "DBpassword");
        },
        // 对管理员账号进行验证
        account: function(id) {
            var value = $("#" + id).val(),
                $tip = $("#" + id + "_tip");
            if (value) {
                if (!reg.account.test(value)) {
                    $tip.text("请输入正确的手机号！").show();
                    return false;
                }
            } else {
                $tip.text("手机号不能为空！").show();
                return false;
            }
            return true;
        },
        // 对管理员密码进行验证
        ADpassword: function(id) {
            return this.common(id, "ADpassword");
        },
        // 对管理员用户名进行验证
        ADname: function(id) {
            return this.common(id, "ADname");
        },
        // 对企业全称进行验证
        fullname: function(id) {
            return this.common(id, "fullname");
        },
        // 对企业简称进行验证
        shortname: function(id) {
            return this.common(id, "shortname");
        },
        // 对企业代码进行验证
        "qycode": function(id) {
            var val = $("#" + id).val(),
                $tip = $("#" + id + "_tip"),
                ajaxverify = +$("#" + id + "_verify").val(),
                status = $("#" + id + "_verify").data("status");
            if( !$.trim(val) ){
                $tip.text("企业代码不能为空！").show();
                return false;
            }
            if (!reg.qycode.test(val)) {
                $tip.text("企业代码格式不正确！").show();
                return false;
            } else {
                if (status == "link" && !ajaxverify) {
                    $tip.text("企业代码已存在！").show();
                    return false;
                }
            }
            return true;
        }
    };


    $("#user_form input").on("blur", function() {
        var $elem = $(this),
            id = this.id,
            type = $elem.data("type");
        switch (type) {
            case "DBpassword":
            case "account":
            case "ADpassword":
            case "ADname":
            case "fullname":
            case "qycode":
                validate[type](id);
                break;
            case "shortname":
                if( validate[type](id) ){
                    var py = pinyinEngine.toPinyin($(this).val(), true),
                        text = py.map(function(item) {
                            return item.substring(0, 1);
                        });
                    $("#qy_code").val(text.join(""));
                }
                break;
            default:
                break;
        }
    }).on("focus", function() {
        $("#" + this.id + "_tip").hide();
    });


    //创建数据页面,点击显示更多后,隐藏部分信息显示
    $("#table_info").on("click", ".show-info", function() {
        $(".hidden-info").slideDown(100, function() {
            $("#database_server").focus();
        });
        $(this).slideUp(100);
    });

    /*
     1.勾选自定义模块,立即安装按钮文字变为"下一步",同时表单提交至"下一步"
     2.取消勾选自定义模块后,下一步按钮文字变为"立即安装",同时表单提交至"立即安装"
     */
    $("#user_defined").on("change", function() {
        var value = $("#user_defined").prop("checked"),
            text = value ? "下一步" : "立即安装";
        $("#btn_install").text(text);
    });

    $("#user_defined").on("change", function () {
        if (this.checked) {
            $("#ext_data").label("uncheck").label("disable");
        } else {
            $("#ext_data").label("enable");
        }
    });

    $("#ext_data").on("change", function () {
        if (this.checked) {
            $("#user_defined").label("uncheck").label("disable");
        } else {
            $("#user_defined").label("enable");
        }
    });


    $("#btn_install").click(function () {
        var $form =  $("#user_form"),
            elems = $form.get(0).elements;
        for (var i = 0; i < elems.length; i++) {
            var elem = elems[i],
                type = elem.getAttribute("data-type"),
                id = elem.id;
            if (validate[type] && !validate[type](id)) {
                // 重置站点数据
                U.clearCookie();
                Ibos.local.clear();
                $(elem).trigger("focus.submit").blink();
                return false;
            }
        }
        U.clearCookie('/');

        $form.trigger("validate", JSON.parse( $form.serializeJSON() ));
    });
})();
