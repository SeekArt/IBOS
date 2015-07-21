<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $this->catid ); ?>
	<!-- Sidebar end -->

	<!-- Mainer right -->
	<div class="mcr">
		<div class="mc-header">
		<!-- Mainer nav -->
			<ul class="mnv nl clearfix">
				<?php $type = Env::getRequest( 'type' ); ?>
				<li <?php if ( !isset( $type ) ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'default/index' , array( 'catid' => $this->catid )); ?>">
						<i class="o-art-all"></i>
						<?php echo $lang['Published']; ?>
					</a>
				</li>
				<li <?php if ( $type == 'new' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'default/index', array( 'type' => 'new' , 'catid' => $this->catid) ); ?>">
						<i class="o-art-unread"></i>
						<?php echo $lang['No read']; ?>
						<?php if($newCount != 0): ?><span class="bubble"><?php echo $newCount; ?></span><?php endif; ?>
					</a>
				</li>
				<li <?php if ( $type == 'old' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'default/index', array( 'type' => 'old' ,'catid' => $this->catid ) ); ?>">
						<i class="o-art-read"></i>
						<?php echo $lang['Read']; ?>
					</a>
				</li>
				<li <?php if ( $type == 'notallow' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'default/index', array( 'type' => 'notallow' ,'catid' => $this->catid ) ); ?>">
						<i class="o-art-uncensored"></i>
						<?php echo IBOS::lang( 'No verify'); ?>
						<?php if($notallowCount != 0): ?><span class="bubble"><?php echo $notallowCount; ?></span><?php endif; ?>
					</a>
				</li>
				<li <?php if ( $type == 'draft' ): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'default/index', array( 'type' => 'draft' , 'catid' => $this->catid ) ); ?>">
						<i class="o-art-draft"></i>
						<?php echo IBOS::lang( 'Draft'); ?>
						<?php if($draftCount != 0): ?><span class="bubble"><?php echo $draftCount; ?></span><?php endif; ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">		
					<button class="btn btn-primary pull-left" data-action="verifyArticle">审核通过</button>
					<button class="btn pull-left" data-action="backArticle">退回</button>
				</div>
                <form action="<?php echo $this->createUrl('default/index',array('param'=>'search')); ?>" method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" placeholder="输入标题查询" name="keyword"  id="mn_search" nofocus <?php if(Env::getRequest( 'param')): ?>value="<?php echo Main::getCookie( 'keyword' ); ?>"<?php endif; ?>>
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
			</div>
			<div class="page-list-mainer art-list">
                <?php if(count($datas)>0): ?>
				<table class="table table-hover article-table" id="article_table">
					<thead>
						<tr>
							<th width="20">
								<label class="checkbox">
									<input type="checkbox" data-name="article[]">
								</label>
							</th>
							<th><?php echo IBOS::lang( 'Title'); ?></th>
							<th width="110">审核流程</th>
							<th width="110">发布者</th>
							<th width="70"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $datas as $data ): ?>
							<tr data-node-type="articleRow" data-id="<?php echo $data['articleid']; ?>">
								<td>
									<label class="checkbox">
										<input type="checkbox" name="article[]" value="<?php echo $data['articleid']; ?>">
									</label>
								</td>
								<td>
									<a href="<?php echo $this->createUrl( 'default/index', array( 'op'=>'show','articleid' => $data['articleid'] ) ); ?>" 
									   class="art-list-title dib"><?php echo $data['subject']; ?></a>
									<?php if( $data['back'] == 1 ): ?>
										<span class="noallow-tip">未通过</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if( !empty( $data['approval'] ) ): ?>
										<p class="fss mbm"><?php echo $data['approvalName']; ?></p>
										<div class="art-flow-show clearfix">
											<?php for( $i = 1; $i<=$data['approval']['level']; $i++ ): ?>
												<?php if( $data['stepNum'] >= $i ): ?>
													<!--已审核过的步骤-->
													<i data-toggle="tooltip" data-original-title="审核人:<?php echo $data['approval'][$i]['approvaler']; ?>" class="<?php if( $i == $data['approval']['level'] ): ?>o-art-one-approval<?php else: ?>o-art-approval<?php endif; ?>"></i>
												<?php else: ?>
													<!--未审核的步骤-->
													<i data-toggle="tooltip" data-original-title="审核人:<?php echo $data['approval'][$i]['approvaler']; ?>" class="<?php if( $i == ($data['stepNum']+1) ): ?>o-art-one-noapproval<?php else: ?>o-art-noapproval<?php endif; ?>"></i>
												<?php endif; ?>
											<?php endfor; ?>
										</div>
									<?php else: ?>
										<div><?php echo $lang['Do not need approval']; ?></div>
									<?php endif; ?>
								</td>
								<td>
									<div class="art-list-modify">
										<em><?php echo $data['author']; ?></em>
                                        <span><?php echo $data['uptime']; ?></span>
									</div>
								</td>
								<td>
									<div class="art-list-funbar">
											<a href="javascript:;" data-url="<?php echo $this->createUrl( 'default/edit', array( 'articleid' => $data['articleid'] ) ); ?>" title="<?php echo IBOS::lang( 'Edit'); ?>" target="_self" class="cbtn o-edit" data-action="editTip"></a>
											<a href="javascript:;" title="<?php echo IBOS::lang( 'Delete'); ?>" class="cbtn o-trash" data-action="removeArticle" data-param='{ "id": "<?php echo $data['articleid']; ?>"}'></a>
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
            <?php if( $pages->getPageCount() > 1 ): ?>
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

