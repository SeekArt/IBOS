<?php

use application\core\utils\Module as ModuleUtil;
?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Information center']; ?></h1>
    </div>
    <div>
        <form action="<?php echo $this->createUrl( 'dashboard/edit' ); ?>" class="form-horizontal" method="post">
            <!-- 邮件发送设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Article setting']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Comment']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="articlecommentenable" value='1' id="" data-toggle="switch" <?php if ( $data['articlecommentenable'] ): ?>checked<?php endif; ?>>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label "><?php echo $lang['Vote']; ?></label>
                        <div class="controls">
                            <?php if ( !ModuleUtil::getIsEnabled( 'vote' ) ): ?>
                                <label class="toggle toggle-off toggle-disabled" title="未安装投票模块" >
                                </label>
                            <?php else: ?>
                                <input type="checkbox" name="articlevoteenable" value='1' id="" data-toggle="switch" <?php if ( $data['articlevoteenable'] ): ?>checked<?php endif; ?>>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- <div class="control-group">
                        <label for="" class="control-label"><?php //echo $lang['Message reminding']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="articlemessageenable" value='1' id="" data-toggle="switch" <?php //if ( $data['articlemessageenable'] ): ?>checked<?php //endif; ?>>
                        </div>
                    </div> -->
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo $lang['Whether to open the thumbnails']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="articlethumbenable" value='1' id="thumb_operate" data-toggle="switch" <?php if ( $data['articlethumbenable'] ): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="control-group thumb-operate" style="<?php if ( $data['articlethumbenable'] ): ?>display:block<?php endif; ?><?php if ( !$data['articlethumbenable'] ): ?>display:none<?php endif; ?>">
                        <label for="" class="control-label"><?php echo $lang['Thumbnail size']; ?></label>
                        <div class="controls">
                            <input type="text" class="span3" name="articlethumbwidth" value='<?php echo $data['articlethumbwidth']; ?>'> &nbsp; X &nbsp; <input type="text" class="span3" name="articlethumbheight" value='<?php echo $data['articlethumbheight']; ?>'>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label"></label>
                        <div class="controls">
                            <button class="btn btn-primary btn-large btn-submit" type="submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    //缩略图设置
    $('#thumb_operate').on('click', function() {
        var checked = $(this).prop('checked');
        if (checked === true) {
            $('.thumb-operate').css('display', 'block');
        } else {
            $('.thumb-operate').css('display', 'none');
        }
    });
</script>