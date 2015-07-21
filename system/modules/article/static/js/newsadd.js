/**
 * 信息中心--新建、编辑页共用 js
 * Module
 * @author 		inaki
 * @version 	$Id$
 */

$(function(){
	// 根据 url 路由判断是在新建页还是编辑页
	var articleEdit = U.getUrlParam().r == "article/default/edit";

	$.formValidator.initConfig({ formID:"article_form", errorFocus: true});
	$("#subject").formValidator({ onFocus: Ibos.l("RULE.SUBJECT_CANNOT_BE_EMPTY") })
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: Ibos.l("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});

	// tab 事件
	$("#content_type [data-toggle=tab]").on("show", function(evt){
		$("#content_type_value").val($.attr(evt.target, "data-value"));
	})
	

	//上传
	var attachUpload = Ibos.upload.attach({
		"module": "article",
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

	// 图片上传配置
	var picUpload = Ibos.upload.attach({
		"module": "article",
		file_types: Ibos.settings.imageTypes,
		button_placeholder_id: "pic_upload",
		custom_settings: {
			containerId: "pic_list",
			inputId: "picids",
			success: function(file, data, item){
				Article.pic.initPicItem(item, data);
			}
		}
	});

	var $picRemove = $("#pic_remove"),
		$picMoveUp = $("#pic_moveup"),
		$picMoveDown = $("#pic_movedown"),
		picSelected = [];

	function resetBtns(){
		var $checked = U.getChecked("pic");
		var count = $checked.length,
			enableRemove = count >= 1,
			enableMove = count === 1;

		// 根据选中条目数决定，删除按钮和移动按钮的显隐
		$picRemove.toggle(enableRemove);
		$picMoveUp.toggle(enableMove);
		$picMoveDown.toggle(enableMove);
	}

	$(document).on("change", "[name='pic']", resetBtns);

	// 删除选中图片项
	$picRemove.on("click", function(){
		Article.pic.removeSelect(U.getCheckedValue("pic").split(","));
		resetBtns();
	});
	// 上移选中图片项
	$picMoveUp.on("click", function(){
		Article.pic.moveUp(U.getCheckedValue("pic").split(",")[0]);
	});
	// 下移选中图片项
	$picMoveDown.on("click", function(){
		Article.pic.moveDown(U.getCheckedValue("pic").split(",")[0]);
	});



    $("#publishScope").userSelect({
        data: Ibos.data.get()
    });

    $('#voteStatus').on('change',function(){
        $('#vote').toggle($.prop(this, 'checked'));
    });
    
    //状态值改变
//    $('#article_status').on('click',function(){
//        $('#status').val($(this).find('.active input').val());
//    });

	// @Todo:
	// 预览功能太弱，需要改进
	// 预览多个页面用到，需要提取到公共函数
	var openPostWindow = function (url, data){
        var tempForm = document.createElement("form");  
        tempForm.id="tempForm1";  
        tempForm.method="post";  
        tempForm.action=url; 
        tempForm.target = "_blank"; 

    	var input;
    	for(var i in data) {
    		input = document.createElement("input");
    		input.type = "hidden";
    		input.name = i;
    		input.value = data[i];
    		tempForm.appendChild(input);
    	}

        //监听事件的方法        打开页面window.open(name);
        // tempForm.addEventListener("onsubmit",function(){  window.open(url, "_blank"); });
        tempForm.addEventListener("onsubmit", function() {
			window.open(url);
		});
        document.body.appendChild(tempForm);  


        tempForm.submit();
        document.body.removeChild(tempForm);
    }

    // 预览
	$('#prewiew_submit').on('click',function(){
	    var type = parseInt($('#content_type_value').val(), 10),
	    	TYPE_ARTICLE = 0, TYPE_PIC = 1, TYPE_URL = 2;

	    // 文章
	    if( type === TYPE_ARTICLE ){
	        var url = Ibos.app.url("article/default/index", {"op": "preview"}),
	        	setting = {
					type: type,
		            subject: $('#subject').val(),
		            content: UE.getEditor('article_add_editor').getContent()
		        };

	        openPostWindow(url, setting);
	    // 超链接
	    }else if( type === TYPE_URL ){
	        var url = $('#article_link_url').val(),
	        	results = U.reg.url.exec(url);

	        if(results){
	        	// 没有协议前缀，自动补全
	        	window.open(results[1] ? url : "http://" + url);
	        } else {
	        	Ui.tip(Ibos.l("RULE.URL_INVALID_FORMAT"), "warning")
	        }
		//图片	
	    } else if ( type === TYPE_PIC ){
			var url = Ibos.app.url("article/default/index", {"op": "preview"}),
	        	setting = {
					type: type,
		            subject: $('#subject').val(),
		            picids: $('#picids').val()
		        };
	        openPostWindow(url, setting);
		}
	});

	$("#add_articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
			catid = this.value,
			url = Ibos.app.url("article/default/add", {op: "checkIsAllowPublish"});

		$.get(url, {catid: catid, uid: uid}, function(res) {
			$("#article_status label").eq(0).toggle(res.checkIsPublish);
			$("#article_status label").eq(1).toggle(res.isSuccess);
		}, 'json');
	});
	
	$("#edit_articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
			catid = this.value,
			url = Ibos.app.url("article/default/add", {op: "checkIsAllowPublish"});

		$.get(url, {catid: catid, uid: uid}, function(res) {
			$("#article_status label").eq(0).toggle(res.checkIsPublish).end().eq(+res.checkIsPublish).trigger("click");
			$("#article_status label").eq(1).toggle(res.isSuccess).end().eq(+res.isSuccess).trigger("click");
		}, 'json');
	});
	

	// 验证表单
	var valiForm = function(){
		if($.formValidator.pageIsValid()){
			var ue = UE.getEditor("article_add_editor");
			var content = ue.getContentTxt()
			var type = $("#content_type_value").val();
			if( type == 0 && $.trim(content) === ""){
				Ui.tip("@ART.CONTENT_CANNOT_BE_EMPTY", "warning");
				ue.focus();
				return false;
			} else if(type == 1 && $("#picids").val() == "") {
				Ui.tip("@ART.PIC_CONTENT_CANNOT_BE_EMPTY", "warning")
				return false;
			} else if(type == 2 && $.trim($("#article_link_url").val()) == "" ){
				Ui.tip("@ART.LINK_CANNOT_BE_EMPTY", "warning")
				return false
			} else {
				return true;
			}
		}
	}

	Ibos.checkFormChange("#article_form");

	// 编辑器
    var ue = UE.getEditor('article_add_editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		autoHeightEnabled:true,
		toolbars: UEDITOR_CONFIG.mode.simple
	});

    ue.ready(function(){
    	if(!articleEdit) {
			(new Ibos.EditorCache(ue, null, "article_add_editor")).restore();
    	}

		ue.addListener("contentchange", function() {
			$("#article_form").trigger("formchange");
		});
    });

	$("#article_form").submit(function(){
		if($.data(this, "submiting")) {
			return false;
		}
		if(valiForm()){
    		$.data(this, "submiting", true);
		}
	});


	// 新手引导
	if(!articleEdit) {
		setTimeout(function(){
			Ibos.guide("art_def_add", [{
				element: "#article_status",
				intro: Ibos.l("ART.INTRO.STATUS"),
				position: "top"
			}]);
		}, 1000)
	}
});