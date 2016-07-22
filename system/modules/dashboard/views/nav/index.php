<?php 
use application\core\utils\IBOS;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Navigation setting']; ?></h1>
	</div>
	<div>
		<form id="sys_nav_form" action="<?php echo $this->createUrl( 'nav/index' ); ?>" method="post" class="form-horizontal">
			<!-- 导航设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Navigation setting']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Tips']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Nav tip']; ?>
					</div>
				</div>
				<div class="sys-nav-area">
					<div class="sys-nav-top clearfix">
						<div class="nav-w36">
							<span class="">名称</span>
						</div>
						<div class="nav-w14">类型</div>
						<div class="nav-w35">链接</div>
						<div class="nav-w6">是否启用</div>
						<div class="nav-w6">新窗打开</div>
					</div>
					<div class="sys-nav-content">
						<ul class="nav-main-list" id="nav_main_list">
						<?php $count = 0; ?>
						<?php foreach ( $navs as $key => $nav ): ?>
							<?php $count++; ?>
							<li data-id="<?php echo $key; ?>">
								<div class="nav-item main-nav-item">
									<div class="mst-board nav-w36">
										<div class="span1 drap-area">
											<i class="drap-icon"></i>
										</div>
										<div class="span6">
											<input type="text" class="input-small" data-type="name" name='navs[<?php echo $key; ?>][name]' value="<?php echo $nav['name']; ?>" />
										</div>
										<div class="span3 mls add-child-btn">
											<a href="javascript:void(0);" data-target="#sys_child_<?php echo $key; ?>_body" data-act="add_sec" class="cbtn o-plus"></a>
											<?php if ( $nav['system'] == '0' ): ?>
												<a href="javascript:void(0);" data-target='<?php echo $key; ?>' data-act="remove_main" class="cbtn o-trash mls"></a>
											<?php endif; ?>
										</div>
									</div>
									<div class="nav-w14" data-type="<?php echo $nav['type']?>">
										<?php if ( $nav['system'] == '1' ): ?>
											<span class="fss">系统内置</span>
										<?php else: ?>
											<span class="fss">超链接</span>
											<!-- 暂时隐藏单页图文功能 -->
											<!-- <select class="type-select input-small span6">
												<option value="0" <?php if($nav['type'] == 0): ?>selected<?php endif; ?>>超链接</option>
												<option value="1" <?php if($nav['type'] == 1): ?>selected<?php endif; ?>>单页图文</option>
											</select> -->
										<?php endif; ?>
									</div>
									<div class="nav-w35 system-url">
										<?php if ( $nav['system'] == '1' ): ?>
											<?php echo $nav['url']; ?>
											<input type="hidden" data-type='url' value="<?php echo $nav['url']; ?>">
										<?php else: ?>
											<div class="nav-url <?php if($nav['type'] == 1): ?>hidden<?php endif; ?>">		
												<input type="text" data-type='url' name="navs[<?php echo $key; ?>][url]" class="input-small span9 <?php if($nav['type'] == 0): ?>mark<?php endif; ?>" value="<?php if($nav['type'] == 0): ?><?php echo $nav['url']; ?><?php endif; ?>">
											</div>
											<div class="single-page <?php if($nav['type'] == 0): ?>hidden<?php endif; ?>">
												<a class="dib" href="<?php echo IBOS::app()->urlManager->createUrl("main/page/edit", array( 'pageid' => $nav['pageid'] )); ?>" target="_blank" data-pageid="<?php echo $nav['pageid']; ?>">修改内容</a>
												<a class="dib mls" href="<?php echo IBOS::app()->urlManager->createUrl('main/page/index', array( 'pageid' => $nav['pageid'], 'name' => $nav['name'] )); ?>" target="_blank">预览</a>
												<input type="hidden" data-type="pageid" value="<?php echo $nav['pageid']; ?>">
											</div>
										<?php endif; ?>
									</div>
									<div class="nav-w6 isuse">
										<label class="checkbox">
											<input type="checkbox" data-action="isUse" name="navs[<?php echo $key; ?>][disabled]" value="0" <?php if ( $nav['disabled'] == '0' ): ?>checked<?php endif; ?> />
										</label>
									</div>
									<div class="nav-w6">
										<label class="checkbox">
											<input type="checkbox" name="navs[<?php echo $key; ?>][targetnew]" value="1" <?php if ( $nav['targetnew'] == '1' ): ?>checked<?php endif; ?> />
										</label>
									</div>
									<input type="hidden" data-type="module" name="navs[<?php echo $key; ?>][module]" value="<?php echo $nav['module']; ?>"
									<input type="hidden" data-type="isSystem" name="navs[<?php echo $key; ?>][isSystem]" value="<?php if ( $nav['system'] == '1' ): ?>1<?php else: ?>0<?php endif; ?>">
								</div>
								<div class="add-nav-item">
									<ul class="nav-child-list" data-id='<?php echo $key; ?>' id="sys_child_<?php echo $key; ?>_body">
										<?php $subCount = count( $nav['child'] ); ?>
										<?php $subIndex = 0; ?>
										<?php foreach ( $nav['child'] as $subKey => $subVal ): ?>
										<?php $subIndex++; ?>
										<li data-id='<?php echo $subVal['id']; ?>' <?php if ( $subIndex == $subCount ): ?>class="msts-last"<?php endif; ?>>
											<div class="nav-item child-nav-item">
												<div class="mst-board nav-w36">
													<div class="span2 drap-area">
														<i class="drap-icon"></i>
													</div>
													<div class="span6">
														<input type="text" name="navs[<?php echo $subVal['id']; ?>][name]" data-type='name' class="input-small" value="<?php echo $subVal['name']; ?>" />
													</div>
													<?php if ( $subVal['system'] == '0' ): ?>
							 							<div class="span3 mls add-child-btn">
							 								<a data-target='<?php echo $subVal['id']; ?>' data-act="remove_sec" href="javascript:void(0);" class="cbtn o-trash"></a>
							 							</div>
							 						<?php endif; ?>
												</div>
												<div class="nav-w14" data-type="<?php echo $subVal['type']?>">
													<?php if ( $subVal['system'] == '1' ): ?>
														<span class="fss">系统内置</span>
													<?php else: ?>
														<span class="fss">超链接</span>
														<!-- <select class="type-select input-small span6">
															<option value="0" <?php if($subVal['type'] == 0): ?>selected<?php endif; ?>>超链接</option>
															<option value="1" <?php if($subVal['type'] == 1): ?>selected<?php endif; ?>>单页图文</option>
														</select> -->
													<?php endif; ?>
												</div>
												<div class="nav-w35 system-url">
													<?php if ( $subVal['system'] == '1' ): ?>
								 						<?php echo $subVal['url']; ?>
								 						<input type="hidden" data-type='url' value="<?php echo $subVal['url']; ?>">
								 					<?php else: ?>
								 						<div class="nav-url <?php if($subVal['type'] == 1): ?>hidden<?php endif; ?>">
								 							<input type="text" data-type='url' class="input-small span9 <?php if($subVal['type'] == 0): ?>mark<?php endif; ?>" value="<?php if($subVal['type'] == 0): ?><?php echo $subVal['url']; ?><?php endif; ?>">	
								 						</div>
								 						<div class="single-page <?php if($subVal['type'] == 0): ?>hidden<?php endif; ?>">
															<a class="dib" href="<?php echo IBOS::app()->urlManager->createUrl("main/page/edit", array( 'pageid' => $subVal['pageid'] )); ?>" target="_blank" data-pageid="<?php echo $subVal['pageid']; ?>">修改内容</a>
															<a class="dib mls" href="<?php echo IBOS::app()->urlManager->createUrl('main/page/index', array( 'pageid' => $subVal['pageid'], 'name' => $subVal['name'] )); ?>" target="_blank">预览</a>
															<input type="hidden" data-type="pageid" value="<?php echo $subVal['pageid']; ?>">
														</div>
								 					<?php endif; ?>				
												</div>
												<div class="nav-w6 isuse">
													<label class="checkbox">
														<input type="checkbox" name="navs[<?php echo $subVal['id']; ?>][disabled]" value="0" <?php if ( $subVal['disabled'] == '0' ): ?>checked<?php endif; ?> />
													</label>
												</div>
												<div class="nav-w6">
													<label class="checkbox">
														<input type="checkbox" name="navs[<?php echo $subVal['id']; ?>][targetnew]" value="1" <?php if ( $subVal['targetnew'] == '1' ): ?>checked<?php endif; ?> />
													</label>
												</div>
												<input type="hidden" data-type="module" name="navs[<?php echo $subVal['id']; ?>][module]" value="<?php echo $subVal['module']; ?>"
												<input type="hidden" data-type="isSystem" name="navs[<?php echo $subVal['id']; ?>][isSystem]" value="<?php if ( $subVal['system'] == '1' ): ?>1<?php else: ?>0<?php endif; ?>">
											</div>
										</li>
										<?php endforeach; ?>
									</ul>													
								</div>
							</li>
						<?php endforeach; ?>
						</ul>
					</div>
					<div class="nav-main-add">
						<a href="javascript:void(0);" data-target="#nav_main_list" class="operate-group" data-act="add_main">
							<i class="cbtn o-plus"></i>
							添加主导航 
						</a>
					</div>
				</div>
			</div>
			<div>
				<button id="nav_submit" type="submit" name="navSubmit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
			</div>
			<input type="hidden" id='removeId' name="removeId" />
		</form>
	</div>
