<?php

use application\core\utils\Env;
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
                        <?php if ($this->getUnreviews() != ''): ?>
                            <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
                        <?php endif; ?>
                        <i class="o-da-appraise"></i>
                        <?php echo Ibos::lang('Review it'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="active">
                <a href="<?php echo $this->createUrl('share/index') ?>">
                    <i class="o-da-concerned"></i>
                    <?php echo Ibos::lang('Share diary'); ?>
                </a>
                <div class="aside-sub">
                    <ul class="da-share-user-list">
                        <?php foreach ($data as $diary): ?>
                            <li>
                                <img src="<?php echo $diary['user']['avatar_middle']; ?>" alt="">
                                <a href="<?php echo $this->createUrl('share/index', array('op' => 'personal', 'uid' => $diary['uid'])); ?>"
                                   <?php if (Env::getRequest('uid') == $diary['uid']): ?>style="color:#3497DB;"<?php endif; ?>><?php echo $diary['user']['realname']; ?></a>
                                <span><?php echo $diary['diarytime'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
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
                <?php echo $this->widget(StatCommon::getWidgetName('diary', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub()), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
