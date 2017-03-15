var IbosCoLogin = {
    // site_url: (function() {
    //     var SITE_URL = [window.location.protocol, "//", window.location.hostname, "/"].join("");
    //     Ibos.app.s("SITE_URL", SITE_URL);
    //     return SITE_URL;
    // })(),
    init: function() {
        this.docEvt();
        this.formValidate();
    },
    elem: {
        // dialog
        register_dialog: $("#ibosco_register_dialog"),
        login_dialog: $("#ibosco_login_dialog"),
        isBinding_login: $('[data-action="loginCorp"]'),
        // button
        next_reg_state: $("#next_reg_state"),
        reg_and_bind: $("#reg_and_bind"),
        login_and_bind: $("#login_and_bind"),
        // tab
        user_reg_verify: $("#user_reg_verify"),
        user_reg_info: $("#user_reg_info"),
        // input register
        inputMobile: $("#inputMobile"),
        inputMobileVerify: $("#inputMobileVerify"),
        // input login
        mobile: $("#mobile"),
        password: $("#password"),
        // develop user info
        user_name: $("#user_name"),
        user_password: $("#user_password"),
        user_invite: $("#user_invite")
    },
    op: {
        getMobileVerifyCode: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/sendverifycode');
            if (Ibos.app.g('page') === 'wxbind') {
                url = Ibos.app.url('dashboard/wxbinding/sendcode');
            }
            return $.post(url, param, $.noop, "json");
        },
        checkverifycode: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/checkverifycode');
            if (Ibos.app.g('page') === 'wxbind') {
                url = Ibos.app.url('dashboard/wxbinding/checkcode');
            }
            return $.post(url, param, $.noop, "json");
        },
        checkmobile: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/checkmobile');
            if (Ibos.app.g('page') === 'wxbind') {
                url = Ibos.app.url('dashboard/wxbinding/checkmobile');
            }
            return $.post(url, param, $.noop, "json");
        },
        registercouser: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/registercouser');
            if (Ibos.app.g('page') === 'wxbind') {
                url = Ibos.app.url('dashboard/wxbinding/register');
            }
            return $.post(url, param, $.noop, "json");
        },
        loginbypassword: function(param) {
            var url = Ibos.app.url('dashboard/cobinding/loginbypassword');
            if (Ibos.app.g('page') === 'wxbind') {
                url = Ibos.app.url('dashboard/wxbinding/login');
            }
            return $.post(url, param, $.noop, "json");
        }
    },
    // 全局事件监听
    docEvt: function() {
        var that = this,
            elem = that.elem;
        // 键盘回车监听
        $(document).on('keydown', function(evt) {
            var evt = evt || window.event;

            if (evt && evt.keyCode === 13) {
                if (elem.isBinding_login.length) {
                    Ibos.evt.fire("loginCorp");
                } else if (elem.login_dialog.css('display') === "block") {
                    that.loginCo();
                } else if (elem.register_dialog.css('display') === "block") {
                    if (elem.user_reg_verify.css('display') === "block") {
                        that.nextState();
                    } else {
                        that.registerCo();
                    }
                } else {
                    evt.preventDefault();
                    evt.returnValue = false;
                }
            }
        });
        // 点击事件监听
        $(document).on('click', function(evt) {
            var evt = evt || window.evt,
                src = evt.target || evt.srcElement;

            var evtname = $(src).data("evtname");
            switch (evtname) {
                case 'nextState':
                    that.nextState();
                    break;
                case 'registerCo':
                    that.registerCo();
                    break;
                case 'loginCo':
                    that.loginCo();
                    break;
            }
        });
    },
    // 表单验证
    formValidate: function() {
        var that = this,
            elem = that.elem;

        $.formValidator.initConfig({
            formID: "ibosco_register_form",
            errorFocus: true,
            validatorGroup: "1"
        });

        elem.inputMobile.formValidator({
            validatorGroup: "1",
            onFocus: "请输入手机号码"
        }).regexValidator({
            regExp: "mobile",
            dataType: "enum",
            onError: function(text) {
                return $.trim(text) ? Ibos.l("RULE.MOBILE_INVALID_FORMAT") : "手机号码不能为空";
            }
        });

        elem.inputMobileVerify.formValidator({
            validatorGroup: "1",
            onFocus: "请输入验证码"
        }).regexValidator({
            regExp: "\\d{4}",
            dataType: "number",
            onError: function(text) {
                return $.trim(text) ? "验证码格式错误" : "验证码不能为空";
            }
        });

        $.formValidator.initConfig({
            formID: "ibosco_login_form",
            errorFocus: true,
            validatorGroup: "2"
        });

        elem.mobile.formValidator({
            validatorGroup: "2",
            onFocus: "请输入手机号码"
        }).regexValidator({
            regExp: "mobile",
            dataType: "enum",
            onError: function(text) {
                return $.trim(text) ? Ibos.l("RULE.MOBILE_INVALID_FORMAT") : "手机号码不能为空";
            }
        });

        elem.password.formValidator({
            validatorGroup: "2",
            onFocus: "请输入密码"
        }).regexValidator({
            regExp: "notempty",
            dataType: "enum",
            onError: "密码不能为空"
        });
    },
    // 手机注册验证，完善下一步
    nextState: function() {
        var that = this,
            inputMobile = that.elem.inputMobile,
            inputMobileVerify = that.elem.inputMobileVerify,
            reg_verify = that.elem.user_reg_verify,
            reg_info = that.elem.user_reg_info;

        if (!$.formValidator.pageIsValid("1")) return;
        that.op.checkverifycode({
            mobile: inputMobile.val(),
            verifyCode: inputMobileVerify.val()
        }).done(function(res) {
            if (res.isSuccess) {
                // 跳转到信息页
                reg_verify.hide();
                reg_info.show();
            } else {
                Ui.tip(res.msg, "danger");
                return false;
            }
        }, "json");
    },
    // 完善信息并注册酷办公
    registerCo: function() {
        var that = this,
            inputMobile = that.elem.inputMobile,
            user_name = that.elem.user_name,
            user_password = that.elem.user_password,
            user_invite = that.elem.user_invite;

        if ($.trim(user_password.val()) === '') {
            Ui.tip(Ibos.l("CO.SET_CO_LOGIN_PWD"), "warning");
            user_password.blink().focus();
            return false;
        }

        that.op.registercouser({
            mobile: inputMobile.val(),
            realname: user_name.val(),
            password: user_password.val(),
            invite: user_invite.val()
        }).done(function(res) {
            if (res.isSuccess) {
                Ui.tip(Ibos.l("CO.CO_REG_SUCCESS"));
                $(IbosCoLogin).trigger("regsuccess", {
                    res: res
                });
            } else {
                Ui.tip(res.msg, "danger");
                return false;
            }
        }, 'json');
    },
    // 登录酷办公
    loginCo: function() {
        var that = this,
            mobile = that.elem.mobile,
            password = that.elem.password;

        if (!$.formValidator.pageIsValid("2")) return;
        that.op.loginbypassword({
            mobile: mobile.val(),
            password: password.val()
        }).done(function(res) {
            if (res.isSuccess) {
                Ui.tip(Ibos.l("CO.LOGIN_SUCCESS"));
                $(IbosCoLogin).trigger("loginsuccess", {
                    res: res
                });
            } else {
                Ui.tip(res.msg, "danger");
                return false;
            }
        });
    }
};
$(function() {

    IbosCoLogin.init();

    Ibos.evt.add({
        // 未绑定登录
        "bindIbosCo": function(param, elem) {
            // 确认登录窗口是否存在
            var login = Ui.getDialog("ibosco_login_dialog");
            if (login) {
                login.close();
            }

            Ui.dialog({
                title: false,
                id: "ibosco_register_dialog",
                lock: true,
                content: document.getElementById("ibosco_register_dialog"),
                okVal: "",
                close: function() {
                    $.formValidator.resetTipState("1");
                    $("#ibosco_register_dialog input").removeClass('input-error');
                    return true;
                }
            });
        },
        "bindLoginCo": function(param, elem) {
            // 确认注册窗口是否存在
            var reg = Ui.getDialog("ibosco_register_dialog");
            if (reg) {
                reg.close();
            }

            Ui.dialog({
                title: false,
                id: "ibosco_login_dialog",
                lock: true,
                content: document.getElementById("ibosco_login_dialog"),
                okVal: "",
                close: function() {
                    $.formValidator.resetTipState("2");
                    $("#ibosco_login_dialog input").removeClass('input-error');
                    return true;
                }
            });
        },
        verifyCodeLock: false,
        // 获取注册验证
        "getMobileVerifyCode": function(param, elem) {
            var $this = $(elem),
                that = this,
                elem = IbosCoLogin.elem,
                loading_text = $this.data("loading-text");

            if (that.verifyCodeLock) {
                Ui.tip("验证码有效时间1分钟内不能重复发送！", "warning");
                return false;
            }

            if (!U.regex(elem.inputMobile.val(), "mobile")) {
                Ui.tip("手机格式不正确", "warning");
                elem.inputMobile.blink().focus();
                return false;
            }

            // 手机验证
            IbosCoLogin.op.checkmobile({
                mobile: elem.inputMobile.val()
            }).done(function(res) {
                if (res.isSuccess) {
                    IbosCoLogin.op.getMobileVerifyCode({
                        mobile: elem.inputMobile.val()
                    }).done(function(res) {
                        if (res.isSuccess) {
                            $this.html(loading_text);
                            var wait = document.getElementById('mobile_counting'),
                                time = --wait.innerHTML,
                                interval = setInterval(function() {
                                    var time = --wait.innerHTML;
                                    if (time === 0) {
                                        $this.html('发送验证码');
                                        that.verifyCodeLock = false;
                                        clearInterval(interval);
                                    }
                                }, 1000);
                            that.verifyCodeLock = true;
                        } else {
                            $this.button('发送验证码');
                            $('#send_mobile_status').html(res.msg);
                        }
                    });
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            }, 'json');
        },
        // 已绑定登录
        "loginCorp": function(param, elem) {
            IbosCoLogin.op.loginbypassword({
                mobile: $("#bind_mobile").val(),
                password: $("#bind_password").val()
            }).done(function(res) {
                if (res.isSuccess) {
                    Ui.tip(Ibos.l("CO.LOGIN_SUCCESS"));
                    $(IbosCoLogin).trigger("loginsuccess", {
                        res: res
                    });
                } else {
                    Ui.tip(res.msg, "danger");
                    return false;
                }
            });
        }
    });
});