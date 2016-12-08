<?php
use application\core\utils\Ibos;
use application\modules\vote\components\Vote;
use application\modules\vote\utils\VoteRoleUtil;
use application\core\utils\StringUtil;

?>
<!-- load css -->
<link rel="stylesheet"
      href="<?php echo Ibos::app()->assetManager->getAssetsUrl('vote'); ?>/css/vote.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <div class="aside" id="aside">
        <div class="sbbf sbbf">
            <div class="fill-ss">
                <a href="<?php echo Ibos::app()->createUrl('vote/form/show'); ?>" class="btn btn-warning btn-block">
                    <i class="o-new"></i> 发起调查
                </a>
            </div>
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
        <form id="vote_form" name="voteForm" action="javascript:;" class="form-horizontal"
              enctype="multipart/form-data">
            <div class="ct fill-zn">
                <!-- Row 1 -->
                <div class="row pt">
                    <div class="span12">
                        <div class="control-group">
                            <label for="subject">标题</label>
                            <input id="subject" type="text" name="vote[subject]"
                                   value="<?php echo @$vote['subject'] ?>">
                        </div>
                    </div>
                </div>
                <!-- Row 2 -->
                <div class="row">
                    <div class="span12">
                        <div class="control-group">
                            <label for="publishScope">发布范围</label>
                            <div id="publishScope_row">
                                <input type="text" name="vote[publishscope]"
                                       value="<?php echo @$vote['publishScope']; ?>" id="publishScope">
                                <div id="publishScope_box"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row 3 -->
                <div class="row">
                    <div class="span12">
                        <label for="content">描述</label>
                        <textarea name="vote[content]" rows="5"
                                  id="content"><?php echo @$vote['content'] ?></textarea>
                    </div>
                </div>
            </div>
            <div class="fill-nn">
                <?php echo Vote::getView('topicsform'); ?>
            </div>
            <div id="submit_bar" class="clearfix fill-nn">
                <button type="button" class="btn btn-large btn-submit pull-left" onclick="history.back();">返回</button>
                <div class="pull-right">
                    <button type="button" id="prewiew_submit" class="btn btn-large btn-submit btn-preview">预览</button>
                    <button type="submit" class="btn btn-large btn-submit btn-primary">发布</button>
                </div>
            </div>
            <?php echo StringUtil::returnFormTokenInput(); ?>
        </form>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/vote_default_articleadd.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/vote_form_add.js?<?php echo VERHASH; ?>"></script>
