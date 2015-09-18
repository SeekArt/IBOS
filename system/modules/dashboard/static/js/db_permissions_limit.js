var Limit = {
	//添加和编辑弹窗的操作
	operateLimit: function(type, durl, purl, param){
		if(type == "add"){
			var title = U.lang("DB.ADD_LIMIT"); 
		}else{
			var title = U.lang("DB.EDIT_LIMIT"); 
		}
		var dialog = Ui.dialog( 
			{
				title: title,
				width: 600,
				ok: function(){
					if ($.trim($('#role_select').val()) === '') {
						Ui.tip(Ibos.l("DB.SELLECT_ROLE"), 'danger');
						return false;
					}
			
					var formData =  this.DOM.content.find("form").serializeArray(),
						postParam =	 formData.concat(param);
					$.post(purl, postParam, function(res){
						if(res.isSuccess){
							Ui.tip("@OPERATION_SUCCESS");
							dialog.close();
							window.location.reload();
						}
					});
					return false;
				},
				cancel: true
		});
		
		$.get(durl, function(res){
			dialog.content(res);
		});
	},
	
	setupLimit:function(url, param, $elem){
		$("#module_select").on("change", function(){
		var $this = $(this);
		window.location.href = $this.val();
	});
	},
	
	//删除权限
	deleteLimit: function(url, param, $elem) {
		Ui.confirm(U.lang("DB.DELET_LIMIT"), function(){
			$.post(url, param, function(res){
				if(res.isSuccess){
					Ui.tip(U.lang('DELETE_SUCCESS'), 'success');
					$elem.closest("tr").remove();
				} else {
					Ui.tip(U.lang('DELETE_FAILED'), 'danger');
				}
			});
		});
	},

	auth: {
		selectMod: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='funcCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		},

		selectCate: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='modCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		}
	}
};

$(function(){
	//选择对应模块，显示对应模块下的授权列表
	$("#module_select").on("change", function(){
		var $this = $(this);
		window.location.href = $this.val();
	});

	//授权列表的按钮操作
	$("#limit_table").on("click", "a", function() {
			var type = $.attr(this, "data-limit"),
				module = $("#module_select").find("option:selected").attr("data-module"),
				id, $elem;
			if (!type) {
				return false;
			}
			id = $.attr(this, "data-id");
			$elem = $(this);
			switch (type) {
				case "add":
					var	postParam = [{name:"module", value: module}],
						dialogParam = {module: module},
						ajaxDialogUrl = Ibos.app.url("dashboard/permissions/add", dialogParam),
						postUrl = Ibos.app.url("dashboard/permissions/add",{addsubmit: 1});
					Limit.operateLimit(type, ajaxDialogUrl, postUrl, postParam);
					break;
				case "edit":
					var postParam = [{name: "module", value: module}, {name:"id", value: id}],
						dialogParam = {module: module, id: id},
						ajaxDialogUrl = Ibos.app.url("dashboard/permissions/edit", dialogParam),
						postUrl = Ibos.app.url("dashboard/permissions/edit",{editsubmit: 1});
					Limit.operateLimit(type, ajaxDialogUrl, postUrl, postParam);
					break;
				case "del":
					var url = Ibos.app.url("dashboard/permissions/del"),
						param = {module: module, id: id};
					Limit.deleteLimit(url, param, $elem);
					break;
			}
	});
});