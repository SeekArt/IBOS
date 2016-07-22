var navigation = {
	/**
	 * 重置主导航
	 * @method resetMainNavLi
	 * @param {Jquery} $elem 对应的主导航li的对象
	 */
	resetMainNavLi : function($elem){
		var mainLiLength = $elem.length,
			d = new Date(),
			childItemHtml = "<div class='add-nav-item'><ul class='nav-child-list' data-id='sys_child_" + d.getTime() + "_body' id='sys_child_" + d.getTime() + "_body'></ul></div>",
			addChindLiHtml = "<div class='span3 mls add-child-btn'><a class='cbtn o-plus' data-target='#sys_child_" + d.getTime() + "_body' data-act='add_sec' href='javascript:void(0);'></a></div>",
			addButtonHtml = "<a class='cbtn o-plus mls' data-target='#sys_child_" + d.getTime() + "_body' data-act='add_sec' href='javascript:void(0);'></a>";
		//循环重置主导航
		for(var index = 0; index < mainLiLength; index++){
			var $mianLi = $elem.eq(index),
				$isUseCheckbox = $mianLi.find(".isuse").find("input"),
				addItemLength = $mianLi.has('.add-nav-item').length,
				addButtonLength = $mianLi.has(".add-child-btn").length,
				addChildBtnLength = $mianLi.find(".nav-w30").eq(0).find(".o-plus").length;
			
			//当主导航缺少子导航时，添加子导航
			if(!addItemLength){
				$mianLi.append(childItemHtml);
				$mianLi.parent().find(".nav-child-list").sortable({
				    connectWith: ".nav-child-list, #nav_main_list",
				    placeholder: "sortable-placeholder",
				    cursor: "move",
					tolerance: "pointer",
					revert: true
				}).disableSelection();
			}

			//当主导航缺少添加子导航按钮时，添加按钮
			if(!addButtonLength){
				$mianLi.find(".nav-w30").append(addChindLiHtml);
			}else{
				if(!addChildBtnLength){
					$mianLi.find(".nav-w30").eq(0).find(".add-child-btn").append(addButtonHtml);
				}
			}

			//显示主导航下的子导航的内容
			$mianLi.find(".add-nav-item").show();

			//显示主导航的增加子导航按钮
			$mianLi.find(".add-child-btn").show();

			//给主导航的是否启用选择框添加事件
			$isUseCheckbox.attr("data-action", "isUse");
		}
	},
	/**
	 * 重置子导航
	 * @method resetChildNavLi
	 * @param {Jquery} $elem 对应的子导航li的对象
	 */
	resetChildNavLi : function($elem){
		var $currentAddItem = $elem.find(".add-nav-item"),
			$isUseCheckbox = $elem.find(".isuse").find("input"),
			$addButton = $elem.find(".add-child-btn"),
			$addChildBtn = $elem.find(".add-child-btn").children(".o-plus"),
			addButtonLength = $addButton.has(".o-plus").length,
			$liBgWamp = $elem.find(".empty-li-bg");

		//将子导航隐藏
		$currentAddItem.hide();

		$liBgWamp.removeClass("empty-li-bg");

		//删除子导航的是否启用选择框的事件
		$isUseCheckbox.removeAttr("data-action");
	},
	/**
	 * 重置主导航的类名
	 * @method resetMainNavLiClass
	 * @param {Jquery} $elem 对应的主导航li的对象
	 */
	resetMainNavLiClass : function($elem){
		//删除由于拖拽附带的子导航的类
		$elem.removeClass("msts-last");
	},
	/**
	 * 重置子导航的类名
	 * @method resetChildNavLiClass
	 * @param {Jquery} $elem 对应的子导航ul的对象
	 */
	resetChildNavLiClass : function($elem){
		//重置子导航的类名
		$("li", $elem).removeClass("msts-last");
		$("li:last-child", $elem).addClass("msts-last");
	},
	/**
	 * 将主导航拖拽至子导航
	 * @method moveMainLiToChildLi
	 * @param {Jquery} $main 对应的主导航ul的对象
	 * @param {Jquery} $elem 对应的主导航li的对象
	 */
	moveMainLiToChildLi : function($main, $elem, $target){
		var nodetpye =  $target.id;
		if(nodetpye != "nav_main_list"){
			var chilsNavliLength = $elem.find(".add-nav-item li").length;
			if(chilsNavliLength){
				$main.sortable("cancel");
				$main.children("li").removeClass(".msts-last");
				$elem.find(".add-nav-item").show();
				$elem.find('.main-nav-item').find('.mst-board').css({"background-position":"50px 0;"});
				Ui.tip(U.lang("DB.REMOVE_CHILD_NAVIGATION"), "danger");
			}
		}
	},
	/**
	 * 增加行
	 * @method addLine
	 * @param {String} target 对应的导航id的字符串
	 * @param {String} temp 增加行的模版内容
	 * @param {String} liType 对应的导航的类型
	 * @param {Function} callback 对应的主导航下子导航ul的id
	 */
	addLine : function(target, temp, liType, callback){
		var $li;
		if(typeof temp === "string"){
			if(liType == "child"){
				$li = $("<li>").addClass("msts-last");
			}else{
				$li = $("<li>");
			}
			$li.html(temp||"");
		}else{
			$li = temp;
		}
		$(target).children().last().removeClass();
		$(target).append($li);
		callback && callback($li);
	},
	/**
	 * 删除行
	 * @method removeLine
	 * @param {Jquery} $li 对应的导航对象
	 * @param {String} mark 标识符，用于标识是主导航还是子导航
	 */
	removeLine : function($li, mark){
		var $ul = $li.parent(),
			length = $ul.children().length;
		if($li.index() === (length-1)){
			if(mark === "remove_sec"){
				$li.prev().addClass("msts-last");	
			}
		}
		$li.remove();
	},
	/**
	 * 切换显示子导航
	 * @method collapseChildNav
	 * @param {Jquery} $elem 对应的导航对象
	 * @param {String} state 状态，用于标识是拖拽开始还是结束
	 */
	collapseChildNav : function($elem, state){
		var $childList = $elem.find(".nav-child-list");
		$childList.toggle(state !== "start");
	},

	/**
	 * 初始化主导航的背景
	 * @method initMainNavBackground
	 * @param {Jquery} $elem 主导航li
	 */
	initMainNavBackground : function($elem){
		var mainLiLength = $elem.length;
		for(var index = 0; index < mainLiLength; index++){
			var $mianLi = $elem.eq(index),
				$mainLiBgWamp = $mianLi.find('.mst-board').eq(0),
				chilsNavliLength = $mianLi.find('.nav-child-list li').length;
			if(chilsNavliLength !== 0){
				$mainLiBgWamp.removeClass("empty-li-bg").addClass("li-bg");
			}else{
				$mainLiBgWamp.removeClass("li-bg").addClass("empty-li-bg");
			}
		}	
	}
};

