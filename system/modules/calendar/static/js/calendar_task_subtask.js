/**
 * 日程-待办-下属待办
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	var $todoList = $("#todo_list"),
		$todoAdd = $("#todo_add"),
		subUid = Ibos.app.g("subUid"),

		todoList = new TodoList($todoList, {
			mark: function(id, mark) {
				_sendRequest(Ibos.app.url("calendar/task/edit", {
					op: "mark",
					uid: subUid
				}), {
					id: id,
					mark: (mark ? 1 : 0)
				});
			},
			complete: function(id, complete) {
				_sendRequest(Ibos.app.url("calendar/task/edit", {
					op: "complete",
					uid: subUid
				}), {
					id: id,
					complete: (complete ? 1 : 0)
				});
			},
			stop: function(data) {
				data && _sendRequest(Ibos.app.url("calendar/task/edit", {
					op: "sort"
				}), data);
			},
			add: function(data) {
				_sendRequest(Ibos.app.url("calendar/task/add", {
					uid: subUid
				}), {
					id: data.id,
					pid: data.pid,
					text: data.text
				});
				$("#no_data_tip").hide()
			},
			remove: function(id) { //删除任务
				_sendRequest(Ibos.app.url("calendar/task/del", {
					uid: subUid
				}), {
					id: id
				});
			},
			save: function(id, text) { //保存(编辑或者添加最后步骤时都用到)
				_sendRequest(Ibos.app.url("calendar/task/edit", {
					op: "save",
					uid: subUid
				}), {
					id: id,
					text: text
				});
			},
			date: function(id, date) { //完成时间
				_sendRequest(Ibos.app.url("calendar/task/edit", {
					op: "date",
					uid: subUid
				}), {
					id: id,
					date: (+date) / 1000
				});
			},
			disabled: Ibos.app.g('allowEditTask') == 1 ? false : true
		});

	/**
	 * 操作请求
	 * @param {str} url 请求路径
	 * @param {sbj} param 请求参数
	 * @returns {isSuccess=true} 返回操作成功
	 */
	function _sendRequest(url, param, callback) {
		param.formhash = Ibos.app.g('formHash');

		$.post(url, param, function(res) {
			callback && callback(res)
		}, "json");
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

	//拿到活动的uid，展开侧边栏
	var supUid = Ibos.app.g("supUid");
	if (supUid !== 0) {
		var $sub = $('.g-sub[data-uid=' + supUid + ']');
		$sub.trigger("click");
		$sub.parent().addClass('active');
	}
});