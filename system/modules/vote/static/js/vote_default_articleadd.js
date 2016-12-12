// 上传组件最在打开图片投票时初始化，不要在一进入页面只初始化
//加载完成后马上初始化三个投票项
$(function () {
    var checkForm = function (ev) {
        var isVoteEnabled = $("#voteStatus").prop("checked");

        if( isVoteEnabled === false ){
            $(this).trigger("form.submit");
        }else{
           var $projects = $(".vote-project");
               $subjects = $projects.find(".vote_subject");

           for (var i = 0; i < $subjects.length; i++) {
               var $subject = $subjects.eq(i);
               if (!$.trim($subject.val())) {
                   $subject.blink();
                   Ui.tip(Ibos.l("VOTE.VOTE_TITLE"), "warning");
                   return false;
               }
               var $project = $projects.eq(i),
                   type = $project.find(".topic-type").val(),
                   validate = 0,
                   $contents;
               if( type == 1 ){
                   $contents = $project.find(".custom-list input[type='text']");
               }else if( type == 2 ){
                   $contents = $project.find(".custom-list [data-picpath]");
               }
               for (var j = 0; j < $contents.length; j++) {
                   if ($.trim($contents[j].value)) {
                       validate++;
                   }
               }
               if( validate < 2 && type == 1 ){
                   Ui.tip(Ibos.l("VOTE.WRITE_AT_LEAST_TWO_ITEM"), "warning");
                   return false;
               }
               if( $contents.length != validate && type == 2 ){
                   Ui.tip(Ibos.l("VOTE.IMAGE_TYPE_MUST_FULL"), "warning");
                   return false;
               }
           }
           $(this).trigger("vote.submit");
           $(this).trigger("form.submit"); 
       }
    };


    // 投票项数验证，至少两条有效数据
    $("#vote_form").on("submit", checkForm);
    $("#article_form").on("submit", checkForm);
});