var op = {
	_fetchData: function($elem){
		var $checkboxes = $elem.find(":checkbox"),
			$select = $elem.find(".nav-w14").children().find("option:selected"),
			index = $select.val(),
			type = $elem.find(".nav-w14").attr("data-type"),
			noSyUrl = $elem.find(".system-url").children().eq(index).find("input").val(),
			syUrl = $elem.find("[data-type='url']").val(),
			url = syUrl ? syUrl : noSyUrl;
		return {
			name: $elem.find("[data-type='name']").val(),
			type: type,
			url: url,
			module: $elem.find("[data-type='module']").val(),
			system: $elem.find("[data-type='isSystem']").val(),
			disabled: +!$checkboxes.eq(0).prop("checked"), 
			targetnew: +$checkboxes.eq(1).prop("checked"),
			pageid: $elem.find("[data-type='pageid']").val()
		};
	},

	//获取导航的数据结构
	formatNavData : function($elems){
		var _this = this;
		var	ret = [];
		$.each($elems, function(index, elem){
			var $elem = $(elem);
			var parentData = _this._fetchData($elem);
			parentData.sort = index + 1;
			parentData.child = [];
			ret.push(parentData);
			$elem.find(".nav-child-list>li").each(function(index, elem){
				var childData = _this._fetchData($(this));
				childData.sort = index + 1;
				parentData.child.push(childData);
			});
		});
		return ret;
	}
};

