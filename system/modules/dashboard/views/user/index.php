<?php

use application\core\utils\Env;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\utils\Role as RoleUtil;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">部门人员管理</h1>
	</div>
	<div>
		<!-- 部门信息 start -->
		<div class="ctb">
			<h2 class="st">组织架构管理</h2>
			<div class="btn-group mb">
				<a class="btn <?php if ( $type === 'enabled' ): ?>active<?php endif; ?>" href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'enabled', 'deptid' => $deptId ) ); ?>"><?php echo $lang['Enable']; ?></a>
				<a class="btn <?php if ( $type === 'lock' ): ?>active<?php endif; ?>" href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'lock', 'deptid' => $deptId ) ); ?>"><?php echo $lang['Lock']; ?></a>
				<a class="btn <?php if ( $type === 'disabled' ): ?>active<?php endif; ?>" href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'disabled', 'deptid' => $deptId ) ); ?>"><?php echo $lang['Disabled']; ?></a>
				<a class="btn <?php if ( $type === 'all' ): ?>active<?php endif; ?>" href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'all', 'deptid' => $deptId ) ); ?>"><?php echo $lang['All']; ?></a>
			</div>
			<div class="mc clearfix">
				<div class="aside">
					<div class="fill-ss">
						<a href="<?php echo $this->createUrl( 'department/add' ); ?>" class="btn btn-warning add-dept-btn">新增部门</a>
					</div>
					<div class="ztree-wrap">
						<div>
							<ul class="ztree org-utree org-corporation-utree">
								<li class="level0">
									<span class="button level0 switch corporation"></span>
									<a href="<?php echo $this->createUrl( 'unit/index' ); ?>"  title="<?php echo $unit['fullname']; ?>" class="<?php if ( $deptId == 0 ): ?>curSelectedNode<?php endif; ?>">
										<span><?php echo $unit['fullname']; ?></span>
										<i class="o-org-ztree-edit pull-right opt-btn opt-edit-btn" title="设置公司信息"  id="edit_corporation"></i>
									</a>
								</li>
							</ul>
						</div>
						<ul id="utree" class="ztree org-utree">
						</ul>
					</div>
				</div>
				<div class="mcr">
					<div class="page-list">
						<div class="page-list-header">
							<div class="pull-left">
								<div class="btn-group">
									<button type="button" onclick="location.href = '<?php echo $this->createUrl( 'user/add' ) . "&deptid=" . Env::getRequest( 'deptid' ); ?>';" class="btn btn-primary"><?php echo $lang['Add user']; ?></button>
								</div>
								<div class="btn-group mlm">
									<button type="button" data-action="batchImport" class="btn btn-primary">批量导入</button>
								</div>
								<div class="btn-group mlm">
									<button type="button" data-action="checkRelationship" class="btn">查看上下级关系</button>
								</div>
								<div class="btn-group mlm">
									<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><?php echo $lang['More operation']; ?>
										<i class="caret"></i>
									</button>
									<ul class="dropdown-menu" id="list_act">
										<li><a data-action="setUserStatus" data-param='{"op": "enabled"}' href="javascript:;"><?php echo $lang['Enable']; ?></a></li>
										<li><a data-action="setUserStatus" data-param='{"op": "lock"}' href="javascript:;"><?php echo $lang['Lock']; ?></a></li>
										<li><a data-action="setUserStatus" data-param='{"op": "disabled"}' href="javascript:;"><?php echo $lang['Disabled']; ?></a></li>
										<li><a data-action="exportUser" href="javascript:;"><?php echo $lang['Export']; ?></a></li>
									</ul>
								</div>
							</div>
							<form method="post" action="<?php echo $this->createUrl( 'user/index', array( 'type' => $type ) ); ?>">
								<div class="search pull-right span4">
									<input type="text" name="keyword" placeholder="<?php echo $lang['User search tip']; ?>" id="mn_search" nofocus>
									<a href="javascript:;">search</a>
								</div>
								<input type="hidden" name="search" value="1" />
								<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
							</form>
						</div>
						<div class="page-list-mainer">
							<?php if ( !empty( $list ) ) : ?>
								<table class="table table-striped table-hover org-user-table" id="org_user_table">
									<thead>
										<tr>
											<th width="20">
												<label class="checkbox">
													<input type="checkbox" data-name="user">
												</label>
											</th>
											<th width="40"></th>
											<th width="100"><?php echo $lang['Full name']; ?></th>
											<th><?php echo $lang['Department']; ?></th>
											<th>角色</th>
											<th>手机</th>
											<th>微信号</th>
											<th width="60"><?php echo $lang['Operation']; ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $dept = DepartmentUtil::loadDepartment(); ?>
										<?php $position = PositionUtil::loadPosition(); ?>
										<?php $role = RoleUtil::loadRole(); ?>
										<?php foreach ( $list as $key => $value ) : ?>
											<tr>
												<td>
													<?php if ( $value['uid'] !== '1' ): ?>
														<label class="checkbox">
															<input type="checkbox" name="user" value="<?php echo $value['uid']; ?>" />
														</label>
													<?php endif; ?>
												</td>
												<td>
													<div class="avatar-box" data-param="uid=<?php echo $value['uid']; ?>">
														<span class="avatar-circle">
															<img src="static.php?type=avatar&uid=<?php echo $value['uid']; ?>&size=small&engine=<?php echo ENGINE; ?>" />
														</span>
													</div>
												</td>
												<td>
													<div class="xcm">
														<?php echo $value['realname']; ?>
													</div>
													<div class="fss">
														<?php echo isset( $position[$value['positionid']] ) ? $position[$value['positionid']]['posname'] : '—'; ?>
													</div>
												</td>
												<td>
													<span class="fss">
														<?php echo isset( $dept[$value['deptid']] ) ? $dept[$value['deptid']]['deptname'] : $unit['fullname']; ?>
													</span>
												</td>
												<td>											
													<span class =  "fss xcr">
													<?php echo isset( $role[$value['roleid']] ) ? $role[$value['roleid']]['rolename'] : " "; ?><!--2015年8月6日11:50:51 角色后面的逗号删掉 gzczj-->
													</span>
													<?php
													if ( isset( $value['relatedRoleid'] ) && !empty( $value['relatedRoleid'] ) ) {
														$arr = explode( ',', $value['relatedRoleid'] );
														if ( !empty( $arr ) ) {
															foreach ( $arr as $row ) {
																?>
																<span class ="fss">
																	<?php echo $role[$row]['rolename'] . " "; ?><!--  2015年8月6日12:40:34  辅助角色后面的分号删掉 界面的美化 gzczj-->
																</span>
																<?php
															}
														}
													} else if ( !isset( $role[$value['roleid']] ) ) {
														echo "—";
													}
													?>
												</td>
												<td>
													<span class="fss"><?php echo $value['mobile'] ?></span>
												</td>
												<td>
													<span class="fss"><?php echo $value['weixin'] ?></span>
												</td>
												<td>
													<a href="<?php echo $this->createUrl( 'user/edit', array( 'uid' => $value['uid'] ) ); ?>" class="cbtn o-edit"></a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php else: ?>
								<div class="no-data-tip"></div>
							<?php endif; ?>
						</div>
						<div class="page-list-footer">
							<?php
							if ( isset( $pages ) ) {
								$this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) );
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="batch_import_dialog" style="display:none;">
	<div class="batch-import-wrap" id="batch_import_wrap">
		<div class="fill-nn" id="upload_wrap">
			<div class="mb clearfix">
				<div class="pull-left">
					<i class="o-step-one"></i>
				</div>
				<div class="pull-right dialog-import-tip">
					<p class="xwb mbs">准备组织与成员信息</p>
					<p class="mbs">
						<span>使用数据模板文件，录入组织与成员信息。为了保证导入成功，请根据表格中批注的数据格式要求进行录入。</span>
						<span class="tcm">若您已准备好数据文件，请直接进行步骤二</span>
					</p>
					<p class="mbs">
						<span>您当前的可登录用户数为</span>
						<span class="current-num xcbu xwb fsm"></span>
						<span>人，可再导入用户数为</span>
						<span class="remain-num xco xwb fsm"></span>
						<span>人。</span>
					</p>
					<a href="<?php echo $this->createUrl( 'user/import', array( 'op' => 'downloadTpl' ) ); ?>" class="btn">下载模版</a>
				</div>
			</div>
			<div class="clearfix">
				<div class="pull-left">
					<i class="o-step-two"></i>
				</div>
				<div class="pull-right dialog-import-tip">
					<p class="xwb fsl mbs">上传数据文件</p>
					<p class="mbs">
						<span>上传数据文件，目前支持的文件类型为(*.xls、*.xlsx)</span>
					</p>
					<div class="att">
                        <div class="attb">
                            <a href="javascript:;" id="upload_btn">上传附件</a>
                            <input type="hidden" id="attachmentid" name="attachmentid" value="">
                        </div>
                        <div>
                            <div class="attl" id="file_target"></div>
                        </div>
                    </div>
				</div>
			</div>
		</div>
		<div class="import-dialog-footer clearfix">
			<div class="pull-right">
				<a href="javascript:;" class="btn" data-action="closeDialog">取消</a>
				<a href="javascript:;" class="btn btn-primary" data-action="importExel">导入</a>
			</div>
		</div>
	</div>
	<div class="batch-result-wrap" id="batch_result_wrap" style="display:none;">
		<div class="fill-nn">
			<div class="xac batch-result-content">
				<p class="mbs">
					<i class="o-result-tip"></i>
				</p>
				<div class="fsl xwb mbs xac"> 
					<span>成功导入</span>
					<span class="xcbu" id="import_success">0</span>
					<span>个员工，</span>
					<span class="xco" id="import_failure">0</span>
					<span>个员工无法导入</span>
				</div>
				<div class="xac mbs">
					<span id="download_error_tip">请根据错误信息修正并重新导入文件。</span>
				</div>
				<div class="xac">
					<a href="" class="btn" id="download_error_info">下载错误信息</a>
				</div>
			</div>
		</div>
		<div class="import-dialog-footer clearfix">
			<div class="pull-right">
				<a href="javascript:;" class="btn" data-action="closeDialog">关闭</a>
				<a href="javascript:;" class="btn" data-action="againImport" data-param='{"type": "success"}'>重新导入</a>
			</div>
		</div>
	</div>
	<div class="batch-falure-wrap" id="batch_falure_wrap" style="display:none;">
		<div class="fill-nn">
			<div class="xac batch-result-content">
				<p class="mbs">
					<i class="o-failure-tip"></i>
				</p>
				<div class="xwb mbs xac"> 
					<span class="info-wrap"></span>
				</div>
				<div class="xac mbs">
					<span>访问地址：</span>
					<a href="" class="website-address xcbu" target="_blink"></a>
				</div>
			</div>
		</div>
		<div class="import-dialog-footer clearfix">
			<div class="pull-right">
				<a href="javascript:;" class="btn" data-action="closeDialog">关闭</a>
				<a href="javascript:;" class="btn" data-action="againImport" data-param='{"type": "failure"}'>重新导入</a>
			</div>
		</div>
	</div>
</div>

<div id="update_userinfo_dialog" style="display:none;">
    <div class="user-form-con">
        <form class="form-horizontal" id="update_userinfo_form">
        	<div class="dialog-form-header">
        		<ul>
        			<li class="active">
        				<a class="form-head-list" data-type="dept" href="javascript:;">按部门</a>
        			</li>
        			<li>
        				<a class="form-head-list" data-type="pos" href="javascript:;">按岗位</a>
        			</li>
        		</ul>
        	</div>
        	<div class="dialog-form-content">
        		<div id="update_user_dept"></div>
        		<div id="update_user_pos" style="display:none;"></div>
        		<input type="hidden" name="deptid" value=""/>
        		<input type="hidden" name="posid" value=""/>
        		<input type="hidden" name="type" value="dept"/>
        	</div>
        </form>
    </div>
</div>

<div id="update_userinfo_box"></div>
<script>
	Ibos.app.setPageParam({
		"selectedDeptId": <?php echo $deptId; ?>,
		"auxiliaryId": [<?php echo $deptStr; ?>]
	})
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_user_index.js?<?php echo VERHASH; ?>'></script>

