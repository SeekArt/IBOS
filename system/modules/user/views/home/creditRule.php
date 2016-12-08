<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;

?>
<!-- Mainer -->
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
        <li>
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'level', 'uid' => $this->getUid())); ?>"><?php echo $lang['Appellation']; ?></a>
        </li>
        <li class="active"><a
                href="<?php echo $this->createUrl('home/credit', array('op' => 'rule', 'uid' => $this->getUid())); ?>"><?php echo $lang['Rule']; ?></a>
        </li>
    </ul>
</div>
<div>
    <div class="pc-container clearfix dib" style="width:656px;">
        <div>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th width="100">积分操作</th>
                    <th>奖励次数</th>
                    <?php foreach ($credits as $index => $credit): ?>
                        <?php if (!empty($credit)): ?>
                            <th width="60"><?php echo $credit['name']; ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($creditRule as $rule): ?>
                    <tr>
                        <td class="xcm"><?php echo $rule['rulename']; ?></td>
                        <td><?php echo Ibos::lang('Cycle type ' . $rule['cycletype'], '', array('{num}' => Convert::ToChinaseNum($rule['rewardnum']))); ?></td>
                        <?php foreach ($credits as $index => $credit): ?>
                            <?php if (!empty($credit)): ?>
                                <td class="xac bgly xwb" width="60"><?php echo $rule['extcredits' . $index]; ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="7">
                        <div
                            class="pull-right"><?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?></div>
                    </td>
                </tr>
                </tfoot>
            </table>
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