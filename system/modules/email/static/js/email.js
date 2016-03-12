/**
 * email.js
 * 邮件模块JS
 * IBOS
 * Email
 * @author		inaki
 * @version		$Id$
 */

var Email = {
	op: {
        /**
         * 删除日志
         * @method deldiary
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		addWebMailBox: function(params) {
			var url = Ibos.app.url("email/web/add");
				param = $.extend( {}, params, { inajax : 1 } );
			return $.post(url, param, $.noop, 'json')
					.done(function(res) {
						$(Email).trigger('addWebMailBox', {
							res: res,
							params: params
						});
					});
		},
        /**
         * 获取计数器
         * @method getRefreshCounter
         * @return {Object}       返回deffered对象
         */
		getRefreshCounter : function(){
			var url = Ibos.app.url('email/api/getCount');
			return $.post(url, $.noop);
		},
		/**
		 * 获取访问
		 * @method postAccess
		 * @param  {String} url   访问的地址
		 * @param  {Object} param 传入JSON格式数据
		 * @param  {String} type  返回的内容格式
		 * @return {Object}       返回deffered对象
		 */
		postAccess : function(url, param, type){
			return type ? $.post(url, param, $.noop, type) : $.post(url, param, $.noop);
		},
		/**
		 * 获取访问
		 * @method getAccess
		 * @param  {String} url   访问的地址
		 * @param  {Object} param 传入JSON格式数据
		 * @param  {String} type  返回的内容格式
		 * @return {Object}       返回deffered对象
		 */
		getAccess : function(url, param, type){
			return type ? $.post(url, param, $.noop, type) : $.post(url, param, $.noop);
		}
	},
	/**
	 * 获取选择邮箱的ID
	 * @method getCheckedId
	 * @return {String} 返回选择的邮箱IDs
	 */
	getCheckedId: function() {
		return U.getCheckedValue("email");
	},
	/**
	 * 邮箱checkbox样式优化
	 * @method select
	 * @param  {Object} selector 传入jquery节点对象
	 */
	select: function(selector) {
		var $cks = $("input[type='checkbox'][name='email']");
		$cks.each(function() {
			var $elem = $(this);
			$elem.prop('checked', $elem.is(selector)).label('refresh');
		});
	},
	/**
	 * 刷新计数器
	 * @method refreshCounter
	 */
	refreshCounter: function() {
		Email.op.getRefreshCounter().done(function(res) {
			for (var prop in res) {
				if (res.hasOwnProperty(prop)) {
					var $ele = $("[data-count='" + prop + "']");
					res[prop] === 0 ? $ele.hide() : $ele.html(res[prop]).show();
				}
			}
		});
	},
	/**
	 * 删除数据
	 * @method removeRows
	 * @param  {String} ids 传入要删除数据ID
	 */
	removeRows: function(ids) {
		var arr = ids.split(',');
		for (var i = 0, len = arr.length; i < len; i++) {
			$('#list_tr_' + arr[i]).remove();
		}
	},
	/**
	 * 访问地址
	 * @method access
	 * @param  {String}   url     访问地址
	 * @param  {Object}   param   传入JSON格式数据
	 * @param  {Function} success 成功后回调函数
	 * @param  {String}   msg     传入confirm内信息
	 */
	access: function(url, param, success, msg) {
		var emailIds = this.getCheckedId();
		var _ajax = function(url, param, success) {
			Email.op.postAccess(url, param).done(function(res) {
				if (res.isSuccess) {
					if (success && $.isFunction(success)) {
						success.call(null, res, emailIds);
					}
					Email.refreshCounter();
					Ui.tip(U.lang("OPERATION_SUCCESS"));
				} else {
					Ui.tip(res.errorMsg, 'danger');
				}
			});
		};
		if (emailIds !== '') {
			param = $.extend({emailids: emailIds}, param);
			if (msg) {
				Ui.confirm(msg, function() {
					_ajax(url, param, success);
				});
			} else {
				_ajax(url, param, success);
			}
		} else {
			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
		}
	},
	/**
	 * 移动到文件夹
	 * @param  {Object} param 传入JSON格式数据
	 */
	moveToFolder : function(param) {
		Email.access(param.url, {fid: param.fid}, function(res, emailIds) {
			Email.removeRows(emailIds);
		});
	}
};
// 自定义文件夹
Email.folderList = {
	$container: $('[data-node-type="folderList"]'),
	_itemTpl: 	'<li data-node-type="folderItem" data-folder-id="<%= fid %>">' +
					'<a href="<%= url %>" title="<%= text %>"><%= text %></a>' +
				'</li>',
	/**
	 * 格式化数据(内部使用)
	 * @method  _formatData
	 * @param  {Object} data 传入JSON格式数据
	 */
	_formatData: function(data){
		return $.extend({ 
			url: '', 
			fid: '', 
			text: ''
		}, data);
	},
	/**
	 * 添加文件夹
	 * @method addItem
	 * @param  {Object} data 传入JSON格式数据
	 */
	addItem: function(data){
		return $.tmpl(this._itemTpl, this._formatData(data)).hide().appendTo(this.$container).fadeIn();
	},
	/**
	 * 删除文件夹
	 * @method removeItem
	 * @param  {String} fid 传入要删除的文件夹ID
	 * @return {Object}     返回jquery节点对象
	 */
	removeItem: function(fid){
		return this.$container.find('[data-node-type="folderItem"][data-folder-id="' + fid + '"]')
					.fadeOut(function(){
						$(this).remove();
					});
	},
	/**
	 * 更新文件夹
	 * @method updateItem
	 * @param  {String} fid  传入要删除的文件夹ID
	 * @param  {Object} data 传入JSON格式数据
	 * @return {Object}      返回jquery节点对象
	 */
	updateItem: function(fid, data){
		return this.$container.find('[data-node-type="folderItem"][data-folder-id="' + fid + '"]')
					.replaceWith( $.tmpl( this._itemTpl, this._formatData(data) ) );
	}
};
/**
 * “移动至”文件夹列表
 * @method moveTargetList
 * @type {Object}
 */
