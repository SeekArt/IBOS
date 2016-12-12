<?php
use application\core\utils\Ibos;
use application\modules\vote\components\Vote as VoteComponent;
use application\modules\vote\utils\VoteRoleUtil;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/vote.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <div class="aside" id="aside">
        <div class="sbbf sbbf">
            <?php if (VoteRoleUtil::canPublish()) : ?>
                <div class="fill-ss">
                    <a href="<?php echo Ibos::app()->createUrl('vote/form/show'); ?>" class="btn btn-warning btn-block">
                        <i class="o-new"></i> 发起调查
                    </a>
                </div>
            <?php endif; ?>
            <ul class="nav nav-strip nav-stacked">
                <li>
                    <a href="<?php echo
                    $this->createUrl('default/index'); ?>">
                        <i class="o-vote-vote"></i>
                        <span class="mls">调查投票</span>
                    </a>
                </li>
                <?php if (VoteRoleUtil::canPublish()): ?>
                    <li>
                        <a href="<?php echo Ibos::app()->createUrl('vote/default/index', array('type' => '4')); ?>">
                            <i class="o-vote-my"></i>
                            <span class="mls">我发起的</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (VoteRoleUtil::canManage()): ?>
                    <li>
                        <a href="<?php echo Ibos::app()->createUrl('vote/default/index', array('type' => '7')); ?>">
                            <i class="o-vote-manage"></i>
                            <span class="mls">管理投票</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <!-- Sidebar end -->
    <div class="mcr">
        <form action="" class="form-horizontal">
            <div class="ct ctview ctview-art" id="vote_content">
                <!-- 文章 -->
            </div>
        </form>
    </div>
</div>
<?php echo VoteComponent::getView('view'); ?>