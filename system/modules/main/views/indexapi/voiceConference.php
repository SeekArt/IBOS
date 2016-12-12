<?php

use application\core\utils\Cloud;

?>
<div class="fill">
    <div class="mb">
        <input type="text" id="in_meeting_user">
    </div>
    <div class="mb">
        <input type="text" id="in_meeting_phone" placeholder="输入邀请加入的会议成员手机号码，按回车键分隔多个">
    </div>
    <!-- <textarea rows="3" placeholder="输入邀请加入的会议成员手机号码，以;号分隔多个" id="confnumbers" class="mb"></textarea> -->
    <button id="createconf" class="btn btn-primary pull-right">创建会议</button>
</div>
<script>
    Ibos.app.s("voiceConfUrl", "<?php echo Cloud::getInstance()->build('Api/Ivr/Confirmconf'); ?>")
</script>
<script>
    $(function () {
        $("#in_meeting_user").userSelect({
            type: "user",
            data: Ibos.data.get("user")
        });

        $("#in_meeting_phone").ibosSelect({
            tags: [],
            width: "100%",
            pinyin: false
        });

        function formatUserInfo(param) {
            if (param.indexOf("u") == 0) {
                var arr = param.split(",");
                var data = $.map(arr, function (uid) {
                    var data = Ibos.data.getUser(uid);
                    return {uid: uid.slice(2), name: data.text, avatar: data.avatar, phone: data.phone}
                });
                return data;
            } else {
                var arr = param.split(",");
                var avatar = Ibos.app.g("emptyAvatar");
                var data = $.map(arr, function (phone) {
                    return {name: "未知", avatar: avatar, phone: phone}
                });
                return data;
            }
        }

        $("#createconf").on("click", function () {
            var uids = $("#in_meeting_user").val(),
                inside = formatUserInfo(uids),
                phones = $("#in_meeting_phone").val(),
                outside = formatUserInfo(phones),
                param;

            if (!uids && !phones) {
                return Ui.tip("请选择人员或输入手机号码！", "warning");
            }

            var uidArr = uids ? uids.split(",") : [];
            var phoneArr = phones ? phones.split(",") : [];

            if (phoneArr.length + uidArr.length < 2) {
                return Ui.tip("语音会议至少需要 2 位参与人员", "warning");
            }

            if (phoneArr.length) {
                var i = 0, len = phoneArr.length;
                for (; i < len; i++) {
                    if (!U.regex(phoneArr[i], "mobile") && !U.regex(phoneArr[i], "tel")) {
                        return Ui.tip("手机号码格式不正确", "warning");
                    }
                }
            }

            if (phones == "") {
                param = {data: inside};
            } else {
                var data = inside.concat(outside);
                param = {data: data};
            }

            var ajaxUrl = Ibos.app.url('main/call/bilateral', param);

            $.get(Ibos.app.url('main/call/chkConf'), function (res) {
                if (res.isSuccess) {
                    Ui.openFrame(ajaxUrl, {
                        width: '580px',
                        height: '523px',
                        title: false,
                        lock: true,
                        skin: "call-dialog"
                    });
                } else {
                    Ui.tip(res.msg, 'warning');
                }
            }, 'json');
        });
    });
</script>