Email.moveTargetList = {
		$container: $('[data-node-type="moveTargetList"]'),
		_itemTpl:  	'<li data-node-type="moveTargetItem" data-id="<%=fid%>">' + 
						'<a href="javascript:;" data-click="moveToFolder" data-param="{&quot;fid&quot;:&quot;<%=fid%>&quot;,&quot;url&quot;: &quot;/?r=email/api/mark&amp;op=move&quot;}"><%=text%></a>' +
					'</li>',
		/**
		 * 格式化数据(内部使用)
		 * @method  _formatData
		 * @param  {Object} data 传入JSON格式数据
		 */
		_formatData: function(data){
			return $.extend({ 
				fid: '', 
				text: ''
			}, data);
		},

		/**
		 * 添加文件夹
		 * @method addItem
		 * @param  {Object} data 传入JSON格式数据
		 */
		addItem: function(data){
			var $items = this.$container.find("li");
			return $.tmpl(this._itemTpl, this._formatData(data)).hide().insertBefore($items.eq(-1)).fadeIn();
		},
		/**
		 * 删除文件夹
		 * @method removeItem
		 * @param  {String} fid 传入要删除的文件夹ID
		 * @return {Object}     返回jquery节点对象
		 */
		removeItem: function(fid){
			return this.$container.find('[data-node-type="moveTargetItem"][data-id="' + fid + '"]').fadeOut(function(){
				$(this).remove();
			});
		},
		/**
		 * 更新文件夹
		 * @method updateItem
		 * @param  {String} fid  传入要删除的文件夹ID
		 * @param  {Object} data 传入JSON格式数据
		 * @return {Object}      返回jquery节点对象
		 */
		updateItem: function(fid, data){
			return this.$container.find('[data-node-type="moveTargetItem"][data-id="' + fid + '"]')
			.replaceWith($.tmpl(this._itemTpl, this._formatData(data)));
		}
	};

