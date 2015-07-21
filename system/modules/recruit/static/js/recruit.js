/**
 * recruit.js
 * 招聘管理模块JS
 * IBOS
 * @author		inaki
 * @version		$Id: recruit.js 4154 2014-09-20 08:12:13Z gzljj $
 */

var Recruit = {
	$contactDialogForm: $("#contact_dialog_form"),
	$interviewDialogForm: $("#interview_dialog_form"),
	$bgcheckDialogForm: $("#bgcheck_dialog_form"),
	
	_setData: function(data, $ctx) {
		var $elem, instance;
		for(var name in data){
			if(data.hasOwnProperty(name)){
				$elem = $("[name='" + name + "']", $ctx);
				instance = $elem.data("userSelect");
				if(instance){
					instance.setValue(data[name]);
				} else {
					$elem.val(data[name]);
				}
			}
		}
	},

	// 单项操作
	singleHandler: function(url, param, callback){
		if(url){
			$.post(url, param, function(res){
				if(res.isSuccess === 1) {
					Ui.tip(res.msg);
					callback && callback(res);
				} else {
					Ui.tip(res.msg, 'danger');
				}
			}, "json");
		}
	},
	
	// 多项操作
	multiHandler: function(url, param, msg, callback){
		if(url){
			Ui.confirm(msg, function(){
				$.post(url, param, function(res){
					if(res.isSuccess === 1) {
						Ui.tip(res.msg);
						callback && callback(res);
					} else {
						Ui.tip(res.msg, "danger");
					}
				}, "json");
			});
		}
	},
	
	//删除多个单个或多个简历
	deleteResumes: function(ids, callback){
		if(ids){
			this.multiHandler(Ibos.app.url("recruit/resume/del"), {resumeids: ids}, Ibos.l('REC.DELETE_RESUMES_CONFIRM'), callback);
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), "warning");
		}
	},
	
	// 导出单个联系记录
	exportContact: function(ids){
		if(ids){
			window.location = Ibos.app.url("recruit/contact/export", { contactids: ids });
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), "warning");
		}
	},
	
	// 删除单个联系记录
	deleteContact: function(id, callback){
		id &&
		this.singleHandler(Ibos.app.url("recruit/contact/del"), { contactids: id }, callback);
	},
	
	//删除多个联系记录
	deleteContacts: function(ids, callback){
		if(ids){
			this.multiHandler(Ibos.app.url("recruit/contact/del"), {contactids: ids}, Ibos.l('REC.DETELE_CONTACTS_CONFIRM'), callback);
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), "warning" );
		}
	},
	
	_updateContact: function(id, data, callback){
		if(id) {
			$.post(Ibos.app.url("recruit/contact/edit", { op: "update" }), $.extend({ contactid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip(Ibos.l('CM.MODIFY_SUCCEED'));
				} else {
					Ui.tip(Ibos.l('CM.MODIFY_FAILED'), 'danger');
				}
			}, "json");
		}
	},
	
	// 编辑联系记录
	editContact: function(id, callback){
		var that = this;
		$('#r_fullname').hide(); ///
		if(id){
			var dialog = Ui.dialog({
				id: "d_contact",
				title: Ibos.l('REC.EDIT_CONTACT'),
				ok: function(){
					var datas = that.$contactDialogForm.serializeArray(),
						data = U.serializedToObject(datas);
					that._updateContact(id, data, callback);
				},
				cancel: true
			});
			$.post(Ibos.app.url("recruit/contact/edit", { op: "getEditData" }), { contactid: id }, function(res){
				that._setData(res, that.$contactDialogForm);
				dialog.content(Dom.byId("contact_dialog"));
				// 联系时间选择器
				$("#contact_time").datepicker();

			}, "json");
		}
	},
	
	_saveContact: function(data, callback){
		if(data){
			$.post(Ibos.app.url("recruit/contact/add"), data, function(res){
				callback && callback(res);
			});
		}
	},
	
	//添加联系记录
	addContact: function(param, callback){
		var that = this;
		$('#r_fullname').show(); ///
		that.$contactDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_contact",
			title: Ibos.l('REC.ADD_CONTACT'),
			content: Dom.byId('contact_dialog'),
			zIndex: 2001,
			init: function(){
				// 联系时间选择器
				$("#contact_time").datepicker();
			},
			ok: function() {
				var datas = that.$contactDialogForm.serializeArray(),
					data = $.extend({}, param, U.serializedToObject(datas));
				that._saveContact(data, callback);
			},
			cancel: true
		});
	},


	_saveInterview: function(data, callback){
		if(data){
			$.post(Ibos.app.url("recruit/interview/add"), data, function(res){
				callback && callback(res);
			});
		}
	},
	
	// 增加面试记录
	addInterview: function(param, callback){
		var that = this;
		$('#r_fullname').show(); ///
		that.$interviewDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_interview",
			title: Ibos.l('REC.ADD_INTERVIEW'),
			content: Dom.byId('interview_dialog'),
			width: 500,
			ok: function() {
				var datas = that.$interviewDialogForm.serializeArray(),
					data = $.extend({}, param, U.serializedToObject(datas));
				that._saveInterview(data, callback);
			},
			cancel: true
		});
	},
	
	// 删除一条面试记录
	deleteInterview: function(id, callback){
		id && 
		this.singleHandler(Ibos.app.url("recruit/interview/del"), { interviewids: id }, callback);		
	},
	
	// 删除多条面试记录
	deleteInterviews: function(ids, callback){
		if(ids) {
			this.multiHandler(Ibos.app.url("recruit/interview/del"), { interviewids: ids }, Ibos.l('REC.DETELE_INTERVIEWS_CONFIRM'), callback);		
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'),  'warning');
		}
	},
	
	_updateInterview: function(id, data, callback){
		if(id) {
			$.post(Ibos.app.url("recruit/interview/edit", { op: "update" }), $.extend({ interviewid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip('MODIFY_SUCCEED')
				} else {
					Ui.tip('MODIFY_FAILED', 'danger')
				}
			}, "json");
		}
	},
	
	// 编辑面试记录
	editInterview: function(id, callback){
		var that = this;
		$('#r_fullname').hide(); //
		if(id){
			$.post(Ibos.app.url("recruit/interview/edit", { op: "getEditData" }), { interviewid: id }, function(res){
				
				that._setData(res, that.$interviewDialogForm);
				Ui.dialog({
					id: "d_interview",
					title: Ibos.l('REC.EDIT_INTERVIEW'),
					content: Dom.byId("interview_dialog"), 
					ok: function(){
						var datas = that.$interviewDialogForm.serializeArray(),
							data = U.serializedToObject(datas);
						that._updateInterview(id, data, callback);
					},
					cancel: true
				});
			}, "json");
		}
	},
	
	// 导出面试记录
	exportInterview: function(ids){
		if(ids){
			window.location = Ibos.app.url("recruit/interview/export", { interviews: ids });
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},

	_saveBgcheck: function(data, callback){
		if(data){
			$.post(Ibos.app.url("recruit/bgchecks/add"), data, function(res){
				callback && callback(res);
			});
		}
	},
	
	// 增加背景调查记录
	addBgcheck: function(param, callback){
		var that = this;
		$('#r_fullname').show();///
		that.$bgcheckDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_bgcheck",
			title: Ibos.l('REC.ADD_BGCHECK'),
			content: Dom.byId('bgcheck_dialog'),
			init: function(){
				// 时间选择
				$("#entrytime_datepicker").datepicker({ target: $("#quittime_datepicker") });
			},
			ok: function() {
				var datas = that.$bgcheckDialogForm.serializeArray(),
					data = $.extend(U.serializedToObject(datas),{
						fullname: $("#fullname").val()
					}, param);
				that._saveBgcheck(data, callback);
			},
			width: 500,
			cancel: true
		});
	},
	
	// 删除一条背景调查记录
	deleteBgcheck: function(id, callback){
		id && 
		this.singleHandler(Ibos.app.url("recruit/bgchecks/del"), { checkids: id }, callback);		
	},
	
	// 删除多条背景调查记录
	deleteBgchecks: function(ids, callback){
		if(ids) {
			this.multiHandler(Ibos.app.url("recruit/bgchecks/del"), { checkids: ids }, Ibos.l('REC.DETELE_INTERVIEWS_CONFIRM'), callback);		
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},
	
	_updateBgcheck: function(id, data, callback){
		if(id) {
			$.post(Ibos.app.url("recruit/bgchecks/edit", { op: "update" }), $.extend({ checkid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip('@CM.MODIFY_SUCCEED')
				} else {
					Ui.tip('@CM.MODIFY_FAILED', 'danger')
				}
			}, "json");
		}
	},
	
	// 编辑背景调查记录
	editBgcheck: function(id, callback){
		var that = this;
		$('#r_fullname').hide();///
		if(id){
			var dialog = Ui.dialog({
				id: "d_bgcheck",
				title: Ibos.l('REC.EDIT_BGCHECK'),
				ok: function(){
					var datas = that.$bgcheckDialogForm.serializeArray(),
						data = U.serializedToObject(datas);
					that._updateBgcheck(id, data, callback);
				},
				width: 500,
				cancel: true
			});

			$.post(Ibos.app.url("recruit/bgchecks/edit", { op: "getEditData" }), { checkid: id }, function(res){
				
				that._setData(res, that.$bgcheckDialogForm);
				dialog.content(Dom.byId("bgcheck_dialog"));
				$("#entrytime_datepicker").datepicker({ target: $("#quittime_datepicker") });

			}, "json");
		}
	},
	
	// 导出背景调查记录
	exportBgcheck: function(ids){
		if(ids){
			window.location = Ibos.app.url("recruit/bgchecks/export", { checkids: ids });
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},
	// 发送邮件
	sendMail: function(ids){
		window.location.href = Ibos.app.url("recruit/resume/sendEmail", { resumeids: ids });
	},

	// 更改状态
	changeResumeStatus: function(status){
		var $ckbs = U.getChecked("resume[]"),
			ids = $ckbs.map(function(){
				return this.value;
			}).get().join(",");
		if(ids !== "") {
			$.post(Ibos.app.url("recruit/resume/edit", { op: "status" }), {resumeid: ids, status: status } , function(res){
				if(res.isSuccess !== 0){
					$ckbs.each(function(){
						$(this).parent().parent().parent().find("td:eq(7)").text(res.showStatus);
					});
					Ui.tip(res.msg);
				}
			}, "json");
		} else {
			Ui.tip(Ibos.l('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	}
};


$(function(){
	Ibos.evt.add({
		// 展开所有详细栏目
		"expandAll": function(param, elem){
			var ctx = $.attr(elem, "data-expand-all");
			$("#" + ctx).find("[data-expand-target]").show();
			$(elem).parent().hide();
		},

		//展开栏目各个详细栏目
		"expandItem": function(param, elem){
			var targetName = $.attr(elem, "data-expand");
			$("div[data-expand-target='" + targetName + "']").show();
			$(elem).hide().next().hide();
		},

		//显示/隐藏查看简历页面个人详细信息
		"togglePersonalDetail": function(param, elem){
			$("#rsm_psn_table").toggleClass("active");
			$(elem).toggleClass("active");
		},

		// 删除单个简历
		"deleteResume": function(param, elem){
			var id = $.attr(elem, "data-id");
			if(id){
				Ui.confirm(Ibos.l('DELETE_RESUME_CONFIRM'), function(){
					Recruit.singleHandler(Ibos.app.url("recruit/resume/del"), { resumeid: id }, function(res){
						$elem.parent().parent().remove();
					});
				});
			}
		},

		//删除多个简历
		"deleteResumes": function(){
			var $ckbs = U.getChecked("resume[]"),
				ids = $ckbs.map(function(){
					return this.value;
				}).get().join(",");

			Recruit.deleteResumes(ids, function(ids){
				$ckbs.each(function(){
					$(this).parent().parent().parent().remove();
				});
			});
		},

		//发送邮件
		"sendMail": function(){
			var ids = U.getCheckedValue("resume[]");
			if(ids !== "") {
				Recruit.sendMail(ids);
			} else {
				Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		},

		// 切换简历标记/取消标记状态
		"toggleResumeMark": function(param, elem){
			var id = $.attr(elem, "data-id"),
				flag = $.attr(elem, "data-flag"),
				$elem = $(elem);

			Recruit.singleHandler(Ibos.app.url("recruit/resume/edit", { op: "mark" }), { resumeid: id, flag: flag }, function(res){
				if(flag == "1"){
					$elem.attr({"data-flag": "0", "title": Ibos.l("REC.MARKED")});
					$elem.find("i").attr("class", "o-rsm-mark");
				} else {
					$elem.attr({"data-flag": "1", "title": Ibos.l("REC.UNMARKED")});
					$elem.find("i").attr("class", "o-rsm-unmark");
				}
			});
		},

		//添加联系记录
		"addContact": function() {
			Recruit.addContact({
				fullname: $("#fullname").val()
			}, function(res){
				if (res && res.contactid) {
					$temp = $.tmpl('contact_template', res);
					$temp.find("input[type='checkbox']").label();
					$('#contact_tbody').prepend($temp);
					$("#no_contact_tip").hide();
					Ui.tip(Ibos.l('REC.ADD_SUCCESS'));
				} else {
					Ui.tip(res.msg, 'danger');
				}
			});
		},

		//编辑联系记录
		"editContact": function(param, elem) {
			var $elem = $(elem);
			Recruit.editContact($elem.attr("data-id"), function(res){
				var $row;
				if (res && res.contactid) {
					$row = $.tmpl('contact_template', res);
					$row.find("input[type='checkbox']").label();
					$elem.parent().parent().replaceWith($row);
				}
			});
		},

		//删除单个联系记录
		"deleteContact": function(param, elem) {
			var $elem = $(elem);
			var contactids = $elem.attr("data-id");
			Recruit.deleteContact(contactids, function(){
				$elem.parent().parent().remove();
			});
		},

		//删除多个联系记录
		"deleteContacts": function(){
			var $ckbs = U.getChecked("contact[]"),
				ids = $ckbs.map(function(){
					return this.value;
				}).get().join(",");

			Recruit.deleteContacts(ids, function(ids){
				$ckbs.each(function(){
					$(this).parent().parent().parent().remove();
				});
			});
		},

		//导出联系记录
		"exportContact": function(){
			var ids = U.getCheckedValue("contact[]");
			Recruit.exportContact(ids);
		},

		//增加面试记录
		"addInterview": function(){
			Recruit.addInterview({
				fullname: Ibos.app.g("fullname")
			}, function(res){
				if (res && res.interviewid) {
					$row = $.tmpl('interview_template', res);                       
					$row.find("input[type='checkbox']").label();
					$('#interview_tbody').prepend($row);
					$("#no_interview_tip").hide();
					Ui.tip(Ibos.l('REC.ADD_SUCCESS'));
				} else {
					Ui.tip(res.msg, 'danger');
				}
			});
		},

		//删除单条面试记录
		"deleteInterview": function(param, elem) {
			var $elem = $(elem);
			var interviewid = $elem.attr("data-id");
			Recruit.deleteInterview(interviewid, function(){
				$elem.parent().parent().remove();
			});
		},

		//删除多条面试记录
		"deleteInterviews": function() {
			var $ckbs = U.getChecked("interview[]"),
				ids = $ckbs.map(function(){
					return this.value;
				}).get().join(",");

			Recruit.deleteInterviews(ids, function(ids){
				$ckbs.each(function(){
					$(this).parent().parent().parent().remove();
				});
			});
		},

		//修改面试记录
		"editInterview": function(param, elem) {
			var $elem = $(elem);
			Recruit.editInterview($elem.attr("data-id"), function(res){
				$row = $.tmpl('interview_template', res);
				$row.find("input[type='checkbox']").label();
				$elem.parent().parent().replaceWith($row);
			});
		},

		//导出面试记录
		"exportInterview": function(){
			var ids = U.getCheckedValue("interview[]");
			Recruit.exportInterview(ids);
		},

		//增加背景调查记录
		"addBgcheck": function() {
			Recruit.addBgcheck(null, function(res){
				if (res && res.checkid) {
					$row = $.tmpl('bgchecks_template', res);                       
					$row.find("input[type='checkbox']").label();
					$('#bgchecks_tbody').prepend($row);
					$("#no_bgchecks_tip").hide();
					Ui.tip(Ibos.l('REC.ADD_SUCCESS'));
				} else {
					Ui.tip(res.msg, 'danger');
				}
			});
		},

		//编辑背景记录
		"editBgcheck": function(param, elem) {
			var $elem = $(elem);
			Recruit.editBgcheck($elem.attr("data-id"), function(res){
				$row = $.tmpl('bgchecks_template', res);
				$row.find("input[type='checkbox']").label();
				$elem.parent().parent().replaceWith($row);
			});
		},

		//删除背景记录
		"deleteBgcheck": function(param, elem) {
			var $elem = $(elem);
			Recruit.deleteBgcheck($elem.attr("data-id"), function(){
				$elem.parent().parent().remove();
			});
		},

		//删除多条背景背景调查数据
		"deleteBgchecks": function() {
			var $ckbs = U.getChecked("bgcheck[]"),
				ids = $ckbs.map(function(){
					return this.value;
				}).get().join(",");

			Recruit.deleteBgchecks(ids, function(){
				$ckbs.each(function(){
					$(this).parent().parent().parent().remove();
				});
			});
		},

		//导出背景记录
		"exportBgcheck": function(){
			var ids = U.getCheckedValue("bgcheck[]");
			Recruit.exportBgcheck(ids);
		},

		// 状态变更
		// 待安排
		moveToArranged: function(){
			Recruit.changeResumeStatus("4");
		},
		// 面试
		moveToInterview:function(){
			Recruit.changeResumeStatus("1");
		},
		// 录用
		moveToEmploy: function(){
			Recruit.changeResumeStatus("2");
		},
		// 淘汰
		moveToEliminate: function(){
			Recruit.changeResumeStatus("5");
		}
	})
});