<!-- Todo: 这个页面好像没用了，能删吗？ -->


<!-- load css -->
<link rel="stylesheet" href='<?php echo STATICURL; ?>/js/Select2/select2.css?<?php echo VERHASH; ?>'>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/recruit.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="wrap">
	<div class="mc clearfix">
		<!-- Sidebar -->
		<?php echo $sidebar; ?>
		<!-- Mainer right -->
		<div class="mcr">
			<!-- Mainer content -->
			<form action="<?php echo $this->createUrl( 'resume/saveInterview' ); ?>" method="post" 
				  id="email_form" class="form-horizontal form-narrow">
				<div class="ct ctform cti">
					<div class="btn-toolbar mbs">
						<button type="button" class="btn" onclick="javascript:history.go(-1);"><?php echo $lang['Return']; ?></button>
						<button type="submit" name="emailbody[issend]" value="1" class="btn btn-primary"><?php echo $lang['Send']; ?></button>
					</div>
					<div class="well well-full">
						<div class="mal-form">
							<!-- Row 1 收件人 -->
							<div class="control-group">
								<label class="control-label">
									<div class="fsl"><?php echo $lang['Addressee']; ?></div>
								</label>
								<div class="controls">
									<input type="text" id="" name="emailbody[towebmail]" class="span12" value="<?php echo $emails; ?>">
								</div>
							</div>

							<div id="other_rec">
								<!-- Row 3 抄送  -->
								<div class="control-group">
									<label class="control-label">
										<?php echo $lang['Cc']; ?>
									</label>
									<div class="controls">
										<!-- @Todo: Selector -->
										<input type="text" name="emailbody[copytoids]" value="">
									</div>
								</div>  
							</div>

							<div id="web_rec">
								<!-- Row 6 发件人外部邮箱  -->
								<div class="control-group">
									<label class="control-label">
										<?php echo $lang['Mail pieces']; ?>
									</label>
									<div class="controls">
										<div class="btn-group btn-group-inverse">
											<ul class="dropdown-menu"  data-toggle="imitate-select" data-input="#web_mail">
												<?php $firstIndex = true; ?>
												<?php foreach ($emailWebList as $emailWeb): ?>
													<li <?php if ( $firstIndex ): ?>class="active"<?php endif; ?> data-value="<?php echo $emailWeb['webid']; ?>">
														<a href="javascript:;" ><?php echo $emailWeb['address']; ?></a>
													</li>
                                                    <input type="hidden" <?php if ( $firstIndex ): ?>value="<?php echo $emailWeb['webid']; ?>"<?php endif; ?> name="emailbody[fromwebid]" id="web_mail">
													<?php
													if ( $firstIndex ) {
														$firstAddr = $emailWeb['address'];
														$firstIndex = false;
													}
													?>
												<?php endforeach; ?>
											</ul>
											<a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown">
												<span><?php echo isset( $firstAddr ) ? $firstAddr : ''; ?></span>
												<i class="caret"></i>
											</a>
											<a target="_blank" href="<?php echo Yii::app()->createUrl( 'email/default/webemail',array( 'uid' => IBOS::app()->user->uid ) ); ?>" class="btn btn-narrow">
												<i class="o-setup"></i>
											</a>
										</div>
									</div>
								</div>
							</div>

							<!-- Row 7 邮件主题  -->
							<div class="control-group">
								<div class="control-label control-label-btn btn-group">
									<a href="#" class="btn btn-block dropdown-toggle" data-toggle="dropdown">
										<span><?php echo $lang['Regular mail']; ?></span>
										<i class="caret"></i>
									</a>
									<ul id="mal_level_list" class="dropdown-menu xal" data-toggle="imitate-select" data-input="#mal_level">
										<li data-value="1" class="active">
											<a href="javascript:;"><?php echo $lang['Regular mail']; ?></a>
										</li>
										<li data-value="2">
											<a href="javascript:;"><?php echo $lang['Emergency messages']; ?></a>
										</li>
										<li data-value="3">
											<a href="javascript:;"><?php echo $lang['Important messages']; ?></a>
										</li>
									</ul>
									<input type="hidden" value="1" name="emailbody[important]" id="mal_level">
								</div>
								<div class="controls">
									<input type="text" name="emailbody[subject]" placeholder="<?php echo $lang['Please enter a message subject']; ?>" id="mal_title" 
									value="">
								</div>
							</div>
						</div>
						<!-- Row 8 编辑器  -->
						<div class="bdbs">
							<script  id="editor" name="emailbody[content]" type="text/plain">
							</script>
						</div>
						<div class="att">
							<div class="attb">
								<a href="javascript:;" id="upload_btn"></a>
								<a href="javascript:;" class="btn btn-icon vat" title="<?php echo $lang['Choose from a file cabinet']; ?>">
									<i class="o-folder-close"></i>
								</a>
							</div>
							<div id="file_target" class="attl"></div>
						</div>
					</div>
					
					<!-- Row 10 Button -->
					<div id="submit_bar" class="clearfix">
						<button type="button" class="btn btn-large btn-submit pull-left" onclick="javascript:history.go(-1);"><?php echo $lang['Return']; ?></button>
						<div class="pull-right">
							<button type="submit" name="emailbody[issend]" value="1" class="btn btn-large btn-submit btn-primary"><?php echo $lang['Send']; ?></button>
						</div>
					</div>
				</div>
				<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" >
				<input type="hidden" name="emailbody[resumeids]" value="<?php echo $resumeids; ?>">
			</form>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/Select2/select2.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit.js?<?php echo VERHASH; ?>'></script>
