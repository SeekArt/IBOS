<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li>
                <a href="<?php echo $this->createUrl('default/index') ?>">
                    <i class="o-da-personal"></i>
                    <?php echo Ibos::lang('Personal'); ?>
                </a>
            </li>
            <?php if ($this->checkIsHasSub()): ?>
                <li>
                    <a href="<?php echo $this->createUrl('review/index') ?>">
                        <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
                        <i class="o-da-appraise"></i>
                        <?php echo Ibos::lang('Review it'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo $this->createUrl('share/index') ?>">
                    <i class="o-da-concerned"></i>
                    <?php echo Ibos::lang('Share diary'); ?>
                </a>
            </li>
            <?php if ($this->issetAttention()): ?>
                <li>
                    <a href="<?php echo $this->createUrl('attention/index') ?>">
                        <i class="o-da-shared"></i>
                        <?php echo Ibos::lang('Attention diary'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['diary'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('diary', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub(), 'fromController' => 'stats'), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
