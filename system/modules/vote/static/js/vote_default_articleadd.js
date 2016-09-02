// 上传组件最在打开图片投票时初始化，不要在一进入页面只初始化
//加载完成后马上初始化三个投票项
$(function() {
    for (var i = 0; i < 3; i++) {
        Vote.textList.addItem();
        Vote.picList.addItem();
    }

     // 投票项数验证，至少两条有效数据
    $("#article_form").on("submit", function(ev) {
        var isVoteEnabled = $("#voteStatus").prop("checked"),
                $items;
        if (isVoteEnabled) {
            var voteType = Vote.getVoteType(),
                    subject = $("#" + (voteType == "1" ? "vote_subject" : "imageVote_subject")).val().replace(/(^\s*)|(\s*$)/g, "");
            if (subject == '' || subject == null) {
                Ui.tip(Ibos.l("VOTE.VOTE_TITLE"), "warning");
                return false;
            }
            $items = Vote.getValidItem();
            if ($items.length < 2) {
                Ui.tip(Ibos.l("VOTE.WRITE_AT_LEAST_TWO_ITEM"), "warning");
                return false;
            }
        }
        $(this).trigger("form.submit");
    });
});
