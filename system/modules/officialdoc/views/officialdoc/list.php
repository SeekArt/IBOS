<?php 

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/officialdoc.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $this->catId ); ?>
	<!-- Sidebar end -->

	<!-- Mainer right -->
	<div class="mcr">
		<!-- Mainer nav -->
		<div class="officialdoc-nav-wrap">
			<ul class="mnv nl clearfix">
				<?php $type = Env::getRequest( 'type' ); ?>
				<li <?php if ( !isset( $type ) ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'officialdoc/index', array( 'catid' => $this->catId ) ); ?>">
						<i class="o-art-all"></i>
						全部
					</a>
				</li>
				<li <?php if ( $type == 'nosign' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'officialdoc/index', array( 'type' => 'nosign', 'catid' => $this->catId ) ); ?>">
						<i class="o-art-unsign"></i>
						<?php echo $lang['No sign']; ?>
						<?php if($countNosign != 0): ?><span class="bubble"><?php echo $countNosign;?></span><?php endif; ?>
					</a>
				</li>
				<li <?php if ( $type == 'sign' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'officialdoc/index', array( 'type' => 'sign', 'catid' => $this->catId ) ); ?>">
						<i class="o-art-sign"></i>
						<?php echo $lang['Sign']; ?>
					</a>
				</li>
				<li <?php if ( $type == 'notallow' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'officialdoc/index', array( 'type' => 'notallow', 'catid' => $this->catId ) ); ?>">
						<i class="o-art-uncensored"></i>
						<?php echo IBOS::lang( 'No verify' ); ?>
						<?php if($countNotAllOw != 0): ?><span class="bubble"><?php echo $countNotAllOw;?></span><?php endif; ?>
					</a>
				</li>
				<li <?php if ( $type == 'draft' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'officialdoc/index', array( 'type' => 'draft', 'catid' => $this->catId ) ); ?>">
						<i class="o-art-draft"></i>
						<?php echo IBOS::lang( 'Draft' ); ?>
						<?php if($countDraft != 0): ?><span class="bubble"><?php echo $countDraft;?></span><?php endif; ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<button class="btn btn-primary pull-left" onclick="location.href = '<?php echo $this->createUrl( 'officialdoc/add', array( 'catid' => $this->catId ) ); ?>'">起草公文</button>
					<div class="btn-group" id="doc_more" style="display:none;">
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<?php echo IBOS::lang( 'More Operating' ); ?>
							<i class="caret"></i>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a href="javascript:;" data-action="moveDoc">
									<i class="o-menu-move"></i>
									<?php echo IBOS::lang( 'Move' ); ?>
								</a>
							</li>
							<li>
								<a href="javascript:;" data-action="topDoc">
									<i class="o-menu-top"></i>
									<?php echo IBOS::lang( 'Top' ); ?>
								</a>
							</li>
							<li>
								<a href="javascript:;" data-action="highlightDoc">
									<i class="o-menu-light"></i>
									<?php echo IBOS::lang( 'Highlight' ); ?>
								</a>
							</li>
							<li>
								<a href="javascript:;" data-action="removeDocs">
									<i class="o-menu-trash"></i>
									<?php echo IBOS::lang( 'Delete' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<form  action="<?php echo $this->createUrl( 'officialdoc/index', array( 'param' => 'search' ) ); ?>" method="post">
					<div class="search search-config pull-right span3">
						<input type="text" placeholder="输入标题查询" name="keyword"  id="mn_search" nofocus <?php if ( Env::getRequest( 'param' ) ): ?>value="<?php echo Main::getCookie( 'keyword' ); ?>"<?php endif; ?>>
						<a href="javascript:;">search</a>
						<input type="hidden" name="type" value="normal_search">
					</div>
				</form>
			</div>
			<div class="page-list-mainer art-list">
				<?php if ( count( $officialDocList ) > 0 ): ?>
					<table class="table table-hover officialdoc-table" id="officialdoc_table">
						<thead>
							<tr>
								<th width="20">
									<label class="checkbox">
										<input type="checkbox" data-name="officialdoc[]">
									</label>
								</th>
								<th width="32">
									<i class="i-lt o-art-list"></i>
								</th>
								<th><?php echo IBOS::lang( 'Title' ); ?></th>
								<th width="120">发布者</th>
								<?php ?>
								<?php if ( $type == 'nosign' || $type == 'draft' || $type == 'notallow'): ?>
								<th width="70"><!-- <?php echo IBOS::lang( 'View' ); ?> -->已查看</th>
								<?php else:?>
								<th width="70"><!-- <?php echo IBOS::lang( 'View' ); ?> -->已签收</th>
								<?php endif;?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $officialDocList as $officialDoc ): ?>
								<tr data-node-type="docRow" data-id="<?php echo $officialDoc['docid']; ?>">
									<td>
										<label class="checkbox">
											<input type="checkbox" name="officialdoc[]" value="<?php echo $officialDoc['docid']; ?>">
										</label>
									</td>
									<td>
										<i class="o-art-normal<?php if ( $officialDoc['signStatus'] == 1 ): ?>-gray<?php else: if( $officialDoc['readStatus'] == 1 ):?>-read<?php endif; endif; ?>"></i>
									</td>
									<td>
										<a href="<?php echo $this->createUrl( 'officialdoc/show', array( 'docid' => $officialDoc['docid'] ) ); ?>" 
										   class="art-list-title"><?php echo $officialDoc['subject']; ?></a>
										<!-- 当置顶时显示下面图标 -->
										<?php if ( $officialDoc['istop'] == 1 ) { ?>
											<span class="o-art-top"></span>
										<?php } ?>
									</td>
									<td>
										<div class="art-list-modify">
											<em><?php echo $officialDoc['author'] ?></em>
											<span><?php echo $officialDoc['uptime']; ?></span>
										</div>
									</td>
									<td>
										<?php if( $type == 'nosign' || $type == 'draft' || $type == 'notallow'): ?>
											<span class="art-clickcount"><?php echo $officialDoc['clickcount']; ?></span>
										<?php else:?>
											<span class="art-clickcount"><?php echo $officialDoc['signNum']; ?></span>
										<?php endif;?>
										<div class="art-form-funbar">
											<?php if($officialDoc['allowEdit']): ?>
												<a href="javascript:;" data-url="<?php echo $this->createUrl( 'officialdoc/edit', array( 'docid' => $officialDoc['docid'] ) ); ?>" title="<?php echo IBOS::lang( 'Edit' ); ?>" target="_self" class="cbtn o-edit" data-action="editTip"></a>
											<?php endif; ?>
											<?php if($officialDoc['allowDel']): ?>
												<a href="javascript:" title="<?php echo IBOS::lang( 'Delete' ); ?>" class="cbtn o-trash" data-action="removeDoc" data-param='{"id": "<?php echo $officialDoc['docid']; ?>"}'></a>
											<?php endif; ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<?php if ( $pages->getPageCount() > 1 ): ?>
				<div class="page-list-footer">
					<div class="pull-right">
						<?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<!-- Mainer content -->
	</div>
