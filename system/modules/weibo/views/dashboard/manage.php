<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Enterprise weibo']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('dashboard/setup'); ?>"><?php echo $lang['Weibo setup']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Manage weibo']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('dashboard/comment'); ?>"><?php echo $lang['Manage comment']; ?></a>
            </li>
            <!--<li>
				<a href="<?php echo $this->createUrl('dashboard/topic'); ?>"><?php echo $lang['Manage topic']; ?></a>
			</li>-->
        </ul>
    </div>
    <div>
        <!-- 管理微博 -->
        <div class="ctb">
            <div class="form-horizontal">
                <h2 class="st"><?php echo $lang['Manage weibo']; ?></h2>
                <div class="btn-group control-group" data-toggle="buttons-radio" id="record">
                    <a href="<?php echo $this->createUrl('dashboard/manage', array('op' => 'list')); ?>"
                       class="btn <?php if ($op == 'list'): ?>active<?php endif; ?>"><?php echo $lang['List']; ?></a>
                    <a href="<?php echo $this->createUrl('dashboard/manage', array('op' => 'recycle')); ?>"
                       class="btn <?php if ($op == 'recycle'): ?>active<?php endif; ?>"><?php echo $lang['Recycle bin']; ?></a>
                </div>
            </div>
            <div>
                <div class="page-list">
                    <div class="page-list-header">
                        <div class="btn-toolbar pull-left">
                            <button class="btn" type="button"
                                    data-param='{"op":"<?php echo $op == 'list' ? 'delFeed' : 'deleteFeed'; ?>"}'
                                    data-action="removeWbs"><?php echo $op == 'list' ? $lang['Del weibo'] : $lang['Delete clear']; ?></button>
                        </div>
                        <div class="search span3 pull-right">
                            <input type="text" placeholder="<?php echo $lang['Weibo search tip']; ?>" name="keyword"
                                   nofocus id="mn_search"/>
                            <a href="javascript:;">search</a>
                        </div>
                    </div>
                    <?php if (!empty($list)): ?>
                        <div class="page-list-mainer">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th width="20"><label class="checkbox"><input type="checkbox"
                                                                                  data-name="weibo"></label></th>
                                    <th width="30">ID</th>
                                    <th><?php echo $lang['Weibo content']; ?></th>
                                    <th width="100"><?php echo $lang['From']; ?></th>
                                    <th width="120"><?php echo $lang['Publish time']; ?></th>
                                    <th width="30"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $feed): ?>
                                    <tr data-row="<?php echo $feed['feedid']; ?>">
                                        <td><label class="checkbox"><input type="checkbox" name="weibo"
                                                                           value="<?php echo $feed['feedid']; ?>"></label>
                                        </td>
                                        <td><?php echo $feed['feedid']; ?></td>
                                        <td>
                                            <div class="wbc-left"><?php echo $feed['body']; ?></div>
                                        </td>
                                        <td class="fss"><?php echo $feed['user_info']['realname']; ?></td>
                                        <td class="fss"><?php echo date('n-j H:i', $feed['ctime']); ?></td>
                                        <td>
                                            <?php if ($op == 'list'): ?>
                                                <a href="javascript:;" class="cbtn o-trash" data-action="removeWb"
                                                   data-param='{"op":"delFeed","id": <?php echo $feed['feedid']; ?>}'></a>
                                            <?php else: ?>
                                                <a href="javascript:;" class="cbtn o-cancel" data-action="recoverWb"
                                                   data-param='{"op":"feedRecover", "id": <?php echo $feed['feedid']; ?>}'></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="page-list-footer"><?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?></div>
                    <?php else: ?>
                        <div style="margin: 20px;text-align: center;"><?php echo $lang['No data']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $moduleAssetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo_dashboard_common.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script>
    // 复选框联动
    $("#mn_search").search(
        function (val) {
            {
                window.location.href = Ibos.app.url('weibo/dashboard/manage', {search: val, op: '<?php echo $op; ?>'});
            }
            );
</script>
