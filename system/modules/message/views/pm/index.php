<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar goes here-->
    <?php echo $this->getSidebar(array('lang' => $lang)); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list" id="pm_list">
            <div class="page-list-header">
                <div class="pull-left">
                    <label class="checkbox btn"><input type="checkbox" data-name="pm"></label>
                    <button type="button" data-action="sendPm"
                            class="btn btn-primary"><?php echo $lang['Send pm']; ?></button>
                    <button type="button" class="btn" data-action="removePms"><?php echo $lang['Delete']; ?></button>
                    <?php if ($unreadCount > 0): ?>
                        <button type="button" class="btn"
                                data-action="markPmsRead"><?php echo $lang['Set read']; ?></button><?php endif; ?>
                </div>
            </div>
            <div class="page-list-mainer">
                <?php if ($unreadCount > 0): ?>
                    <div class="band band-primary">
                        <?php echo $lang['View'] ?>
                        <strong><?php echo $unreadCount; ?></strong> <?php echo $lang['New conversation']; ?>
                        <a href="javascript:;" data-action="markAllPmRead"
                           class="anchor ilsep"><?php echo $lang['Set all read']; ?></a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($list)): ?>
                    <ul class="main-list main-list-hover msg-pm-list">
                        <?php foreach ($list as $row): ?>
                            <li class="main-list-item curp" id="pm_<?php echo $row['listid']; ?>"
                                data-url="id=<?php echo $row['listid']; ?>&type=<?php echo $row['type']; ?>">
                                <div class="avatar-box pull-left posr">
                                    <?php if ($row['lastmessage']['touid'][0] == Ibos::app()->user->uid): ?>
                                        <a href="<?php echo $row['lastmessage']['user']['space_url']; ?>"
                                           class="avatar-circle" data-toggle="usercard"
                                           data-param='uid=<?php echo $row['lastmessage']['user']['uid']; ?>'>
                                            <img class="mbm"
                                                 src="<?php echo $row['lastmessage']['user']['avatar_middle']; ?>">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo $row['touserinfo'][$row['lastmessage']['touid'][0]]['space_url']; ?>"
                                           data-toggle='usercard'
                                           data-param='uid=<?php echo $row['touserinfo'][$row['lastmessage']['touid'][0]]['uid']; ?>'
                                           class="avatar-circle">
                                            <img class="mbm"
                                                 src="<?php echo $row['touserinfo'][$row['lastmessage']['touid'][0]]['avatar_middle']; ?>">
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($row['new']): ?><span class="bubble">new</span><?php endif; ?>
                                </div>
                                <div class="main-list-item-body">
                                    <div>
                                        <p class="mb">
                                            <?php if ($row['lastmessage']['fromuid'] == Ibos::app()->user->uid) { ?>
                                                <?php echo $lang['Me'], $lang['To']; ?>
                                                <strong><?php echo($row['touserinfo'][$row['lastmessage']['touid'][0]]['realname']); ?></strong> <?php echo $lang['Say']; ?>
                                            <?php } else { ?>
                                                <strong><?php echo $row['lastmessage']['user']['realname']; ?></strong> <?php echo $lang['To'], $lang['Me'], $lang['Say']; ?>
                                            <?php } ?>
                                            <!-- 单行，过长时截断字符 -->
                                            <a href="<?php echo $this->createUrl('pm/detail', array('id' => $row['listid'], 'type' => $row['type'])) ?>"
                                               class="xcm"><?php echo StringUtil::parseHtml(StringUtil::cutStr($row['lastmessage']['content'], 100)); ?></a>
                                        </p>
                                        <div>
                                            <div class="pull-left">
                                                <label class="checkbox checkbox-inline mbz">
                                                    <input type="checkbox" value="<?php echo $row['listid']; ?>"
                                                           name="pm">
                                                </label>
                                                <span
                                                    class="tcm fss"><?php echo Convert::formatDate($row['listctime'], 'u'); ?></span>
                                                <span class="tcm fss ilsep">|</span>
                                                <span
                                                    class="tcm fss"><?php echo $lang['Total']; ?><?php echo $row['messagenum']; ?><?php echo $lang['Item']; ?></span>
                                            </div>
                                            <div class="pull-right">
                                                <a href="javascript:;" title="<?php echo $lang['Delete']; ?>"
                                                   class="cbtn o-trash" data-action="removePm"
                                                   data-param='{"id": "<?php echo $row['listid']; ?>"}'></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-data-tip"></div>
                <?php endif; ?>
            </div>
            <div id="pm_message" style="width: 500px;display: none;">
                <form id="pm_form">
                    <table class="table table-condensed">
                        <tr>
                            <td class="xar" width="50"><?php echo $lang['Receiver']; ?></td>
                            <td>
                                <input type="text" name="touid" data-toggle="userSelect" id="to_uid">
                            </td>
                        </tr>
                        <tr>
                            <td class="xar" style="vertical-align: top;"><?php echo $lang['Content']; ?></td>
                            <td>
                                <textarea name="content" id="to_message" rows="5"></textarea>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                </form>
            </div>
            <div class="page-list-footer">
                <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<script>
    Ibos.app.setPageParam({
        'dataUid': '<?php echo StringUtil::wrapId(Ibos::app()->user->uid) ?>'
    })

</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message_pm_index.js?<?php echo VERHASH; ?>'></script>
