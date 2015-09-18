/**
 * Diary dashboard
 * 2014-02-17
 */

$(function(){
	// 图章功能开关
	$("#stamp_switch").change(function() {
        $("#stamp_setup_box").toggle($.prop(this, "checked"));
		// $("#auto_review_control, #stamp_table_control").toggle($.prop(this, "checked"))
	});


	// 图章拖拽功能
	$('[data-node-type="stampItem"]').draggable({
        helper: "clone",
    });

	// 图章放置至插槽
	$('[data-node-type="stampSlot"]').droppable({
	    over: function(evt, data){ 
	        $(evt.target).addClass("active");
	    },
	    out: function(evt, data){ 
	        $(evt.target).removeClass("active");
	    },
	    drop: function(evt, data){
	        var $target = $(evt.target),
	            $children = $target.children(),
	            $drag = data.draggable,
	            $source = $drag.parent();

	        // 如果drop目标处已有图章节点，则交换位置
	        if($children.length){
	            $source.append($children.eq(0))
	            .next("input[type='hidden']").val($children.eq(0).attr("data-stamp-id"));
	        }

	        $target.removeClass("active").append($drag)
	        .next("input[type='hidden']").val($drag.attr("data-stamp-id"));
	    }
	});

	// 图章放置回备选区
	$('#stamp_item_box').droppable({
	    drop: function(evt, data){
	        data.draggable.parent().next('input[type="hidden"]').val("");
	        $(evt.target).append(data.draggable);
	    }
	});

	// 切换分制
	$("#point_sys").on("change", function(){
	    var $table = $("#stamp_slot_table"),
	        $rows = $table.find("tbody tr");

	    // 显示符合条件的行
	    $rows.show().filter(":lt(-"+ this.value +")").hide();
	    // 改变单选框状态
	    $rows.eq("-" + this.value).find("input[type='radio']").label("check");

	    // 还原图章位置，清空字段值
	    $table.find('[data-node-type="stampItem"]').each(function(){
	        var $elem = $(this);
	        $elem.parent().next('input[type="hidden"]').val("");
	        $elem.appendTo($("#stamp_item_box"));
	    });
	});

	// 自动评阅开关
	$("#auto_apprise").change(function(){
	    $("#stamp_slot_table .radio").toggle(this.checked);
	}).parent().tooltip();
	
	// 判断是否已开启自动评阅
	if(Ibos.app.g('AUTO_REVIEW') == '1'){
		$("#auto_apprise").label('check');
		$("#stamp_slot_table .radio").toggle(this.checked);
	}
});