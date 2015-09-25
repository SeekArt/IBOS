<?php 
use application\core\utils\Convert;
use application\core\utils\IBOS;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/email.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $op ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<div class="btn-group">
						<a href="javascript:;" class="btn btn-narrow ">
							<label class="checkbox"><input type="checkbox" data-name="email"></label>
						</a>
						<a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown">
							<i class="caret"></i>
						</a>
						<ul class="dropdown-menu">
							<li><a href="javascript:;" data-click="selectReverse"><?php echo $lang['Reverse selected']; ?></a></li>
							<li><a href="javascript:;" data-click="selectAttach"><?php echo $lang['Attachment']; ?></a></li>
                            <?php if($op == "inbox" || $op == "todo") : ?>
							<li><a href="javascript:;" data-click="selectUnread"><?php echo $lang['Unread']; ?></a></li>
							<li><a href="javascript:;" data-click="selectRead"><?php echo $lang['Read']; ?></a></li>
                            <?php endif; ?>
						</ul>
					</div>
					<?php if ( $op == 'archive' ): ?>
						<div class="btn-group">
                            <select data-change='subop' data-param="<?php echo $this->createUrl('list/index', array('op' => 'archive', 'archiveid' => $archiveId)); ?>">
								<option value="in" <?php if ( $subOp == 'in' ): ?>selected<?php endif; ?>><?php echo $lang['Inbox']; ?></option>
								<option value="send" <?php if ( $subOp == 'send' ): ?>selected<?php endif; ?>><?php echo $lang['Outbox']; ?></option>
							</select>
						</div>
					<?php endif; ?>
					<?php if ( $op == 'del' ): ?>
						<div class="btn-group">
                            <button type="button" class="btn" data-click="restore" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'restore')); ?>&quot;}"><?php echo $lang['Restore']; ?></button>
						</div>
					<?php endif; ?>
					<?php if ( in_array( $op, array( 'del', 'send' ) ) ): ?>
						<div class="btn-group">
                            <button type="button" class="btn" data-click="erase" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/cpDel'); ?>&quot;}"><?php echo $lang['Completely remove']; ?></button>
						</div>
					<?php endif; ?>
					<?php if ( $op == 'draft' ): ?>
						<div class="btn-group">
                            <button type="button" class="btn" data-click="erase" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/delDraft', array('archiveid' => $this->archiveId)); ?>&quot;}"><?php echo $lang['Delete draft']; ?></button>
						</div>
					<?php endif; ?>
					<?php if ( $allowRecall && $op == 'send' ): ?>
						<div class="btn-group">
                            <button type="button" class="btn" data-click="recall" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/recall') ?>&quot;}"><?php echo $lang['Recall']; ?></button>
						</div>
					<?php endif; ?>
					<?php if ( $op == 'web' ): ?>
						<div class="btn-group">
							<a href="javascript:;" class="btn btn-primary" data-toggle="dropdown" ><?php echo $lang['Receive mail']; ?></a>
							<ul class="dropdown-menu">
                                <li><a href="javascript:;" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('web/receive'); ?>&quot;,&quot;name&quot;:&quot;<?php echo $lang['All emails']; ?>&quot;}" data-click="receiveMail"><?php echo $lang['All']; ?></a></li>
								<?php foreach ( $this->webMails as $val ): ?>
                                    <li><a href="javascript:;" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('web/receive', array('webid' => $val['webid'])); ?>&quot;,&quot;name&quot;:&quot;<?php echo $val['address']; ?>&quot;}" data-click="receiveMail"><?php echo $val['address']; ?></a></li>
								<?php endforeach; ?>
							</ul>
                            <a target="_blank" href="<?php echo $this->createUrl('web/index'); ?>" class="btn btn-primary btn-icon" ><i class="o-setup"></i></a>
						</div>
					<?php endif; ?>
					<?php if ( in_array( $op, array( 'inbox', 'todo', 'folder', 'web' ) ) ): ?>
						<div class="btn-group">
                            <button type="button" class="btn" data-click="del" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'batchdel')); ?>&quot;}"><?php echo $lang['Delete']; ?></button>
						</div>
						<div class="btn-group">
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><?php echo $lang['Marked']; ?><i class="caret"></i></button>
							<ul class="dropdown-menu">
                                <li><a href="javascript:;" data-click="markRead" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'read')); ?>&quot;}"><?php echo $lang['Read messages']; ?></a></li>
                                <li><a href="javascript:;" data-click="markUnread" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'unread')); ?>&quot;}"><?php echo $lang['Unread messages']; ?></a></li>
								<?php if ( $op == 'todo' ): ?>
                                    <li><a href="javascript:;" data-click="unmark" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'todo')); ?>&quot;}"><?php echo $lang['Cancel todo']; ?></a></li>
								<?php else: ?>
                                    <li><a href="javascript:;" data-click="mark" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'todo')); ?>&quot;}"><?php echo $lang['Todo email']; ?></a></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( in_array( $op, array( 'inbox', 'todo', 'send', 'folder', 'web' ) ) ): ?>
						<div class="btn-group">
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
								<?php echo $lang['Move to']; ?>
								<i class="caret"></i>
							</button>
							<ul class="dropdown-menu" data-node-type="moveTargetList">
								<?php if ( $op == 'folder' ): ?>
									<li data-node-type="moveTargetItem" data-id="1">
                                        <a href="javascript:;" data-click="moveToFolder" data-param="{&quot;fid&quot;:&quot;1&quot;,&quot;url&quot;: &quot;<?php echo $this->createUrl('api/mark', array('op' => 'move')); ?>&quot;}"><?php echo $lang['Move to inbox']; ?></a>
									</li>
								<?php endif; ?>
								<?php foreach ( $folders as $folder ): ?>
									<li data-node-type="moveTargetItem" data-id="<?php echo $folder['fid']; ?>">
                                        <a href="javascript:;" data-click="moveToFolder" data-param="{&quot;fid&quot;:&quot;<?php echo $folder['fid']; ?>&quot;,&quot;url&quot;: &quot;<?php echo $this->createUrl('api/mark', array('op' => 'move')); ?>&quot;}"><?php echo $folder['name']; ?></a>
									</li>
								<?php endforeach; ?>
								<li>
                                    <a href="javascript:;" data-click="moveToNewFolder" data-param="{&quot;newUrl&quot;:&quot;<?php echo $this->createUrl('folder/add'); ?>&quot;,&quot;url&quot;:&quot;<?php echo $this->createUrl('api/mark', array('op' => 'move')); ?>&quot;}"><?php echo $lang['New Folder and move']; ?></a>
								</li>
							</ul>
						</div>
					<?php endif; ?>
				</div>
                <form id="normal_search" action="<?php echo $this->createUrl('list/search'); ?>" method="post">
					<div class="search search-config pull-right span3">
						<input type="text" placeholder="Search" name="search[keyword]"  data-toggle="search" id="mal_search">
						<a href="javascript:;">search</a>
						<input type="hidden" name="type" value="normal_search" />
					</div>
				</form>
			</div>
			<div class="page-list-mainer">
				<?php if ( count( $list ) > 0 ): ?>
					<?php if ( !in_array( $op, array( 'send', 'draft' ) ) ): ?>
						<div class="band band-primary">
							<?php if ( $unreadCount > 0 ): ?>
								<?php echo IBOS::lang( 'New unread messages', '', array( '{count}' => $unreadCount ) ) ?>,<a href="javascript:;" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl( 'api/setAllRead' ); ?>&quot;}" data-click="markReadAll" class="anchor ilsep"><?php echo $lang['Set all read']; ?></a>
							<?php else: ?>
								<?php echo $lang['No unread mail']; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<table class="table table-striped table-hover">
						<tbody>
							<?php $importantDriver = array( 0 => '', 1 => 'xcgn', 2 => 'xcr' ); ?>
							<?php foreach ( $list as $data ): ?>
								<?php $importantClass = $importantDriver[$data['important']]; ?>
								<?php
								if ( $op == 'draft' ) {
									$id = $data['bodyid'];
									$clickUrl = $this->createUrl( 'content/edit', array( 'id' => $id ) );
									$isRead = 1;
								} else {
									$id = $data['emailid'];
									$clickUrl = $this->createUrl( 'content/show', array( 'id' => $id ) );
									$isRead = intval( $data['isread'] );
								}
								?>
								<tr id="list_tr_<?php echo $id; ?>">
									<td width="20">
										<label class="checkbox">
                                            <input type="checkbox" name="email" data-read="<?php echo $isRead; ?>" data-attach="<?php if (!empty($data['attachmentid'])): ?>1<?php else: ?>0<?php endif; ?>" value="<?php echo $id; ?>">
										</label>
									</td>
									<td width="40" class="j-read">
										<?php if ( $isRead == 0 ): ?><i class="o-mal-new"></i><?php endif; ?><?php if ( !empty( $data['attachmentid'] ) ): ?><i class="o-mal-attach"></i><?php endif; ?>
									</td>
									<?php if ( $op != 'send' && $op != 'draft' ): ?>
										<td width="70">
											<a href="<?php echo $clickUrl; ?>" class="art-list-title">
												<?php if ( $isRead == 0 ): ?><strong class="<?php echo $importantClass; ?>"><?php echo $data['fromuser']; ?></strong><?php else: ?><span class="<?php echo $importantClass; ?>"><?php echo $data['fromuser']; ?></span><?php endif; ?>
											</a>
										</td>
									<?php endif; ?>
									<td>
										<a href="<?php echo $clickUrl; ?>" class="art-list-title">
											<?php if ( $isRead == 0 ): ?><strong class="<?php echo $importantClass; ?>"><?php echo $data['subject']; ?></strong><?php else: ?><span class="<?php echo $importantClass; ?>"><?php echo $data['subject']; ?></span><?php endif; ?>
										</a>
									</td>
									<td width="120"><div class="fss"><?php echo Convert::formatDate( $data['sendtime'], 'u' ); ?></div></td>
									<td width="10" class="j-mark">
										<?php if ( !in_array( $op, array( 'draft', 'send', 'folder' ) ) ): ?>
                                            <a href="javascript:;" title="<?php echo $lang['Click to mark this message']; ?>" class="<?php if ($data['ismark'] == 1): ?>o-mark<?php else: ?>o-unmark<?php endif; ?>" 
                                               data-click="toggleMark" data-param="{&quot;url&quot;: &quot;<?php echo $this->createUrl('api/mark', array('op' => 'todo', 'emailids' => $id)); ?>&quot;}">
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="page-list-footer">
					<div class="page-num-select">
						<div class="btn-group dropup">
							<?php $pageSize = $pages->getPageSize(); ?>
							<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize; ?>">
								<i class="o-setup"></i><span><?php echo $lang['Each page']; ?> <?php echo $pageSize; ?></span><i class="caret"></i>
							</a>
                            <ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl('list/index', array('op' => $op)); ?>" >
								<li data-value="5" <?php if ( $pageSize == 5 ): ?>class="active"<?php endif; ?>><a href="javascript:;"><?php echo $lang['Each page']; ?> 5</a></li>
								<li data-value="10" <?php if ( $pageSize == 10 ): ?>class="active"<?php endif; ?>><a href="javascript:;"><?php echo $lang['Each page']; ?> 10</a></li>
								<li data-value="20" <?php if ( $pageSize == 20 ): ?>class="active"<?php endif; ?>><a href="javascript:;"><?php echo $lang['Each page']; ?> 20</a></li>
							</ul>
						</div>
					</div>
					<div class="pull-right">
						<?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?>
					</div>
				</div>
			<?php else: ?>
				<div class="no-data-tip"></div>
			<?php endif; ?>
		</div>
	</div>
</div>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js?<?php echo VERHASH; ?>'></script>
<script>
	(function() {
		// 初始化搜索
		var userData = Ibos.data.get("user");
		$('#mal_search').search(function() {
				$('#normal_search').submit();
			}, function() {
				Ui.dialog({
					title: U.lang("ADVANCED_SETTING"),
					content: Dom.byId('mn_search_advance'),
					id: 'd_advanced',
					init: function() {
						$("#sender, #addressee").userSelect({
							type: 'user',
							maximumSelectionSize: 1,
							data: userData
						});
					},
					ok: function() {
						$('#mn_search_advance_form').submit();
					},
					width: 500
				});
			}
		);

		// 列表条数设置
		var $pageNumCtrl = $("#page_num_ctrl"),
				$pageNumMenu = $("#page_num_menu"),
				pageNumSelect = new P.PseudoSelect($pageNumCtrl, $pageNumMenu, {
			template: '<i class="o-setup"></i> <span><%=text%></span> <i class="caret"></i>'
		});
		$pageNumCtrl.on("select", function(evt) {
			window.location.href = Ibos.app.url('email/list/index', { op: '<?php echo $op; ?>', pagesize: evt.selected });
		});
	})();

</script>