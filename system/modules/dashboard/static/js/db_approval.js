$(function(){
    var process = {
            //切换审批等级对应的审批人选择框显示方式
            switchDisplay : function($elem, val){
                $elem.hide();
                for(var i = 0; i < val; i++){
                    $elem.eq(i).show();
                }
            },
            //初始化渲染框
            initSelectInput : function($elem){
                $elem.userSelect({
                    data: Ibos.data.get("user"),
                    type: "user"
                });
            }
        },
        apprLevelSelect = $("#approve_level_select");

    levelDisplay(); 

    //审批等级显示
    function levelDisplay(){
        var $select = $(".pf-select-area .control-group");
        var val = apprLevelSelect.find('option:selected').val();
        process.switchDisplay($select, val);   
    }
    
    //选择审批等级
    apprLevelSelect.change(function() {
        levelDisplay();
    });

    //删除审批流程
    $(".o-trash").on("click", function(){
        var $li = $(this).parents("li"),
            liId = $li.attr("data-id"),
            url = Ibos.app.url("dashboard/approval/del");
        Ui.confirm(Ibos.l("APPROVE.SURE_DELET_APPROVE_FLOW"), function(){
            $.post(url, {id:liId}, function(res) {
                if(res.isSuccess){
                   $li.remove();
                    Ui.tip(res.msg, "success");
                } else {
					Ui.tip(res.msg, "danger");
				}
            }); 
        });
    });

    //
    $("#process_setting_form").submit(function() {
        var apprLevel = apprLevelSelect.val(),
            apprName = $("#approval_name").val(),
            infoStatus = true;
        if(apprName === ""){
            Ui.tip(Ibos.l("APPROVE.APPROVE_NAME_CANNOT_BE_EMPTY"), "danger");
            infoStatus = false;
        }else{
            for(var index = 0; index < apprLevel; index++){
                var $apprSelect = $("input.approval-select").eq(index),
                    apprVal = $apprSelect.val();
                if(apprVal === ""){               
                    var tipText = $apprSelect.closest(".control-group").find(".control-label").text() + "不能为空！";
                    Ui.tip(tipText, "danger");
                    infoStatus = false;
                    break;
                }
            }  
        }
        return infoStatus;
    });

    //等级审核人员
    var auditor = [
        "level_one_auditor", 
        "level_two_auditor", 
        "level_three_auditor", 
        "level_four_auditor", 
        "level_five_auditor", 
        "exempt_auditor"
    ];

    //初始化审核人员
    $.each( auditor, function(index){
        process.initSelectInput( $("#"+ auditor[index]) );
    } );
    
    /*
    //初始化一级审核人员
	var $levelOne = $("#level_one_auditor");
    process.initSelectInput($levelOne);

    //初始化二级审核人员
    var $levelTwo = $("#level_two_auditor");
    process.initSelectInput($levelTwo);

    //初始化三级审核人员
    var $levelThree = $("#level_three_auditor");
    process.initSelectInput($levelThree);

    //初始化四级审核人员
    var $levelFour = $("#level_four_auditor");
    process.initSelectInput($levelFour);

    //初始化五级审核人员
    var $levelFive = $("#level_five_auditor");
    process.initSelectInput($levelFive);

    //初始化免审核人员
    var $exempt = $("#exempt_auditor");
    process.initSelectInput($exempt);*/

})