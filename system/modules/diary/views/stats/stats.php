<?php

use application\core\utils\Ibos;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $statAssetUrl; ?>/css/statistics.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <div class="mcr">
        <?php if ($type == 'personal'): ?>
            <div class="mc-header">
                <div class="mc-header-info clearfix">
                    <div class="usi-terse">
                        <a href="<?php echo Ibos::app()->user->space_url; ?>" class="avatar-box">
                            <span class="avatar-circle"><img class="mbm"
                                                             src="<?php echo Ibos::app()->user->avatar_middle; ?>"
                                                             alt="<?php echo Ibos::app()->user->realname; ?>"></span>
                        </a>
                        <span class="usi-terse-user"><?php echo Ibos::app()->user->realname; ?></span>
                        <span class="usi-terse-group"><?php echo Ibos::app()->user->deptname; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="page-list">
            <div class="page-list-header">
                <?php echo $this->widget($widgets['header'], array('type' => $type), true); ?>
            </div>
            <div class="page-list-mainer">
                <div>
                    <?php echo $this->widget($widgets['summary'], array('type' => $type), true); ?>
                    <?php echo $this->widget($widgets['count'], array('type' => $type), true); ?>
                    <?php if ($type == 'review'): ?>
                        <?php echo $this->widget($widgets['footer'], array(), true); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary_review_index.js?<?php echo VERHASH; ?>'></script>