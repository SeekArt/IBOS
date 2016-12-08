<div class="aside" id="aside">
    <div class="fill-ss">
        <a href="<?php echo $this->createUrl('content/add'); ?>" class="btn btn-warning btn-block">
            <i class="o-new"></i><?php echo $lang['Write email']; ?>
        </a>
    </div>
    <div class="sbb sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($op == 'inbox' || $op == 'new' || $op == 'reply' || $op == 'replyall'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('list/index', array('op' => 'inbox')); ?>">
                    <span class="badge pull-right" data-count="inboxcount" style="display: none;"></span>
                    <i class="o-mal-inbox"></i> <?php echo $lang['Inbox']; ?>
                </a>
            </li>
            <?php if ($allowWebMail): ?>
                <li <?php if ($op == 'web'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('list/index', array('op' => 'web')); ?>">
                        <i class="o-mal-outward"></i> <?php echo $lang['External inbox']; ?>
                    </a>
                </li>
            <?php endif; ?>
            <li <?php if ($op == 'todo'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('list/index', array('op' => 'todo')); ?>">
                    <span class="badge pull-right" data-count="todocount" style="display: none;"></span>
                    <i class="o-mal-todo"></i> <?php echo $lang['Todo email']; ?>
                </a>
            </li>
            <li <?php if ($op == 'draft'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('list/index', array('op' => 'draft')); ?>">
                    <i class="o-mal-draft"></i> <?php echo $lang['Drafts']; ?>
                </a>
            </li>
            <li <?php if ($op == 'send'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('list/index', array('op' => 'send')); ?>">
                    <i class="o-mal-sended"></i> <?php echo $lang['Has been sent']; ?>
                </a>
            </li>
            <li <?php if ($op == 'del'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('list/index', array('op' => 'del')); ?>">
                    <span class="badge pull-right" data-count="delcount" style="display: none;"></span>
                    <i class="o-mal-trash"></i> <?php echo $lang['Deleted']; ?>
                </a>
            </li>
        </ul>
    </div>
    <div>
        <div>
            <div class="sbbtw clearfix">
                <!-- 跳转 -->
                <a href="javascript:;" class="pull-right o-mal-setup" title="<?php echo $lang['Setup']; ?>"
                   data-click="setupFolder"
                   data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl('folder/index'); ?>&quot;}"></a>
                <a href="javascript:;" class="pull-left active" data-action="toggleSidebarList">
                    <span class="o-caret"><i class="caret"></i></span>
                    <strong class="sbbt"><?php echo $lang['My folders']; ?></strong>
                </a>
            </div>
            <ul class="sbb-list" data-node-type="folderList">
                <?php foreach ($folders as $folder): ?>
                    <li data-node-type="folderItem" data-folder-id="<?php echo $folder['fid'] ?>"
                        <?php if ($fid == $folder['fid']): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo $this->createUrl('list/index', array('op' => 'folder', 'fid' => $folder['fid'])); ?>">
                            <?php echo $folder['name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php if ($hasArchive): ?>
        <!-- <div class="sbb sbbl">
			<div>
				<div class="sbbtw">
					<a href="javascript:;" class="active" data-action="toggleSidebarList">
						<span class="o-caret"><i class="caret"></i></span>
						<strong class="sbbt"><?php echo $lang['Archived']; ?></strong>
					</a>
				</div>
				<ul class="sbb-list nl">
					<?php foreach ($archiveTable['info'] as $tableId => $archiveInfo): ?>
						<?php if ($tableId != 0): ?>
							<li <?php if ($archiveId == $tableId): ?>class="active"<?php endif; ?>>
								<a href="<?php echo $this->createUrl('list/index', array('op' => 'archive', 'archiveid' => $tableId)); ?>"><?php echo $archiveInfo['displayname']; ?></a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div> -->
    <?php endif; ?>
</div>

<!-- 高级设置弹窗 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" class="form-horizontal form-compact"
          action="<?php echo $this->createUrl('list/search', array('op' => $op)); ?>" method="post">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Keyword']; ?>：</label>
            <div class="controls">
                <input type="text" name="search[keyword]" class="input-small">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Location']; ?>：</label>
            <div class="controls">
                <select name="search[pos]" class="input-small">
                    <option value="subject"><?php echo $lang['Email subject']; ?></option>
                    <option value="attachment"><?php echo $lang['Attach name']; ?></option>
                    <option value="content"><?php echo $lang['Email content']; ?></option>
                    <option value="all"><?php echo $lang['Email all content']; ?></option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Sender']; ?>：</label>
            <div class="controls">
                <input type="text" name="search[sender]" id="sender" class="input-small">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Recipient']; ?>：</label>
            <div class="controls">
                <input type="text" name="search[recipient]" id="addressee" class="input-small">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Folders']; ?>：</label>
            <div class="controls">
                <select name="search[folder]" id="" class="input-small">
                    <optgroup label="<?php echo $lang['System']; ?>">
                        <option value="allbynoarchive"><?php echo $lang['All emails (exclude archive)']; ?></option>
                        <option value="all"><?php echo $lang['All emails']; ?></option>
                        <option value="1"><?php echo $lang['Inbox']; ?></option>
                        <option value="3"><?php echo $lang['Has been sent']; ?></option>
                    </optgroup>
                    <?php if (!empty($folders)): ?>
                        <optgroup label="<?php echo $lang['Personal folder']; ?>">
                            <?php foreach ($folders as $folder): ?>
                                <option value="<?php echo $folder['fid']; ?>"><?php echo $folder['name']; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if ($hasArchive): ?>
                        <optgroup label="<?php echo $lang['Email archive']; ?>">
                            <?php foreach ($archiveTable['info'] as $tableId => $archiveInfo): ?>
                                <?php if ($tableId != 0): ?>
                                    <option
                                        value="archive_<?php echo $tableId; ?>"><?php echo $archiveInfo['displayname']; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Time']; ?>：</label>
            <div class="controls">
                <select name="search[dateRange]" id="" class="input-small">
                    <option value="-1"><?php echo $lang['No limit']; ?></option>
                    <option value="1"><?php echo $lang['Search timescope 1']; ?></option>
                    <option value="3"><?php echo $lang['Search timescope 2']; ?></option>
                    <option value="7"><?php echo $lang['Search timescope 3']; ?></option>
                    <option value="14"><?php echo $lang['Search timescope 4']; ?></option>
                    <option value="30"><?php echo $lang['Search timescope 5']; ?></option>
                    <option value="60"><?php echo $lang['Search timescope 6']; ?></option>
                    <option value="180"><?php echo $lang['Search timescope 7']; ?></option>
                    <option value="365"><?php echo $lang['Search timescope 8']; ?></option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Include attachment']; ?>：</label>
            <div class="controls">
                <select name="search[attachment]" id="" class="input-small">
                    <option value="-1"><?php echo $lang['No limit']; ?></option>
                    <option value="1"><?php echo $lang['Include']; ?></option>
                    <option value="0"><?php echo $lang['Exclude']; ?></option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Read']; ?>/<?php echo $lang['Unread']; ?>：</label>
            <div class="controls">
                <select name="search[readStatus]" id="" class="input-small">
                    <option value="-1"><?php echo $lang['No limit']; ?></option>
                    <option value="1"><?php echo $lang['Read']; ?></option>
                    <option value="0"><?php echo $lang['Unread']; ?></option>
                </select>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>
<img id="img_loading" style="display: none;" src="<?php echo STATICURL ?>/image/common/loading.gif"/>