</div>
<script type="text/ibos-template" id="new_main_nav">
	<div class="nav-item main-nav-item">
		<div class="mst-board nav-w36">
			<div class="span1 drap-area">
				<i class="drap-icon"></i>
			</div>
			<div class="span6">
				<input type="text" class="input-small" data-type="name" name='newnavs[<%=id%>][sort]' />
			</div>
			<div class="span3 mls add-child-btn">
				<a href="javascript:void(0);" data-target="#sys_child_<%=id%>_body" data-act="add_sec" class="cbtn o-plus"></a>
				<a href="javascript:void(0);" data-act="remove_main" class="cbtn o-trash mls"></a>
			</div>
		</div>
		<div class="nav-w14" data-type="0">
			<span class="fss">超链接</span>
		</div>
		<div class="nav-w35 system-url">
			<div class="nav-url">
				<input type="text" data-type='url' class="input-small span9 mark" name="newnavs[<%=id%>][url]">
				<input type="hidden" data-type="isSystem" name="newnavs[<%=id%>][isSystem]" value="0">
			</div>
			<div class="single-page hidden">
				<a class="dib" href="">修改内容</a>
				<a class="dib mls" data-action="previewPage" href="javascript:;">预览</a>
				<input type="hidden" data-type="pageid" value="0">
			</div>
		</div>
		<div class="nav-w6 isuse">
			<label class="checkbox">
				<input data-action="isUse" checked type="checkbox" name="newnavs<%=id%>[disabled]" />
			</label>
		</div>
		<div class="nav-w6">
			<label class="checkbox">
				<input type="checkbox" name="newnavs[<%=id%>][targetnew]" />
			</label>
		</div>
	</div>
	<div class="add-nav-item">
		<ul class="nav-child-list ui-sortable" data-id="<%=id%>" id="sys_child_<%=id%>_body"></ul>
	</div>
