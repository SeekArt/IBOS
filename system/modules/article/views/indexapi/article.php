<?php

use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_article.css' ?>">
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>
<?php if (!empty($articles)): ?>
    <table class="table table-underline">
        <tbody>
        <?php foreach ($articles as $index => $article): ?>
            <tr<?php if ($index == 0): ?> class="active"<?php endif; ?>>
                <td class="vat" width="40">
                    <i class="vat <?php echo $article['iconStyle']; ?>"></i>
                </td>
                <td>
                    <div>
                        <span class="tcm pull-right"><?php echo date('n月j日', $article['addtime']); ?></span>
                        <a href="<?php echo Ibos::app()->urlManager->createUrl('article/default/show', array('articleid' => $article['articleid'])); ?>"
                           class="title xcm"><?php echo $article['subject']; ?></a>
                        <?php if ($index == 0): ?>
                            <div class="content mbs"><a
                                    href="<?php echo Ibos::app()->urlManager->createUrl('article/default/show', array('articleid' => $article['articleid'])); ?>"><?php echo $article['content']; ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="mbox-base">
        <div class="fill-hn xac">
            <a href="<?php echo Ibos::app()->urlManager->createUrl('article/default/index'); ?>" class="link-more">
                <i class="cbtn o-more"></i>
                <span class="ilsep"><?php echo $lang['See more news']; ?></span>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="in-art-empty"></div>
<?php endif; ?>
