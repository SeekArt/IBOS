<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<!-- load css -->
<div class="aside">
    <div class="sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if (Ibos::app()->controller->id == 'resume'): ?> class="active" <?php endif; ?>>
                <a href="<?php echo $this->createUrl('resume/index'); ?>">
                    <i class="o-rct-talents"></i>
                    <?php echo Ibos::lang('Talent management'); ?>
                </a>
            </li>
            <li <?php if (Ibos::app()->controller->id == 'contact'): ?> class="active" <?php endif; ?>>
                <a href="<?php echo $this->createUrl('contact/index'); ?>">
                    <i class="o-rct-interview"></i>
                    <?php echo Ibos::lang('Contact record'); ?>
                </a>
            </li>
            <li <?php if (Ibos::app()->controller->id == 'interview'): ?> class="active" <?php endif; ?>>
                <a href="<?php echo $this->createUrl('interview/index'); ?>">
                    <i class="o-rct-backdrop"></i>
                    <?php echo Ibos::lang('Interview management'); ?>
                </a>
            </li>
            <li <?php if (Ibos::app()->controller->id == 'bgchecks'): ?> class="active" <?php endif; ?>>
                <a href="<?php echo $this->createUrl('bgchecks/index'); ?>">
                    <i class="o-rct-contact"></i>
                    <?php echo Ibos::lang('Background investigation'); ?>
                </a>
            </li>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['recruit'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('recruit', StatConst::SIDEBAR_WIDGET), array(), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
