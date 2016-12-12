<?php

use application\core\utils\Ibos;

?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo Ibos::lang('Information center'); ?></h1>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('dashboard/edit', array('op' => 'update')); ?>"
              class="form-horizontal" method="post">
            <!-- 邮件发送设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo Ibos::lang('Officialdoc template edit'); ?></h2>
                <div class="span8">
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo Ibos::lang('Template name'); ?></label>
                        <div class="controls">
                            <input type="text" name="name" value="<?php echo $data['name']; ?>">
                        </div>
                    </div>
                </div>
                <div class="span8">
                    <div class="control-group">
                        <label for="" class="control-label">内容</label>
                        <div class="controls">
                            <script id="editor" name="content"
                                    type="text/plain"><?php echo $data['escape_content']; ?></script>
                            <input type="hidden" name="content_text" id="content_text"/>
                        </div>
                    </div>
                </div>
                <div class="span8">
                    <div class="control-group">
                        <label for="" class="control-label"></label>
                        <div class="controls">
                            <button class="btn btn-primary btn-large btn-submit"
                                    type="button"><?php echo Ibos::lang('Submit'); ?></button>
                        </div>
                    </div>
                    <input type="hidden" name="rcid" value="<?php echo $data['rcid']; ?>">
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>"></script>
<script>
    (function () {
        UEDITOR_CONFIG.mode.simple[0].push('pagebreak', 'source');
        new UE.ui.Editor({
            initialFrameWidth: 738,
            minFrameWidth: 738,
            toolbars: UEDITOR_CONFIG.mode.simple
        }).render('editor');
        var ue = UE.getEditor('editor');
        $('.btn-submit').on('click', function () {
            var $form = $('form');
            $form[0].content.value = ue.getContent();
            $('#content_text').val(ue.getContentTxt());
            $form.submit();
        });


    })();
</script>
</body>
</html>
