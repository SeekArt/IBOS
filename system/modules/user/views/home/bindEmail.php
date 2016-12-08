<div style="width: 450px;">
    <form class="form-horizontal">
        <div class="control-group">
            <input type="text" id="inputEmail" value="<?php echo $user['email']; ?>"
                   placeholder="<?php echo $lang['Bind email tip']; ?>">
        </div>
        <div class="control-group">
            <button type='button' id="verify" data-loading-text="<?php echo $lang['Send loading']; ?>"
                    class="btn"><?php echo $lang['Send verify']; ?></button>
            <span id='send_status'></span>
        </div>
        <div class="control-group">
            <input type="text" id="inputVerify" placeholder="<?php echo $lang['Email verify']; ?>">
        </div>
    </form>
</div>
<script>
    $('#verify').on('click', function () {
        var that = this;
        if ($.trim($('#inputEmail').val()) === '') {
            $('#inputEmail').blink().focus();
            return false;
        }

        if (!U.regex($('#inputEmail').val(), "email")) {

            Ui.tip("@RULE.EMAIL_INVALID_FORMAT", "warning");
            $('#inputEmail').blink().focus();
            return false;
        }
        $(that).button('loading');

        $.get(Ibos.app.url("user/home/checkRepeat", {op: "email", "uid": Ibos.app.g("uid")}), {
            data: encodeURI($('#inputEmail').val())
        }, function (res) {
            if (res.isSuccess) {
                $('#inputEmail').parent().addClass('success');
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
                $('#inputEmail').parent().addClass('error');
                $('#send_status').html(res.msg);
            }
        }, 'json');
    });
</script>