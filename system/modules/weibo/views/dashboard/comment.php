<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Enterprise weibo']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('dashboard/setup'); ?>"><?php echo $lang['Manage weibo']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('dashboard/manage'); ?>"><?php echo $lang['Manage weibo']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Manage comment']; ?></span>
            </li>
            <!--<li>
                <a href="wb_topic_manage.html">管理话题</a>
            </li>-->
        </ul>
    </div>
    <div>
        <!-- 管理评论 -->
        <div class="ctb">
            <div class="form-horizontal">
                <h2 class="st"><?php echo $lang['Manage comment']; ?></h2>
                <div class="btn-group control-group" data-toggle="buttons-radio" id="record">
                    <a href="<?php echo $this->createUrl('dashboard/comment', array('op' => 'list')); ?>"
                       class="btn <?php if ($op == 'list'): ?>active<?php endif; ?>"><?php echo $lang['List']; ?></a>
                    <a href="<?php echo $this->createUrl('dashboard/comment', array('op' => 'recycle')); ?>"
                       class="btn <?php if ($op == 'recycle'): ?>active<?php endif; ?>"><?php echo $lang['Recycle bin']; ?></a>
                </div>
            </div>
            <div>
                <div class="page-list">
                    <div class="page-list-header">
                        <div class="btn-toolbar pull-left">
                            <button class="btn" type="button"
                                    data-param='{"op":"<?php echo $op == 'list' ? 'delComment' : 'deleteComment'; ?>"}'
                                    data-action="removeCms"><?php echo $op == 'list' ? $lang['Del comment'] : $lang['Delete clear']; ?></button>
                        </div>
                        <div class="search span3 pull-right">
                            <input type="text" placeholder="<?php echo $lang['Comment search tip']; ?>" name="keyword"
                                   id="mn_search" nofocus="">
                            <a href="javascript:;">search</a>
                        </div>
                    </div>
                    <?php if (!empty($list)): ?>
                        <div class="page-list-mainer">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th width="20"><label class="checkbox"><input type="checkbox"
                                                                                  data-name="comment"></label></th>
                                    <th width="30">ID</th>
                                    <th><?php echo $lang['Comment content']; ?></th>
                                    <th width="100"><?php echo $lang['From']; ?></th>
                                    <th width="120"><?php echo $lang['Publish time']; ?></th>
                                    <th width="30"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $cm): ?>
                                    <tr data-row="<?php echo $cm['cid']; ?>">
                                        <td><label class="checkbox"><input type="checkbox" name="comment"
                                                                           value="<?php echo $cm['cid']; ?>"></label>
                                        </td>
                                        <td><?php echo $cm['cid']; ?></td>
                                        <td>
                                            <?php echo $cm['content']; ?>
                                        </td>
                                        <td class="fss"><?php echo $cm['user_info']['realname']; ?></td>
                                        <td class="fss"><?php echo date('n-j H:i', $cm['ctime']); ?></td>
                                        <td>
                                            <?php if ($op == 'list'): ?>
                                                <a href="javascript:;" class="cbtn o-trash" data-action="removeCm"
                                                   data-param='{"op":"delComment", "id": <?php echo $cm['cid']; ?>}'></a>
                                            <?php else: ?>
                                                <a href="javascript:;" class="cbtn o-cancel" data-action="recoverCm"
                                                   data-param='{"op":"commentRecover", "id": <?php echo $cm['cid']; ?>}'></a>
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
<script>
    // 复选框联动
    $("#mn_search").search(
        function (val) {
            window.location.href = Ibos.app.url('weibo/dashboard/comment', {search: val, op: '<?php echo $op; ?>'});
        }
    );
</script>
