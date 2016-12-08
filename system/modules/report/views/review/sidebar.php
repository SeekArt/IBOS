<?php

use application\core\utils\Env;
use application\core\utils\Module;
use application\core\utils\Org;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($this->id == 'default'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('default/index') ?>">
                    <i class="o-rp-personal"></i>
                    <?php echo $lang['Personal']; ?>
                </a>
            </li>
            <li <?php if ($this->id == 'review'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('review/index') ?>">
                    <i class="o-rp-appraise"></i>
                    <?php echo $lang['Reveiw']; ?>
                </a>
                <!-- 汇报类型 -->
                <?php
                $typeid = Env::getRequest('typeid');
                $getUid = Env::getRequest('uid');
                $getUser = Env::getRequest('user');
                ?>
                <div class="rp-cycle">
                    <div class="rp-cycle-header">
                        <strong><?php echo $lang['Report type']; ?></strong>
                    </div>
                    <ul class="aside-list" id="rp_type_aside_list">
                        <?php foreach ($reportTypes as $reportType): ?>
                            <?php $typeid = Env::getRequest('typeid'); ?>
                            <li <?php if ($reportType['typeid'] == $typeid): ?>class="active"<?php endif; ?>
                                data-id="<?php echo $reportType['typeid'] ?>">
                                <a href="<?php echo $this->createUrl('review/index', array('typeid' => $reportType['typeid'], 'uid' => $getUid, 'user' => $getUser)); ?>">
                                    <i>&gt;</i> <?php echo $reportType['typename']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!--下属-->
                <div>
                    <ul class="mng-list" id="mng_list">
                        <?php if (!empty($deptArr)): ?>
                            <?php foreach ($deptArr as $dept): ?>
                                <li>
                                    <div class="mng-item mng-department active">
                                        <span class="o-caret dept"><i class="caret"></i></span>
                                        <a href="<?php echo $this->createUrl('review/index', array('subUids' => $dept['subUids'], 'typeid' => $typeid)); ?>">
                                            <i class="o-org"></i>
                                            <?php echo $dept['deptname']; ?>
                                        </a>
                                    </div>
                                    <ul class="mng-scd-list">
                                        <?php foreach ($dept['user'] as $user): ?>
                                            <li>
                                                <div class="mng-item">
                                                    <span
                                                        class="o-caret g-sub" <?php if ($user['hasSub']): ?> data-action="toggleSubUnderlingsList" <?php endif; ?>
                                                        data-uid="<?php echo $user['uid']; ?>"><?php if ($user['hasSub']): ?>
                                                            <i class="caret"></i><?php endif; ?></span>
                                                    <a href="<?php echo $this->createUrl('review/index', array('op' => 'personal', 'uid' => $user['uid'], 'typeid' => $typeid)); ?>"
                                                       <?php if (Env::getRequest('uid') == $user['uid']): ?>style="color:#3497DB;"<?php endif; ?>>
                                                        <img
                                                            src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                                            alt="">
                                                        <?php echo $user['realname']; ?>
                                                    </a>
                                                </div>
                                                <!--下属资料,ajax调用生成-->
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['report'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('report', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub()), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