(function() {
	// 定时更新未读条数

	var eventHandler = {
		"click": {
			// 切换 抄送、密送、外部收件人显隐状态
			"toggleRec": function(elem, param) {
				var $target = $("#" + param.targetId), $input = $("#" + param.inputId), 
					value = $input.val(),
					isValue = value === "0";

				$input.val( isValue ? "1" : "0" );
				$target[ isValue ? "show" : "hide" ]();
			},
			// 切换发送人详细信息
			"toggleSenderDetail": function(elem, param) {
				var $elem = $(elem),
						$brief = $("#" + param.briefId),
						$detail = $("#" + param.detailId),
						speed = 200;

				if ($(elem).hasClass("active")) {
					$detail.slideUp(speed, function() {
						$brief.show();
						$elem.removeClass("active");
					});
				} else {
					$brief.hide(0, function() {
						$detail.slideDown(speed);
						$elem.addClass("active");
					});
				}
			},
			// 发送快捷回复
			"sendQuickReply": function(elem, param) {
				var $content = $("#" + param.targetId), 
					content = $content.val(),
					url = param.url;
				param = {
					content: content,
					formhash: param.formhash,
					islocal: 1
				};
				if ($.trim(content) === "") {
					Ui.tip(U.lang('EM.INPUT_REPLY'), 'danger');
				} else {
					Email.op.postAccess(url, param).done(function(res) {
						if (res.isSuccess) {
							Ui.tip(U.lang("REPLY_SUCCESS"));
							$content.val('');
						} else {
							Ui.tip(res.errorMsg, 'danger');
						}
					}, "json");
				}
			},
			// 删除一封邮件，需要传入邮件id，及删除操作的Url地址
			"deleteOneEmail": function(elem, param) {
				var msg = U.lang("EM.DELETE_EMAIL_CONFIRM"), 
					data = {}, 
					url = param.url;

				if (param.emailids && url) {
					Email.op.postAccess(url, param).done(function(res) {
						if (res.isSuccess) {
							Ui.tip( U.lang("DELETE_SUCCESS") );
							window.location.href = res.url;
						} else {
							Ui.tip(res.errorMsg, 'danger');
						}
					}, "json");
				} else {
					Ui.tip(U.lang('PARAM_ERROR'), 'danger');
				}
			},
			// 切换是否标记为待办
			"toggleMark": function(elem, param) {
				var $elem = $(elem), 
					toMark = $elem.hasClass("o-unmark"),
					url = param.url;
				param = {ismark: toMark};

				$elem.hide().parent().append($('#img_loading').clone().show());

				Email.op.postAccess(url, param).done(function(res) {
					if (res.isSuccess) {
						$elem.attr({
							'class': (toMark ? 'o-mark' : 'o-unmark')
						}).show().next('img').remove();
						Email.refreshCounter();
					} else {
						Ui.tip(res.errorMsg, 'danger');
					}
				}, "json");
			},
			// 增加外部邮箱
			"addWebMail": function(elem, param) {
				function validate() {
					if ($.trim($("#mal").val()) === "") {
						$("#mal").blink().focus();
						return false;
					}
					if (!U.regex($("#mal").val(), "email")) {
						Ui.tip(U.lang("EM.INCORRECT_EMAIL_ADDRESS"), "warning");
						$("#mal").blink().focus();
						return false;
					}
					if ($.trim($("#pwd").val()) === "") {
						$("#pwd").blink().focus();
						return false;
					}
					return true;
				}

				$.artDialog({
					title: U.lang("EM.ADD_WEB_MAIL"),
					id: "d_new_web_mail",
					padding: "0 0",
					init: function() {
						var api = this,
							url = Ibos.app.url("email/web/add"),
							param = { inajax: 1};
						Email.op.getAccess(url, param).done(function(res) {
							res && api.content(res);
						});
					},
					ok: function() {
						if(!validate()) {
							return false;
						}
						var dialog = this;
						var $form = $("#add_form").waiting(U.lang("EM.BEING_VALIDATED"), "mini", true);

						Email.op.addWebMailBox($form.serializeObject()).done(function(res) {
							$form.stopWaiting();
							// 需要更多信息
							if (typeof res.moreinfo !== "undefined") {
								dialog.content(res.content);
								return false;
							}

							// 添加成功
							var isSuccess = res.isSuccess == 1;
							isSuccess & dialog.close();
							Ui.tip(res.msg, isSuccess ? "" : "danger");
						});

						return false;
					},
					cancel: true
				});
			},
			// 设置默认外部邮箱
			"setDefaultWebMailBox": function(elem, param) {
				var isDefault = $.attr(elem, "data-isDefault");
				// 已经是默认外部邮箱时，直接返回
				if (isDefault === "1") {
					return false;
				}
				if (param.id && param.url) {
					var url = param.url;
						param = { webid: param.id };

					Email.op.postAccess(url, param).done(function(res) {
						if (res.isSuccess) {
							Ui.tip(U.lang("SETUP_SUCCEESS"));
							$("[data-click='setDefaultWebMailBox']").each(function() {
								$(this).attr({
									"data-isDefault": "0",
									"title": U.lang("EM.SET_DEFAULT")
								}).text(U.lang("EM.SET_DEFAULT")).removeClass("active");
							});
							$(elem).text(U.lang("CM.DEFAULT")).attr("title", U.lang("CM.DEFAULT")).addClass("active");
						} else {
							Ui.tip(res.errorMsg, 'danger');
						}
					});
				} else {
					Ui.tip(U.lang("PARAM_ERROR"),'danger');
				}
			},
			// 删除外部邮箱
			"deleteWebMailBox": function(elem, param) {
				var ids = Email.getCheckedId(),
					url = param.url;
					param = {webids: ids};
				Email.access(url, param, function(res, ids) {
					Email.removeRows(ids);
				}, U.lang("EM.DELETE_EMAILBOX_CONFIRM"));
			},
			// 处理回执
			"receipt": function(elem, param) {
				var url = param.url;
				param = {emailids: param.id};

				Email.op.getAccess(url, param).done(function(res) {
					if (res.isSuccess) {
						$(elem).parent().parent().remove();
					} else {
						res.msg && Ui.tip(res.msg, "danger");
					}
				});
			},
			// 接受邮件
			"receiveMail": function(elem, param) {
				var url = param.url;
				$('.page-list').waiting(U.lang("EM.BEING_RECEIVE") + param.name + '...', 'mini', true);

				Email.op.getAccess(url, null).done(function(res) {
					$('.page-list').stopWaiting();
					if (res.isSuccess) {
						alert(U.lang('EM.RECEIVE_SUCCESS_TIP'));
						window.location.reload();
					} else {
						Ui.tip(res.msg, "danger");
					}
				}, 'json');
			},
			//设置所有为已读
			"markReadAll": function(elem, param) {
				var url = param.url;
				Email.op.postAccess(url, null).done(function(res) {
					if (res.isSuccess === true) {
						Ui.tip(U.lang("OPERATION_SUCCESS"));
						window.location.reload();
					} else {
						Ui.tip(res.errorMsg, 'danger');
					}
				});
			},
			//移动至文件夹
			"moveToFolder": function(elem, param) {
				Email.moveToFolder(param);
			},
			// 移动至新建文件夹
			"moveToNewFolder": function(elem, param) {
				var emailIds = Email.getCheckedId();
				if (emailIds !== "") {
					Ui.dialog({
						title: U.lang("EM.NEW_DIR_AND_MOVE"),
						content: '<input type="text" placeholder="' + U.lang("EM.INPUT_DIR_NAME") + '" id="new_folder">',
						id: 'd_myfolder_new_and_move',
						width: 400,
						ok: function() {
							var folderName = $('#new_folder').val();
							if ($.trim(folderName) !== '') {
								var url = param.newUrl,
									moveParam = {name: folderName};
								Email.op.postAccess(url, moveParam).done(function(res) {
									if (res.isSuccess) {
										param.fid = res.fid;
										Email.moveToFolder(param);
									}
								});
							} else {
								Ui.tip(U.lang("EM.INPUT_DIR_NAME"), 'danger');
								return false;
							}
						}
					});
				} else {
					Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
				}
			},
			// 恢复已删除邮件
			"restore": function(elem, param) {
				Email.access(param.url, null, function(res, emailIds) {
					Email.removeRows(emailIds);
				});
			},
			// 删除邮箱
			"del": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					Email.removeRows(ids);
				}, U.lang("EM.DELETE_EMAIL_CONFIRM"));
			},
			// 删除外部邮箱
			"deleteWebMail": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					Email.removeRows(ids);
				}, U.lang("EM.DELETE_WEBEMAIL_CONFIRM"));
			},
			// 回复所有
			'replyAll': function(elem, param) {
				if (param.isSecretUser) {
					Ui.confirm(U.lang("EM.SCRECT_USER_REPLY"), function() {
						window.location.href = param.url;
					});
				} else {
					window.location.href = param.url;
				}
			},
			// 彻底删除
			"erase": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					Email.removeRows(ids);
				}, U.lang("EM.CP_DELETE_EMAIL_CONFIRM"));
			},
			// 彻底删除一条邮件
			"eraseOneEmail": function(elem, param){
				Ui.confirm(U.lang("EM.CP_DELETE_EMAIL_CONFIRM"), function() {
					var url = param.url;
					Email.op.postAccess(url, null).done( function(res) {
						if(res.isSuccess){
							Ui.tip(U.lang("OPERATION_SUCCESS"), 'success');
							window.location.href = Ibos.app.url("email/list/index", {op: "del"});
						} else {
							Ui.tip(res.errorMsg, 'warning');
						}
					});
				});
			},
			// 标为已读
			"markRead": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					var arr = ids.split(",");
					for (var i = 0, len = arr.length; i < len; i++) {
						$('#list_tr_' + arr[i]).children().eq(1).empty();
					}
				});
			},
			// 标为未读
			"markUnread": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					var arr = ids.split(",");
					for (var i = 0, len = arr.length; i < len; i++) {
						$('#list_tr_' + arr[i]).children().eq(1).html('<i class="o-mal-new"></i>');
					}
				});
			},
			// 批量标记为待办
			"mark": function(elem, param) {
				Email.access(param.url, {ismark: 'true'});
			},
			// 批量取消待办
			"unmark": function(elem, param) {
				Email.access(param.url, {ismark: 'false'}, function(res, ids) {
					Email.removeRows(ids);
				});
			},
			// 撤回
			"recall": function(elem, param) {
				Email.access(param.url, null, function(res, ids) {
					Email.removeRows(ids);
				});
			},
			// 显示一条
			"showRow": function(elem, param) {
				$(elem).hide();
				$("#" + param.targetId).show();
			},
			// 反选
			"selectReverse": function() {
				Email.select(":not(:checked)");
			},
			// 附件
			"selectAttach": function() {
				Email.select("[data-attach='1']");
			},
			// 未读
			"selectUnread": function() {
				Email.select("[data-read='0']");
			},
			// 已读
			"selectRead": function() {
				Email.select("[data-read='1']");
			},
			// 新建文件夹
			"setupFolder": function(elem, param) {
				Ui.dialog({
					title: U.lang("EM.MY_FOLDER_SETUP"),
					id: 'd_myfolder_setup',
					padding: "0 0",
					init: function() {
						var api = this,
							url = param.url;
						Email.op.postAccess(url, null).done(function(res) {
							res && api.content(res);
						});
					},
					ok: false,
					cancel: true,
					cancelVal: U.lang('CLOSE')
				});
			},
			// 修改文件夹
			"editFolder": function(elem) {
				var $elem = $(elem), $row = $elem.parent().parent(), $cells = $row.find("td"),
					sort = $cells.eq(0).html(), name = $cells.eq(1).html();
				$row.data({
					"sort": sort,
					"name": name
				});
				$cells.eq(0).html('<input type="text" name="sort" class="input-small" size="2" value="' + (sort || "") + '">');
				$cells.eq(1).html('<input type="text" name="name" class="input-small" value="' + (name || "") + '">');
				$elem.attr('data-click', 'saveFolder').html(U.lang("SAVE")).next().attr('data-click', 'cancelFolderEdit').html(U.lang("CANCEL"));
			},
			// 取消文件夹修改
			'cancelFolderEdit': function(elem) {
				var $elem = $(elem), $row = $elem.parent().parent(), $cells = $row.find("td");
				$cells.eq(0).html($row.data("sort") || "");
				$cells.eq(1).html($row.data("name") || "");

				$row.removeData("sort name");
				$elem.attr("data-click", "deleteFolder").html(U.lang("DELETE")).prev().attr("data-click", "editFolder").html(U.lang("EDIT"));
			},
			// 保存文件夹修改
			'saveFolder': function(elem, param) {
				var $elem = $(elem), $row = $elem.parent().parent(), $cells = $row.find("td");
				var rowData = $row.find("input").serializeArray(), postData = {};
				if ($.trim(rowData[0].value) === "") {
					$cells.eq(0).find("input").blink();
				} else if ($.trim(rowData[1].value) === "") {
					$cells.eq(1).find("input").blink();
				} else {
					var url = param.saveUrl;
					postData.fid = param.fid;
					postData[rowData[0].name] = rowData[0].value;
					postData[rowData[1].name] = rowData[1].value;

					Email.op.postAccess(url, postData).done(function(res) {
						if (res.isSuccess) {
							$cells.eq(0).html(res.sort);
							$cells.eq(1).html(res.name);
							$elem.attr('data-click', 'editFolder').html(U.lang("EDIT")).next().attr('data-click', 'deleteFolder').html(U.lang("DELETE"));
							// 更新侧栏对应文件夹信息
							Email.folderList.updateItem(param.fid, {
								fid: param.fid,
								text: res.name,
								url: Ibos.app.url('email/list/index', { op: 'folder', fid: res.fid })
							});
							// 更新“移动至”列表
							Email.moveTargetList.updateItem(param.fid, {
								fid: param.fid,
								text: res.name
							});
						} else {
							Ui.tip(res.msg, 'danger');
						}
					}, "json");
				}
			},
			// 删除文件夹
			"deleteFolder": function(elem, param) {
				Ui.dialog({
					id: 'd_folder_delete',
					width: 300,
					title: U.lang("EM.DELETE_DIR"),
					content: '<h5 class="mbs">' + U.lang("EM.DELETE_DIR_CONFIRM") + '</h5><label for="clean_mail" class="checkbox"><input type="checkbox" id="clean_mail" />' + U.lang("EM.DELETE_DIR_TIP") + '</label>',
					init: function() {
						$("#clean_mail").label();
					},
					ok: function() {
						var toCleanMail = $('#clean_mail').prop('checked'),
							url = param.delUrl,
							params = {fid: param.fid, delemail: +toCleanMail};
						Email.op.postAccess(url, params).done(function(res) {
							if (res.isSuccess) {
								// 移除一行
								$(elem).parent().parent().fadeOut(function(){ 
									$(this).remove();
								});
								// 移除侧栏对应文件夹
								Email.folderList.removeItem(param.fid);
								// 移除“移到至”对应项
								Email.moveTargetList.removeItem(param.fid);
							} else {
								Ui.tip(res.errorMsg, 'danger');
							}
						}, "json");
					}
				});
			},
			// 增加文件夹
			"addFolder": function(elem, param) {
				var $elem = $(elem), $row = $elem.parent().parent(), 
					$table = $row.parent().prev(), 
					$cells = $row.find("td");

				var rowData = $row.find("input").serializeArray(), postData = {};

				if ($.trim(rowData[0].value) === "") {
					$cells.eq(0).find("input").blink();
				} else if ($.trim(rowData[1].value) === "") {
					$cells.eq(1).find("input").blink();
				} else {
					var url = param.addUrl;
					postData[rowData[0].name] = rowData[0].value;
					postData[rowData[1].name] = rowData[1].value;

					Email.op.postAccess(url, postData).done(function(res) {
						if (res.isSuccess) {
                            postData[rowData[0].name] = res.sort;
                            postData[rowData[1].name] = res.name;
							// 插入一行
							$.tmpl("add_folder_tpl", $.extend({
								fid: res.fid
							}, postData)).appendTo($table).hide().fadeIn();
							// 清空
							$cells.eq(0).find("input").val("");
							$cells.eq(1).find("input").val("");

							// 增加侧栏文件夹
							Email.folderList.addItem({ 
								fid: res.fid,
								text: res.name,
								url: Ibos.app.url('email/list/index', { op: 'folder', fid: res.fid })
							});
							// 增加“移动至”项
							Email.moveTargetList.addItem({ 
								fid: res.fid,
								text: res.name
							});
						} else {
							Ui.tip(res.errorMsg, 'danger');
						}
					}, "json");
				}
			}
		},
		"focus": {
			// 快捷回复
			"flexArea": function(elem, param) {
				var $elem = $(elem), $target = $("#" + param.targetId);
				$(elem).animate({"height": "100px"}, 100);
				$target.show();
			}
		},
		"blur": {
			// 快捷回复
			"flexArea": function(elem, param) {
				var $elem = $(elem), $target = $("#" + param.targetId);
				if ($.trim($elem.val()) === "") {
					setTimeout(function() {
						$elem.animate({"height": ""}, 100);
						$target.hide();
					}, 200);
				}
			}
		},
		"change": {
			"subop": function(elem, url) {
				window.location.href = url + '&' + $.param({subop: $(elem).val()});
			}
		}
	};

	var _trigger = function(elem, type) {
		var prop = "data-" + type, name = $.attr(elem, prop), param;
		if (eventHandler[type][name] && $.isFunction(eventHandler[type][name])) {
			param = $(elem).data("param");
			eventHandler[type][name].call(eventHandler[type], elem, param);
		}
	};

	$(document).on("click", "[data-click]", function() {
		_trigger(this, "click");
	}).on("focus", "[data-focus]", function() {
		_trigger(this, "focus");
	}).on("blur", "[data-blur]", function() {
		_trigger(this, "blur");
	}).on("change", "[data-change]", function() {
		_trigger(this, "change");
	}).ready(function() {
		setTimeout(function() {
			var loopCount = '';
			var getCount = function() {
				Email.refreshCounter();
			};
			loopCount = setInterval(getCount, 10000); //10秒轮询一次
			getCount();
		}, G.settings.notifyInterval);
	});
})();



$(function(){
	Ibos.evt.add({
		// 我的文件夹
		"toggleSidebarList": function(param, elem){
			$(elem).toggleClass("active").parent().next().toggle();
		}
	});
});