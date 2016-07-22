<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/home.css?<?php echo VERHASH; ?>">
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet" />
<div class="ct sp-ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Wechat corp']; ?></h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'wxbinding/index' ); ?>"><?php echo $lang['Wechat binding'] ?></a>
			</li>
			<li>
				<span><?php echo $lang['Department and user sync'] ?></span>
			</li>
		</ul>
	</div>
	<div>
		<div class="ctb ps-type-title">
			<!-- 同步start -->
			<div class="box-shadow bind-info-wrap">
				<div class="clearfix">
					<div class="box-shadow ibos-qy">
						<div class="aes-key" data-toggle="tooltip" data-html="true" title="<div class='aes-key-tip'><p class='xwb'>AES KEY：</p><p><?php echo $aeskey; ?></p></div>">AES KEY</div>
						<div class="company-logo mbs">
							<img src="<?php echo $unit['logourl']; ?>" alt="<?php echo $unit['shortname']; ?>">
							<div class="ibos-logo">
								<i class="o-binding-ibos"></i>
							</div>
						</div>
						<p class="lhl t"><?php echo $unit['fullname']; ?></p>
						<p class="lhl"><?php echo $unit['systemurl']; ?></p>
					</div>
					<div class="box-shadow weixin-qy">
						<div class="company-logo mbs">
							<img src="<?php echo $wxqy['logo']; ?> " alt="<?php echo $wxqy['name']; ?>">
							<div class="weixin-logo">
								<i class="o-binding-weixin"></i>
							</div>
						</div>
						<p class="lhl"><?php echo $wxqy['name']; ?></p>
						<p class="lhl">CorpID : <?php echo $wxqy['corpid']; ?></p>
					</div>
					<div class="co-binding-state" data-toggle="tooltip" title="解绑需要到微信企业号后台取消套件托管">
						<div class="co-binding-icon">
							<i class="o-binding-success"></i>
							<span></span>绑定成功
						</div>
						<div class="co-unbinding-icon" onclick="window.open('http://doc.ibos.com.cn/article/detail/id/329' ,'_blank');">
							<i class="o-unbinding-success"></i>
							<span></span>解除绑定
						</div>
					</div>
				</div>
				<div class="clearfix" id="sync_opt_wrap">
					<div class="row pts pbs">
						<div class="span6">
							<p class="mbs fsm">
								<span><?php echo $lang['IBOS not sync wechat']; ?></span>
								<span class="xwb"><?php echo $localCount; ?></span>
								<span>人</span>
								<!-- <a class="anchor pull-right" data-action="getDetail" data-param='{"op": "ibos"}' href="javascript:;">详情</a> -->
							</p>
						</div>
						<div class="span6">
							<p class="mbs fsm">
								<span><?php echo $lang['Wechat not bind IBOS'] ?></span>
								<span class="xwb"><?php echo $wxCount; ?></span>
								<span>人</span>
								<!-- <a class="anchor pull-right" data-action="getDetail" data-param='{"op": "qyh"}' href="javascript:;">详情</a> -->
							</p>
						</div>
					</div>
					<div class="wrap-footer" id="wrap_footer">
						<label class="checkbox">
							<input type="checkbox" id="send_email" checked />
							<span><?php echo $lang['Invite employees focus on'] ?></span>
						</label>
						<button class="btn btn-primary btn-large btn-block" type="button" data-action="syncData"><?php echo $lang['Start synchronization'] ?></button>
					</div>
				</div>
			</div>
			<!-- 备份 -->
			<div class="box-shadow bind-info-wrap dn">
				<div class="clearfix">
					<div class="box-shadow ibos-qy">
						<div class="aes-key" data-toggle="tooltip" data-html="true" title="<div class='aes-key-tip'><p class='xwb'>AES KEY：</p><p>9Pcz3VcUe6kh-GEAgU3vL99rHUk5F7C-libcteUhQkYC72D8qf</p></div>">AES KEY</div>
						<div class="company-logo mbs">
							<img src="" alt="">
							<div class="ibos-logo">
								<i class="o-binding-ibos"></i>
							</div>
						</div>
						<p class="lhl t">优网科技</p>
						<p class="lhl">http://oa.uweb.net.cn</p>
					</div>
					<div class="box-shadow weixin-qy">
						<div class="company-logo mbs">
							<img src="" alt="">
							<div class="weixin-logo">
								<i class="o-binding-weixin"></i>
							</div>
						</div>
						<p class="lhl t">优网科技</p>
						<p class="lhl">CorpID : wx2e5512eae17e2e2c</p>
					</div>
					<div class="binding-relation">
						<i class="o-binding-success"></i>绑定成功
					</div>
				</div>
				<div class="clearfix mb" id="sync_opt_wrap">
					<div class="mb">
						<div class="clearfix">
							<div class="pull-left">
								<p class="mbs fsm">
									<span><?php echo $lang['IBOS not sync wechat']; ?></span>
									<span class="xwb">12</span>
									<span>人</span>
								</p>
								<div>
									<select multiple size="10" class="sync-data-select"></select>
								</div>
							</div>
							<div class="pull-right">
								<p class="mbs fsm">
									<span><?php echo $lang['Wechat not bind IBOS'] ?></span>
									<span class="xwb">12</span>
									<span>人</span>
								</p>
								<div>
									<select multiple size="10" class="sync-data-select"></select>
								</div>
							</div>
						</div>
						<div class="wrap-footer" id="wrap_footer">
							<label class="checkbox">
								<input type="checkbox" id="send_email" checked />
								<span><?php echo $lang['Invite employees focus on'] ?></span>
							</label>
							<button class="btn btn-primary btn-large btn-block" type="button" data-action="syncData"><?php echo $lang['Start synchronization'] ?></button>
						</div>
					</div>
				</div>
				<div class="auto-sync">
					<p class="lhf">自动同步</p>
					<p class="tcm mbh">开启后将定时检测用户差异并从IBOS同步到企业号绑定</p>
					<input type="checkbox" data-action="autoSync" value="1" data-toggle="switch" class="visi-hidden">
				</div>
				<div class="clearfix pt">
					<div class="pull-left lhf xwb">
						已授权<span class="xco fsl"> 12 </span>个应用
					</div>
					<div class="pull-right" style="margin-right: -64px">
						<button class="btn">添加或修改授权应用</button>
					</div>
				</div>
			</div>
			<!-- 同步end -->
		</div>
	</div>
