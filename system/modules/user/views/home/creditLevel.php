<?php

use application\core\utils\Ibos;

?>
<div class="mc mcf clearfix">
    <?php echo $this->getHeader($lang); ?>
    <div>
        <div>
            <ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
                <li>
                    <a href="<?php echo $this->createUrl('home/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Home page']; ?></a>
                </li>
                <?php if ($this->getIsWeiboEnabled()): ?>
                    <li><a
                        href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Weibo']; ?></a>
                    </li><?php endif; ?>
                <li class="active"><a
                        href="<?php echo $this->createUrl('home/credit', array('uid' => $this->getUid())); ?>"><?php echo $lang['Credit']; ?></a>
                </li>
                <li>
                    <a href="<?php echo $this->createUrl('home/personal', array('uid' => $this->getUid())); ?>"><?php echo $lang['Profile']; ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="pc-header clearfix">
    <ul class="nav nav-skid">
        <li>
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'log', 'uid' => $this->getUid())); ?>"><?php echo $lang['Record']; ?></a>
        </li>
        <li class="active">
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'level', 'uid' => $this->getUid())); ?>"><?php echo $lang['Appellation']; ?></a>
        </li>
        <li>
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'rule', 'uid' => $this->getUid())); ?>"><?php echo $lang['Rule']; ?></a>
        </li>
    </ul>
</div>
<div>
    <div class="level-content clearfix dib">
        <div>
            <div class="clearfix">
                <?php if (!empty($level)): ?>
                    <ul class="appellation-list">
                        <?php foreach ($level as $index => $group): ?>
                            <li <?php if ($group['gid'] == $user['groupid']): ?>class="active"<?php endif; ?>>
                                <div>
                                    <div class="fill-hn level-info">
                                        <i class="lv lv<?php echo $index; ?>"></i>
                                        <span class="dib mls"><?php echo $group['title']; ?></span>
                                    </div>
                                    <div class="fill-hn level-number"><?php echo $group['creditshigher']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-data-tip"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- 右栏 积分情况 -->
    <?php echo $this->getCreditSidebar($lang); ?>
</div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script>
    //积分公式的气泡提示
    $('#integral_tip').tooltip();
</script>
