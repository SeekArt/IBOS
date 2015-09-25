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
	// 日程列表
	var calList = (function(){
		/**
		 * 数据访问
		 * @method _access
		 * @param  {Object}   param     传入JSON格式数据
		 * @param  {Object}  			返回deffered对象
		 */
		var _access = function(param) {
			if(!PAGE_URL.list) {
				return false;
			}
			return $.post(PAGE_URL.list, param, $.noop, "json");
		};
		/**
		 * 获取当前项目
		 * @method _getRow
		 * @param  {String} id 当期的ID
		 * @return {Object}    返回当前的jquery节点
		 */
		var _getRow = function(id) {
			return $("#cal_list_" + id);
		};


		return {
			/**
			 * 项目完成
			 * @method itemComplete
			 * @param  {String} id 传入当前的id
			 */
			itemComplete: function(id){
				var $row = _getRow(id);
				$row.addClass("cal-list-complete");
				$row.find(".o-ok").addClass("active");
			},
			/**
			 * 项目未完成
			 * @method itemUncomplete
			 * @param  {String} id 传入当前的id
			 */
			itemUncomplete: function(id){
				var $row = _getRow(id);
				$row.removeClass("cal-list-complete");
				$row.find(".o-ok").removeClass("active");
			},
			/**
			 * 项目完成
			 * @method ajaxItemComplete
			 * @param  {String} id 传入当前的id
			 */
			ajaxItemComplete: function(id){
				var that = this,
					param = { op: "complete", id: id, complete: 1};
				_access(param).done(function(){
					that.itemComplete(id);
				});
			},
			/**
			 * 项目未完成
			 * @method ajaxItemUncomplete
			 * @param  {String} id 传入当前的id
			 */
			ajaxItemUncomplete: function(id){
				var that = this,
					param = { op: "complete", id: id, complete: 0};
				_access(param).done(function(){
					that.itemUncomplete(id);
				});
			},
			/**
			 * 删除当前行
			 * @method deleteRow
			 * @param  {String} id 传入当前的id
			 */
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
			/**
			 * 删除当前行
			 * @method ajaxDeleteRow
			 * @param  {String} id 传入当前的id
			 */
			ajaxDeleteRow: function(id){
				var that = this,
					param = { op: "delete", id: id};
				_access(param).done(function(){
					that.deleteRow(id);
				});
			},
			/**
			 * 打开编辑窗口
			 * @method openEditDialog
			 * @param  {String} id 传入当前的id
			 */
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
				var param = {op: "getdetail", id: id};
				_access(param).done(function(res){
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

						that[ isComplete ?　'ajaxItemUncomplete' : 'ajaxItemComplete' ](id);
						$elem.attr("data-complete", isComplete ? "1" : "0").text( U.lang( isComplete ? 'CM.COMPLETE' : 'CM.UNCOMPLETE' ) )
						.next()[ isComplete ? "show" : "hide"]();
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
				});
			}
		};
	})();
	// 事件处理
	var eventHandler = {
		"click": {
			// 切换项目完成状态
			"toggleItemComplete": function(elem, param){
				var isActive = $(elem).hasClass("active");
				calList[isActive ? "ajaxItemUncomplete" : "ajaxItemComplete"](param.id);
			},
			// 删除当前行
			"deleteRow": function(elem, param){
				calList.ajaxDeleteRow(param.id);
			},
			// 编辑当前行
			"editRow": function(elem, param){
				calList.openEditDialog(param.id);
			}
		}
	};

	// 事件委派处理
	var _trigger = function(elem, type) {
		var prop = "data-" + type,
			name = $.attr(elem, prop),
			param;

		if(eventHandler[type][name] && $.isFunction(eventHandler[type][name])) {
			param = $(elem).data("param");
			return eventHandler[type][name].call(eventHandler[type], elem, param);
		}	
	};

	$(document).on("click", "[data-click]", function(){
		_trigger(this, "click");
	});
})();
