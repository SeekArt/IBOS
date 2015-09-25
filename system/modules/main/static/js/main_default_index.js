(function() {
    // 初始化引导
    var guideUrl = Ibos.app.url('main/default/guide'),
        guideNextTime = Ibos.app.g("guideNextTime");

    if (!guideNextTime) {

        $.post(guideUrl, {op: 'checkIsGuided'}, function(res) {
            if (res.isNewcommer) {
                $("body").append(res.guideView);
                // 使用 formValidate ajax 验证的输入框使用placeholder时
                // 需要把placeholder初始化放在 ajaxValidate之前
                // 否则会由于事件执行顺序干扰造成取值失败
                if ($.fn.placeholder) {
                    $("#initialize_guide [placeholder][type='text']").placeholder();
                }

                var guideJs = res.isadministrator ? 'main_default_adminguide.js' : 'main_default_initguide.js';
                
                Ibos.statics.loads([
                    Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload.packaged.js"),
                    Ibos.app.getStaticUrl("/js/lib/SWFUpload/handlers.js"),
                    Ibos.app.g("assetUrl") + "/js/" + guideJs
                ])
                .done(function(){
                    var left = $(window).width() / 2 - 340;
                    //设置初始化时，初始化页面和遮罩的层级关系
                    $("#initialize_guide").css({"top": "100px", "left": left, "position": "absolute", "zIndex": "12"});
                    Ui.modal.show({zIndex: 11, backgroundColor: "black"});

                    // 当密码框同时需要表单验证和 placeholder 初始化时
                    // 表单验证需要在placeholder 之前
                    // 否则会由于 placeholder 替换导致事件绑错节点
                    if ($.fn.placeholder) {
                        $("#initialize_guide [placeholder][type='password']").placeholder();
                    }
                });
            }
        }, 'json');
    }
})();