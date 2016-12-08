<?php
use application\core\utils\Ibos;

?>
<ul class="mng-trd-list">
    <?php $num = 0; ?>
    <?php foreach ($users as $user): ?>
        <?php if ($num < $item): ?>
            <li class="mng-item">
                <a href="<?php echo Ibos::app()->urlManager->createUrl("assignment/unfinished/subList", array('uid' => $user['uid'])); ?>">
                    <img src="<?php echo $user['avatar_middle']; ?>" alt="">
                    <?php echo $user['realname']; ?>
                </a>
            </li>
            <?php $num++; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (count($users) > $item): ?>
        <li class="mng-item view-all" data-uid="<?php echo $uid; ?>">
            <a href="javascript:;">
                <i class="o-am-allsub"></i>
                <?php echo Ibos::lang('View all subordinate'); ?>
            </a>
        </li>
    <?php endif; ?>
</ul>