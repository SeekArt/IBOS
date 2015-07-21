<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">绑定酷办公，体验真正的移动办公！</h1>
	</div>
	<div>
		<!-- 企业信息 start -->
		<div class="ctb">
			<h2 class="st">酷办公绑定</h2>
			<div class="co-result-wrap">
				<!-- 当只是酷办公注册的新账号，没用创建且加入企业时，不显示企业信息栏，即rbox-top -->
				<div class="rbox-top clearfix">
					<div class="logo-wrap pull-left">
						<img src="<?php echo $data['corplogo'] ?>" alt="企业LOGO">
					</div>
					<div class="rbox-info-wrap pull-left">
						<p class="fsl xwb xcm mbs"><?php echo $data['corpshortname']; ?></p>
						<p class="mbs">
							<span class="tcm">全称：</span>
							<span><?php echo $data['corpname'] ?></span>
						</p>
						<p>
							<span class="tcm">企业代码：</span>
							<span><?php echo $data['corpcode'] ?></span>
						</p>
					</div>
					<?php if ( $op == 'index' ): ?>
						<!-- 当用户OA已绑定酷办公，且登录账号为该企业酷办公超管账号时，显示解绑操作 -->
						<div class="rbox-opt-wrap pull-right">
							<a href="javascript:;" data-action="unbindingIbosCo">解除绑定</a>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $op == 'index' ): ?>
					<div id="rbox_box">
						<!-- 酷办公账号匹配，登录成功 -->
						<div class="rbox-box">
							<div class="clearfix">
								<div class="rinfo-box">
									<p class="tits">已绑定</p>
									<p>
										<span class="fsf"><?php echo count( $data['userBinding'] ) ?></span>
										<span>人</span>
									</p>
								</div>
								<div class="rinfo-box">
									<p class="tits">未绑定</p>
									<p>
										<span class="fsf"><?php echo count( $data['oaUser'] ) - count( $data['userBinding'] ); ?></span>
										<span>人</span>
									</p>
								</div>
								<button type="button" class="btn btn-primary binding-btn" data-action="bindingIbosCo" data-token="<?php echo $data['accesstoken'] ?>">绑定酷办公用户</button>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $op == 'unbindingoa' ): ?>
						<!-- 本地没有绑定酷办公，但酷办公已绑定其他OA -->
						<div class="rbox-box">
							<div class="warning-box">
								<div class="warning-box-content xac">
									<i class="o-warning-tip mb"></i>
									<p class="fsm mbs">
										<span><?php echo $data['corpshortname'] ?> 已绑定过IBOS地址 “</span>
										<span class="xcm xwb"><?php echo $data['systemurl'] ?></span>
										<span> ”，</span>
									</p>
									<p class="fsm">如需关联当前地址，请解绑后进行。</p>
								</div>
								<div class="warning-box-footer">
									<a href="<?php echo $this->createUrl( 'cobinding/login' ); ?>">
										<i class="o-link-logo"></i>
										<span>重新登陆</span>
									</a>
									<button type="button" class="btn btn-primary unbinding-btn" data-action="imUnbindingIbos">立即解绑</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<!-- 本地没有绑定酷办公，且酷办公没有绑定OA,但企业代码不一致 -->
					<?php if ( $op == 'oacode' ): ?>
						<div class="rbox-box">
							<div class="warning-box">
								<div class="warning-box-content xac">
									<i class="o-warning-tip mb"></i>
									<p class="fsm mbs">
										<span>酷办公企业代码</span>
										<span class="xcm xwb">[</span>
										<span class="xcm xwb"><?php echo $data['cocode'] ?></span>
										<span class="xcm xwb">]</span>
										<span>与本地</span>
										<span class="xcm xwb">[</span>
										<span class="xcm xwb"><?php echo $data['oacode'] ?></span>
										<span class="xcm xwb">]</span>
										<span>不一致，请统一！</span>
									</p>
									<div>
										<span>我要统一为：</span>
										<label class="radio dib">
											<input type="radio" name="code" class="radio" checked="checked" value="<?php echo $data['cocode'] ?>" />
											<span><?php echo $data['cocode'] ?></span>
										</label>
									</div>
								</div>
								<div class="warning-box-footer">
									<a href="">
										<i class="o-link-logo"></i>
										<span>重新登陆</span>
									</a>
									<button type="button" class="btn btn-primary unify-code-btn" data-action="unifyCode">确定</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $op == 'userquitorlogin' ): ?>
						<!-- 本地没有绑定酷办公，酷办公已绑定其它OA但不是企业超管 -->
						<div class="rbox-box">
							<div class="warning-box">
								<div class="warning-box-content xac">
									<i class="o-warning-tip mb"></i>
									<p class="fsm mbs">
										<span>你还不是</span>
										<span class="xcm xwb"><?php echo $data['corpshortname'] ?></span>
										<span>超级管理员，如需绑定请退出并创建新企业！</span>
									</p>
									<p class="fss tcm">注意：退出后你需要创建新企业完成绑定</p>
								</div>
								<div class="warning-box-footer">
									<a href="<?php echo $this->createUrl( 'cobinding/login' ); ?>">
										<i class="o-link-logo"></i>
										<span>重新登陆</span>
									</a>
									<button type="button" class="btn btn-primary unbinding-btn" data-action="exitBusiness">退出企业</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<!-- 本地没有办法定酷办公，酷办公没有绑定其它OA但不是企业超管 -->
					<div class="rbox-box" style="display:none;">
						<div class="warning-box">
							<div class="warning-box-content xac">
								<i class="o-warning-tip mb"></i>
								<p class="fsm mbs">
									<span>你还不是</span>
									<span class="xcm xwb">优网科技</span>
									<span>超级管理员，无法进行绑定！</span>
								</p>
								<p>
									<span>请登录超级管理员</span>
									<span>ellenlun</span> 
									<span>进行操作。</span>
								</p>
							</div>
							<div class="warning-box-footer">
								<!-- 跳转到登录页 -->
								<a href="" class="btn btn-primary again-login-btn">重新登陆</a>
							</div>
						</div>
					</div>
					<!-- 本地没有办法定酷办公，没有加入酷办公企业 -->
					<?php if ( $op == 'usercreate' ): ?>
						<div class="rbox-box">
							<div class="warning-box">
								<div class="warning-box-content xac">
									<i class="o-warning-tip mb"></i>
									<p class="fsm xcm mbs">
										<span>你还没有企业，是否按如下信息创建企业绑定？</span>
									</p>
									<p class="mbs">
										<span>创建后的企业简称、全称可在酷办公后台修改</span>
									</p>
									<div class="xal cy-info-wrap">
										<p class="fss mbs">
											<span class="tcm">企业简称：</span>
											<span><?php echo $data['corpshortname'] ?></span>
										</p>
										<p class="fss mbs">
											<span class="tcm">全称：</span>
											<span><?php echo $data['corpname'] ?></span>
										</p>
										<p class="fss">
											<span class="tcm">企业代码：</span>
											<span><?php echo $data['corpcode'] ?></span>
										</p>
									</div>
								</div>
								<div class="warning-box-footer">
									<a href="<?php echo $this->createUrl( 'cobinding/login' ); ?>">
										<i class="o-link-logo"></i>
										<span>重新登陆</span>
									</a>
									<button type="button" class="btn btn-primary unbinding-btn" data-action="createAndBindingIbosCo">创建并绑定</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function() {
		Ibos.app.s({
			"CoCompanyName": "优网科技",
			"IbosCompanyName": "优网科技",
			"csrftoken": "123"
		});
	});
</script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/ibosco.js"></script>