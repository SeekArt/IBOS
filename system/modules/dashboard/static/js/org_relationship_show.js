  $(function() {
	var Relationship = {
		curTarget: null,
		curTmpTarget: null,
		noSel: function() {
			try {
				window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
			} catch(e){}
		},
		"searchNode": function() {
			var _this = this,
					value = $.trim($("#r_search").val()),
					treeObj = $.fn.zTree.getZTreeObj("rtree"),
					nodeList = treeObj.getNodesByParamFuzzy("name", value),
					allNode = treeObj.getNodesByParamFuzzy("name", "");
			_this.updateNodes(allNode, false);
			
			if(nodeList.length > 0){
				if(value){
					_this.updateNodes(nodeList, true);
				}else{
					treeObj.expandNode(allNode[0], false, true, true);	
				}
			}else{
				treeObj.expandNode(allNode[0], false, true, true);
			}	
		},
		"updateNodes": function(nodeList, highlight) {
			var treeObj = $.fn.zTree.getZTreeObj("rtree"),
				length = nodeList.length;

			for (var i = 0, l = nodeList.length; i < l; i++) {
				nodeList[i].highlight = highlight;
				treeObj.updateNode(nodeList[i]);
			}
			treeObj.selectNode(nodeList[0]);
			treeObj.expandNode(nodeList[i], true, true, true);
		},
		"addDiyDom": function(treeId, treeNode) {
			var aObj = $("#" + treeNode.tId + "_a"),
					avatar = "<img class='user-avatar' src='static.php?type=avatar&uid=" + treeNode.id + "&size=small&engine=LOCAL' />";
			aObj.prepend(avatar);
		},
		"getFontCss": function(treeId, treeNode) {
			return (!!treeNode.highlight) ? {color: "#e26f50", "font-weight": "700"} : {color: "#82939e", "font-weight": "normal"};
		},
		"zTreeOnDrop": function(event, treeId, treeNodes, targetNode, moveType) {
			var node = treeNodes[0],
					tid = node.tId,
					index = $("#" + tid).index(),
					id = node.id,
					pid;
			if (moveType == "inner") {
				pid = targetNode ? targetNode.id : 0;
			} else {
				pid = targetNode ? targetNode.pid : 0;
			}
			var param = {id: id, pid: pid, index: index},
				url = Ibos.app.url('dashboard/user/relation', {'op': 'setUpuid'});
			if(targetNode){
				$.post(url, param, function(res){
					if (res.isSuccess) {
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					} else {
						Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
						window.location.reload();
					}
				}, 'json');
			}
		},
		"refreshUserList": function(currPage, userData) {
			var strat = (currPage - 1) * 9,
					end = currPage * 9,
					currArray = userData.slice(strat, end),
					userList = $.template("tpl_user_list", {data: currArray});
			$("#noexist_list_wrap").html(userList);
			$(".noexist-list-li").on("mousedown", Relationship.bindMouseDown);
		},
		"bindMouseDown": function(e) {
			var target = e.delegateTarget,
				doc = $(document), 
				target = $(target),
				docScrollTop = doc.scrollTop(),
				docScrollLeft = doc.scrollLeft(),
				id = target.data("id"),
				imgUrl = target.find(".image-wrap").attr("src"),
				name = target.find(".user-name").text(),
				position = target.find(".user-position").text(),
				domStr = "<li class='noexist-list-li drag-list-li' data-id='" + id +"'>" + 
							"<div class='clearfix'>" + 
								"<span class='avatar-circle pull-left'>" +
									"<img src='" + imgUrl + "'/>" + 
								"</span>" + 
								"<span class='pull-left fss mls user-name'>" + name +"</span>" + 
								"<span class='pull-right fss user-position'>" + position +"</span>" +
							"</div>" + 
						"</li>",
				$curDom = $(domStr);
			$curDom.appendTo("body");

			$curDom.css({
				"top": (e.clientY + docScrollTop + 3) + "px",
				"left": (e.clientX + docScrollLeft + 3) + "px"
			});
			Relationship.curTarget = target;
			Relationship.curTmpTarget = $curDom;

			doc.bind("mousemove", Relationship.bindMouseMove);
			doc.bind("mouseup", Relationship.bindMouseUp);
			doc.bind("selectstart", Relationship.docSelect);
			
			if(e.preventDefault) {
				e.preventDefault();
			}
		},
		"bindMouseMove": function(e) {
			Relationship.noSel();
			var doc = $(document), 
			docScrollTop = doc.scrollTop(),
			docScrollLeft = doc.scrollLeft(),
			tmpTarget = Relationship.curTmpTarget;
			if (tmpTarget) {
				tmpTarget.css({
					"top": (e.clientY + docScrollTop + 3) + "px",
					"left": (e.clientX + docScrollLeft + 3) + "px"
				});
			}
			return false;
		},
		"bindSelect": function() {
			return false;
		},
		bindMouseUp: function(e) {
			var doc = $(document);
			doc.unbind("mousemove", Relationship.bindMouseMove);
			doc.unbind("mouseup", Relationship.bindMouseUp);
			doc.unbind("selectstart", Relationship.docSelect);

			var target = Relationship.curTarget, 
				tmpTarget = Relationship.curTmpTarget;
			if (tmpTarget) tmpTarget.remove();

			if ($(e.target).parents("#utree").length === 0) {
				if (target) {
					// target.removeClass("domBtn_Disabled");
					// target.addClass("domBtn");
				}
				Relationship.curTarget = null;
				Relationship.curTmpTarget = null;
			}
		},
		"addToTree": function(e, treeId, treeNode){
			var target = Relationship.curTarget,
				tmpTarget = Relationship.curTmpTarget;
			if (!target) return;
			var zTree = $.fn.zTree.getZTreeObj("rtree"), parentNode;
			if (treeNode !== null && treeNode.isParent) {
				parentNode = treeNode;
			} else if (treeNode !== null && !treeNode.isParent) {
				parentNode = treeNode.getParentNode();
			}

			if (tmpTarget) tmpTarget.remove();
			if (!!parentNode) {
				var id = target.attr("data-id"),
					pid = parentNode.id,
					$dragli = $(".noexist-list-li[data-id='" + id + "']"),
					name = target.find(".user-name").text(),
					url = Ibos.app.url('dashboard/user/relation', {'op': 'setUpuid'}),
					param = {id: id, pid: pid};
				$.post(url, param, function(res){
					if(res.isSuccess){
						var nodes = zTree.addNodes(parentNode, {id:id, name: name});
						zTree.selectNode(nodes[0]);
						$dragli.remove();
						Relationship.removeUpUser(id, userData);
						Relationship.refreshUserList(currPage, userData);
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					}else{
						Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
					}
				});	

			} else {
				var id = target.attr("data-id"),
					$dragli = $(".noexist-list-li[data-id='" + id + "']"),
					name = target.find(".user-name").text(),
					url = Ibos.app.url('dashboard/user/relation', {'op': 'setUpuid'}),
					param = {id: id, pid: 0};
				$.post(url, param, function(res){
					if(res.isSuccess){
						var nodes = zTree.addNodes(null, {id:id, name: name});
						zTree.selectNode(nodes[0]);
						$dragli.remove();
						Relationship.removeUpUser(id, userData);
						Relationship.refreshUserList(currPage, userData);
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					}else{
						Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
					}
				});	
			}
			Relationship.curTarget = null;
			Relationship.curTmpTarget = null;
		},
		"removeUpUser": function(uid, userData){
			for(var i = 0; i < userData.length; i++){
				if(userData[i].uid == uid){
					userData.splice(i,1);
					Ibos.app.setPageParam({"upUsers" : userData});
					if(userData.length < 9){
						currPage = 1;
					}
				}
			}
		}
	};

	//生成树的初始化设置
	var settings = {
		data: {
			simpleData: {enable: true}
		},
		view: {
			showLine: false,
			selectedMulti: false,
			showIcon: false,
			addDiyDom: Relationship.addDiyDom,
			fontCss: Relationship.getFontCss
		},
		edit: {
			enable: true,
			drag: {
				isCopy: false,
				isMove: true,
				showRemoveBtn: true
			}
		},
		callback: {
			onDrop: Relationship.zTreeOnDrop,
			onMouseUp: Relationship.addToTree
		}
	};

	var $tree = $("#rtree");
	$tree.waiting(null, 'mini');
	$.get(Ibos.app.url('dashboard/user/relation',{'op': 'getUsers'}), function(data) {
		$.fn.zTree.init($tree, settings, data);
		$tree.waiting(false);
		// Relationship.bindDom();
		//搜索功能
		// $("#r_search").keyup(function(evt) {
		// 	Relationship.searchNode();
		// });

		$("#r_search").on("change", function(){
			Relationship.searchNode();
		});

	}, 'json');


	var currPage = 1, //当前页数			
		allPageNum; //分页后的总页数
	
	var data = Ibos.app.g("upUsers"),
		userData = data, //为设置上下级用户数组
		dataLength = userData.length;
	if (dataLength <= 9) {
		allPageNum = 1;
	} else {
		if (dataLength % 9 === 0) {
			allPageNum = parseInt(dataLength / 9);
		} else {
			allPageNum = parseInt(dataLength / 9) + 1;
		}
		$(".opt-toolbar").show();
		$("#page_next").removeClass("disabled");
	}
	Relationship.refreshUserList(1, userData);

	Ibos.evt.add({
		"prevPage": function(param, elem) {
			if (currPage == 1) {
				Ui.tip(Ibos.l("ORG.IS_THE_FIRST_PAGE"), "warning");
			} else {
				currPage--;
				Relationship.refreshUserList(currPage, userData);
				if (currPage == 1) {
					$(this).addClass("disabled");
				}
				$("#page_next").removeClass("disabled");
			}
		},
		"nextPage": function(param, elem) {
			if (currPage == allPageNum || allPageNum == 1) {
				Ui.tip(Ibos.l("ORG.IS_THE_LAST_PAGE"), "warning");
			} else {
				currPage++;
				Relationship.refreshUserList(currPage, userData);
				$("#page_prev").removeClass("disabled");
				if (currPage == allPageNum) {
					$(this).addClass("disabled");
				}
			}
		}
	});
});