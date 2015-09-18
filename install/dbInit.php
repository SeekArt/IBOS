<!DOCTYPE HTML>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $lang['Install guide']; ?></title>
	<meta name="keywords" content="IBOS" />
	<meta name="generator" content="IBOS 3.0" />
	<meta name="author" content="IBOS Team" />
	<meta name="coryright" content="2014 IBOS Inc." />
	<link href="<?php echo IBOS_STATIC; ?>css/base.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo IBOS_STATIC; ?>css/common.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo IBOS_STATIC; ?>js/lib/artDialog/skins/ibos.css" rel="stylesheet" type="text/css" />
	<link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
	<!-- IE8 fixed -->
	<!-- [if lt IE 9]> -->
	<link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
	<!--[endif] -->
</head>
<body>
	<div class="main">
		<div class="main-content">
			<div class="main-top posr">
				<i class="o-top-bg"></i>
				<div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
			</div>
			<div class="specific-content">
				<div class="relation-info-wrap">
					<div class="relation-content" id="relation_content">
						<div class="status-going clearfix">
							<div class="pull-left tips">
								<i class="o-not-pass"></i>
								<span class="xwb">关联酷办公吗？</span>
								<span>(关联后才可使用IBOS手机端、PC客户端。<a href="http://www.ibos.com.cn/introduce?tab=1" class="xcbu" target="_blank">了解更多</a>)</span>
							</div>
							<div class="pull-right">
								<button type="button" class="btn btn-warning btn-small" data-action="activeRegistered">立即注册</button>
								<button type="button" class="btn btn-warning btn-small mlm" data-action="activeRelation">立即关联</button>
								<button type="button" class="btn btn-small mlm" data-action="hideRelation">暂时不用</button>
							</div>
						</div>
						<div class="status-result clearfix">
							<div class="item-title pull-left">
								<span class="fsm">关联酷办公</span>
							</div>
							<div class="item-content pull-left">
								<span class="link-tits">已关联账号</span>
								<span id="realname"></span>
								<span>(</span>
								<span id="cophone" class="xwb mlm"></span>
								<span>)</span>
								<button type="button" class="btn btn-small exit-btn" data-action="exitAccount" data-role="0">退出</button>
							</div>
						</div>
					</div>
				</div>
				<div class="db-install-wrap">
					<form action="index.php?op=dbInit" method="post" class="form-horizontal form-narrow" id="user_form">
						<table class="table table-info" id="table_info">
							<tbody>
								<tr>
									<th>管理员账号</th>
									<td>
										<div class="control-group">
											<label class="control-label">设置账号<span class="xcr">*</span></label>
											<div class="controls">
												<input type="text" class="span6" data-type="account" id="administrator_account" name="adminAccount" placeholder="请输入手机号码">
												<span id="result_account"></span>
												<span id="administrator_account_tip" class="ml nomatch-tip">账号不能为空！</span>
												<input type ="hidden" name ="extraData" id ="extraData"/>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label"><?php echo $lang['Password']; ?><span class="xcr">*</span></label>
											<div class="controls">
												<input type="text" class="span6" data-type="ADpassword" id="administrator_password" name="adminPassword" placeholder="请输入密码">
												<span id="administrator_password_tip" class="ml nomatch-tip"><?php echo $lang['Password tip']; ?></span>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th>企业信息</th>
									<td>
										<div class="control-group">
											<label class="control-label">企业简称<span class="xcr">*</span></label>
											<div class="controls">
												<input type="text" name="shortname" class="span6" id="short_name" data-type="shortname" placeholder="企业名称缩写，可在后台修改" />
												<span id="short_name_tip" class="ml nomatch-tip">企业简称不能为空！</span>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label">企业代码<span class="xcr">*</span></label>
											<div class="controls">
												<input type="text" name="qycode" class="span6" id="qy_code" data-type="qycode" placeholder="通常为4~20位英文或数字，不可更改 " />
												<span id="qy_code_result"></span>
												<span id="qy_code_tip" class="ml nomatch-tip">企业代码不能为空！</span>
												<input type="hidden" id="qy_code_verify" value="1" data-status="unlink" />
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang['Db info']; ?></th>
									<td>
										<div class="control-group">
											<label class="control-label"><?php echo $lang['Db username']; ?><span class="necessary-write">*</span></label>
											<div class="controls">
												<input type="text" class="span6" data-type="username" id="database_name" name="dbAccount" value="<?php echo $dbInitData['username']; ?>">
												<span id="database_name_tip" class="ml nomatch-tip"><?php echo $lang['Dbaccount not empty']; ?></span>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label"><?php echo $lang['Db password']; ?><span class="necessary-write">*</span></label>
											<div class="controls">
												<input type="text" class="span6" data-type="DBpassword" id="database_password" name="dbPassword" value='<?php echo $dbInitData['password']; ?>'>
												<span id="database_password_tip" class="ml nomatch-tip"><?php echo $lang['Password not empty']; ?></span>
											</div>
										</div>
										<div class="mbs">
											<a href="javascript:;" class="dib show-info">
												<span class="dib"><?php echo $lang['Show more']; ?></span>
												<i class="o-pack-down mlm"></i>
											</a>
										</div>
										<div class="hidden-info">
											<div class="control-group">
												<label class="control-label"><?php echo $lang['Db host']; ?></label>
												<div class="controls">
													<input type="text" class="span6" id="database_server" name="dbHost" value="<?php echo $dbInitData['host']; ?><?php
													if ( !empty( $dbInitData['port'] ) ) {
														echo ':' . $dbInitData['port'];
													}
													?>">
													<span class="write-tip">一般为localhost</span>
												</div>
											</div>
											<div class="control-group">
												<label class="control-label"><?php echo $lang['Db name']; ?></label>
												<div class="controls">
													<input type="text" class="span6" id="dbname" name="dbName" value="<?php echo $dbInitData['dbname']; ?>">
												</div>
											</div>
										</div>
										<div class="control-group install-choose" id="tablepre_exist_tip">
											<label class="control-label"><span class="constraint-install"><?php echo $lang['Mandatory installation']; ?></span></label>
											<div class="controls">
												<div class="constraint-label">
													<label class="checkbox constraint-check">
														<input type="checkbox" name="enforce" id="enforce" ><?php echo $lang['Del data']; ?>
													</label>
												</div>
												<div class="constraint-tip">
													<span id="enforce_info">
