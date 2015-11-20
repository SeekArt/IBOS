/**
 * Assignment/default/list
 * 分派任务列表
 * @version  $Id$
 */

var AssignmentList = {
	/**
	 * 初始化页面
	 * @method initPage
	 */
	initListPage : {
		init : function(){
			//初始化发布框字符计数
			this.publishInput();
			//初始化事件选择器
			this.timeSelector();
			//初始化用户信息
			this.userData();

			//自动焦点输入框
			$("#am_publish_input").focus();

			// 初始化 tooltip
			$(".om-am-warning, .om-am-clock").tooltip();
		},
		/**
		 * 初始化发布框字符计数
		 * @method publishInput
		 */
		publishInput : function(){
			// 初始化发布框字符计数
			$("#am_publish_input").charCount({
				display: "am_publish_charcount",
				max: Assignment.ASSIGN_MAXCHAR,
				template: U.lang("ASM.CHARCOUNT_TPL"),
				warningTemplate: U.lang("ASM.CHARCOUNT_WARNING_TPL")
			}).on({
				"blur": function(e) {
					$(this).parent().toggleClass("has-value", $.trim(this.value) !== "");
				},
				"countchange": function(e, data){
					// 当字符超出规定时，改变样式
					$(this).parent().toggleClass("has-error", data.remnant < 0);
				}
			});
		},
		/**
		 * 初始化事件选择器
		 * @method timeSelector
		 */
		timeSelector : function(){
			// 初始化时间选择器
			$("#am_starttime").datepicker({
				target: "am_endtime",
				format: "yyyy-mm-dd hh:ii",
				pickTime: true,
				pickSeconds: false
			});
			// 设置初始时间为当前时间
			$("#am_starttime").datepicker("setLocalDate", new Date());
			// 设置初始时间为三天后
			$("#am_endtime").datepicker("setStartDate", new Date()).datepicker("setLocalDate", new Date(+new Date + 259200000));

			// 快捷选择结束时间
			$("#am_bar_endtime").datepicker({
				format: "yyyy-mm-dd hh:ii",
				pickTime: true,
				pickSeconds: false
			}).datepicker("setStartDate", new Date).datepicker("setLocalDate", new Date(+new Date + 259200000));
		},
		/**
		 * 初始化用户信息
		 * @method userData
		 */
		userData : function(){
			// 初始化人员选择
			var userData = Ibos.data.get("user");
			// 负责人为单选
			$("#am_charge, #am_bar_charge").userSelect({
				data: userData,
				type: "user",
				maximumSelectionSize: 1,
				placeholder: U.lang("ASM.CHARGER"),
				clearable: false
			});
			// 相互同步负责人
			$("#am_charge").on("uschange", function(evt, data){
				$("#am_bar_charge").userSelect("setValue", data.val, true);
			});
			$("#am_bar_charge").on("uschange", function(evt, data){
				$("#am_charge").userSelect("setValue", data.val, true);
			});

			// 同步负责人至简洁视图

			// 参与人人员选择
			$("#am_participant").userSelect({
				data: userData,
				type: "user",
				placeholder: U.lang("ASM.PARTICIPANT")
			});

			var template = "<strong><%=count%></strong>/<strong><%=maxcount%></strong>";
			// 任务说明计数器
			$("#am_description").charCount({
				display: "am_description_charcount",
				template: template,
				warningTemplate: template,
				countdown: false
			});
		}
	},
	/**
	 * 发布模式(简易/高级)的切换
	 * @method publishToggle
	 */
	publishToggle : function(){
		// 切换发布模式（简易、高级）
		$(".am-publish-toggle").on("click", function(){
			var $box = $(this).closest(".am-publish-box");
			// 展开
			if(!$box.hasClass("open")) {
				$box.removeClass("shut").addClass("open");
				$box.find(".am-publish-dt").slideDown(200);
			// 收起
			} else {
				$box.find(".am-publish-dt").slideUp(200, function(){
					$box.removeClass("open").addClass("shut");
				});
			}
		});

		// 从高级视图同步时间至简洁视图
		var syncDateToSimple = function(val) {
			$("#am_bar_endtime_input").val(val);
		};
		var syncDateToAdvanced = function(val){
			$("#am_endtime_input").val(val);
		};
		$("#am_endtime").on("hide", function(evt, data){
			syncDateToSimple($(this).data("date"));
		});
		$("#am_endtime_input").on("change", function(){
			syncDateToSimple(this.value);
		});
		// 从简洁视图同步时间至高级视图
		$("#am_bar_endtime").on("hide", function(evt, data){
			syncDateToAdvanced($(this).data("date"));
		});
		$("#am_bar_endtime_input").on("change", function(){
			syncDateToAdvanced(this.value);
		});

	},
	/**
	 * 将有内容的分类置顶
	 * @method putToTop
	 */
	putToTop : function(){
		var $items = $(".am-block"),
			$target = $("[data-node-type='taskView']");
		$.each($items, function(index, val) {
			var $this = $(this),
				subItemLength = $this.find("table>tbody>tr").length;

			subItemLength && $this.prependTo($target);
		});
	}
};

$(function(){
	//初始化页面
	AssignmentList.initListPage.init();

	//发布模式(简易/高级)的切换
	AssignmentList.publishToggle();

	//将有内容的分类置顶
	AssignmentList.putToTop();

	// 按 enter 键新增任务
	$("#am_publish_input, #am_endtime_input").keydown(function(e){
		(e.which === 13) && Assignment.addTask(this.form);
	});


	// 附件上传功能
	Ibos.upload.attach({
		post_params: { module: 'assignment' },
		button_placeholder_id: 'am_att_upload',
		button_image_url: '',
		custom_settings: {
			containerId: 'am_att_list',
			inputId: 'attachmentid'
		}
	});


	Ibos.evt.add({
		// 在简洁视图选择负责人
		"updateCharge": function(param, elem){
			var userselectIns = $("#am_charge").data("userSelect");
			userselectIns.selectBox.show();
			// 重新定位
			userselectIns.selectBox.$element.position({
				of: elem,
				my: "center+92 bottom",
				at: "center top-30"
			});
		},

		// 在简洁视图选择结束时间
		"updateEndtime": function(param, elem){
			var datepickerIns = $("#am_endtime").data("datetimepicker");
			datepickerIns.show();
			// 重新定位
			datepickerIns.widget.position({
				of: elem,
				my: "center+15 bottom",
				at: "center top-30"
			});
		},

		// 发布任务
		"addTask": function(param, elem){
			Assignment.addTask(elem.form);
		}
	});
});

