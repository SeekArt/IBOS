<div class="sign-info-title fill-nn">
    共<span class="fsl"><?php echo $signedCount; ?></span> 人已签收
</div>
<div id="issign_content">
    <div id="art_sing_table" class="art-sing-table">
        <?php foreach ($signUsers as $deptid => $dept): ?>
            <h5 class="doc-reader-dep"><?php echo $dept['deptname'] ?></h5>
            <ul class="doc-reader-list clearfix">
                <?php foreach ($dept['users'] as $uid => $user): ?>
                    <li>
                        <div class="media">
                            <div class="pull-left">
                                <a href="<?php echo $user['space_url']; ?>" class="avatar-circle avatar-circle-small">
                                    <img src="<?php echo $user['avatar_small']; ?>">
                                </a>
                            </div>
                            <div class="media-body">
                                <p class="fss"><?php echo $user['realname']; ?></p>
                                <p class="fss tcm"><?php echo date('Y年m月d日 H:i', $user['signInfo']['signtime']); ?>
                                    <?php if ($user['signInfo']['frommobile']): ?>
                                        <i class="o-art-pc-phone" data-toggle="tooltip" data-placement="top"
                                           data-original-title="签于手机端"></i>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</div>