$(function(){
	//初始化主导航拖拽
	$("#nav_main_list").sortable({
		cursor: "move",
		connectWith: "#nav_main_list, .nav-child-list",
		placeholder: "sortable-placeholder",
		tolerance: "pointer",
		revert: true
	}).disableSelection();

	//初始化子导航拖拽
	$(".nav-child-list").sortable({
	    connectWith: ".nav-child-list, #nav_main_list",
	    placeholder: "sortable-placeholder",
	    cursor: "move",
		tolerance: "pointer",
		revert: true
	}).disableSelection();

	//拖拽主导航时，将对应的子导航隐藏
	$("#nav_main_list").on("sortstart", function(e, ui){
		var $mainLi = ui.item,
			$mainItemLi = $mainLi.find(".main-nav-item"),
			state = "start";
		navigation.collapseChildNav($mainLi, state);

		//开始拖动主导航时，给当前拖动的主导航增加投影
		$mainItemLi.addClass("moving-box-shadow");
	});

	//拖拽主导航结束后,重置主导航
	$("#nav_main_list").on("sortstop", function(e, ui){
		var $mainNavLi = $("#nav_main_list>li"),
			$currentLi = ui.item,
			$currentMainLi = $currentLi.find(".main-nav-item"),
			state = "stop";
		navigation.resetMainNavLi($mainNavLi);
		navigation.resetMainNavLiClass($mainNavLi);

		var $ChildNavLi = $(".nav-child-list");
		navigation.resetChildNavLi($ChildNavLi);
		navigation.resetChildNavLiClass($ChildNavLi);

		//拖拽主导航结束后，将对应的子导航显示出来
		navigation.collapseChildNav($currentLi, state);

		//当拖动结束后，取消当前主导航增加的投影
		$currentMainLi.removeClass("moving-box-shadow");
	});


	//当将主导航拖拽至子导航时的操作
	$("#nav_main_list").on("sortupdate", function(e, ui){
		var $mainUl = $("#nav_main_list"),
			$mainNavLi = $("#nav_main_list>li"),
			$target = e.target,
			$moveMainNavLi = ui.item;
		navigation.moveMainLiToChildLi($mainUl, $moveMainNavLi, $target);

		//重置主导航的类
		navigation.resetMainNavLiClass($mainNavLi);

		navigation.initMainNavBackground($mainNavLi);
	});

	$(".nav-child-list").on("sortstart", function(e, ui) {
		var $currentLi = ui.item.find(".child-nav-item");
		$currentLi.addClass("moving-box-shadow");
	});

	//拖拽子导航结束后，重置子导航
	$(".nav-child-list").on("sortstop", function(e, ui) {
		var $target = e.target,
			$ChildNavLi = $(".nav-child-list"),
			$mianLi = $("#nav_main_list>li"),
			$currentLi = ui.item.find(".child-nav-item");
		navigation.resetChildNavLiClass($ChildNavLi);
		navigation.initMainNavBackground($mianLi);

		$currentLi.removeClass("moving-box-shadow");
	});

	//列表中删除和增加按钮的操作集合
	$("#sys_nav_form").on("click", "a", function(){
		var $this = $(this),
			act = $this.attr("data-act"),
			target = $this.attr("data-target"),
			$mianLi = $("#nav_main_list>li");
		switch(act){
			//增加主导航
			case "add_main":
				var mdate = new Date(),
					mnewNav = $.template('new_main_nav', {id: mdate.getTime()}),
					mliType = "main"; 
				navigation.addLine(target, mnewNav, mliType, function($li){
					$li.find("input[type=checkbox]").label();
					$li.attr("data-id", mdate.getTime());
				});
			break;
			//增加子导航
			case "add_sec":
				var subdate = new Date(),
				 	pid = $(target).attr("data-id"),
				 	liType = "child",
				 	newNav = $.template('new_nav', {id: subdate.getTime(), pid: pid});
				navigation.addLine(target, newNav, liType, function($li){
					$li.find("input[type=checkbox]").label();
					navigation.initMainNavBackground($mianLi);
					$li.attr("data-id", subdate.getTime());

					$(".nav-child-list").sortable({
					    connectWith: ".nav-child-list, #nav_main_list",
					    placeholder: "sortable-placeholder",
					    cursor: "move",
						tolerance: "pointer",
						revert: true
					}).disableSelection();
				});
			break;
			//删除行
			case "remove_main":
			case "remove_sec":
				var $li = $this.parents("li").eq(0),
					mark = act;
				if (typeof target !== 'undefined'){
					if (act === 'remove_main'){
						$this.parents("li").eq(0).remove();
					}
				}
				navigation.removeLine($li, mark);
				navigation.initMainNavBackground($mianLi);
			break;
		}
	});

	//点击选择单页图文或超链接
	$("#sys_nav_form").delegate('.type-select', 'change', function() {
		var index = parseInt($(this).find('option:selected').val()),
			$navType= $(this).parent(),
			$linkList = $(this).parent().siblings(".system-url").children(),
			$urlInput = $linkList.find("input");
		$linkList.addClass("hidden");
		$urlInput.removeClass("mark");
		$linkList.eq(index).removeClass("hidden");
		$urlInput.eq(index).addClass("mark");
		$navType.attr("data-type",index);
	});

	//重置主导航的背景样式
	var $mianLi = $("#nav_main_list>li");
	navigation.initMainNavBackground($mianLi);

	// 将数据结构发送给后台
	$('#sys_nav_form').on('submit', function() {
		var $mianNavLi = $("#nav_main_list>li"),
			navData = op.formatNavData($mianNavLi),
			url = Ibos.app.url("dashboard/nav/index"),
			param = {data: navData};
			param.navSubmit = 1;

		var errCount = 0;
		$('[data-type="url"].mark,[data-type="name"]').each(function() {
			if ($.trim(this.value) === '') {
				$(this).blink().focus();
				errCount++;
				return false;
			}
		});

		var isempty = errCount ? false : true;
		if(isempty){
			$.post(url, param, function(res) {
				if(res.isSuccess){
					Ui.tip("@OPERATION_SUCCESS");
					window.location.reload();
				}else{
					Ui.tip(res.msg, "danger");
				}
			}, "json");

		}
		return false;
	});

	//点击修改内容操作
	Ibos.evt.add({
		"isUse": function(param, elem){
			var checkboxVal = $(elem).prop("checked"),
				$childCheckbox = $(elem).closest('li').find(".nav-child-list").find(":checkbox");
			if(!checkboxVal){
				$childCheckbox.prop("checked", checkboxVal).label("refresh");
			}
		}
	});
});