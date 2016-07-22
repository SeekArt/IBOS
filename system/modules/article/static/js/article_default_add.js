/**
 * 信息中心--新建、编辑页共用 js
 * Module
 * @author 		inaki
 * @version 	$Id$
 */

var ArticleAdd = {
	/**
	 * 表单验证
	 * @method formVerify
	 */
	formVerify : function(){
		//article_form
		var af = "article_form";
		$.formValidator.initConfig({ formID : af, errorFocus: true});
		$("#subject").formValidator({ onFocus: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY") })
		.regexValidator({
			regExp:"notempty",
			dataType:"enum",
			onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
		});

		// 验证表单
		var valiForm = function(){
			if($.formValidator.pageIsValid()){
				var ue = UE.getEditor("article_add_editor");
				var content = ue.getContentTxt();
				var type = $("#content_type_value").val();
				if( type == 0 && $.trim(content) === ""){
					Ui.tip( U.lang("ART.CONTENT_CANNOT_BE_EMPTY"), "warning");
					ue.focus();
					return false;
				} else if(type == 1 && $("#picids").val() == "") {
					Ui.tip( U.lang("ART.PIC_CONTENT_CANNOT_BE_EMPTY"), "warning");
					return false;
				} else if(type == 2 && $.trim($("#article_link_url").val()) == "" ){
					Ui.tip( U.lang("ART.LINK_CANNOT_BE_EMPTY"), "warning");
					return false;
				} else {
					return true;
				}
			}
		};

		$("#"+af).submit(function(){
			if($.data(this, "submiting")) {
				return false;
			}

			valiForm() && $.data(this, "submiting", true);
		});

		Ibos.checkFormChange("#"+af);
	},
	/**
	 * 文本编辑器
	 * @method articleEdit
	 */
	articleEdit : function(){
		// 根据 url 路由判断是在新建页还是编辑页
		var articleEdit = U.getUrlParam().r == "article/default/edit";

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

		// 新手引导
		if(!articleEdit) {
			setTimeout(function(){
				Ibos.guide("art_def_add", [{
					element: "#article_status",
					intro: U.lang("ART.INTRO.STATUS"),
					position: "top"
				}]);
			}, 1000);
		}
	},
	/**
	 * 文件上传配置
	 * @method uploadFile
	 */
	uploadFile : function(){
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
	},
	/**
	 * 图片操作
	 * @method picAction
	 */
	picAction : function(){
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
	},
	/**
	 * 预览功能
	 * @method preview
	 */
	preview : function(){
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
		        if( tempForm.addEventListener ){
        	        tempForm.addEventListener("onsubmit", function() {
        				window.open(url);
        			});
		        }else{
			        tempForm.attachEvent("onsubmit", function() {
						window.open(url);
					});
		    	}

		        document.body.appendChild(tempForm);  


		        tempForm.submit();
		        document.body.removeChild(tempForm);
		    };

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
			        	window.open(results[1] ? url : "http://" + url, "_blank");
			        } else {
			        	Ui.tip(U.lang("RULE.URL_INVALID_FORMAT"), "warning")
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
	},
	/**
	 * add/edit其他功能
	 * @method init
	 */
	otherFunction : function(){
		// tab 事件
		$("#content_type [data-toggle=tab]").on("show", function(evt){
			$("#content_type_value").val($.attr(evt.target, "data-value"));
		});

		// 发布范围
	    $("#publishScope").userSelect({
	        data: Ibos.data.get()
	    });
	    
	    // 投票
	    $('#voteStatus').on('change',function(){
	        $('#vote').toggle($.prop(this, 'checked'));
	    });

	    //add默认分类
		$("#add_articleCategory").on("change", function() {
			var uid = Ibos.app.g("uid"),
				catid = this.value,
				param = {catid: catid, uid: uid};

			Article.op.addArticleCategory(param).done(function(res){
				var label = $("#article_status label"),
					check = label.eq(0),
					publish = label.eq(1);
				check.toggle(res.checkIsPublish);
				res.checkIsPublish ? check.find('input').prop('checked', true) : check.find('input').prop('checked', false);
				publish.toggle(res.isSuccess);
				res.isSuccess ? publish.find('input').prop('checked', true) : publish.find('input').prop('checked', false);
			});
		});
		
		//edit默认分类
		$("#edit_articleCategory").on("change", function() {
			var uid = Ibos.app.g("uid"),
				catid = this.value,
				param = {catid: catid, uid: uid};

			Article.op.addArticleCategory(param).done(function(res){
				var label = $("#article_status label");
				label.eq(0).toggle(res.checkIsPublish).end().eq(+res.checkIsPublish).trigger("click");
				label.eq(1).toggle(res.isSuccess).end().eq(+res.isSuccess).trigger("click");
			});
		});
	}
};



$(function(){
	//add/edit其他功能
	ArticleAdd.otherFunction();

	//表单验证
	ArticleAdd.formVerify();

	//文本编辑器
	ArticleAdd.articleEdit();

	//文件上传配置
	ArticleAdd.uploadFile();

	//图片操作
	ArticleAdd.picAction();

	//预览功能
	ArticleAdd.preview();
});