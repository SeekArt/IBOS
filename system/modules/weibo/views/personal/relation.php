<?php

use application\core\utils\StringUtil;
use application\core\utils\Org;
use application\modules\user\utils\User;

?>
<?php if (!empty($list)): ?>
    <ul class="list-inline wb-ava-list" data-node-type="relationList">
        <?php foreach ($list as $user): ?>
            <li>
                <a data-toggle="usercard" data-param="uid=<?php echo $user['uid']; ?>"
                   href="<?php echo User::getSpaceUrl($user['uid']); ?>" title="<?php echo $user['realname']; ?>">
                    <span class="avatar-circle">
                        <img src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                             alt="<?php echo $user['realname']; ?>"/>
                    </span>
                    <p><?php echo StringUtil::cutStr($user['realname'], 10) ?></p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>