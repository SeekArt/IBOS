(function(){
    var af = "vote_form";
    $.formValidator.initConfig({formID: af, onError: Ibosapp.formValidate.pageErro});
    $("#subject").formValidator({onFocus: U.lang("VOTE.SUBJECT_CANNOT_BE_EMPTY")})
        .regexValidator({
            regExp: "notempty",
            dataType: "enum",
            onError: U.lang("VOTE.SUBJECT_CANNOT_BE_EMPTY")
        });

    $('#publishScope').formValidator({
            relativeID: "publishScope_row",
            onFocus: U.lang("VOTE.PUBLISH_RANGE_CANNOT_BE_EMPTY")
        })
        .functionValidator({
            fun: function () {
                if (!!$('#publishScope').val()) {
                    return true;
                }

                return false;
            },
            onError: U.lang("VOTE.PUBLISH_RANGE_CANNOT_BE_EMPTY")
        }).on("change", function () {
        $(this).trigger("blur");
    });

    $("#content").formValidator({onFocus: U.lang("VOTE.DESC_CANNOT_BE_EMPTY")})
        .regexValidator({
            regExp: "notempty",
            dataType: "enum",
            onError: U.lang("VOTE.DESC_CANNOT_BE_EMPTY")
        });

    // 验证表单
    $("#" + af).on("vote.submit", function (ev) {
        var _this = this;
        if ($.data(this, "submiting")) {
            return false;
        }
        if ($.formValidator.pageIsValid()) {
            var data = $(this).serializeArray();
            $.data(_this, "submiting", true);
            data.push({
                name: "vote[voteid]",
                value: U.getUrlParam().voteid
            });
            $.post(Ibos.app.url("vote/form/addorupdate"), data, function (res) {
                if (res.isSuccess) {
                    location.href = Ibos.app.url("vote/default/index");
                } else {
                    $.data(_this, "submiting", false);
                    Ui.tip(res.msg, "danger");
                }
            }, "json").error(function(res) { 
                Ui.tip(JSON.parse(res.responseText).msg, "danger");
                $.data(_this, "submiting", false);
            });
        }
        return false;
    });

    Ibos.checkFormChange("#" + af);

    $("#publishScope").userSelect({
        data: Ibos.data.get()
    });

    // 预览
    $('#prewiew_submit').on('click', function () {
        var url = Ibos.app.url("vote/default/show");
        window.open(url);
    });
})();