<!--退回-->
<div id="rollback_reason" style="display:none;">
	<form action="javascript:;" method="post" id="rollback_form">
		<textarea rows="8" cols="60" id="rollback_textarea" name="reason" placeholder="退回理由...."></textarea>
	</form>
</div>
<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl('default/index',array('param'=>'search')); ?>" method="post">
	<div class="form-horizontal form-compact">
		<div class="control-group">
			<label for="" class="control-label"><?php echo IBOS::lang( 'Keyword'); ?></label>
			<div class="controls">
				<input type="text" id="keyword" name="search[keyword]">					
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label"><?php echo IBOS::lang( 'Start time'); ?></label>
			<div class="controls">
				<div class="datepicker" id="date_start">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" name="search[starttime]">
				</div>
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label"><?php echo IBOS::lang( 'End time'); ?></label>
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
<div id="dialog_art_move" style="width: 400px; display:none;">
	<div class="form-horizontal form-compact">
		<div class="control-group">
			<label class="control-label"><?php echo IBOS::lang( 'Directory'); ?></label>
			<div class="controls">
				<select name="articleCategory"  id="articleCategory">
					<?php echo $categoryOption; ?>
				</select>
			</div>
		</div>
	</div>
</div>
<!-- 设置置顶 -->
<div id="dialog_art_top" class="form-horizontal form-compact" style="width: 400px; display:none;">
	<form action="javascript:;">
		<div class="control-group">
			<label class="control-label" id="test"><?php echo IBOS::lang( 'Expired time'); ?></label>
			<div class="controls">
				<div class="datepicker" id="date_time_top">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" name="topEndTime" value="<?php echo date('Y-m-d'); ?>" />
				</div>
			</div>
		</div>
	</form>
</div>
<!-- 高亮对话框  -->
<div id="dialog_art_highlight" class="form-horizontal form-compact" style="width: 400px; display:none;">
	<form action="javascript:;">
		<div class="control-group">
			<label class="control-label" id="test"><?php echo IBOS::lang( 'Expired time'); ?></label>
			<div class="controls">
				<div class="datepicker" id="date_time_highlight">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" name="highlightEndTime" value="<?php echo date('Y-m-d'); ?>" />
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls" id="simple_editor"></div>
			<input type="hidden" id="highlight_color" name="highlight_color" value="00b050">
			<input type="hidden" id="highlight_bold" name="highlight_bold" value="0">
			<input type="hidden" id="highlight_italic" name="highlight_italic" value="0">
			<input type="hidden" id="highlight_underline" name="highlight_underline" value="0">
		</div>
	</form>
</div>
<!-- load script -->
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_default_index.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
	$(function(){
		$(".o-art-approval").tooltip();
		$(".o-art-noapproval").tooltip();
		$(".o-art-one-approval").tooltip();
		$(".o-art-one-noapproval").tooltip();
	});
</script>
<!-- load script end -->
