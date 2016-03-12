// JavaScript Document

//正则表达式规则集合(可扩展)
var rNoEmpty = /\S+/; //不为空
var reg = {
    username: rNoEmpty,
    DBpassword: rNoEmpty,
    account: /^1\d{10}$/,
    ADpassword: /^.{5,32}$/, //6到32位数字或者字母组成
    ADname: rNoEmpty,
    shortname: /^.{4,8}$/,
    fullname: rNoEmpty,
    qycode: /^[a-zA-Z]{4,20}$/,
    mobile: /^1\d{10}$/
};

// 对表单中每项进行验证   
var validate = {
    // 对数据库用户名进行验证
    username: function(id) {
        var value = $("#" + id).val();
        if (!reg.username.test(value)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
    },
    // 对数据库密码进行验证
    DBpassword: function(id) {
        var value = $("#" + id).val();
        if (!reg.DBpassword.test(value)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
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
            $tip.text("账号不能为空！").show();
        }
        return true;
    },
    // 对管理员密码进行验证
    ADpassword: function(id) {
        var value = $("#" + id).val();
        if (!reg.ADpassword.test(value)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
    },
    // 对管理员用户名进行验证
    ADname: function(id) {
        var value = $("#" + id).val();
        if (!reg.fullname.test(value)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
    },
    // 对企业全称进行验证
    fullname: function(id) {
        var val = $("#" + id).val();
        if (!reg.fullname.test(val)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
    },
    // 对企业简称进行验证
    shortname: function(id) {
        var val = $("#" + id).val();
        if (!reg.shortname.test(val)) {
            $("#" + id + "_tip").show();
            return false;
        }
        return true;
    },
    // 对企业代码进行验证
    "qycode": function(id) {
        var val = $("#" + id).val(),
            $tip = $("#" + id + "_tip"),
            ajaxverify = +$("#" + id + "_verify").val(),
            status = $("#" + id + "_verify").data("status");
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

$(function() {
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

    //对数据库账号在获取焦点和失去焦点时进行验证操作
    $("#database_name").on({
        "blur": function() {
            var $elem = $(this);
            validate.username(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    //对数据库密码在获取焦点和失去焦点时进行验证操作
    $("#database_password").on({
        "blur": function() {
            validate.DBpassword(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    //对管理员账号在获取焦点和失去焦点时进行验证操作
    $("#administrator_account").on({
        "blur": function() {
            validate.account(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    // 对管理员密码在获取焦点和失去焦点时进行验证操作
    $("#administrator_password").on({
        "blur": function() {
            validate.ADpassword(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    // 对管理员用户名在获取焦点和失去焦点时进行验证操作
    $("#administrator_name").on({
        "blur": function() {
            validate.ADname(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    })

    // 企业全称进行验证
    $("#full_name").on({
        "blur": function() {
            validate.fullname(this.id);
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    // 企业简称进行验证
    $("#short_name").on({
        "blur": function() {
            if (validate.shortname(this.id)) {
                var py = pinyinEngine.toPinyin($(this).val(), true),
                    text = py.map(function(item) {
                        return item.substring(0, 1);
                    });
                $("#qy_code").val(text.join(""));
            }
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    // 企业代码进行验证
    $("#qy_code").on({
        "blur": function() {
            var id = this.id,
                val = $("#" + id).val(),
                param = {
                    code: val
                },
                $tip = $("#" + id + "_tip"),
                $ajaxverify = $("#" + id + "_verify");
            status = $ajaxverify.attr("data-status");
            // 先验证企业代码
            if (status == "link") {
                dbInit.op.verifyCorpCode(param).done(function(res) {
                    if (res.isSuccess) {
                        var isAvailable = res.available;
                        if (!isAvailable) {
                            $tip.text("企业代码已存在！").show();
                            $ajaxverify.val("0");
                        } else {
                            $ajaxverify.val("1");
                            $tip.hide();
                        }
                        validate.qycode(id);
                    } else {
                        Ui.tip(res.msg, "danger");
                    }
                });
            } else {
                validate.qycode(id);
            }
        },
        "focus": function() {
            $("#" + this.id + "_tip").hide();
        }
    });

    //点击立即安装时,对表单进行验证
    $("#user_form").submit(function() {
        var elems = $(this).get(0).elements;
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
    });
});
