<ul class="list">
    <?php foreach ($datas as $data): ?>
        <li>
            <a href="<?php echo $this->createUrl('default/show', array('articleid' => $data['articleid'])); ?>"
               class="art-list-title"><?php echo $data['subject']; ?></a>
        </li>
    <?php endforeach; ?>
</ul>