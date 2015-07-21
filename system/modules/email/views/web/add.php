<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/email.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar(); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="ct fill">
			<form id="add_form" action="<?php echo $this->createUrl( 'web/add' ); ?>" method="post" class="form-horizontal">
				<?php if ( isset( $errMsg ) ): ?>
					<div class="alert alert-danger"><?php echo $errMsg; ?></div>
				<?php endif; ?>
				<fieldset>
					<legend><?php echo $lang['Add web mail']; ?></legend>		
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Email address']; ?>：</label>
						<div class="controls">
							<input type="text" id="mal" placeholder="(<?php echo $lang['For example']; ?>：yourname@ibos.com.cn)" name="web[address]" class="span6" value="<?php if(isset($web['address'])):echo $web['address'];endif; ?>" />
						</div>
					</div>
					<div class="control-group" id="mal_reset_row">
						<label class="control-label"><?php echo $lang['Password']; ?>：</label>
						<div class="controls">
							<input type="password" name="web[password]" class="span6" value="<?php if(isset($web['password'])):echo $web['password'];endif; ?>" />
						</div>
					</div>
					<?php if ( $more ): ?>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Web mail nickname']; ?>：</label>
							<div class="controls">
								<input type="text" name="web[nickname]" class="span6" value="<?php if(isset($web['nickname'])):echo $web['nickname'];endif; ?>" />
								<span class="ilsep tcm">(<?php echo $lang['Optional']; ?>)</span>
							</div>
						</div>
						<div class="control-group mbm">
							<label class="control-label"><?php echo $lang['Receive mail server']; ?>：</label>
							<div class="controls">
								<input type="text" id="mal_pop_server" name="web[server]" class="span6" value="<?php if(isset($web['server'])):echo $web['server'];endif; ?>" />
								<a href="javascript:;" class="ilsep" data-click="showRow" data-param="{ &quot;targetId&quot;: &quot;mal_pop_row&quot;}">[<?php echo $lang['Set port']; ?>]</a>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">服务器协议：</label>
							<div class="controls">
								<label class="radio radio-inline checked"><span class="icon"></span><span class="icon-to-fade"></span>
										<input type="radio" name="web[agreement]" value="1" checked="checked">POP
								</label>&nbsp;
								<label class="radio radio-inline"><span class="icon"></span><span class="icon-to-fade"></span>
										<input type="radio" name="web[agreement]" value="2" >IMAP
								</label>&nbsp;
							</div>
						</div>
						<div class="control-group mbm" id="mal_pop_row" style="display:none;">
							<label class="control-label"><?php echo $lang['Port']; ?>：</label>
							<div class="controls">
								<input type="text" id="mal_pop_port" name="web[port]" value="<?php if(isset($web['port'])):echo $web['port']; else: echo 110; endif; ?>" class="span6" />
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<label class="checkbox">
									<input type="checkbox" name="web[ssl]" id="" value="1" <?php if(isset($web['ssl'])&&$web['ssl'] == '1'): ?>checked<?php endif; ?> /><?php echo $lang['Use ssl connect']; ?>
								</label>
							</div>
						</div>
						<div class="control-group mbm">
							<label class="control-label"><?php echo $lang['Send server']; ?>（SMTP）：</label>
							<div class="controls">
								<input type="text" id="mal_smtp_server" name="web[smtpserver]" class="span6" value="<?php if(isset($web['smtpserver'])):echo $web['smtpserver'];endif; ?>" />
								<a href="javascript:;" class="ilsep" data-click="showRow" data-param="{&quot;targetId&quot;: &quot;mal_smtp_row&quot;}">[<?php echo $lang['Set smtp port']; ?>]</a>
							</div>
						</div>
						<div class="control-group mbm" id="mal_smtp_row" style="display:none;">
							<label class="control-label"><?php echo $lang['Smtp port']; ?>：</label>
							<div class="controls">
								<input type="text" id="mal_smtp_port" name="web[smtpport]" class="span6" value="<?php if(isset($web['smtpport'])):echo $web['smtpport']; else: echo 25; endif; ?>" />
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<label class="checkbox">
									<input type="checkbox" name="web[smtpssl]" id="" value="1" <?php if(isset($web['smtpssl'])&&$web['smtpssl'] == '1'): ?>checked<?php endif; ?> /><?php echo $lang['Use ssl connect']; ?>
								</label>
							</div>
						</div>
						<input type="hidden" name="moreinfo" value="1">
					<?php endif; ?>
					<div class="control-group">
						<div class="controls">
							<button type="submit" name="emailSubmit" class="btn btn-large btn-submit btn-primary"><?php echo $lang['Submit']; ?></button>&nbsp;
							<button type="button" onclick="javascript:history.go(-1);" class="btn btn-large btn-submit"><?php echo $lang['Return']; ?></button>
						</div>
					</div>
					<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js?<?php echo VERHASH; ?>'></script>
<script>
	$(function(){
		$.formValidator.initConfig({formID: "add_form"});
		$("#mal").formValidator().regexValidator({
			dataType: "enum",
			regExp: ['email'],
			onError: Ibos.l("RULE.EMAIL_INVALID_FORMAT")
		});
		var urlVali = {
			dataType: "enum",
			regExp: "url",
			onError: Ibos.l("EM.SERVER_URL_VALIDATE")
		};
		$("#mal_pop_server, #mal_smtp_server").formValidator().regexValidator(urlVali);
		var portVali = {
			dataType: "enum",
			regExp: "num1",
			onError: Ibos.l("EM.PORT_VALIDATE")
		};
		$("#mal_pop_port, #mal_smtp_port").formValidator().regexValidator(portVali);
	})
</script>
