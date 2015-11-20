/**
 * Officialdoc/officialdoc/index
 */

var OfficialIndex = {
    op : {
        // 获取传送地址
        getUrl : Ibos.app.url('officialdoc/officialdoc/edit'),
        /**
         * 移动公文
         * @method moveDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        moveDoc : function(param){
            var url = this.getUrl;
            param = $.extend({}, param, {op: "move"});
            return $.post(url, param, $.noop);
        },
        /**
         * 高亮公文
         * @method highlightDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        highlightDoc : function(param){
            var url = this.getUrl;
            param = $.extend({}, param, {op: "highLight"});
            return $.post(url, param, $.noop);
        },
        /**
         * 顶置公文
         * @method topDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        topDoc : function(param){
            var url = this.getUrl;
            param = $.extend({}, param, {op: "top"});
            return $.post(url, param, $.noop);
        },
        /**
         * 验证公文
         * @method verifyDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        verifyDoc : function(param){
            var url = this.getUrl;
            param = $.extend({}, param, {op: "verify"});
            return $.post(url, param, $.noop);
        },
        /**
         * 回退公文
         * @method backDocs
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        backDocs : function(param){
            var url = this.getUrl;
            param = $.extend({}, param, {op: "back"});
            return $.post(url, param, $.noop);
        }
    }
};

 $(function() {
 	// 选中一条或多条公文时，出现操作菜单
	$(document).on("change", 'input[type="checkbox"][name="officialdoc[]"]', function(){
		var $opBtn = $('#doc_more'),
			hasSelected = !!U.getChecked('officialdoc[]').length;
    	$opBtn.toggle(hasSelected);
    	setTimeout(function(){
    		$opBtn.toggleClass("open", hasSelected);
    	}, 0);
	});

	//高级搜索
    $("#mn_search").search(null, function(){
        Ui.dialog({
            id: "d_advance_search",
            title: Ibos.l("ADVANCED_SETTING"),
            content: document.getElementById("mn_search_advance"),
            cancel: true,
            init: function(){
                var form = this.DOM.content.find("form")[0];
                form && form.reset();
                // 初始化日期选择
                $("#date_start").datepicker({ target: $("#date_end") });
            },
            ok: function(){
                this.DOM.content.find("form").submit();
            },
        });
    });


    Ibos.evt.add({
    	// 移动公文
    	"moveDoc": function(){
    		Ui.dialog({
    			id: "d_doc_move",
    			title: Ibos.l("DOC.MOVETO"),
    			content: Dom.byId('dialog_doc_move'),
    			cancel: true,
    			ok: function(){
    				var catid = $('#articleCategory').val(),
    					docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                        param = {'docids': docids,'catid': catid};

                    OfficialIndex.op.moveDoc(param).done(function(res){
    					if(res.isSuccess === true){
    						Ui.tip(Ibos.l("CM.MOVE_SUCCEED"));
    						window.location.reload();
    					}else{
    						Ui.tip(Ibos.l("CM.MOVE_FAILED", 'danger'));
    					}
    				});
    			}
    		});
    	},
    	// 高亮公文
    	"highlightDoc": function(){
    		Ui.dialog({
    			id: "d_art_highlight",
    			title: Ibos.l("ART.HIGHLIGHT"),
				content: Dom.byId('dialog_art_highlight'),
				cancel: true,
				init: function(){
					// highlightForm
					var hf = this.DOM.content.find("form")[0], 
						$sEditor = $("#simple_editor");

					// 防止重复初始化
					if(!$sEditor.data('simple-editor')){
						//初始化简易编辑器
						var se = new P.SimpleEditor($('#simple_editor'), {
							onSetColor: function(hex){
								hf.highlight_color.value = hex;
							},
							onSetBold: function(status){
								// 转换为数字类型
								hf.highlight_bold.value = +status;
							},
							onSetItalic: function(status){
								hf.highlight_italic.value = +status;
							},
							onSetUnderline: function(status){
								hf.highlight_underline.value = +status;
							}
						});
						$sEditor.data('simple-editor', se);
					}

                    $("#date_time_highlight").datepicker();
				},
				ok: function(){
                    var hf = this.DOM.content.find("form")[0],
                    	hlData = {
                    		docids: U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                    		highlightEndTime: hf.highlightEndTime.value,
                    		color: hf.highlight_color.value,
                    		bold: hf.highlight_bold.value,
                    		italic: hf.highlight_italic.value,
                    		underline: hf.highlight_underline.value
                    	};

                    OfficialIndex.op.highlightDoc(hlData).done(function(res){
						if(res.isSuccess === true){
							Ui.tip(res.info);
							window.location.reload();
						}
					});
				}
    		});
    	},
    	// 置顶公文
    	"topDoc": function(){
    		Ui.dialog({
    			id: "d_art_top",
    			title: Ibos.l('ART.SET_TOP'),
    			content: Dom.byId('dialog_art_top'),
    			cancel: true,
                init: function(){
                      $("#date_time_totop").datepicker();
                },
    			ok: function(){
    				// topform
    				var tf = this.DOM.content.find("form")[0],
                        param = { 
                            'docids': U.getCheckedValue("officialdoc[]",  $("#officialdoc_table")),
                            'topEndTime': tf.topEndTime.value
                        };
                    OfficialIndex.op.topDoc(param).done(function(res){
    					if(res.isSuccess===true){
    						Ui.tip(res.info);
    						window.location.reload();
    					}
    				});
    			}
    		});
    	},
    	// 删除一条公文
    	"removeDoc": function(param, elem) {
            Ui.confirm(Ibos.l("DOC.SURE_DEL_DOC"), function() {
        		Official.op.removeDocs(param.id).done(function(res) {
        			if( res.isSuccess === true ){
        				Ui.tip(res.info);
        				$(elem).closest("tr").remove();
        			}
        		});
            });
    	},
    	// 删除多条公文
    	"removeDocs": function() {
    		var docids = U.getCheckedValue("officialdoc[]");

            Ui.confirm(Ibos.l("DOC.SURE_DEL_DOC"), function() {
        		Official.op.removeDocs(docids).done(function(res){
        			if( res.isSuccess === true ){
    					Ui.tip( res.info);
        				$.each(docids.split(","), function(index, docid){
        					$("[data-node-type='docRow'][data-id='" + docid + "']").remove();
        				});
        			}
        		});
            });
    	},
    	// 审核公文
    	"verifyDoc": function(){
    		var docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table"));
    		if(docids.length > 0){
                var param = { docids: docids };

                OfficialIndex.op.verifyDoc(param).done(function(res){
                    var hasTrue = res.isSuccess === true;
		            Ui.tip( res.info, (hasTrue ? "" : "warning") );
		            hasTrue && window.location.reload();
    		    });
    		}else{
    		    Ui.tip( Ibos.l("SELECT_AT_LEAST_ONE_ITEM") , 'warning');
    		}
    	},
		// 回退公文
    	"backDocs": function(){
    		var docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table"));
    		if(docids.length > 0){
				Ui.dialog({
					id: "doc_rollback",
					title: Ibos.l("DOC.DOC_ROLLBACK"),
					content: document.getElementById("rollback_reason"),
					cancel: true,
					ok: function(){
						var reason = $("#rollback_textarea").val(),
                            param = { docids: docids, reason: reason };

						OfficialIndex.op.backDocs(param).done(function(res){
                            var hasTrue = res.isSuccess === true;
                            Ui.tip( res.info, (hasTrue ? "" : "warning") );
                            hasTrue && window.location.reload();
						});
					}
				});
    		}else{
    			Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
    		}
    	},
		// 编辑前提示
		"editTip": function(params, elem){
			Ui.confirm(Ibos.l("DOC.EDIT_AT_SURE"), function() {
				var url = $(elem).attr("data-url");
				location.href = url;
			});
		}
    });
 });