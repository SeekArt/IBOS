<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($id == 'default'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->getController()->createUrl('default/index') ?>">
                    <i class="o-da-personal"></i>
                    <?php echo Ibos::lang('Personal'); ?>
                </a>
            </li>
            <li <?php if ($id == 'review'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->getController()->createUrl('review/index') ?>">
                    <i class="o-da-appraise"></i>
                    <?php echo Ibos::lang('Review it'); ?>
                </a>
                <?php echo $this->getController()->widget('application\modules\diary\widgets\DiarySublist', array('stats' => false), true); ?>
            </li>
            <?php if ($config['sharepersonnel']): ?>
                <li <?php if ($id == 'share'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->getController()->createUrl('share/index') ?>">
                        <i class="o-da-concerned"></i>
                        <?php echo Ibos::lang('Share diary'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($config['attention']): ?>
                <li <?php if ($id == 'attention'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->getController()->createUrl('attention/index') ?>">
                        <i class="o-da-shared"></i>
                        <?php echo Ibos::lang('Attention diary'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['diary'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('diary', StatConst::SIDEBAR_WIDGET), array('hasSub' => $hasSub, 'fromController' => 'review'), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>