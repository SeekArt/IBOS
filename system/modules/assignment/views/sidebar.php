<?php
use application\core\utils\Ibos;

?>
<!-- 任务指派 -->
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($this->getId() == 'unfinished'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('unfinished/index'); ?>">
                    <?php if ($unfinishCount > 0): ?>
                        <span class="badge pull-right"><?php echo $unfinishCount; ?></span>
                    <?php endif; ?>
                    <i class="o-am-unfinished"></i>
                    <?php echo Ibos::lang('Unfinished assignment') ?>
                </a>
            </li>
            <li <?php if ($this->getId() == 'finished'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('finished/index'); ?>">
                    <i class="o-am-finished"></i>
                    <?php echo Ibos::lang('Finished assignment') ?>
                </a>
            </li>
            <?php if ($hasSubUid): ?>
                <li>
                    <a href="<?php echo $this->createUrl('unfinished/subList'); ?>">
                        <i class="o-am-under"></i>
                        <?php echo Ibos::lang('Under assignment') ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>