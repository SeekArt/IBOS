<?php

use application\core\utils\Ibos;

?>
<li <?php if (Ibos::app()->controller->id == 'stats'): ?> class="active" <?php endif; ?>>
    <a href="<?php echo Ibos::app()->createUrl('recruit/stats/index'); ?>">
        <i class="os-statistics"></i>
        <?php echo Ibos::lang('Recruit statistics'); ?>
    </a>
</li>