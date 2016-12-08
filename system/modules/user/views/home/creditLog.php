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
        <li class="active">
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'log', 'uid' => $this->getUid())); ?>"><?php echo $lang['Record']; ?></a>
        </li>
        <li>
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'level', 'uid' => $this->getUid())); ?>"><?php echo $lang['Appellation']; ?></a>
        </li>
        <li>
            <a href="<?php echo $this->createUrl('home/credit', array('op' => 'rule', 'uid' => $this->getUid())); ?>"><?php echo $lang['Rule']; ?></a>
        </li>
    </ul>
</div>
<div>
    <div class="pc-container clearfix dib" style="width:656px;">
        <div>
            <div class="page-list clearfix">
                <div class="page-list-header clearfix">
                    <div class="btn-group" id="points_record_tab">
                        <a href="javascript:;" data-target="#reward"
                           class="btn active"><?php echo $lang['System reward']; ?></a>
                        <a href="javascript:;" data-target="#income"
                           class="btn"><?php echo $lang['Integral benefits']; ?></a>
                    </div>
                </div>
                <div class="page-list-mainer special-mainer">
                    <div id="reward">
                        <?php if (!empty($relateRules)): ?>
                            <table class="table rule-table">
                                <thead>
                                <tr>
                                    <th width="">奖励名称</th>
                                    <th>最后奖励时间</th>
                                    <th>周期次数</th>
                                    <th>总次数</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($relateRules as $rule): ?>
                                    <tr>
                                        <td>
                                            <p class="xcm mbm"><?php echo isset($creditRule[$rule['rid']]) ? $creditRule[$rule['rid']]['rulename'] : ""; ?></p>
                                            <p class="mbs reward-info">
                                                <?php foreach ($credits as $index => $credit): ?>
                                                    <?php if (!empty($credit)): ?>
                                                        <?php
                                                        if ($rule['extcredits' . $index] == 0) {
                                                            continue;
                                                        }
                                                        ?>
                                                        <span class="mls"><?php echo $credit['name']; ?><em class="xco">+<?php echo $rule['extcredits' . $index] ?></em></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="fss"><?php echo Convert::formatDate($rule['dateline'], 'Y-m-d H:i'); ?></p>
                                        </td>
                                        <td><?php echo $rule['cyclenum']; ?></td>
                                        <td><?php echo $rule['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data-tip"></div>
                        <?php endif; ?>
                    </div>
                    <div id="income" style="display:none;">
                        <?php if (!empty($creditLog)): ?>
                            <table class="table rule-table">
                                <thead>
                                <tr>
                                    <th><?php echo $lang['Credit desc']; ?></th>
                                    <th width="80"><?php echo $lang['Credit desc']; ?></th>
                                    <th width="120"><?php echo $lang['Credit change']; ?></th>
                                    <th width="80"><?php echo $lang['Credit total']; ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($creditLog as $log): ?>
                                    <tr>
                                        <td><?php echo $log['operation']; ?></td>
                                        <td>+1</td>
                                        <td><?php echo Convert::formatDate($log['dateline'], 'Y-m-d H:i'); ?></td>
                                        <td><?php echo $log['curcredits']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($pages)): ?>
                                    <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
                                        </td>
                                    </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        <?php else: ?>
                            <div class="no-data-tip"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 右栏 积分情况 -->
    <?php echo $this->getCreditSidebar($lang); ?>
</div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script>
    // Tab
    new P.Tab($("#points_record_tab"), "a", function ($ctrl) {
        $ctrl.addClass("active").siblings().removeClass("active");
    });

    //积分公式的气泡提示
    $('#integral_tip').tooltip();
</script>
