<div class="ct">
    <div id="cm_content">
        <?php if (!empty($setting['apilist'])): ?>
            开通的服务:
            <ul>
                <?php foreach ($setting['apilist'] as $row): ?>
                    <li><a href='http://www.ibos.com.cn/' target='blank'><?php echo $row['title']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <?php echo '暂无可用服务列表，何不去买？'; ?>
        <?php endif; ?>
        <a href='<?php echo $this->createUrl('service/edit'); ?>'>编辑 SECRET</a>
        <a href='<?php echo $this->createUrl('service/updateApi'); ?>'>更新API信息</a>
    </div>
</div>