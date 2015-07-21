/**
 * diary.js
 * 工作日志
 * IBOS
 * Diary
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-11
 */

// 日志模块命名空间
var Diary = (function(){
    var D = {
		/**
		 * 切换详细日志显隐状态
		 * @param  {Jquery} $el            触发对象
			 * @param  {String} [act="show"]   显隐，只有"show", "hide"两种状态
		 * @return {[type]}     [description]
		 */
		toggleDetail: function($el, act){
			var toggleSpeed = 100,
				$item = $el.parents("li").eq(0),
				$summary = $item.find(".da-summary"),
				$detail = $item.find(".da-detail");
			if(act === "hide"){
				$detail.slideUp(toggleSpeed);
				$summary.slideDown(toggleSpeed);
				$item.removeClass("open")
			}else{
				$detail.slideDown(toggleSpeed);
				$summary.slideUp(toggleSpeed);
				$item.addClass("open")
			}
		},
		toggleTree: function($tree, callback){
			var isShowed = $tree.css("display") !== "none" ? true : false;
			if(isShowed){
				$tree.hide();
			}else{
				$tree.show();
			}
			callback && callback(isShowed);
		},
		changePlanDate: function(date){
			var	$elem = $("#da_plan_date_display"), // [data-node-type='planDate']
                tpl = '<strong><%=day%></strong> <div class="mini-date-body"> <p><%=weekDay%></p> <p> <%=fullYear%>-<%=month%></p> </div> ';

			return $elem.html($.template(tpl, {
                day: fixDate(date.getDate()),
                weekDay: U.lang("TIME.WEEKS") + (U.lang("TIME.WEEKDAYS").charAt(date.getDay())),
                month: fixDate(date.getMonth() + 1),
                fullYear: date.getFullYear()
            }));
		}
	}

	// 日期补零
	function fixDate (num){
		return +num >= 10 ? num : "0" + num;
	}

	D.keyHandler = function(evt, handlers){
	    var keycodes = {
	        "up": 38,
	        "down": 40,
	        "delete": 46,
	        "enter": 13,
	        "tab": 9,
            "backspace": 8
	    }
	    handlers = handlers || {}
	    for(var i in keycodes){
	        if(keycodes.hasOwnProperty(i) && keycodes[i] === evt.which) {
	            handlers[i] && handlers[i].call(evt.target, evt);
	        }   
	    }
	}

	D.orderTable = function($container, template, options){
        options = $.extend({
            indexSelector: "[data-toggle='badge']",
            indexFormat: "<%=index%>."
        }, options);

        var _cache = {};

        this.reorderIndex = function(){
            $container.find(options.indexSelector).each(function(i){
                $(this).text($.template(options.indexFormat, {index: (i + 1)}));
            });
        };

        this.getPrevRow = function(id) {
            return _cache[id].elem.prev("tr");
        }

        this.getNextRow = function(id){
            return _cache[id].elem.next("tr");
        }

        this.focus = function($row){
            $("input[type='text']", $row).focus();
        }

        this.add = function(data, callback){
            var $row;
            data = $.extend({ id: parseInt(U.uniqid(), 16), subject: ""}, data);

            $row = $.tmpl(template, data);
            // 插入倒数第二行
            $row.insertBefore($container.find("tr:last"));

            _cache[data.id] = {
                elem: $row,
                data: data
            }

            // 重新排序
            this.reorderIndex();
            
            callback && callback($row);
            return $row;
        };

        this.remove = function(id, callback) {
            if(_cache[id] && _cache[id].elem) {
                this.focus(this.getPrevRow(id));
                _cache[id].elem.remove();
            }
            delete _cache[id];
            // 重新排序
            this.reorderIndex();
            callback && callback();
        }
    }


    // 创建标尺
    D.createVernier = function($elem, options){
        var $container = $("<ul class='vernier'></ul>"),
            isFormatValid,
            cellWidth,
            res,
            num;

        options =  $.extend({
            cell: 10,   // 总单元格数
            subcell: 1, // 每单元格里子单元格数，即没有标识的格子
            min: 0,     // 单元格起点
            step: 1,    // 每单元格每代表的数值
            template: "<%=num%>",   // 标识模板
            format: null // 标尺格式化函数
        }, options);

        options.cell = Number(options.cell) || 10;

        isFormatValid = options.format && typeof options.format === "function";
        cellWidth = 100 / (options.cell);

        for(var i = 0; i < options.cell; i++) {
            var num = options.min + i * options.step;
            if(isFormatValid) {
                res = options.format(num);
                num = typeof res !== "undefined" ? res : num;
            }

            if(i % options.subcell === 0) {
                $("<li class='vernier-cell'>" + num + "</li>").width(cellWidth + "%").appendTo($container);
            } else {
                $("<li class='vernier-subcell'></li>").width(cellWidth + "%").appendTo($container);
            }
        }
        $container.appendTo($elem)
    }

    D.removeDiary = function(diaryId){
        $.post(Ibos.app.url("diary/default/del"), { diaryids: diaryId }, function(res){
            if(res.isSuccess) {
                Ui.tip(res.msg);
                window.location.href = Ibos.app.url("diary/default/index");
            } else {
                Ui.tip(res.msg, "warning");
            }
        }, "json")
    }

    var diaryComment = {
        module: 'diary',
        table: 'diary',
        offset: 0,
        limit: 10
    }
    // 读取更多评论
    var loadMoreDiaryComment = function($button, param){
        var offset = $button.attr("data-offset"),
            param = $.extend(param, {
                offset: offset,
                loadmore: true
            });

        $button.hide().parent().waiting(null, "normal");
        $.post(Ibos.app.url("message/comment/getcomment"), param, function(res){
            if(res.IsSuccess) {
                $button.show().parent().waiting(false).before(res.data);
                // 如果没有更多已经没有更多了，则隐藏“加载更多”
                // 否则，更新评论起始值offset
                if(parseInt(res.count, 10) - offset < diaryComment.limit) {
                    $button.parent().hide();
                } else {
                    $button.parent().show();
                    $button.attr("data-offset", +offset + diaryComment.limit);
                }
            }
        })
    }


	return D;
})();