</div>

<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl( 'officialdoc/index', array( 'param' => 'search' ) ); ?>" method="post">
		<div class="form-horizontal form-compact">
			<div class="control-group">
				<label for="" class="control-label"><?php echo IBOS::lang( 'Keyword' ); ?></label>
				<div class="controls">
					<input type="text" id="keyword" name="search[keyword]">					
				</div>
			</div>
			<div class="control-group">
				<label for="" class="control-label"><?php echo IBOS::lang( 'Start time' ); ?></label>
				<div class="controls">
					<div class="datepicker" id="date_start">
						<a href="javascript:;" class="datepicker-btn"></a>
						<input type="text" class="datepicker-input" name="search[starttime]">
					</div>
				</div>
			</div>
			<div class="control-group">
				<label for="" class="control-label"><?php echo IBOS::lang( 'End time' ); ?></label>
				<div class="controls">
					<div class="datepicker" id="date_end">
						<a href="javascript:;" class="datepicker-btn"></a>
						<input type="text" class="datepicker-input" name="search[endtime]">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="type" value="advanced_search">
    </form>
</div>
<!-- 移动目录 -->
<div id="dialog_doc_move" style="width: 400px; display:none;">
	<div class="form-horizontal form-compact">
		<div class="control-group">
			<label class="control-label"><?php echo IBOS::lang( 'Directory' ); ?></label>
			<div class="controls">
				<select name="articleCategory"  id="articleCategory">
					<?php echo $categorySelectOptions; ?>
				</select>				
			</div>
		</div>
	</div>
</div>

<!-- 设置置顶 -->
<div id="dialog_art_top" class="form-horizontal form-compact" style="width: 400px; display:none;">
	<form action="javascript:;">
		<div class="control-group">
			<label class="control-label" id="test"><?php echo IBOS::lang( 'Expired time' ); ?></label>
			<div class="controls">
				<div class="datepicker" id="date_time_totop">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" name="topEndTime" value="<?php echo date( 'Y-m-d' ); ?>">
				</div>
			</div>
		</div>
	</form>
</div>

<!-- 高亮对话框  -->
<div id="dialog_art_highlight" class="form-horizontal form-compact" style="width: 400px; display:none;">
	<form action="javascript:;">
		<div class="control-group">
			<label class="control-label" id="test"><?php echo IBOS::lang( 'Expired time' ); ?></label>
			<div class="controls">
				<div class="datepicker" id="date_time_highlight">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" name="highlightEndTime" value="<?php echo date( 'Y-m-d' ); ?>">
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls" id="simple_editor"></div>
			<input type="hidden" id="highlight_color" name="color">
			<input type="hidden" id="highlight_bold" name="bold" value="0">
			<input type="hidden" id="highlight_italic" name="italic" value="0">
			<input type="hidden" id="highlight_underline" name="underline" value="0">
		</div>
	</form>
</div>

<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/officialdoc.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/doc_officialdoc_index.js?<?php echo VERHASH; ?>"></script>

<!-- load script end -->