<script>
	(function() {
		//抄送密送外部收件
		$("#extra_rec").on("click", "a", function() {
			var target = $(this).data("target");
			if (target) {
				if (target == '#other_rec') {
					var isOtherRec = $('#isOtherRec');
					if (isOtherRec.val() == 1) {
						isOtherRec.val('');
					} else {
						isOtherRec.val(1);
					}
				} else if (target == '#web_rec') {
					var isWebRec = $('#isWebRec');
					if (isWebRec.val() == 1) {
						isWebRec.val('');
					} else {
						isWebRec.val(1);
					}
				}
				$(target).toggle();
			}
		})

		//编辑器
		UEDITOR_CONFIG.toolbars = UEDITOR_CONFIG.mode.simple;
		var ue = new UE.ui.Editor({
			initialFrameWidth: 738,
			minFrameWidth: 738
		}).render('editor');

		var attachUpload = Ibos.upload.attach({
			upload_url: "../../test.php",
			post_params: {},
			custom_settings: {
				//上传进度条
				containerId: "file_target"
			}
		});
		//邮件标题颜色
		var MAL_LEVEL_COLOR = {
			NORMAL: '',
			URGENCY: 'xcr',
			IMPORTANT: 'xcgn'
		}
		var MAL_LEVEL_MAP = {
			'1': MAL_LEVEL_COLOR.NORMAL,
			'2': MAL_LEVEL_COLOR.URGENCY,
			'3': MAL_LEVEL_COLOR.IMPORTANT
		}
		var malTitle = $("#mal_title");
		$("#mal_level_list").on('click', 'li', function() {
			var value = $(this).attr('data-value');
			malTitle.attr('class', MAL_LEVEL_MAP[value]);
		})
        //得到webid
        $('.dropdown-menu').on('click','a',function(){
            var webid=$(this).parent().attr('data-value');
            $('#web_mail').val(webid);
        });
	})();
	//@Debug, 用户选择框的测试
	var rec = $("#recipients");
	(function() {
		var format = function(result) {
			if (result.id != undefined) {
				var type = result.id.substr(0, result.id.indexOf('_')) || '0';
			}
			var icon = ['o-mal-inbox', 'o-mal-todo', 'o-mal-draft', 'o-mal-sended'];
			return '<span><i class="' + icon[type] + '"></i>' + result.text + '</span>';
		}

		var ibosData = Ibos.data.get();
		rec.select2({
			data: Ibos.data.converToArray(ibosData),
			multiple: true,
			initSelection: function(element, callback) {
				var data = [];
				$(element.val().split(",")).each(function() {
					var item = Ibos.data.getUser(this);
					data.push({id: this, text: item && item.text});
				});
				callback(data);
			},
			formatSelection: format,
			formatResult: format
		})
	})();
</script>