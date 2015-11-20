/**
 * reportType.js
 * 总结计划--汇报类型设置
 * IBOS
 * Report
 * @author		gzhzh
 * @version		$Id$
 */
var ReportType = {
	op : {
		/**
		 * 添加类型
		 * @method addType
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		addType : function(param){
			var url = Ibos.app.url('report/type/add');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 删除类型
		 * @method removeType
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		removeType : function(param){
			var url = Ibos.app.url('report/type/del');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 更新类型
		 * @method updateType
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		updateType : function(param){
			var url =Ibos.app.url('report/type/edit');
			return $.post(url, param, $.noop, 'json');
		}
	}
};

// 汇报类型
ReportType.typeTableInited = false;
ReportType.typeTable = {
	// 节点ID
	el: "#rp_type_table",
	// 模板ID
	template: "rp_type_tpl",
	/**
	 * 初始化
	 * @method init
	 */
	init: function(){
		var that = this;

		this.$el = $(this.el);

		this.$el.bindEvents({
			"click .o-plus": function(){
				var formData = that.$el.find("tfoot input, tfoot select").serializeArray();
				that.addType(U.serializedToObject(formData));
			},

			"click [data-click='removeType']": function(){
				that.removeType($.attr(this, "data-id"));
			},

			"click [data-click='editType']": function(){
				that.editType($.attr(this, "data-id"));
			},

			"click [data-click='saveType']": function(){
				that.updateType($.attr(this, "data-id"));
			},

			"click [data-click='cancelEdit']": function(){
				that.cancelEdit($.attr(this, "data-id"));
			},

			// 区间改变，如果值为“其他”，显示天数框，否则隐藏
			"change [name='intervaltype']": function(){
				$(this).next().toggle(this.value == "5");
			}
		});

		this.$el.on("validerror", function(evt, data){
			var $elem = $('[name="' + data.name + '"]', data.context || $(this).find("tfoot"));

			$elem.blink().focus();
			
			Ui.tip(data.msg, "warning");
		});
	},
	/**
	 * 验证信息的有效性
	 * @method validTypeData
	 * @param  {Object}  data    传入JSON格式数据
	 * @param  {String}  context 传入错误信息内容
	 * @return {Boolean}         返回是否通过验证
	 */
	validTypeData: function(data, context){
		var errorInfo = null;
		// 序号不为空
		if($.trim(data.sort) === "") {
			errorInfo = {
				name: "sort",
				msg: "@RP.SORT_CAN_NOT_BE_EMPTY"
			};
		// 序号必须为数字
		} else if(!U.isPositiveInt(data.sort)) {
			errorInfo = {
				name: "sort",
				msg: "@RP.SORT_ONLY_BE_POSITIVEINT"
			};
		// 类型名不为空
		} else if($.trim(data.typename) === "") {
			errorInfo = {
				name: "typename",
				msg: "@RP.TYPENAME_CAN_NOT_BE_EMPTY"
			};
		// 自定义区间周期
		} else if(data.intervaltype == "5") {
			// 区间天数不为空
			if($.trim(data.intervals) === "") {
				errorInfo = {
					name: "intervals",
					msg: "@RP.INTERVALS_CAN_NOT_BE_EMPTY"
				};
			// 区间天数必须为数字
			} else if(!U.isPositiveInt(data.intervals)) {
				errorInfo = {
					name: "intervals",
					msg: "@RP.INTERVALS_ONLY_BE_POSITIVEINT"
				};
			}
		}

		if(errorInfo) {
			errorInfo.source = data;
			errorInfo.context = context;
			this.$el.trigger("validerror", errorInfo);
			return false;
		}

		return true;
	},

	/**
	 * 添加汇报类型
	 * @method addType
	 * @param {String} data 传入汇报类型
	 */
	addType: function(data){
		var that = this;

		if(this.validTypeData(data)) {
			var param = { typeData: data };
			ReportType.op.addType(param).done(function(res) {
				var $item,
					hasSuccess = res.isSuccess === true;
				// AJAX成功后，返回数据，添加一行
				if (hasSuccess) {
					$item = $.tmpl(that.template, res).hide().appendTo(that.$el.find("tbody")).fadeIn();
					that.$el.trigger("addType", { data: res, $item: $item });
				}
				Ui.tip(res.msg, hasSuccess ? "" : 'danger');
				that.$el.find("tfoot input").val("");
			});
		}
	},
	/**
	 * 移除汇报类型
	 * @method removeType
	 * @param  {String} id 传入汇报类型的ID
	 */
	removeType: function(id){
		var that = this;

		Ui.confirm(Ibos.l('RP.SURE_DEL_REPORT_TYPE'), function(){
			var param = {typeid : id};
			ReportType.op.removeType(param).done(function(res) {
				var hasSuccess = res.isSuccess === true;
				// AJAX成功后，移除一行
				if (hasSuccess) {
					var $item = that.$el.find("tr[data-id='" + id + "']").fadeOut(function(){
						$(this).remove();	
					});
					that.$el.trigger("removeType", {
						id: id,
						$item: $item
					});
				}
				Ui.tip(res.msg, hasSuccess ? 'success' : "danger");	
			});
			
		});
	},
	/**
	 * 进入编辑状态
	 * @method editType
	 * @param  {String} id 传入汇报类型的ID
	 */
	editType: function(id){
		var $row = this.$el.find("tr[data-id='" + id + "']");
		var data = { typeid: id };

		$row.find("[data-name]").each(function(){
			data[$.attr(this, "data-name")] = $.attr(this, "data-value");
		});

		// 将原本的节点备份并替换成编辑状态
		var $newRow = $.tmpl("rp_type_edit_tpl", data);
		$newRow.data("oldRow", $row);

		$row.replaceWith($newRow);
	},
	/**
	 * 更新类型数据
	 * @method updateType
	 * @param  {String} id 传入汇报类型的ID
	 */
	updateType: function(id){
		var that = this;
		var $row = this.$el.find("tr[data-id='" + id + "']");
		var data = U.serializedToObject($row.find("select, input").serializeArray());

		if(this.validTypeData(data, $row)) {
			var param = {
					typeid: id,
					typeData: data
				};
			ReportType.op.updateType(param).done(function(res) {
				// AJAX成功后，返回数据，添加一行
				var hasSuccess = res.isSuccess === true;
				if (hasSuccess) {
					var $item = $.tmpl(that.template, res).hide().replaceAll($row).fadeIn();
					that.$el.trigger("updateType", { id: id, data: res, $item: $item });
				}
				Ui.tip(data.msg, hasSuccess ? "" : "danger");
			});
		}
	},
	/**
	 * 取消编辑
	 * @method cancelEdit
	 * @param  {String} id 传入汇报类型的ID
	 */
	cancelEdit: function(id) {
		var $row = this.$el.find("tr[data-id='" + id + "']");
		var $oldRow = $row.data("oldRow");
		
		if($oldRow) {
			$row.removeData("oldRow");
			$row.replaceWith($oldRow);
		}
	}
};

$(function(){
	function initTypeTable(){
		if(!ReportType.typeTableInited) {
			ReportType.typeTable.init();
			var sideTypeList = new Ibos.CmList("#rp_type_aside_list", {
				tpl: "rp_type_sidebar_tpl"
			});

			ReportType.typeTable.$el.on({
				"addType": function(evt, evtData){
					sideTypeList.addItem(evtData.data);
				},
				"updateType": function(evt, evtData){
					sideTypeList.updateItem(evtData.id, evtData.data);
				},
				"removeType": function(evt, evtData){
					sideTypeList.removeItem(evtData.id);
				}
			});

			ReportType.typeTableInited = true;
		}
	}

	// 汇报类型设置
	$("#rp_type_setup").on("click", function() {
		Ui.dialog({
			title: Ibos.l('RP.REPORT_TYPE_SETTING'),
			id: "report_type_dialog",
			content: Dom.byId("d_report_type"),
			init: initTypeTable,
			ok: false,
			cancel: true,
			cancelVal: Ibos.l("CLOSE"),
			padding: 0
		});
	});
});
