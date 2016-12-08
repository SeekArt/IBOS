<?php

use application\core\utils\Ibos;

?>
<li <?php if ($inPersonal): ?>class="active"<?php endif; ?>>
    <a href="<?php echo Ibos::app()->createUrl('diary/stats/personal'); ?>">
        <i class="os-personal-statistic"></i>
        <?php echo Ibos::lang('Personal statistics'); ?>
    </a>
</li>
<?php if ($hasSub): ?>
    <li <?php if ($inReview): ?>class="active"<?php endif; ?>>
        <a href="<?php echo Ibos::app()->createUrl('diary/stats/review'); ?>">
            <i class="os-statistics"></i>
            <?php echo Ibos::lang('Review statistics'); ?>
        </a>
        <?php if ($inReview): ?>
            <?php echo $this->getController()->widget('application\modules\diary\widgets\DiarySublist', array('stats' => true, 'fromController' => $fromController), true); ?>
        <?php endif; ?>
    </li>
<?php endif; ?>


