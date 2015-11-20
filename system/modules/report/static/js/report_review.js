/**
 * 总结计划 -- 评阅下属
 * @author 		inaki
 * @version 	$Id$
 */
 $(function(){
 	// 侧栏伸缩
 	var $mngList = $("#mng_list");
 	$mngList.on("click", ".g-sub", function() {
 		var $el = $(this),
 			$item = $el.parents(".mng-item").eq(0),
 			$next = $item.next();

 		if (!$el.attr('data-init')) {
 			$.get(Ibos.app.url("report/review/index", { op: "getsubordinates" }), {
 				uid: $el.attr('data-uid')
 			}, function(res) {
 				$el.parent().after(res);
 				$item.addClass('active');
 			});
 		}

 		$el.attr('data-init', '1');
 		
 		if ($next.is("ul")) {
 			Report.toggleTree($next, function(isShowed) {
 				$item.toggleClass("active", !isShowed);
 			});
 		}
 	});

 	//展开部门
 	$mngList.on("click", ".dept", function() {
 		var $el = $(this),
 			$item = $el.parents(".mng-item").eq(0),
 			$next = $item.next();

 		Report.toggleTree($next, function(isShowed) {
 			$item.toggleClass("active", !isShowed);
 		});
 	});
 	
 	//查看所有下属
 	$mngList.on("click", ".view-all", function() {		
 		var $el = $(this);
 		$.get(Ibos.app.url("report/review/index", { op: "getsubordinates", item: "999999" }), {
 			uid: $el.attr('data-uid')
 		}, function(res) {
 			$el.parent().replaceWith(res);
 		});
 	});
 	
 	$('[data-action="toggleSubUnderlingsList"][data-uid="' + Ibos.app.g("currentSubUid") + '"]').click();
 });