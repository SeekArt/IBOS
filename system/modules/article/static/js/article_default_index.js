/**
 * Article/default/index
 */

var ArticleIndex = {
    /**
     * 选中一条或多条新闻时，出现操作菜单
     * @method selectNewsShowMenu
     * @return {[type]} [description]
     */
    selectNewsShowMenu : function(){
        $(document).on("change", 'input[type="checkbox"][name="article[]"]', function(){
            var $opBtn = $('#art_more'),
                hasSelected = !!U.getChecked('article[]').length;
            $opBtn.toggle(hasSelected);
            setTimeout(function(){
                $opBtn.toggleClass("open", hasSelected);
            }, 0);
        });
    },
    /**
     * 高级搜索
     * @method highSearch
     */
    highSearch : function(){
        $("#mn_search").search(null, function(){
            Ui.dialog({
                id: "d_advance_search",
                title: U.lang("ADVANCED_SETTING"),
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
    }
};


$(function() {
    //选中一条或多条新闻时，出现操作菜单
    ArticleIndex.selectNewsShowMenu();
    //高级搜索
    ArticleIndex.highSearch();

    Ibos.evt.add({
    	// 移动新闻
    	"moveArticle": function(){
    		Ui.dialog({
    			id: "d_art_move",
    			title: U.lang("ART.MOVETO"),
    			content: Dom.byId('dialog_art_move'),
    			cancel: true,
    			ok: function(){
    				var catid = $('#articleCategory').val(),
    					articleids = U.getCheckedValue("article[]", $("#article_table")),
                        param = {'articleids':articleids,'catid':catid};

                    Article.op.moveArticle(param).done(function(res){
                        if(res.isSuccess === true){
                            Ui.tip(U.lang("CM.MOVE_SUCCEED"));
                            window.location.reload();
                        }else{
                            Ui.tip(U.lang("CM.MOVE_FAILED"), 'warning');
                        }
                    });
    			}
    		});
    	},
    	// 高亮新闻
    	"highlightArticle": function(){
    		Ui.dialog({
    			id: "d_art_highlight",
    			title: U.lang("ART.HIGHLIGHT"),
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
                    	param = {
                    		articleids: U.getCheckedValue("article[]", $("#article_table")),
                    		highlightEndTime: hf.highlightEndTime.value,
                    		highlight_color: hf.highlight_color.value,
                    		highlight_bold: hf.highlight_bold.value,
                    		highlight_italic: hf.highlight_italic.value,
                    		highlight_underline: hf.highlight_underline.value
                    	};

					Article.op.highLight(param).done(function(res){
                        res.isSuccess && window.location.reload();
                        Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
					});
				}
    		});
    	},
    	// 置顶新闻
    	"topArticle": function(){
    		Ui.dialog({
    			id: "d_art_top",
    			title: U.lang('ART.SET_TOP'),
    			content: Dom.byId('dialog_art_top'),
    			cancel: true,
                init: function(){
                    $("#date_time_top").datepicker();
                },
    			ok: function(){
    				// topform
    				var tf = this.DOM.content.find("form")[0],
                        param = {
                            'articleids': U.getCheckedValue("article[]",  $("#article_table")),
                            'topEndTime': tf.topEndTime.value
                        };

                    Article.op.topArticle(param).done(function(res){
                        res.isSuccess && window.location.reload();
                        Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
                    });
    			}
    		});
    	},
    	// 删除一条新闻
    	"removeArticle": function(param, elem) {
            Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
        		Article.op.removeArticles(param.id).done(function(res) {
        			if( res.isSuccess === true ){
        				$(elem).closest("tr").remove();
        			}
                    Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
        		});
            });
    	},
    	// 删除多条新闻
    	"removeArticles": function() {
    		var aids = U.getCheckedValue("article[]");
            Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
        		Article.op.removeArticles(aids).done(function(res){
        			if( res.isSuccess === true ){
        				$.each(aids.split(","), function(index, aid){
        					$("[data-node-type='articleRow'][data-id='" + aid + "']").remove();
        				});
        			}
                    Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
        		});
            });
    	},
    	// 审核新闻
    	"verifyArticle": function(){
    		var articleids = U.getCheckedValue("article[]", $("#article_table")),
                param = { articleids: articleids };
    		if(articleids.length > 0){
                Article.op.verifyArticle(param).done(function(res){
                    res.isSuccess && window.location.reload();
                    Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
                });
    		}else{
    			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
    		}
    	},
		// 退回新闻
    	"backArticle": function(){
    		var articleids = U.getCheckedValue("article[]", $("#article_table"));
    		if(articleids.length > 0){
				Ui.dialog({
					id: "art_rollback",
					title: L.ART.DOC_ROLLBACK,
					content: document.getElementById("rollback_reason"),
					cancel: true,
					ok: function(){
						var reason = $("#rollback_textarea").val(),
                            param = { articleids: articleids, reason: reason };
                        Article.op.backArticle(param).done(function(res){
                            res.isSuccess && window.location.reload();
                            Ui.tip(res.msg, res.isSuccess ? "" : "wraning");
                        });
					}
				});
    		}else{
    			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
    		}
    	},
		// 编辑前提示
		"editTip": function(params, elem){
			Ui.confirm(U.lang("ART.EDIT_AT_SURE"), function() {
				var url = $(elem).attr("data-url");
				location.href = url;
			});
		}
    });
 });