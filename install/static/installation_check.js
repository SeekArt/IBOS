// JavaScript Document
$(function(){
	//安装检测页面中,对环境检测数据,目录文件权限，函数依赖性检查的显示与隐藏切换
			$(".showmore").on("click", function(){
				var $elem = $(this),
					$icon = $elem.find("i");
				// 收起
				if($icon.hasClass("o-pack-up")){
					$icon.removeClass("o-pack-up").addClass("o-pack-down");
					$elem.find("span").text("展开")
					$elem.parent().next().slideUp(200);
				} else {
					$icon.removeClass("o-pack-down").addClass("o-pack-up");
					$elem.find("span").text("收起")
					$elem.parent().next().slideDown(200);
				}
			})		
	});