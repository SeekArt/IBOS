<div class="sign-info-title fill-nn">
    <span>共<span class="fsl"><?php echo $unsignedCount; ?></span> 人未签收，</span>
    <a href="javascript:;" class="at-once-remind" id="at_once_remind">马上提醒</a>
</div>
<div id="isnosign_content">
    <div id="art_no_sing_table" class="art-no-sing-table">
        <?php foreach ($unsignUsers as $deptid => $dept): ?>
            <h5 class="doc-reader-dep"><?php echo $dept['deptname'] ?></h5>
            <ul class="doc-reader-list clearfix">
                <?php foreach ($dept['users'] as $uid => $user): ?>
                    <li>
                        <a href="<?php echo $user['space_url']; ?>" class="avatar-circle avatar-circle-small">
                            <img src="<?php echo $user['avatar_small']; ?>" data-id="<?php echo $user['uid']; ?>"/>
                        </a>
                        <?php echo $user['realname']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
        <input type="hidden" id="unsigned_uids" value="<?php echo $unsignUids; ?>">
    </div>
</div>
