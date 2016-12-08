<?php

use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li>
                <a href="<?php echo $this->createUrl('default/index'); ?>">
                    <i class="o-rp-personal"></i>
                    <?php echo $lang['Personal']; ?>
                </a>
            </li>
            <?php if ($this->checkIsHasSub()): ?>
                <li>
                    <a href="<?php echo $this->createUrl('review/index'); ?>">
                        <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
                        <i class="o-rp-appraise"></i>
                        <?php echo $lang['Reveiw']; ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['report'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('report', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub()), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>