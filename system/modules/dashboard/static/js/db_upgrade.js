(function() {
	var $info = $('#upgrade_info'),
		upgrade = {
			// 默认请求路由
			baseUrl: Ibos.app.url('upgrade/index'),
			/**
			 * 检查升级
			 * @returns {mixed}
			 */
			check: function() { //检查升级
				$.get(this.baseUrl, {op: 'checking', 'callback': 'upgrade.showUpgrade'}, function(callback) {
					callback && eval(callback);
				});
			},
			/**
			 * 返回检查升级信息
			 * @returns {void}
			 */
			showUpgrade: function() {
				$.get(this.baseUrl, {op: 'showupgrade'}, function(data) {
					if (data.isHaveUpgrade) {
						var list = '', op = '';
						$.each(data.list, function(i, n) {
							op = n.upgrade ? '<a class="btn" data-act="upgrade" data-target="' + n.link + '">'+ Ibos.l("UPGRADE.UPGRADE_AUTOMATICALL") +'</a>' : n.link;
							list += $.template('list_tr', {desc: n.desc, op: op});
						});
						$info.html($.template('list_table', {list: list}));
					} else {
						$info.html($.template('info', {msg: data.msg}));
					}
				}, 'json');
			},
			/**
			 * 开始升级
			 * @param {object} obj 点击的dom对象
			 * @returns {mixed}					 
			 */
			upgrade: function(obj) {
				var aObj = $(obj), target = aObj.data('target');
				$.artDialog({
					title: Ibos.l("UPGRADE.SURE_OPERATE"),
					content: Ibos.l("UPGRADE.UPGRADE_BACKUP_REMIND"),
					id: 'confirm_upgrade_act',
					lock: true,
					ok: function() {
						// 进度条
						$info.html("<img src='"+ Ibos.app.getStaticUrl('/image/common/loading.gif') +"' />");
						$.get(target, function(data) {
							return upgrade.processingStep(data.step, data.data);
						}, 'json');
					}
				});
			},
			/**
			 * 步骤分发
			 * @param {integer} step 当前步骤
			 * @param {object} data 服务器返回操作数据
			 * @returns {@exp;upgrade@call;processingShowUpgrade|@exp;upgrade@call;processingCompareFile|@exp;upgrade@call;processingDownloadFile}					
			 */
			processingStep: function(step, data) {
				switch (step) {
					case 1:
						return upgrade.processingShowUpgrade(data);
						break;
					case 2:
						return upgrade.processingDownloadFile(data);
						break;
					case 3:
						return upgrade.processingCompareFile(data);
					case 4:
						return upgrade.processingUpdateFile(data);
					case 5:
						return upgrade.processingTempFile(data);
						break;
				}
			},
			/**
			 * 第一步：显示更新列表
			 * @param {object} data 服务器返回的操作数据
			 * @returns {void}
			 */
			processingShowUpgrade: function(data) {
				var list = upgrade.getFileList(data.list, 'code');
				$info.html($.template('pre_update_list', {list: list, actionUrl: data.actionUrl}));
			},
			/**
			 * 第二步：下载文件
			 * @param {object} data 服务器返回的操作数据
			 * @returns {mixed}
			 */
			processingDownloadFile: function(data) {
				$info.append('<blockquote><p>' + data.msg + '</p></blockquote>');
				if (data.IsSuccess) {
					upgrade.getAndPass(data.url);
				} else {
					$info.append('<button type="button" data-target="' + data.url + '" data-loading-text="'+ Ibos.l("UPGRADE.DOWN") +'" autocomplete="off" data-act="processStep" class="btn">'+ Ibos.l("UPGRADE.SURE") +'</button>');
				}

			},
			/**
			 * 第三步：对比文件
			 * @param {object} data 服务器返回的操作数据
			 * @returns {void}
			 */
			processingCompareFile: function(data) {
				if (typeof data.msg !== 'undefined') {
					$info.append('<hr/>');
					$info.append('<div class="alert alert-error">' + data.msg + '</div>');
				} else {
					var variable = {
						diffList: upgrade.getFileList(data.list.diff, 'li'),
						normalList: upgrade.getFileList(data.list.normal, 'li'),
						newList: upgrade.getFileList(data.list.newfile, 'li'),
						version: data.param.version,
						release: data.param.release,
						actionUrl: data.url,
						actionClass: data.forceUpgrade ? 'btn-danger' : 'btn-primary',
						actionDesc: data.forceUpgrade ? Ibos.l("UPGRADE.UPGRADE_FORCE") : Ibos.l("UPGRADE.UPGRADE_REGULAR")
					};
					$info.html($.template('compare_list', variable));
				}
			},
			/**
			 * 第四步：更新文件
			 * @param {object} data
			 * @returns {@exp;upgrade@call;ftpSetup}
			 */
			processingUpdateFile: function(data) {
				switch (data.status) {
					// 错误信息，提供重试与设置选项
					case 'no_access':
					case 'upgrade_ftp_upload_error':
					case 'upgrade_copy_error':
						$info.html($.template('update_confirm'), {
							msg: data.msg,
							retryUrl: data.retryUrl,
							ftpUrl: data.ftpUrl
						});
						break;
						// 提示信息，显示并执行下一步
					case 'upgrade_backuping':
					case 'upgrade_backup_complete':
						$info.html($.template('info', {msg: data.msg}));
						upgrade.getAndPass(data.url);
						break;
						// 更新数据库，跳转到另外的处理文件，所以是alert提醒
					case 'upgrade_database':
						alert(data.msg);
						window.location.href = data.url;
						break;
						// 更新文件完成，直接跳转到最后一步
					case 'upgrade_file_successful':
						upgrade.getAndPass(data.url);
						break;
						// 设置ftp
					case 'ftpsetup':
						return upgrade.ftpSetup(data.url);
						break;
						// 备份错误
					case 'upgrade_backup_error':
						$info.html($.template('info', {msg: data.msg}));
						break;
					default:
						break;
				}
			},
			/**
			 * 第五步：处理临时文件
			 * @param {object} data
			 * @returns {void}
			 */
			processingTempFile: function(data) {
				$info.html($.template('upgrade_complete', {msg: data.msg}));
			},
			/**
			 * 通用的获取列表函数
			 * @param {object} list 遍历的对象
			 * @param {string} label 要返回的标签
			 * @returns {String} 组成的列表字符串
			 */
			getFileList: function(list, label) {
				var newList = '';
				if (list) {
					$.each(list, function(i, file) {
						newList += '<' + label + '>' + file + '</' + label + '>';
					});
				}
				return newList;
			},
			/**
			 * 通用步骤执行方法
			 * @returns {mixed}
			 */
			getAndPass: function(target) {
				$.get(target, function(data) {
					return upgrade.processingStep(data.step, data.data);
				}, 'json');
			},
			/**
			 * 设置FTP
			 * @param {string} target 提交的地址
			 * @returns {void}
			 */
			ftpSetup: function(target) {
				$.artDialog({
					title: Ibos.l("UPGRADE.FTP_SETTING"),
					content: $.template('ftp_setup'),
					id: 'sys_ftp_setup',
					cancel: true,
					ok: function() {
						$.post(target, $('#sys_ftp_form').serializeArray(), function(data) {
							return upgrade.processingStep(data.step, data.data);
						}, 'json');
					}
				});
				$('#sys_ftp_form').find("[data-toggle='switch']").iSwitch("");
			}
		};
	// 点击升级绑定操作
	$('[data-act="upgrade"]').live('click', function() {
		return upgrade.upgrade(this);
	});
	// 步骤分发绑定操作
	$('[data-act="processStep"]').live('click', function() {
		$(this).button('loading');
		var target = $(this).data('target');
		return upgrade.getAndPass(target);
	});
	// 载入时先显示滚动条
	$info.html($.template('progress_bar'));
	// 载入后首先检查升级
	upgrade.check();
})();