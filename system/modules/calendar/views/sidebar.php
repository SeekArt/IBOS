<?php
use application\core\utils\Ibos;

?>
<!-- Sidebar -->
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li class="active">
                <a href="<?php echo $this->createUrl('schedule/index'); ?>">
                    <i class="o-cal-personal"></i>
                    <?php echo $lang['Personal']; ?>
                </a>
                <div class="sbb sbbf">
                    <ul class="aside-list">
                        <li <?php if (Ibos::app()->getController()->getId() == 'schedule'): ?>class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl('schedule/index'); ?>"><i class="o-cal-calendar"></i>
                                <?php echo $lang['Schedule']; ?>
                            </a>
                        </li>
                        <li <?php if (Ibos::app()->getController()->getId() == 'task'): ?>class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl('task/index'); ?>"><i class="o-cal-todo"></i>
                                <?php echo $lang['Task']; ?>
                            </a>
                        </li>
                        <li <?php if (Ibos::app()->getController()->getId() == 'loop'): ?>class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl('loop/index'); ?>"><i class="o-cal-affairs"></i>
                                <?php echo $lang['Periodic affairs']; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php if ($hasSubUid): ?>
                <li>
                    <a href="<?php echo $this->createUrl('schedule/subschedule'); ?>">
                        <i class="o-cal-underling"></i>
                        <?php echo $lang['Subordinate']; ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($hasShareUid !== false): ?>
                <li>
                    <a href="<?php echo $this->createUrl('schedule/shareschedule'); ?>">
                        <i class="o-cal-shareme"></i>
                        <?php echo $lang['Share'] ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