//初始化表情函数
function initCommentEmotion($context) {
        //按钮[data-node-type="commentEmotion"]
    $('[data-node-type="commentEmotion"]', $context).each(function(){
        var $elem = $(this),
            $target = $elem.closest('[data-node-type="commentBox"]').find('[data-node-type="commentText"]');
            $elem.ibosEmotion({ target: $target });
        }
    )
}

$(function(){
    Ibos.evt.add({
        // 删除一篇日志
        "removeDiary": function(param, elem){
            Ui.confirm(U.lang("DA.DELETE_ONE_DIARY_CONFIRM"), function(){
                Diary.removeDiary(param.id);
            })
        },
        // 删除多篇日志
        "removeDiarys": function(){
            var diaryIds = $("input[name='diaryids']:checked").map(function(){
                return this.value;
            }).get();

            if(!diaryIds.length) {
                Ui.tip(U.lang("DA.SELECT_AT_LEAST_ONE_WORK_RECORD"), 'warning');
            } else {
                diaryIds = diaryIds.join(",");
                Ui.confirm(U.lang('DA.SURE_TO_DEL'), function(){
                    Diary.removeDiary(diaryIds)
                })
            }
        },
        // 展开详细日志
        "showDiaryDetail": function(param, elem){
            var $elem = $(elem),
                $item = $elem.closest("li"),
                $detail = $item.find(".da-detail"),
                loaded = $item.data("loaded"),
                postData = {
                    op: 'showdiary',
                    diaryid: param.id
                };

            // 此参数用于控制是否出现图章
            if(param.fromController) {
                postData.fromController = param.fromController;
            }
			
			//此参数用于判断展开视图的左上角是头像还是时间
			if(param.isShowDiarytime){
				postData.isShowDiarytime = param.isShowDiarytime;
			}
			
            // 若未有缓存，则AJAX读取内容后，缓存并显示
            if(!loaded) {
                $item.waiting(null, "normal");
				$.ajax({
					url: Ibos.app.url("diary/default/index", postData),
					type: "get",
					dataType: "json",
					cache: false,
					success: function(res) {
						if(res.isSuccess === true){
							$detail.append(res.data)
							// 初始化进度条
							.find("[data-toggle='bamboo-pgb']").each(function(){
								var $pgb = $(this),
									defaultValue = +$pgb.parent().find('input').val();
								$pgb.studyplay_star({
									CurrentStar: defaultValue,
									Enabled: false
								});
							})
							$detail.show();
							// 展开详情
							Diary.toggleDetail($elem, "show");
							// 记录已缓存
							$item.data("loaded", true).waiting(false);

                            //当点击查看时，动态的给需要查看大图的img外层添加<a>标签
                            $(".summary-td img", $detail).each(function() {
                                var $elem  = $(this);
                                $elem.wrap("<a data-lightbox='diary' href='" + $elem.attr("src") + "' title='" + $elem.attr("title") + "'></a>");
                            });

                            var $diary = $('#diary_' + param.id);
							// 图章
							if(Ibos.app.g('stampEnable')) {
                                var $commentBtn = $detail.find("[data-act='addcomment']");
                                var $stampBtn = $detail.find('[data-toggle="stampPicker"]');
                                
								if($stampBtn.length) {
									Ibosapp.stampPicker($stampBtn, Ibos.app.g('stamps'));
									$stampBtn.on("stampChange", function(evt, data) {
										// Preview Stamp
										var stamp = '<img src="' + Ibos.app.g('stampPath') + data.stamp + '" width="150px" height="90px" />',
											smallStamp = '<img src="'+ data.path + '" width="60px" height="24px" />',
											$parentRow = $stampBtn.closest("div");

										$("#preview_stamp_"+ param.id ).html(stamp);
										$parentRow.find(".preview_stamp_small").html(smallStamp);
                                        $.extend($commentBtn.data("param"), {stamp: data.value});
									});
								}
								if(Ibos.app.g('autoReview') == '1') {
									$.get(Ibos.app.url("diary/review/edit", {'op': 'changeIsreview'}), {diaryid: param.id});
								}
							}
						} else {
							Ui.tip(res.msg, 'warning');
						}
					}
                });
            } else {
                Diary.toggleDetail($elem, "show");
            }
        },
        // 收起详细日志
        "hideDiaryDetail": function(param, elem){
            Diary.toggleDetail($(elem), "hide");
        },
        // 关注/取消关注
        "toggleAsterisk": function(param, elem){
            var $elem = $(elem),
                isAtt = $elem.hasClass("o-da-asterisk"),
                op = isAtt ? "unattention" : "attention";
            // AJAX记录数据，回调以下
            $.post(Ibos.app.url("diary/attention/edit", {'op': op}), {auid: param.id}, function(res) {
                if (res.isSuccess === true) {
                    Ui.tip(res.info);
                    $elem.attr("class", isAtt ? "o-da-unasterisk" : "o-da-asterisk");
                    $("a[data-node-type='udstar'][data-id='"+ param.id + "']").attr("class", isAtt ? "o-udstar pull-right" : "o-gudstar pull-right");
                }
            });
        },
        // 侧栏关注下属
        "toggleAsteriskUnderling": function(param, elem) {
            var $elem = $(elem),
                isAtt = $elem.hasClass("o-gudstar"),
                op = isAtt ? "unattention" : "attention";
            // AJAX记录数据，回调以下
            $.post(Ibos.app.url("diary/attention/edit", {'op': op}), {auid: param.id}, function(res) {
                if (res.isSuccess === true) {
                    Ui.tip(res.info);
                    if(isAtt){
                        $elem.addClass("o-udstar").removeClass("o-gudstar");
                        $("i[data-id='" + param.id + "']").addClass("o-da-unasterisk").removeClass("o-da-asterisk");
                    } else {
                        $elem.addClass("o-gudstar").removeClass("o-udstar");
                         $("i[data-id='" + param.id + "']").addClass("o-da-asterisk").removeClass("o-da-unasterisk");
                    }
                }
            });
        }
    });

    // 阅读人员ajax
    $("[data-node-type='loadReader']").each(function() {
        $(this).ajaxPopover(Ibos.app.url("diary/default/index", { op: "getreaderlist", diaryid: $.attr(this, "data-id")}));
    });

    //点评人员ajax
    $("[data-node-type='loadCommentUser']").each(function(){
        $(this).ajaxPopover(Ibos.app.url("diary/default/index", { op: "getcommentlist", diaryid: $.attr(this, "data-id")}));
    })

})