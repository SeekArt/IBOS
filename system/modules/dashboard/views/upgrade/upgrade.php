<?php 
use application\core\utils\IBOS;
?>

<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
	</div>
	<div>
		<div class="ctb">
			<h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
			<div id="upgrade_info">

			</div>
		</div>
	</div>
</div>
<script type="text/ibos-template" id="progress_bar">
	<div style="width:300px;">
	<div id="progress_bar" class="progress progress-striped active" title="Progress-bar">
	<div class="progress-bar" style="width: 100%;"></div>
	</div>
	</div>
</script>
<script type="text/ibos-template" id="list_table">
	<div>
	<table class="table table-striped">
	<thead>
	<tr>
	<th><?php echo $lang['Upgrade list']; ?></th>
	<th><?php echo $lang['Operation']; ?></th>
	</tr>
	</thead>
	<%=list%>		
	</table>
	</div>
</script>
<script type="text/ibos-template" id="list_tr">
	<tr>
	<td>
	<p> <%=desc%> <span class="label label-warning">NEW！</span> </p>
	</td>
	<td>
	<%=op%>
	</td>
	</tr>
</script>
<script type="text/ibos-template" id="info">
	<div>
	<blockquote>
	<p><%=msg%></p>
	</blockquote>
	</div>
</script>
<script type="text/ibos-template" id="pre_update_list">
	<div>
	<h3><?php echo $lang['Upgrade preupdatelist']; ?></h3>
	<%=list%>
	<p></p>
	<p>
	<button type="button" data-target="<%=actionUrl%>" data-loading-text="<?php echo $lang['Downloading']; ?>" autocomplete="off" data-act="processStep" class="btn btn-primary btn-large"><?php echo $lang['Upgrade download']; ?></button>
	</p>
	</div>
</script>
<script type="text/ibos-template" id="compare_list">
	<div>
	<h3><?php echo $lang['Upgrade diff show']; ?></h3>
	<div class="alert alert-error">
	<h4><?php echo $lang['Diff']; ?></h4>
	<ul class="list-inline"><%=diffList%></ul>
	</div>
	<div class="alert alert-info">
	<h4><?php echo $lang['Normal']; ?></h4>
	<ul class="list-inline"><%=normalList%></ul>
	</div>
	<div class="alert alert-success">
	<h4><?php echo $lang['New add']; ?></h4>
	<ul class="list-inline"><%=newList%></ul>
	</div>
	<blockquote>
	<p><?php echo $lang['Upgrade download file']; ?><code>/data/update/IBOS <%=version%> Release[<%=release%>]</code></p>
	</blockquote>
	<blockquote>
	<p><?php echo $lang['Upgrade backup file']; ?><code>/data/back/IBOS <?php echo VERSION; ?> Release[<?php echo VERSION_DATE; ?>]</code><?php echo $lang['Upgrade backup file2']; ?></p>
	</blockquote>
	<p>
	<button type="button" data-target="<%=actionUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off" data-act="processStep" class="btn <%=actionClass%> btn-large"><%=actionDesc%></button>
	</p>
	</div>
</script>
<script type="text/ibos-template" id="update_confirm">
	<div>
	<div class="alert alert-error>"><%=msg%></div>
	<button type="button" data-target="<%=retryUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off" data-act="processStep" class="btn btn-large"></button>
	<button type="button" data-target="<%=ftpUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off" data-act="processStep" class="btn btn-large mls"></button>
	</div>
</script>
<script type="text/ibos-template" id="ftp_setup">
	<form id="sys_ftp_form" method="post" class="form-horizontal">
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Enabled ssl']; ?></label>
	<div class="controls">
	<input type="checkbox" name="ftp[ssl]" value="1" data-toggle="switch" class="visi-hidden" />
	</div>
	</div>
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Ftp host']; ?></label>
	<div class="controls">
	<input type="text" name="ftp[host]" />
	</div>
	</div>
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Ftp port']; ?></label>
	<div class="controls">
	<input type="text" name="ftp[port]" value="25" />
	</div>
	</div>
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Ftp user']; ?></label>
	<div class="controls">
	<input type="text" name="ftp[username]" />
	</div>
	</div>
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Ftp pass']; ?></label>
	<div class="controls">
	<input type="text" name="ftp[password]" />
	</div>
	</div>
	<div class="control-group">
	<label class="control-label"><?php echo $lang['Ftp pasv']; ?></label>
	<div class="controls">
	<input type="checkbox" name="ftp[pasv]" value="1" data-toggle="switch" class="visi-hidden" />
	</div>
	</div>
	</form>
</script>
<script type="text/ibos-template" id="upgrade_complete">
			<div>
			<div class="alert alert-success">
				<%=msg%>
				<br/>
				<p><?php echo $lang['Upgrade complete recommand']; ?></p>
				<button type="button" onclick="window.location.href = '<?php echo $this->createUrl( 'update/index' ); ?>';" class="btn btn-primary btn-large"><?php echo $lang['Update cache']; ?></button>
			</div>
			</div>
</script>
<script>
	(function() {
		var $info = $('#upgrade_info'),
				upgrade = {
					// 默认请求路由
					baseUrl: '<?php echo $this->createUrl( 'upgrade/index' ); ?>',
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
									op = n.upgrade ? '<a class="btn" data-act="upgrade" data-target="' + n.link + '"><?php echo $lang['Upgrade automatically']; ?></a>' : n.link;
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
							title: "<?php echo IBOS::lang( 'Confirm action', 'message' ); ?>",
							content: '<?php echo $lang['Upgrade backup remind']; ?>',
							id: 'confirm_upgrade_act',
							lock: true,
							ok: function() {
								// 进度条
								$info.html("<img src='<?php echo STATICURL; ?>/image/common/loading.gif' />");
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
							$info.append('<button type="button" data-target="' + data.url + '" data-loading-text="<?php echo $lang['Downloading']; ?>" autocomplete="off" data-act="processStep" class="btn"><?php echo $lang['Sure']; ?></button>');
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
								actionDesc: data.forceUpgrade ? '<?php echo $lang['Upgrade force']; ?>' : '<?php echo $lang['Upgrade regular']; ?>'
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
							title: "<?php echo $lang['Ftp setting']; ?>",
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
</script>