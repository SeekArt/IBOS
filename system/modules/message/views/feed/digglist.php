<?php

use application\core\utils\Ibos;

?>
<?php foreach ($result as $key => $res): ?>
    <li title="在<?php echo $res['diggtime']; ?>赞过" data-uid="<?php echo $res['user']['uid']; ?>" class="posr">
        <?php if ($res['user']['uid'] == Ibos::app()->user->uid): ?><i class="o-wbi-close" data-action="removeFeedDigg"
                                                                       data-param='{"feedid":<?php echo $feedid; ?>}'></i><?php endif; ?>
        <a href="<?php echo $res['user']['space_url']; ?>" target="_blank" data-toggle="usercard"
           data-param="uid=<?php echo $res['user']['uid']; ?>">
            <img src="<?php echo $res['user']['avatar_small']; ?>" alt="<?php echo $res['user']['realname']; ?>"
                 width="30" height="30" class="circle">
        </a>
    </li>
<?php endforeach; ?>
<?php if ($count > 4): ?>
    <li>
        <a href="javascript:;" class="cbtn o-more" data-param='{"feedid":<?php echo $feedid; ?>}'
           data-action="openDiggUserDialog"></a>
    </li>
<?php endif; ?>