</div>

<script type="text/template" id="ibosqyh_sync_tmpl">
	<div id="ibosqyh_sync_dialog">
	<div style="width:740px; min-height:400px;">
	<div class="position-mumber-wrap">
	<div class="ibosco-sync-list span12">
	<ul class="ibosco-member-list clearfix">
	<% for(var i=0; i<data.length; i++){ %>
	<li id="binding_member_<%=data[i].uid%>">
	<div class="ibosco-avatar-box">
	<a href="javascript:;" class="ibosco-avatar-circle"><img src="<%=data[i].avatar%>" alt=""></a>
	</div>
	<div class="ibosco-member-item-body">
	<p class="ellipsis xcn xwb" title="<%=data[i].realname%>"><%=data[i].realname%></p>
	<p class="tcm"><%=data[i].detail%></p>
	</div>
	</li>
	<% } %>
	</ul>
	</div>
	</div>
	</div>
	</div>
</script>

<script type="text/template" id="result_syncing_tpl">
	<div class="row pt">
	<div class="xac syncing-info-wrap span6 offset3">
	<ul class="list-inline mb">
	<li>
	<i class="o-ibos-tip"></i>
	</li>
	<li class="mlm">
	<i class="o-transport-right mbm"></i>
	<p class="mbs">同步IBOS成员</p>
	<i class="o-transport-right mbm"></i>
	</li>
	<li class="mlm">
	<i class="o-weixin-tip"></i>
	</li>
	</ul>
	<p class="mbs">
	<span class="fsm"><%= data.msg %></span>
	</p>
	<div class="progress">
	<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="<%= data.percentage %>" aria-valuemin="0" aria-valuemax="100" style="width: <%= data.percentage %>%">
	</div>
	</div>
	</div>
	</div>
</script>

<script type="text/template" id="result_sending_tpl">
	<div class="xac result-info-wrap">
	<i class="o-opt-success mb"></i>
	<p class="mbm">
	<span class="fsl">成功邀请关注</span>
	</p>
	</div>
</script>
<script type="text/template" id="result_success_tpl">
	<div class="xac result-info-wrap">
	<i class="o-opt-success mb"></i>
	<p class="mbm">
	<span class="fsl">恭喜，同步成功！</span>
	</p>
	<p class="clearfix bdbs lhf">
	<span class="pull-left">企业号</span>
	<span class="pull-right">新增 <span class="xcbu xwb"><%= data.successCount %></span></span>
	</p>
	<p class="clearfix lhf">
	<span class="pull-left">已绑定人数</span>
	<span class="pull-right xcbu xwb">0</span>
	</p>
	<a href="javascript:location.reload();" class="btn btn-block btn-primary btn-large">确定</button>
	</div>
</script>
<script type="text/template" id="result_error_tpl">
	<div class="xac result-info-wrap">
	<i class="o-opt-faliue mb"></i>
	<p class="mbs">
	<span class=" xcr"><%= data.errorCount %></span>
	<span>个联系人无法同步</span>
	</p>
	<p class="mbs">
	<span>请根据错误信息修正并重新同步。</span>
	</p>
	<p>
	<a href="<%= data.downUrl %>" class="btn">下载错误信息</a>
	</p>
	</div>
</script>
<script type="text/template" id="result_half_tpl">
	<div class="xac result-info-wrap">
	<i class="o-opt-success mb"></i>
	<p class="mbm">
	<span>成功同步</span>
	<span class="xcbu"><%= data.successCount %></span>
	<span>个员工信息</span>
	</p>
	<p class="mbs">
	<span class=" xcr"><%= data.errorCount %></span>
	<span>个联系人无法同步</span>
	</p>
	<p class="mbs">
	<span>请根据错误信息修正并重新同步。</span>
	</p>
	<p>
	<a href="<%= data.downUrl %>" class="btn">下载错误信息</a>
	</p>
	</div>
</script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/syncdata.js"></script>
