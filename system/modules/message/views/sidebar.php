<!-- Sidebar -->
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($this->getId() == 'mention'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('mention/index'); ?>">
                    <i class="o-msg-at"></i>
                    <?php echo $lang['Mention me']; ?>
                    <?php if (!empty($unreadMap['mention'])): ?><span
                        class="badge pull-right"><?php echo $unreadMap['mention']; ?></span><?php endif; ?>
                </a>
            </li>
            <li <?php if ($this->getId() == 'comment'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('comment/index'); ?>">
                    <i class="o-msg-comment"></i>
                    <?php echo $lang['Comment']; ?>
                    <?php if (!empty($unreadMap['comment'])): ?><span
                        class="badge pull-right"><?php echo $unreadMap['comment']; ?></span><?php endif; ?>
                </a>
            </li>
            <li <?php if ($this->getAction()->getId() == 'digg' && $this->getId() == 'notify'): ?>class="active"<?php endif; ?>
                style="display: none">
                <a href="<?php echo $this->createUrl('notify/digg'); ?>">
                    <i class="o-msg-praise"></i>
                    <?php echo $lang['My digg']; ?>
                </a>
            </li>
            <li <?php if ($this->getId() == 'pm'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('pm/index'); ?>">
                    <i class="o-msg-private"></i>
                    <?php echo $lang['PM']; ?>
                    <?php if (!empty($unreadMap['pm'])): ?><span
                        class="badge pull-right"><?php echo $unreadMap['pm']; ?></span><?php endif; ?>
                </a>
            </li>
            <li <?php if (($this->getAction()->getId() == 'index' || $this->getAction()->getId() == 'detail') && $this->getId() == 'notify'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('notify/index'); ?>">
                    <i class="o-msg-remind"></i>
                    <?php echo $lang['Notify']; ?>
                    <?php if (!empty($unreadMap['notify'])): ?><span
                        class="badge pull-right"><?php echo $unreadMap['notify']; ?></span><?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
</div>