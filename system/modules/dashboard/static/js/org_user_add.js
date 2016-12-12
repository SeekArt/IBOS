$(function() {
    $("#user_supervisor").userSelect({
        data: Ibos.data.get("user"),
        type: "user",
        maximumSelectionSize: "1"
    });

    $("#sub_subordinate").userSelect({
        data: Ibos.data.get("user"),
        type: "user"
    });

    $("#user_department").userSelect({
        data: Ibos.data.get("department"),
        type: "department",
        maximumSelectionSize: "1"
    });

    $("#auxiliary_department").userSelect({
        data: Ibos.data.get("department"),
        type: "department"
    });

    $("#user_position").userSelect({
        data: Ibos.data.get("position"),
        type: "position",
        maximumSelectionSize: "1"
    });

    $("#auxiliary_position").userSelect({
        data: Ibos.data.get("position"),
        type: "position"
    });

    $("#sub_position").userSelect({
        data: Ibos.data.get("position"),
        type: "user"
    });

    // 角色初选择框始化
    $("#role_select").userSelect({
        data: Ibos.data.get("role"),
        type: "role",
        maximumSelectionSize: "1"
    });

    // 辅助角色初始化
    $("#auxiliary_role_select").userSelect({
        data: Ibos.data.get("role"),
        type: "role"
    });

    // 通用AJAX验证配置
    var ajaxValidateSettings = {
        type: 'GET',
        dataType: "json",
        async: true,
        url: Ibos.app.url("dashboard/user/isRegistered"),
        success: function(res) {
            //数据是否可用？可用则返回true，否则返回false
            return !!res.isSuccess;
        },
        buttons: $(".btn btn-large btn-submit btn-primary"),
    };

    $.formValidator.initConfig({
        formID: "user_form",
        errorFocus: true
    });

    // 用户名
    $("#username").formValidator({
            empty: true,
            onFocus: U.lang("V.USERNAME_VALIDATE")
        })
        .inputValidator({
            min: 4,
            max: 20,
            onError: U.lang("V.USERNAME_VALIDATE")
        })
        //验证用户名是否已被注册
        .ajaxValidator($.extend(ajaxValidateSettings, {
            onError: U.lang("V.USERNAME_EXISTED")
        }));

    // 密码
    var pwdSettings = Ibos.app.g("password"),
        pwdErrorTip = U.lang("V.PASSWORD_LENGTH_RULE", {
            min: pwdSettings.minLength,
            max: pwdSettings.maxLength
        });

    $("#password").formValidator({
            onFocus: pwdErrorTip
        })
        .inputValidator({
            min: pwdSettings.minLength,
            max: pwdSettings.maxLength,
            onError: pwdErrorTip
        })
        .regexValidator({
            regExp: pwdSettings.regex,
            dataType: "string",
            onError: U.lang("RULE.CONTAIN_NUM_AND_LETTER")
        });

    // 真实姓名
    $("#realname").formValidator()
        .regexValidator({
            regExp: "notempty",
            dataType: "enum",
            onError: U.lang("RULE.REALNAME_CANNOT_BE_EMPTY")
        });

    $("#mobile").formValidator()
        .regexValidator({
            regExp: "mobile",
            dataType: "enum",
            onError: U.lang("RULE.MOBILE_INVALID_FORMAT")
        })
        //验证手机是否已被注册
        .ajaxValidator($.extend(ajaxValidateSettings, {
            onError: U.lang("V.MOBILE_EXISTED"),
        }));

    $("#email").formValidator({
            empty: true
        })
        .regexValidator({
            regExp: "email",
            dataType: "enum",
            onError: U.lang("RULE.EMAIL_INVALID_FORMAT")
        })
        //验证邮箱是否已被注册
        .ajaxValidator($.extend(ajaxValidateSettings, {
            onError: U.lang("V.EMAIL_EXISTED"),
        }));

    $("#jobnumber").formValidator({
            empty: true
        })
        //验证工号是否已被注册
        .ajaxValidator($.extend(ajaxValidateSettings, {
            onError: U.lang("V.JOBNUMBER_EXISTED"),
        }));

    $(".toggle-btn").on("click", function() {
        var target = $(this).data("target");
        $(target).toggle();
    });

    $('#user_form').submit(function() {
        window.sessionStorage.clear();
    });
});