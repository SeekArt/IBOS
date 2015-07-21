/**
 * 日程-待办-首页
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	var $todoList = $("#todo_list"),
		$todoAdd = $("#todo_add");

	var todoList = new TodoList($todoList, {
		mark: function(id, mark) {
			_sendRequest(Ibos.app.url("calendar/task/edit", {
				op: "mark"
			}), {
				id: id,
				mark: (mark ? 1 : 0)
			});
		},

		complete: function(id, complete) {
			_sendRequest(Ibos.app.url("calendar/task/edit", {
				op: "complete"
			}), {
				id: id,
				complete: (complete ? 1 : 0)
			});
		},

		// start: function() {},
		// sort: function() {},
		// change: function() {},
	
		stop: function(data) { //拖拽过程中
			data && _sendRequest(Ibos.app.url("calendar/task/edit", {
				op: sort
			}), data);
		},

		add: function(data) {
			_sendRequest(Ibos.app.url("calendar/task/add"), {
				id: data.id,
				pid: data.pid,
				text: data.text
			});
			$("#no_data_tip").hide()
		},

		remove: function(id) { //删除任务
			_sendRequest(Ibos.app.url("calendar/task/del"), {
				id: id
			});
		},

		// edit: function() {},
		save: function(id, text) { //保存(编辑或者添加最后步骤时都用到)
			_sendRequest(Ibos.app.url("calendar/task/edit", {
				op: "save"
			}), {
				id: id,
				text: text
			});
		},

		date: function(id, date) { //完成时间
			_sendRequest(Ibos.app.url("calendar/task/edit", {
				op: "date"
			}), {
				id: id,
				date: (+date) / 1000
			});
		}
	});

	/**
	 * 操作请求
	 * @param {String} url 请求路径
	 * @param {Object} param 请求参数
	 * @return
	 */
	function _sendRequest(url, param, callback) {
		param.formhash = Ibos.app.g('formHash');
		$.post(url, param, callback, "json");
	}

	// 新增一条Todo
	$todoAdd.on("keydown", function(evt) {
		var $add;
		if (evt.which === 13) {
			$add = $(this);
			todoList.addItem({
				text: $add.val()
			});
			$add.val("");
		}
	});

	// 初始化数据
	var taskData = Ibos.app.g("taskData");

	if (taskData && taskData.length) {
		$.each(taskData, function(i, d) {
			if (d.pid == '') {
				delete d.pid;
			}
		});
		todoList.set(taskData);
	} else {
		$("#no_data_tip").show();
	}

	//搜索
	$("#mn_search").search();

	// 新手引导
	setTimeout(function() {
		Ibos.guide("cal_task_index", function() {
			var guideData = [{
				element: "#todo_add",
				intro: Ibos.l("CAL.INTRO.TASK_ADD")
			}];

			var dragSelector = "#todo_list .todo-item .o-todo-drag",
				dateSelector = "#todo_list .todo-item .o-date";

			if ($(dragSelector).length) {
				guideData.push({
					element: dragSelector,
					intro: Ibos.l("CAL.INTRO.TASK_DRAG")
				})
			}

			if ($(dateSelector).length) {
				guideData.push({
					element: dateSelector,
					intro: Ibos.l("CAL.INTRO.IMPORT_TO_SCH"),
					position: "left"
				});
			}

			$("#todo_list .todo-item").eq(0).addClass("introing");

			return guideData;

		}, function(){
			$("#todo_list .todo-item").eq(0).removeClass("introing");
		});

	}, 1000);
});