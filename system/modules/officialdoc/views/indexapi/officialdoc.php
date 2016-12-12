<?php

use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_officialdoc.css'; ?>">
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>
<?php if (!empty($docs)): ?>
    <table class="table table-underline">
        <tbody>
        <?php foreach ($docs as $doc): ?>
            <tr>
                <td>
                    <div class="mbs">
                        <?php if ($doc['isSign']): ?>
                            <span class="badge pull-right"><?php echo $lang['Already sign']; ?></span>
                        <?php else: ?>
                            <span class="badge pull-right badge-danger"><?php echo $lang['No sign']; ?></span>
                        <?php endif; ?>
                        <a href="<?php echo Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $doc['docid'])); ?>"
                           class="title xcm"><?php echo $doc['subject']; ?></a>
                    </div>
                    <div class="fss tcm">
                        <?php if ($doc['isSign']): ?><span
                            class="pull-right"><?php echo $lang['Sign in']; ?><?php echo date('n' . $lang['Month'] . 'j' . $lang['Day'] . 'H:i', $doc['sign']['signtime']); ?></span><?php endif; ?>
                        <span><?php echo $doc['author']; ?><?php echo $lang['Posted on']; ?><?php echo date('Y' . $lang['Year'] . 'n' . $lang['Month'] . 'j' . $lang['Day'] . 'H:i', $doc['addtime']); ?></span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="mbox-base">
        <div class="fill-hn xac">
            <a href="<?php echo Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/index'); ?>"
               class="link-more">
                <i class="cbtn o-more"></i>
                <span class="ilsep"><?php echo $lang['See more docs']; ?></span>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="in-officialdoc-empty"></div>
<?php endif; ?>
