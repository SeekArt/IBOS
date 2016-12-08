<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Vote']; ?></h1>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('dashboard/edit'); ?>" class="form-horizontal" method="post">
            <!-- 邮件发送设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Vote setting']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label for=""
                               class="control-label"><?php echo $lang['Whether to open the thumbnails']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="votethumbenable" value='1' id="thumb_operate"
                                   data-toggle="switch" class="visi-hidden"
                                   <?php if ($votethumbenable): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="control-group thumb-operate"
                         style="<?php if ($votethumbenable): ?>display:block<?php endif; ?><?php if (!$votethumbenable): ?>display:none<?php endif; ?>">
                        <label for="" class="control-label"><?php echo $lang['Thumbnail size']; ?></label>
                        <div class="controls">
                            <input type="text" class="span3" name="votethumbwidth"
                                   value='<?php echo $votethumbwidth; ?>'> &nbsp; X &nbsp; <input type="text"
                                                                                                  class="span3"
                                                                                                  name="votethumbheight"
                                                                                                  value='<?php echo $votethumbheight; ?>'>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label"></label>
                        <div class="controls">
                            <button class="btn btn-primary btn-large btn-submit"
                                    type="submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    var userData = Ibos.data.get("user");
    $("#articleapprover").userSelect({
        box: $("#articleapprover_box"),
        type: 'user',
        data: userData
    });
    //缩略图设置
    $('#thumb_operate').on('click', function () {
        var checked = $(this).prop('checked');
        if (checked === true) {
            $('.thumb-operate').css('display', 'block');
        } else {
            $('.thumb-operate').css('display', 'none');
        }
    });
</script>