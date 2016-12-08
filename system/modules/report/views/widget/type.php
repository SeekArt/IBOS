<?php

use application\core\utils\Env;

?>
<div class="rp-cycle">
    <div class="rp-cycle-header">
        <strong><?php echo $lang['Report type']; ?></strong>
    </div>
    <ul class="aside-list" id="rp_type_aside_list">
        <?php foreach ($reportTypes as $reportType): ?>
            <?php $typeid = Env::getRequest('typeid'); ?>
            <li <?php if ($reportType['typeid'] == $typeid): ?>class="active"<?php endif; ?>
                data-id="<?php echo $reportType['typeid'] ?>">
                <a href="<?php echo $this->getController()->createUrl('stats/' . $type, array('typeid' => $reportType['typeid'], 'uid' => $uid)); ?>">
                    <i>&gt;</i> <?php echo $reportType['typename']; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>