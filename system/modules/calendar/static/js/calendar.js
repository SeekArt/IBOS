/**
 * calendar.js
 * 日程安排
 * IBOS
 * @module		Global
 * @submodule	Calendar
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-18
 */

(function(){
	// @Todo 日程列表
	var calList = (function(){
		var _access = function(param, success) {
			if(!PAGE_URL.list) {
				return false;
			}
			$.post(PAGE_URL.list, param, function(res) {
				if(res.isSuccess) {
					// tip 操作成功
					success && success.call(null, res);
				} else {
					// tip 操作失败
				}
			}, "json")
		}

		var _getRow = function(id) {
			return $("#cal_list_" + id);
		}


		return {
			itemComplete: function(id){
				var $row = _getRow(id);
				$row.addClass("cal-list-complete");
				$row.find(".o-ok").addClass("active");
			},

			itemUncomplete: function(id){
				var $row = _getRow(id);
				$row.removeClass("cal-list-complete");
				$row.find(".o-ok").removeClass("active");
			},

			ajaxItemComplete: function(id){
				var that = this;
				_access({ op: "complete", id: id, complete: 1}, function(){
					that.itemComplete(id);
				});
			},

			ajaxItemUncomplete: function(id){
				var that = this;
				_access({ op: "complete", id: id, complete: 0}, function(){
					that.itemUncomplete(id);
				});
			},

			deleteRow: function(id){
				var $row = _getRow(id);
					$nextRow = $row.next();
				// 若删除行为某一日期的第一行时
				if($row.index() === 0) {
					// 若有下一行时，将下一行设为当前tbody的第一行，主要目的是显示日期
					if($nextRow && $nextRow.length) {
						$nextRow.addClass("cal-list-first");
					// 否则，此行为最后一行，删除其tbody容器
					} else {
						$row.parent("tbody").remove();
					}
				}

				$row.remove();
			},

			ajaxDeleteRow: function(id){
				var that = this;
				_access({ op: "delete", id: id}, function(){
					that.deleteRow(id);
				});
			},

			openEditDialog: function(id){
				var that = this,
					_init = false,
					_cache = {};
				var d = $.artDialog({
					id: "d_edit_dialog",
					ok: function(){
						// 如果内容未初始化，则不允许提交
						if(!_init) {
							return false;
						}
						this.DOM.content.find("form").submit();
					},
					cancel: function(){
						// 销毁时间选择器
						$("#date_time_start, #date_time_end, #date_start").datetimepicker("remove");
						// 解绑事件
						this.DOM.content.off(".complete");
					},
					width: 500
				});
				_access({op: "getdetail", id: id}, function(res){
					// 读取完后设置内容
					d.content(res.content);
					// 初始化复选框
					var $fullday = $("#cal_fullday"),
						$calTimeInterval = $("#cal_time_interval"),
						$calDateInterval = $("#cal_date_interval");

					$fullday.label().on("change", function(){
						var isFullday = $.prop(this, "checked");

						$calTimeInterval.toggle(!isFullday);
						$calDateInterval.toggle(isFullday);

					});

					// 绑定事件
					d.DOM.content.on("click.complete", "[data-complete]", function(){
						var isComplete = $.attr(this, "data-complete") === "1" ? true : false,
							$elem = $(this);

						if(isComplete) {
							that.ajaxItemUncomplete(id);
							$elem.attr("data-complete", "0").text(U.lang('CM.COMPLETE'))
							.next().hide();
						} else {
							that.ajaxItemComplete(id);
							$elem.attr("data-complete", "1").text(U.lang('CM.UNCOMPLETE'))
							.next().show();
						}
					});

					// 初始化时间选择
					$("#date_time_start").datepicker({
						target: $("#date_time_end"),
						format: "yyyy-mm-dd hh:ii",
						startView: 1,
						minView: 0
					});

					// 初始化日期选择
					$("#date_start").datetimepicker();

					// 选色器
					var $calColor = $("#cal_color");
					$calColor.colorPicker({
						data: ["#99C8E8", "#C8E698", "#FEF0B1", "#F6C585", "#F0A7A7", "#EDD6FF", "#C0C9CE", "#EDF0F5", "#99CBED"],
						onPick: function(hex){
							$calColor.find("span").css("background-color", hex);
							$calColor.next("input").val(hex);
						}
					});
					_init = true;
				})
			}
		}	
	})();

	var eventHandler = {
		"click": {
			"toggleItemComplete": function(elem, param){
				if($(elem).hasClass("active")){
					calList.ajaxItemUncomplete(param.id);
				} else{
					calList.ajaxItemComplete(param.id);
				}
			},
			"deleteRow": function(elem, param){
				calList.ajaxDeleteRow(param.id);
			},
			"editRow": function(elem, param){
				calList.openEditDialog(param.id);
			}
		}
	}

	var _trigger = function(elem, type) {
		var prop = "data-" + type,
			name = $.attr(elem, prop),
			param;

		if(eventHandler[type][name] && $.isFunction(eventHandler[type][name])) {
			param = $(elem).data("param");
			return eventHandler[type][name].call(eventHandler[type], elem, param);
		}	
	}

	$(document).on("click", "[data-click]", function(){
		_trigger(this, "click");
	})

})();
