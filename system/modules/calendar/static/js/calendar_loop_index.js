/**
 * 日程--周期性日程--首页
 * Calendar/Loop/Index
 * @author 		inaki
 * @version 	$Id$
 */
var LoopIndex = {
	op : {
		/**
		 * 添加周期性日程
		 * @method addLoop
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象  
		 */
		addLoop : function(param){
			var url = Ibos.app.url('calendar/loop/add');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 编辑周期性日程
		 * @method editLoop
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象  
		 */
		editLoop : function(param){
			var url = Ibos.app.url('calendar/loop/edit');
			return $.post(url, param, $.noop);
		},
		/**
		 * 删除周期性日程
		 * @method delLoop
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象  
		 */
		delLoop : function(param){
			var url = Ibos.app.url('calendar/loop/del');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 获取单个周期性日程信息
		 * @method getLoopInfo
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象  
		 */
		getLoopInfo : function(param){
			var url = Ibos.app.url('calendar/loop/edit');
			param = $.extend({}, param, {op : 'geteditdata'});
			return $.get(url, param, $.noop);
		}
	}
};
$(function(){
	// 获取周期性任务列表
	var loopData = Ibos.app.g("loopList");
	if(!loopData.length){
		$("#no_data_tip").show();
	}

	var $loopBody = $("#loop_tbody");
	var loopTable = new Ibos.List($loopBody, "loop_template", {idField: "calendarid"});

	$loopBody.on("list.add list.update", function(evt, data){
		data.item.find(".checkbox input[type='checkbox']").label();
	});
	// 默认的颜色
	var defaultColors = ["#3497DB", "#A6C82F", "#F4C73B", "#EE8C0C", "#E76F6F", "#AD85CC", "#98B2D1", "#82939E"],
		getColor = function(value){
			value = parseInt(value, 10);
			return defaultColors[ defaultColors[value] ? value :  0];
		},
		toTplData = function(data){
			return {
				calendarid: data.calendarid,
				bgcolor: getColor(data.category),
				subject: data.subject,
				uptime: data.uptime,
				cycle: data.cycle
			};
		};

	for(var i = 0; i < loopData.length; i++) {
		loopTable.addItem(toTplData(loopData[i]));
	}


	var loop = {
		/** 
		 * 改变循环类型(周/月/年)
		 * @method  _setType
		 * @param {String} type 设置循环类型
		 */
		_setType: function(type){
			$("#repeat_per_" + type).show().siblings().hide();
			if(type === "year") {
				$("#loop_year_day_picker").datepicker({
					format: "mm-dd"
				});
			}
		},

		/**
		 * 获取新建编辑对话框中的数据
		 * @method  _getDialogData
		 * @return {Object} 返回对话框的参数
		 */
		_getDialogData: function(){
			return {
				subject: $("#loop_subject").val(),
				starttime: $("#loop_start_time").val(),
				// 复数？？
				endtimes: $("#loop_end_time").val(),
				category: $('#loop_theme').val(),
				reply: true,
				recurringbegin: $('#loop_start_day').val(),
				recurringend: $('#loop_end_day').val(),
				recurringtype: $('#loop_type').val(),
				weekbox: U.getCheckedValue("weekbox[]"),
				month: $('#loop_month_day').val(),
				year: $('#loop_year_day').val()
			};
		},
		/**
		 * 设置新建编辑对话框中的数据
		 * @param  {Object} data 传入JSON格式数据
		 */
		_setDialogData: function(data){
			var that = this,
				$form = $("#add_calendar_form"),
				vals,
				date = new Date,
				startTime = data.starttime ? 
					moment(data.starttime, "HH:mm").toDate() : 
					moment().toDate(),

				endTime = data.endtime ? 
					moment(data.endtime, "HH:mm").toDate() : 
					moment().add(30, "m").toDate(),

				startDate = data.recurringbegin ? 
					moment(data.recurringbegin).toDate() : 
					moment().toDate(),

				endDate = data.recurringend ? 
					moment(data.recurringend).toDate() : 
					null;

			data.subject = U.entity.unescape(data.subject);
			!U.isUnd(data.subject) && $("#loop_subject").val(data.subject);

			// 初始化开始时间及结束时间， 默认为当前时间至30分钟后
			$("#loop_start_time_datepicker").datetimepicker("setLocalDate", startTime);
			$("#loop_end_time_datepicker").datetimepicker("setLocalDate", endTime );

			!U.isUnd(data.category) && $('#loop_theme').val(data.category).trigger("change");

			$("#loop_start_day_datepicker").datetimepicker("setLocalDate", startDate);
			
			(endDate) && $('#loop_end_day_datepicker').datetimepicker("setLocalDate", endDate);


			!U.isUnd(data.recurringtype) && $("#loop_type").val(data.recurringtype);
			// 还原循环类型
			$('#loop_type').off("change").on("change", function() {
				that._setType(this.value);
			}).trigger("change");

			// 还原复选框选中状态
			switch(data.recurringtype){
				case "week" :
					 vals = data.recurringtime.split(",");
					 $form.find("[name='weekbox[]']").each(function(){
					 	$(this).prop("checked", $.inArray(this.value, vals) !== -1).label("refresh");
					 });
					break;
				case "month" :
				case "year" :
					data.recurringtime && $("#loop_"+ data.recurringtype +"_day").val(data.recurringtime);
					break;
			}
		},
		/**
		 * 检验提交的数据
		 * @param  {Object} data 传入JSON格式数据
		 * @return {Boolean}     返回真
		 */
		_validateData: function(data){
			return true;
		},
		/**
		 * 显示弹窗
		 * @method _showDialog
		 * @param  {Object} options 传入JSON格式数据 title, ok, init, cancel
		 */
		_showDialog: function(options){ 
			var that = this;
			options = options || {};
			Ui.dialog({
				id: 'd_loop_dialog',
				title: options.title,
				content: Dom.byId('loop_dialog'),
				ok: function(){
					if ($.trim($('#loop_subject').val()) === '') {
						$('#loop_subject').blink().focus();
						return false;
					}
					var loopData = that._getDialogData();
					that._validateData(loopData) && options.ok && options.ok(loopData);
				},
				init: function(){
					// 开始时间 结束时间组 这里由于做了容错，所以不对时间范围做限制
					$("#loop_start_time_datepicker, #loop_end_time_datepicker").datepicker({
						pickTime: true,
						pickDate: false,
						pickSeconds: false,
						format: "hh:ii"
					});

					// 开始日期 结束日期组
					$("#loop_start_day_datepicker").datepicker({
						target: "loop_end_day_datepicker"
					});

					// 颜色选择器
					var $pickerBtn = $("#color_picker_btn"),
						$pickerInput = $("#loop_theme"),
						theme = $pickerInput.val();

					var setColor = function(val){
						color = getColor(val);
						$pickerBtn.css("background-color", color);
					};

					$pickerInput.off("change").on("change", function(){
						setColor(this.value);
					}).trigger("change");

					$pickerBtn.colorPicker({ 
						data: defaultColors,
						onPick: function(hex){
							$pickerInput.val(hex ? $.inArray(hex, defaultColors) : -1).trigger("change");
						}
					});

					options.init && options.init();
				},
				cancel: options.cancel || true
			});
		},

		/**
		 * 新增周期性事务
		 * @method add
		 */
		add: function(){
			var that = this;
			this._showDialog({
				title: U.lang("CAL.PREIODIC_AFFAIRS"),
				init: function(){
					that._setDialogData({
						subject: "",
						starttime: "",
						endtime: "",
						category: "-1",
						recurringend: "",
						recurringtype: "week",
						recurringtime: ""
					});
				},
				ok: function(data){
					LoopIndex.op.addLoop(data).done(function(res){
						if(res.isSuccess) {
							loopTable.addItem(toTplData(res), true);
							Ui.tip('@OPERATION_SUCCESS');
						} else {
							Ui.tip('@OPERATION_FAILED', 'danger');
						}
					});
					$("#no_data_tip").hide();
				}
			});
		},
		/** 
		 * 编辑周期性事务
		 * @method  edit
		 * @param  {String} id 周期性事务的ID
		 */
		edit: function(id) {
			var that = this;

			Ui.dialog({
				title: U.lang("CAL.PREIODIC_AFFAIRS"),
				init: function(){
					var _this = this,
						param = { editCalendarid: id };
					LoopIndex.op.getLoopInfo(param).done(function(res){
						_this.close();
						that._showDialog({
							title: U.lang("CAL.PREIODIC_AFFAIRS"),
							init: function(){
								that._setDialogData(res);
							},
							ok: function(data){
								var param = $.extend({ editCalendarid: id }, data);
								LoopIndex.op.editLoop(param).done(function(res){
									if(res.isSuccess){
										res.calendarid = id;
										loopTable.updateItem(toTplData(res));
										Ui.tip('@OPERATION_SUCCESS');
									} else {
										Ui.tip('@OPERATION_FAILED', 'warning');
									}
								});
							}
						});
					});
				}
			});
		},
		/** 
		 * 删除周期性事务
		 * @method  remove
		 * @param  {String} id 周期性事务的ID
		 */
		remove: function(id) {
			if(id){
				Ui.confirm(U.lang("CAL.CONFIRM_TO_DELETE_THIS_SERIES"), function(){
					var param = { delCalendarid: id };
					LoopIndex.op.delLoop(param).done(function(res){
						var ids;
						if(res.isSuccess) {
							ids = id.split(",");
							$.each(ids, function(index, oneId) {
								loopTable.removeItem(oneId);
							});
							Ui.tip("@OPERATION_SUCCESS");
						} else {
							Ui.tip("@OPERATION_FAILED", "warning");
						}
					});
				});
			}
		}
	};


	Ibos.evt.add({
		// 添加周期性任务
		addLoop: function(){ loop.add(); },

		// 编辑周期性任务
		editLoop: function(param, elem){
			var id = $.attr(elem, 'data-id');
			loop.edit(id);
		},

		// 删除单个周期性任务
		deleteLoop: function(param, elem){
			loop.remove($.attr(elem, 'data-id'));
		},

		// 删除多个周期性任务
		deleteLoops: function(){
			var ids = U.getCheckedValue("loop[]");
			if(!ids) {
				Ui.tip("@SELECT_AT_LEAST_ONE_ITEM", "warning");
				return false;
			}
			loop.remove(ids);
		}
	});
});

