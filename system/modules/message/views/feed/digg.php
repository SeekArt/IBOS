<li data-uid="<?php echo $user['uid']; ?>" class="posr">
    <i class="o-wbi-close" data-action="removeFeedDigg" data='{"uid": <?php echo $user['uid']; ?>}'></i>
    <a href="<?php echo $user['space_url']; ?>" target="_blank" data-toggle="usercard"
       data-param="uid=<?php echo $user['uid']; ?>">
        <img src="<?php echo $user['avatar_small']; ?>" alt="<?php echo $user['realname']; ?>" width="30" height="30"
             class="circle">
    </a>
</li>