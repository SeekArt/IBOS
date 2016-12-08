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
    $.formValidator.initConfig({
        formID: "user_form",
        errorFocus: true
    });

    $("#username").formValidator({
            empty: true,
            onFocus: U.lang("V.USERNAME_VALIDATE")
        })
        .inputValidator({
            min: 4,
            max: 20,
            onError: U.lang("V.USERNAME_VALIDATE")
        });

    // 密码
    var pwdSettings = Ibos.app.g("password"),
        pwdErrorTip = U.lang("V.PASSWORD_LENGTH_RULE", {
            min: pwdSettings.minLength,
            max: pwdSettings.maxLength
        });

    $("#password").formValidator({
            onFocus: pwdErrorTip,
            empty: true
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
        });

    $("#email").formValidator({
            empty: true
        })
        .regexValidator({
            regExp: "email",
            dataType: "enum",
            onError: U.lang("RULE.EMAIL_INVALID_FORMAT")
        });

    $(".toggle-btn").on("click", function() {
        var target = $(this).data("target");
        $(target).toggle();
    });

    $('#user_form').submit(function() {
        window.sessionStorage.clear();
    });
});