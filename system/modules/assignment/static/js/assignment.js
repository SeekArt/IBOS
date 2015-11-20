/**
 * Assignment
 * 指派任务通用 JS
 * @version $Id$
 */

var Assignment = {
	// 任务最大字符数
	ASSIGN_MAXCHAR: 25,
	ASSIGN_DESC_MAXCHAR: 140,


	op: {
		/**
		 * 发布任务
		 * @method addTask
		 * @param  {Array} param 传入Array格式数据
		 * @return          	 返回deferred对象
		 */
		addTask: function(param){
			if(param && param.length) {
				var url = Ibos.app.url('assignment/default/add', { addsubmit : 1 });
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 发布更新任务信息任务
		 * @method updateTask
		 * @param  {Number} param 传入任务ID
		 * @param  {Array}  param 传入Array格式数据
		 * @return          	  返回deferred对象
		 */
		updateTask: function(id, param){
			if(id && param && param.length){
				var url = Ibos.app.url('assignment/default/edit', {'updatesubmit': 1, 'id': id});
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 获取任务信息
		 * @method getTask
		 * @param  {Number} param 传入任务ID
		 * @return          	  返回deferred对象
		 */
		getTask: function(id){
			if(id){
				var url = Ibos.app.url('assignment/default/edit'),
					param = {id: id};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 移除任务
		 * @method removeTask
		 * @param  {Number} id 传入任务ID
		 * @return             返回deferred对象
		 */
		removeTask: function(id){
			if(id){
				var url = Ibos.app.url("assignment/default/del"),
					param = { id: id };
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 催办任务
		 * @method urgeTask
		 * @param  {Number} id 传入任务ID
		 * @return             返回deferred对象
		 */
		urgeTask: function(id){
			if(id){
				var url = Ibos.app.url("assignment/unfinished/ajaxentrance"),
					param = { op: "push", id: id };
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 完成任务
		 * @method finishTask
		 * @param  {Number} id 传入任务ID
		 * @return             返回deferred对象
		 */
		finishTask: function(id){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'),
					param = {op: 'toFinished', id : id};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 评价任务，添加图章
		 * @method addStamp
		 * @param  {Number} id 传入任务ID
		 * @return             返回deferred对象
		 */
		addStamp: function(id, stampId){
			if(id && stampId) {
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'),
					param = {op: 'stamp',  id: id, stamp: stampId };
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 重启任务
		 * @method restartTask
		 * @param  {Number} id 传入任务ID
		 * @return             返回deferred对象
		 */
		restartTask: function(id){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'),
					param = {op: 'restart', id: id};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 申请延期任务
		 * @method applyDelayTask
		 * @param  {Number} id 	  传入任务ID
		 * @param  {Object} param 传入JSON格式数据
		 * @return                返回deferred对象
		 */
		applyDelayTask: function(id, param){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance');
					param = $.extend( {}, param, { op: "applyDelay", id: id });
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 发布人延期任务
		 * @method delayTask
		 * @param  {Number} id 	  传入任务ID
		 * @param  {Object} param 传入JSON格式数据
		 * @return                返回deferred对象
		 */
		delayTask: function(id, param){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance');
					param = $.extend( {}, param, {op: "delay", id: id });
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 处理任务延期申请
		 * @method dealDelayApply
		 * @param  {Number}  id    传入任务ID
		 * @param  {Boolean} agree 传入布尔值
		 * @return                 返回deferred对象
		 */
		dealDelayApply: function(id, agree){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'),
					param = {op: 'runApplyDelayResult', id:id , agree : +agree};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 申请取消任务
		 * @method applyCancelTask
		 * @param  {Number} id    传入任务ID
		 * @param  {Object} param 传入JSON格式数据
		 * @return                 返回deferred对象
		 */
		applyCancelTask: function(id, param){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance');
					param = $.extend( {}, param, { op: 'applyCancel', id: id});
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 取消任务
		 * @method applyCancelTask
		 * @param  {Number} id    传入任务ID
		 * @return                 返回deferred对象
		 */
		cancelTask: function(id){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'), 
					param = {'op': 'cancel', id: id};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 处理取消任务
		 * @method dealCancelApply
		 * @param  {Number}  id    传入任务ID
		 * @param  {Boolean} agree 传入布尔值
		 * @return                 返回deferred对象
		 */
		dealCancelApply: function(id, agree){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance'),
					param = {op: 'runApplyCancelResult', id : id, agree : +agree};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 添加提醒
		 * @method addRemind
		 * @param  {Number} id    传入任务ID
		 * @param  {Object} param 传入JSON格式数据
		 * @return                 返回deferred对象
		 */
		addRemind: function(id, param){
			if(id){
				var url = Ibos.app.url('assignment/unfinished/ajaxentrance');
					param = $.extend({}, param, {op: 'remind', remindsubmit: 1, id : id});
				return $.post(url, param, $.noop, "json");
			}
		}
	},
	/**
	 * 验证任务信息的正确性
	 * @method  validateTaskForm
	 * @param  {Element} form 传入form表单元素
	 * @return {Boolean}      返回布尔值
	 */
	validateTaskForm: function(form){
		if(!form || !form.elements){
			return false;
		}

		var subject = $.trim(form.subject.value);
		// 任务内容
		if(!subject){
			Ui.tip(U.lang("ASM.PLEASE_INPUT_SUBJECT"), "warning");
			return false;
		}
		if(U.getCharLength(subject) > this.ASSIGN_MAXCHAR) {
			Ui.tip(U.lang("ASM.SUBJECT_OVERCOUNT"), "warning");
			return false;
		}

		// 负责人
		if(!form.chargeuid.value){
			Ui.tip(U.lang("ASM.PLEASE_SELECT_CHARGE"), "warning");
			return false;
		}

		if(!form.endtime.value){
			Ui.tip(U.lang("ASM.PLEASE_SELECT_ENDTIME"), "warning");
			return false;
		}

		if(U.getCharLength(form.description.value) > this.ASSIGN_DESC_MAXCHAR) {
			Ui.tip(U.lang("ASM.DESCRIPTION_OVERCOUNT"), "warning");
			return false;
		}

		return true;
	},
	/**
	 * 列表加载
	 * @method inLoading
	 */
	inLoading: function(inLoad){
		$('[data-node-type="taskView"]').waiting( inLoad ? (null, "normal") : false);
	},
	/**
	 * 添加任务
	 * @method addTask
	 * @param {Element}  form       传入form表单元素
	 * @param {Function} [callback] 回调函数
	 */
	addTask: function(form, callback){
		// 添加任务时的视图变化
		var addTaskView = function(data){
			if(data){
				var tableTpl = '<table class="table table-hover am-op-table" data-node-type="taskTable"></table>',
					$table = null,
					id = "",
					replaceContent = "";

				// 如果是安排给自己，则另外需要添加一行负责任务
				if(data.charge.uid == Ibos.app.g("uid")){
					id = "am_my_charge";
					// 目前还没有任何一行时，新建表格并去除空值
					replaceContent = '#'+ id +' .am-charge-empty';

					data.chargeself = true;
				// 否则添加一行指派任务
				} else {
					id = "am_my_designee";
					replaceContent = '#'+ id +' .am-designee-empty';
				}
				$table = $("#"+ id +" [data-node-type='taskTable']");

				!$table.length && ($table = $(tableTpl).replaceAll(replaceContent));
				
				$table.prepend($.template("tpl_task", data));

			}
		};

		// 重置新增任务表单
		var resetAddForm = function(form){
			var $form = $(form);
			// 取回焦点
			$($form[0].subject).val("").focus();
			$form[0].description.value = "";
			
			// 移除所有附件
			$('[data-node-type="attachRemoveBtn"]', $form).trigger("click");
		};

		if(this.validateTaskForm(form)){
			var $form =$(form);
			$form.waiting(null, "small", true);
			var param = $(form).serializeArray();
			Assignment.op.addTask(param).done(function(res){
				$form.waiting(false);
				if(res.isSuccess) {
					addTaskView(res.data);
					resetAddForm(form);
					Ui.tip(U.lang("ASM.ADD_TASK_SUCCESS"));				
				} else {
					Ui.tip(res.msg, "danger");
				}
			});
		}
	},
	/**
	 * 更新任务
	 * @method updateTask
	 * @param  {[type]}   id         传入任务ID
	 * @param  {Element}   form      传入form表单元素
	 * @param  {Function} [callback] 回调函数
	 */
	updateTask: function(id, form, callback){
		if(this.validateTaskForm(form)){
			Assignment.op.updateTask(id, $(form).serializeArray()).done(callback);
		}
	},
	/**
	 * 从视图上移除对应任务
	 * @method  removeTask
	 * @param  {Number} ids 传入任务ID 
	 */
	removeTask: function(ids){
		var idArr;
		if(ids){ idArr = (ids + "").split(","); }

		if(idArr.length){
			$.each(idArr, function(i, id){
				$("[data-node-type='taskTable'] tr[data-id='" + id + "']").fadeOut(function(){
					$(this).remove();
				});
			});
		}
	},
	/**
	 * 任务编辑弹窗
	 * @method openTaskEditDialog
	 * @param  {Number} id 传入任务ID
	 */
	openTaskEditDialog: function(id){
		var _this = this;
		Ui.closeDialog("d_am_edit");
		Ui.ajaxDialog(Ibos.app.url("assignment/default/edit", { id: id }), {
			id: "d_am_edit",
			title: U.lang("ASM.EDIT_TASK"),
			width: 780,
			padding: "20px"
		});
	},
	/**
	 * 提醒弹窗
	 * @method showRemindDialog
	 * @param  {Number} id 传入任务ID
	 */
	showRemindDialog: function(id){
		Ui.ajaxDialog(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'remind', 'id': id}), {
			id: "d_task_remind",
			title: U.lang("ASM.SETUP_REMIND"),
			ok: function(){
				var dialog = this,
					$form = this.DOM.content.find("form"),
					remindDate = $form[0].reminddate.value,
					remindTime = $form[0].remindtime.value,
					param = {};

				param.remindTime = remindDate ? remindDate + " " + remindTime : "";
				param.remindContent = $form[0].remindcontent.value;
				Assignment.op.addRemind(id, param).done(function(res){
					Ui.tip(res.msg, res.isSuccess ? "" : "danger");

					if(res.isSuccess) {
						dialog.close();
						window.location.reload();
					}
				});
				return false;
			},
			cancel: true
		});
	}
};

/**
 * 下拉菜单日期选择器
 * @method DropdownDatepicker
 * @param {Object} selector 传入Jquery对象节点
 */
var DropdownDatepicker = function(selector){
	var $elem = $(selector);
	var createItem = function(dateOffset){
		var $item;
		var today = (new Date()).getDay();
		var dayLang = [U.lang("ASM.DATE.SUNDAY"), U.lang("ASM.DATE.MONDAY"), U.lang("ASM.DATE.TUESDAY"), U.lang("ASM.DATE.WEDNESDAY"), U.lang("ASM.DATE.THURSDAY"), U.lang("ASM.DATE.FRIDAY"), U.lang("ASM.DATE.SATURDAY")];
		var tpl = 	'<li class="<%= active ? \'active\' : \'\' %>">' + 
						'<a href="javascript:;" data-node-type="dateOffset" data-offset="<%= offset %>"><%= dayName %></a>' +
					'</li>';

		// 今天
		if(dateOffset === 0){
			$item = $.tmpl(tpl, { active: true, offset: 0, dayName: U.lang("ASM.DATE.TODAY") });
		// 下周
		} else if(dateOffset + today > 6) {
			$item = $.tmpl(tpl, { active: false, offset: dateOffset, dayName: U.lang("ASM.DATE.DOWN") +  dayLang[dateOffset + today - 7] });
		// 本周
		} else {
			$item = $.tmpl(tpl, { active: false, offset: dateOffset, dayName: dayLang[dateOffset + today] });
		}
		return $item;
	};

	var createMenu = function(){
		var $menu = $('<ul class="dropdown-menu dropdown-datepicker-menu"></ul>');
		for(var i = 0; i < 7; i++) {
			$menu.append(createItem(i));
		}
		$menu.append(
			'<li class="divider" style="margin: 0;"></li>',
			'<li><a href="javascript:;" data-node-type="dateOther">'+ U.lang("ASM.OTHER_DATE") +'</a></li>',
			'<li class="divider" style="margin: 0;"></li>',
			'<li><a href="javascript:;" data-node-type="dateEmpty">'+ U.lang("ASM.NO_LONGER") +'</a></li>'
		);

		// 初始化其他日期
		var $pickerCtrl = $menu.find('[data-node-type="dateOther"]').datepicker()
		// 显示日期选择器时，重新定位
		.on("show", function(){
			var widget = $(this).data("datetimepicker").widget;
			widget.position({
				of: this,
				at: "right top",
				my: "left+5 top"
			});
		})
		// 关闭日期选择器同时关闭下拉菜单
		.on("hide", function(){
			$(document).trigger("click.dropdown.data-api");
		})
		.on("changeDate", function(evt){
			notifyChange({ date: evt.localDate });
		});

		$menu.bindEvents({
			// 快捷日期选择
			"click [data-node-type='dateOffset']": function(){
				var offset = +$.attr(this, "data-offset"),
					date = new Date();
				date.setDate(date.getDate() + offset);
				notifyChange({ date: date });
				select($(this).parent());
			},
			"click [data-node-type='dateEmpty']": function(){
				notifyChange({ date: null });
				select($(this).parent());
			}
		});

		return $menu;
	};

	var select = function($elem){
		$elem.addClass("active").siblings().removeClass("active");
	};

	var notifyChange = function(data){
		$elem.trigger("changeDate", data);
	};
	
	$elem && $elem.length && ($elem.attr("data-toggle", "dropdown").after(createMenu()));
};

$(function(){
	// 更改任务状态
	$(document).on("click", ".am-checkbox:not(.disabled)", function(){
		var id = $.attr(this, "data-id"),
			$elem = $(this),
			_callback = function(res){
				Assignment.inLoading(false);
				res.isSuccess && Assignment.removeTask(id);
				Ui.tip(res.msg, res.isSuccess ? "" : "danger");
			};

		$elem.addClass("checked");
		Assignment.inLoading(true);		
		// 取消完成（跟重启任务是同一操作？）
		Assignment.op[ $elem.hasClass("am-checkbox-ret") ? "restartTask" : "finishTask"](id).done(_callback);
	});

	Ibos.evt.add({
		// 回到我负责的任务顶部
		"toCharge": function(){
			Ui.scrollYTo("am_my_charge", -75);
		},
		// 回到我指派的任务顶部
		"toDesignee": function(){
			Ui.scrollYTo("am_my_designee", -75);
		},
		// 回到我参与的任务顶部
		"toParticipant": function(){
			Ui.scrollYTo("am_my_participant", -75);
		},
		// 回到页面顶部
		"totop": function(){
			Ui.scrollToTop();
		},

		// 打开提醒设置对话框
		"openRemindDialog": function(param){
			Assignment.showRemindDialog(param.id);
		},

		// 打开编辑任务对话框
		"openTaskEditDialog": function(param){ 
			Assignment.openTaskEditDialog(param.id);
		},

		// 移除任务
		"removeTask": function(param){
			var $ele = $(this);
			Ui.confirm(U.lang("ASM.REMOVE_TASK_CONFIRM"), function(){
				Assignment.inLoading(true);
				Assignment.op.removeTask(param.id).done(function(res){
					var isSuccess = res.isSuccess;
					Assignment.inLoading(false);
					isSuccess && Assignment.removeTask(param.id);
					Ui.tip(res.msg, isSuccess ? "" : "danger");
					if( isSuccess ){
						if( $ele.closest(".am-block").find("tr").length === 1 ){
							window.location.reload();
						}
					}
				});
			});
		},

		// 更新任务数据
		"updateTask": function(param){
			var dialog = Ui.getDialog("d_am_edit"),
				form = dialog.DOM.content.find("form")[0];

			Assignment.updateTask(param.id, form, function(res){
				Ui.tip(res.msg, res.isSuccess ? "" : "danger");
				if(res.isSuccess){
					dialog.close();
					window.location.reload();
				}
			});
		}
	});
});