<?php echo $lang['Dbinfo forceinstall invalid']; ?>
													</span>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="content-foot nbt clearfix">
							<div class="pull-left protocol-check">
								<span class="fss">
									点击“立即安装”即同意
									<a href="http://www.ibos.com.cn/" target="_blank">《IBOS用户使用协议》</a>
								</span>
							</div>
							<div class="pull-right">
								<label class="checkbox checkbox-inline mbz">
									<input type="checkbox" id="ext_data" name="extData" value="1" checked="checked" />
									<span><?php echo $lang['Suc tip']; ?></span>
								</label>
								<label class="checkbox checkbox-inline disabled user-defined">
									<input type="checkbox" name="custom" id="user_defined" disabled/>
									<span><?php echo $lang['Custom module']; ?></span>
								</label>
								<!--1.当未勾选自定义模块时,按钮显示为立即安装,点击后去往安装页面.
									2.当勾选自定义模块后,按钮显示为下一步,点击后去往模块设置页面. 
									js会动态改变a的href值,需要将url写入到js中-->
								<input type="hidden" name="submitDbInit" value="1" />
								<button type="button" class="btn btn-large btn-primary btn-install" id="btn_install"><?php echo $lang['Install now']; ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="relation_dialog" style="display:none;">
		<div class="relation-wrap" id="relation_wrap">
			<div class="relation-opt-wrap" id="relation_opt_wrap">
				<div class="relation-login relate-item mb">
					<div class="item-header mb">
						<i class="o-ibosco-logo mb"></i>
						<p class="title">欢迎登录 IBOS · 酷办公</p>
					</div>
					<div class="item-body">
						<form method="post" action="#" id="login_form">
							<div class="mb">
								<input type="text" name="mobile" id="mobile" placeholder="手机号" />
							</div>
							<div class="mb">
								<input type="password" name="password" id="password" placeholder="密码" />
							</div>
							<div class="mbs">
								<button type="button" class="btn btn-primary opt-btn" data-action="loginIbosco" data-loading-text="登录">登录</button>
							</div>
							<div class="xal">
								<span>还没账号？</span>
								<a href="javascript:;" class="xcbu" data-action="toggleShow" data-param='{"target": "register"}'>免费注册</a>
							</div>
						</form>
					</div>
				</div>
				<div class="relation-register relate-item">
					<div class="item-header mb">
						<i class="o-ibosco-logo mb"></i>
						<p class="title">欢迎注册 酷办公</p>
					</div>
					<div class="item-body">
						<div class="mb">
							<input type="text" name="registermobile" id="register_mobile" placeholder="手机号" />
						</div>
						<div class="mbs">
							<button type="button" class="btn btn-warning opt-btn" data-action="registerAccount" data-loading-text="注册" id="registerAccount">注册</button>
						</div>
						<div class="xal">
							<span>已有账号？</span>
							<a href="javascript:;" class="xcbu" data-action="toggleShow" data-param='{"target": "login"}'>立即登录</a>
						</div>
					</div>
				</div>
				<div class="relation-code relate-item">
					<div class="item-header mb">
						<i class="o-ibosco-logo mb"></i>
						<p>
							<span>已发送到</span>
							<span class="fsl xwb" id="send_mobile"></span>
						</p>
					</div>
					<div class="item-body">
						<div class="mb">
							<div class="input-group">
								<input type="text" id="code_input" name="code" placeholder="输入短信校验码">
								<span class="input-group-btn">
									<button class="btn again-send-btn" type="button" data-loading-text="重新发送(<span id='counting'>60</span>)" data-action="afreshCode" id="afreshCode">重新发送(60)</button>
								</span>
							</div>
						</div>
						<div>
							<button type="button" class="btn btn-warning opt-btn" data-action="verifyCode">确定</button>
						</div>
					</div>
				</div>
				<div class="relation-setting relate-item">
					<div class="item-header mb">
						<i class="o-ibosco-logo mb"></i>
						<p class="fsl">
							<span>设置密码</span>
						</p>
					</div>
					<div class="item-body">
						<div class="mbs">
							<input type="password" id="set_password" placeholder="输入密码" />
						</div>
						<div class="mb">
							<input type="password" id="reset_password" placeholder="确认密码" />
						</div>
						<div>
							<input type="hidden" id="reg_mobile" />
							<button type="button" class="btn btn-warning opt-btn" data-action="setPassword">确定</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
	<script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
	<script src='<?php echo IBOS_STATIC; ?>js/lib/artDialog/artDialog.min.js'></script>
	<script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
	<script src="static/create_data.js"></script>
	<script>
		(function() {

		$("#btn_install").click(function() {
		if ($("#enforce").is(":checked")) {
		$("#user_form").submit();
		} else {
		var dbInfo = {
		dbHost: $("#database_server").val(),
		dbAccount: $("#database_name").val(),
		dbPassword: $("#database_password").val(),
		dbName: $("#dbname").val(),
		tablePre: $("#tableprefix").val()
		};
		$.post("index.php?op=tablepreCheck", dbInfo, function(res) {
		if (res.isSuccess) {
		$("#user_form").submit();
		} else {
		if (res.tableExist === true) {
		// 显示强制数据库插入信息
		$("#tablepre_exist_tip").css('display', 'block');
		} else {
		window.location.href = "index.php?op=installResult&res=0&msg=" + res.msg;
		}
		}
		}, 'json');
		}
		});

		$("#user_defined").on("change", function() {
		if (this.checked) {
		$("#ext_data").label("uncheck").label("disable");
		} else {
		$("#ext_data").label("enable");
		}
		});

		$("#ext_data").on("change", function() {
		if (this.checked) {
		$("#user_defined").label("uncheck").label("disable");
		} else {
		$("#user_defined").label("enable");
		}
		});
		})();
	</script>
</body>
</html>