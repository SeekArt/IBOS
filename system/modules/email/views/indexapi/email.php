<!-- @Todo: 这里样式表重复加载了，需要解决 -->
<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>

<link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_email.css' ?>"/>
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>

<?php if (!empty($emails)): ?>
    <table class="table table-striped">
        <tbody>
        <?php foreach ($emails as $email): ?>
            <tr>
                <td width="50">
                    <?php if (!$email['isread']): ?><i class="o-mal-new"></i><?php endif; ?>
                    <?php if (!empty($email['attachmentid'])): ?><i class="o-mal-attach"></i><?php endif; ?>
                </td>
                <td>
                    <a title="<?php echo $email['subject']; ?>"
                       href="<?php echo Ibos::app()->urlManager->createUrl('email/content/show', array('id' => $email['emailid'])); ?>"
                       <?php if (!$email['isread']): ?>class="xwb"<?php endif; ?>><?php echo StringUtil::cutStr($email['subject'], 25); ?></a>
                </td>
                <td width="80">
                    <span
                        class="<?php if (!$email['isread']): ?>xwb<?php endif; ?> fss"><?php echo $email['realname']; ?></span>
                </td>
                <td width="20">
                    <?php if ($email['ismark']): ?><i class="o-mark"></i><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="mbox-base">
        <div class="fill-hn xac">
            <a href="<?php echo Ibos::app()->urlManager->createUrl('email/list/index'); ?>" class="link-more">
                <i class="cbtn o-more"></i>
                <span class="ilsep"><?php echo $lang['See more email']; ?></span>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="in-mal-<?php echo $tab; ?>-empty"></div>
<?php endif; ?>
