<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">酷办公</h1>
		<ul class="mn">
			<li>
				<span>酷办公绑定</span>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'cosync/index' ) ?>">部门及用户同步</a>
			</li>
		</ul>
	</div>
	<div>
		<!-- 企业信息 start -->
		<div class="ctb">
			<h2 class="st">酷办公绑定</h2>
			<div class="co-banding-wrap">
				<!-- 当只是酷办公注册的新账号，没用创建且加入企业时，不显示企业信息栏，即rbox-top -->
				<div class="box-top">
					<form action="#" method="post" id="ibosco_login_form">
						<p class="xwb mb">请登录IBOS · 酷办公</p>
						<div>
							<input type="text" name="mobile" id="mobile" class="phone-input" placeholder="手机" nofocus <?php
							if ( isset( $readonly ) ) { ?>readonly value ="<?php echo $mobile;?>" <?php }?>/>
								   <input type="password" name="password" id="password" class="password-input mls" placeholder="密码" nofocus />
							<button type="button" class="btn btn-primary opt-btn mls" data-action="loginIbosCo">登录</button>
							<a href="http://www.ibos.cn/" target="_blank" class="btn mlm opt-btn">注册</a>
						</div>
					</form>
				</div>
				<div class="box-body">
					<div class="logo-tip-wrap">
						<i class="o-logo-tip"></i>
					</div>
					<i class="o-image-tip"></i>
				</div>
			</div>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscologin.js"></script>