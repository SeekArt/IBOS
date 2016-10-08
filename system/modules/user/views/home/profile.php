<?php

use application\core\utils\Ibos;

?>
<!-- Mainer -->
<div class="mc mcf clearfix">
	<?php echo $this->getHeader( $lang ); ?>
	<div>
		<div>
			<ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
				<li>
					<a href="<?php echo $this->createUrl( 'home/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Home page']; ?></a>
				</li>
				<?php if ( $this->getIsWeiboEnabled() ): ?><li><a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/personal/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Weibo']; ?></a></li><?php endif; ?>
				<?php if ( $this->getIsMe() ): ?>
					<li><a href="<?php echo $this->createUrl( 'home/credit', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Credit']; ?></a></li>
				<?php endif; ?>
				<li class="active">
					<a href="<?php echo $this->createUrl( 'home/personal', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Profile']; ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>
<div class="pc-header clearfix">
	<ul class="nav nav-skid">
		<li class="active">
			<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'profile', 'uid' => $this->getUid() ) ); ?>">
				<?php if ( $this->getIsMe() ): ?>
					<?php echo $lang['My profile']; ?>
				<?php else: ?>
					<?php echo $lang['Other profile']; ?>
				<?php endif; ?>
			</a>
		</li>
		<?php if ( $this->getIsMe() ): ?>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'avatar', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Upload avatar']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'password', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Change password']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'remind', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Remind setup']; ?></a></li>
			<li>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'history', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Login history']; ?></a>
			</li>
		<?php endif; ?>
	</ul>
</div>
<div class="clearfix">
	<?php if ( $this->getIsMe() ): ?>
		<div class="pc-container clearfix dib left-sidebar">
			<div>
				<form id="profile_form" action="<?php echo $this->createUrl( 'home/personal' ); ?>" method="post" class="form-horizontal form-narrow">
					<!-- 基本信息 -->
					<div class="data-title mb">
						<i class="o-edit-data"></i><span class="fsl vam"><?php echo $lang['Base info']; ?></span>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Real name']; ?></label>
						<div class="controls">
							<div class="controls-content"><?php echo $user['realname']; ?></div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Department']; ?></label>
						<div class="controls">
							<div class="controls-content"><?php echo $user['deptname']; ?></div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Position']; ?></label>
						<div class="controls">
							<div class="controls-content">
								<?php echo $user['posname']; ?>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Birth day']; ?></label>
						<div class="controls">
							<div class="datepicker span5" id="birthday">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" name="birthday" class="datepicker-input" value="<?php
								if ( $user['birthday'] > 0 ) :echo date( 'Y-m-d', $user['birthday'] );
								endif;
								?>">
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Personal profile']; ?></label>
						<div class="controls">
							<div class="span10" style="padding-left: 0;">
								<textarea name="bio" rows="3" maxLength="30"><?php echo $user['bio']; ?></textarea>
								<p class="fss tcm mls introduction-tip">个人简介最多只能输入30个字符！</p>
							</div>
						</div>
					</div>
					<div class="the-line">
						<span class="dib halving-line"></span>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Cell phone']; ?></label>
						<div class="controls">
							<input type="text" value="<?php echo $user['mobile']; ?>" id="mobile" name="mobile" class="span7"<?php if ( $user['validationmobile'] == 1 ): ?> disabled <?php endif; ?> >
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Email']; ?></label>
						<div class="controls">
							<input type="text" value="<?php echo $user['email']; ?>" id="email" name="email" class="span7"<?php if ( $user['validationemail'] == 1 ): ?> disabled<?php endif; ?> >
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">QQ</label>
						<div class="controls">
							<input type="text" value="<?php echo $user['qq']; ?>" name="qq" class="span7" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Telephone']; ?></label>
						<div class="controls">
							<input type="text" value="<?php echo $user['telephone']; ?>" name="telephone" class="span7" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Address']; ?></label>
						<div class="controls">
							<div class="span10" style="margin-left:-10px">
								<textarea name="address" rows="3"><?php echo $user['address']; ?></textarea>
							</div>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
							<input type="hidden" name="op" value="<?php echo $op; ?>" />
							<input type="hidden" name="uid" value="<?php echo $this->getUid(); ?>" />
							<input type="submit" name="userSubmit" value="<?php echo $lang['Save']; ?>" class="btn btn-large btn-primary btn-great" /></div>
					</div>
				</form>
			</div>
		</div>
		<?php $this->widget( 'application\modules\user\components\UserProfileTracker', array( 'user' => $user ) ) ?>
	<?php else: ?>
		<!-- 他人信息左栏 -->
		<div class="other-info pull-left">
			<div class="data-title mb">
				<i class="o-edit-data"></i><span class="fsl vam"><?php echo $lang['Base info']; ?></span>
			</div>
			<form action="" method="post" class="form-horizontal form-narrow">
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Real name']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span class="xcbu"><?php echo $user['realname']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Department']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span class="xcbu"><?php echo $user['deptname']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Position']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span class="xcbu"><?php echo $user['posname']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Birth day']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span class="tcm"><?php  if ( $user['birthday'] > 0 ) :echo date( 'Y-m-d', $user['birthday'] );endif; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Personal profile']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['bio']; ?></span>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- 他人信息右栏 -->
		<div class="pull-right other-info ml">
			<div class="data-title mb">
				<i class="o-contact-way"></i><span class="fsl vam"><?php echo $lang['Contact']; ?></span>
			</div>
			<form action="" method="post" class="form-horizontal form-narrow">
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Cell phone']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['mobile']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Email']; ?></label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['email']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">
						QQ
					</label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['qq']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">
						<?php echo $lang['Telephone']; ?>
					</label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['telephone']; ?></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">
						<?php echo $lang['Address']; ?>
					</label>
					<div class="controls">
						<div class="controls-content">
							<span><?php echo $user['address']; ?></span>
						</div>
					</div>
				</div>
			</form>
		</div>
	<?php endif ?>
</div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script>
	$(function() {
		//日期选择器
		$("#birthday").datepicker();
	});
</script>