</script>
<script type="text/template" id="new_nav">
	<div class="nav-item child-nav-item">
		<div class="mst-board nav-w36">
			<div class="span2">
				<i class="drap-icon"></i>
			</div>
			<div class="span6">
				<input type="text" data-type="name" class="input-small" name="newnavs[<%=id%>][sort]" />
			</div>
			<div class="span3 mls add-child-btn">
				<a data-act="remove_sec" href="javascript:void(0);" class="cbtn o-trash"></a>
			</div>
		</div>
		<div class="nav-w14" data-type="0">
			<span class="fss">超链接</span>
		</div>
		<div class="nav-w35 system-url">
			<div class="nav-url">
				<input type="text" data-type="url" class="input-small span9 mark" name="newnavs[<%=id%>][url]" />
				<input type="hidden" data-type="isSystem" name="newnavs[<%=id%>][isSystem]" value="0">
			</div>
			<div class="single-page hidden">
				<a class="dib" href="">修改内容</a>
				<a class="dib mls" data-action="previewPage" href="javascript:;">预览</a>
				<input type="hidden" data-type="pageid" value="0">
			</div>
		</div>
		<div class="nav-w6 isuse">
			<label class="checkbox">
				<input type="checkbox" checked name="newnavs[<%=id%>][disabled]" />
			</label>
		</div>
		<div class="nav-w6">
			<label class="checkbox">
				<input type="checkbox" name="newnavs[<%=id%>][targetnew]" />
			</label>
		</div>
		<div class=""></div>
	</div>
</script>
<script src='<?php echo $assetUrl; ?>/js/systemnav.js?<?php echo VERHASH; ?>'></script>