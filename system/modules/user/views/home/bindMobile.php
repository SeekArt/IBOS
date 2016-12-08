<div style="width: 450px;">
    <form class="form-horizontal">
        <div class="control-group">
            <input type="text" id="inputMobile" value="<?php echo $user['mobile']; ?>"
                   placeholder="<?php echo $lang['Bind Mobile tip']; ?>">
        </div>
        <div class="control-group">
            <button type='button' id="verify" data-loading-text="<?php echo $lang['Send loading']; ?>"
                    class="btn"><?php echo $lang['Send verify']; ?></button>
            <span id='send_status'></span>
        </div>
        <div class="control-group">
            <input type="text" id="inputVerify" placeholder="<?php echo $lang['Mobile verify']; ?>">
        </div>
    </form>
</div>
<script>
    $('#verify').on('click', function () {
        var that = this;
        if ($.trim($('#inputMobile').val()) === '') {
            $('#inputMobile').blink().focus();
            return false;
        }

        if (!U.regex($('#inputMobile').val(), "mobile")) {
            Ui.tip("@RULE.MOBILE_INVALID_FORMAT", "warning");
            $('#inputMobile').blink().focus();
            return false;
        }

        $(that).button('loading');

        $.get(Ibos.app.url("user/home/checkRepeat", {op: "mobile", uid: Ibos.app.g("uid")}), {
            data: encodeURI($('#inputMobile').val())
        }, function (res) {
            if (res.isSuccess) {
                $('#inputMobile').parent().addClass('success');
                var wait = document.getElementById('counting'),
                    time = --wait.innerHTML,
                    interval = setInterval(function () {
                        var time = --wait.innerHTML;
                        if (time === 0) {
                            $(that).button('reset');
                            clearInterval(interval);
                        }
                    }, 1000);
            } else {
                $(that).button('reset');
                $('#inputMobile').parent().addClass('error');
                $('#send_status').html(res.msg);
            }
        }, 'json');
    });
</script>