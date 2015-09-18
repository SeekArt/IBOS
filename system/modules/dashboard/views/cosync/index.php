<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">

<div class="ct">
	<div class="clearfix">
		<h1 class="mt">酷办公</h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'cobinding/index' ) ?>">酷办公绑定</a>
			</li>
			<li>
				<span>部门及用户同步</span>
			</li>
		</ul>
	</div>
	<div>
		<div class="ctb ps-type-title">
			<h2 class="st">同步数据</h2>
			<div class="alert trick-tip">
				<div class="trick-tip-title">
					<i></i>
					<strong>技巧提示</strong>
				</div>
				<div class="trick-tip-content">
					<ul class="trick-tip-list">
						<li>
							<span>点击“开始同步”后即可将选择以“**”为准的组织架构和所有人员的数据同步</span>
						</li>
						<li>
							<span>IBOS未同步指：OA用户由于某些个人信息不完善，导致没有将该类型用户同步至酷办公后台的人数</span>
						</li>
						<li>
							<span>酷办公未同步是指：在酷办公添加的新用户，但在OA没有对应添加该新用户的人数</span>
						</li>
					</ul>					
				</div>
			</div>
		</div>
		<div class="conpamy-info-wrap">
			<div class="clearfix" id="sync_opt_wrap">
				<div class="pull-left ml">
					<div class="bind-info-wrap setting-bind-iframe">
						<div class="wrap-body fill-nn" id="wrap_body">
							<div>
								<div class="clearfix">
									<div class="pull-left sync-wrap">
										<div class="mbs clearfix">
											<i class="o-ibo-tip pull-left mls"></i>
											<div class="pull-right pls prs">
												<p>IBOS待同步</p>
												<span class="fsl xwb xcbu"><?php echo $ibosUnsyncCount; ?></span>
												<span>人</span>
											</div>
										</div>
										<div>
											<select multiple size="10" class="sync-data-select">
												<?php if ( !empty( $ibosUnsync ) ): ?>
													<?php foreach ( $ibosUnsync as $key => $value ) { ?>
														<option><?php echo $value['realname']; ?></option>
													<?php } ?>
												<?php endif; ?>
											</select>
										</div>
									</div>
									<div class="pull-right sync-wrap">
										<div class="mbs clearfix">
											<i class="o-co-tip pull-left mls"></i>
											<div class="pull-right pls prs">
												<p>酷办公待同步</p>
												<span class="fsl xwb xcbu"><?php echo $coUnsyncCount; ?></span>
												<span>人</span>
											</div>
										</div>
										<div>
											<select multiple size="10" class="sync-data-select">
												<?php if ( !empty( $coUnsync ) ): ?>
													<?php foreach ( $coUnsync as $key => $value ) { ?>
														<option><?php echo $value['realname']; ?></option>
													<?php } ?>
												<?php endif; ?>
											</select>
										</div>
									</div>
								</div>
								<div class="pts">
									<div class="t pts pbs">部门同步</div>
									<div class="row">
										<label class="span6">
											<input type="radio" name="datum" value="0" checked>
											<span>以 IBOS 为准</span>
										</label>
										<label class="span6">
											<input type="radio" name="datum" value="1">
											<span>以 酷办公 为准</span>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="wrap-footer fill-nn xac" id="wrap_footer">
							<label class="checkbox mbs mls">
								<input type="checkbox" id="send_request" checked />
								<span>同步成功后自动向未加入企业成员发起邀请</span>
							</label>
							<button class="btn btn-primary strat-sync-btn" type="button" data-loading-text="同步数据中" data-action="startSyncData" data-url="" id="sync_data_btn">开始同步</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/template" id="result_syncing_tpl">
    <div class="xac syncing-info-wrap">
    <ul class="list-inline mb">
    <li>
    <i class="o-weixin-tip"></i>
    </li>
    <li class="mlm">
    <i class="o-transport-right mbm"></i>
    <p class="mbs">同步组织架构与人员</p>
    <i class="o-transport-left mbm"></i>
    </li>
    <li class="mlm">
    <i class="o-ibos-tip"></i>
    </li>
    </ul>
    <p class="mbs">
    <span class="fsm xcbu"><%= data.msg %></span>
    </p>
    <div class="progress">
    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="<%= data.percentage %>" aria-valuemin="0" aria-valuemax="100" style="width: <%= data.percentage %>%">
    </div>
    </div>
    </div>
</script>
<script type="text/template" id="result_success_tpl">
    <div class="xac result-info-wrap">
    <i class="o-opt-success mb"></i>
    <p class="mbm">
    <span class="fsl">成功同步</span>
    <span class="fsl xcbu"><%= data.successCount %></span>
    <span class="fsl">个员工信息</span>
    </p>
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
<script type="text/template" id="result_half_tpl">
    <div class="xac result-info-wrap">
    <i class="o-opt-success mb"></i>
    <p class="mbm">
    <span class="fsl">成功同步</span>
    <span class="fsl xcbu"><%= data.successCount %></span>
    <span class="fsl">个员工信息</span>
    </p>
    <p class="mbs">
    <span class="fsl xcr"><%= data.errorCount %></span>
    <span class="fsl">个联系人无法同步</span>
    </p>
    <p class="mbs">
    <span>请根据错误信息修正并重新同步。</span>
    </p>
    <p>
    <a href="<%= data.downUrl %>;" class="btn">下载错误信息</a>
    </p>
    </div>
</script>
<script type="text/template" id="result_error_tpl">
    <div class="xac result-info-wrap">
    <i class="o-opt-faliue mb"></i>
    <p class="mbs">
    <span class="fsl xcr"><%= data.errorCount %></span>
    <span class="fsl">个联系人无法同步</span>
    </p>
    <p class="mbs">
    <span>请根据错误信息修正并重新同步。</span>
    </p>
    <p> 
    <a href="<%= data.downUrl %>" class="btn">下载错误信息</a>
    </p>
    </div>
</script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/ibosco.js?<?php echo VERHASH; ?>"></script>